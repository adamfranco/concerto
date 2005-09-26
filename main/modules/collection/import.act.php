<?php

//require_once(MYDIR."/domit/xml_domit_include.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/TabRepositoryImporter.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/ExifRepositoryImporter.class.php");

require_once(HARMONI."utilities/Dearchiver.class.php");
require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");
require_once(HARMONI."utilities/MIMETypes.class.php");

class importAction extends RepositoryAction {
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/08/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$this->getRepositoryId());
	}

	/**
 	 * Return the "unauthorized" string to print
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to import assets into this <em>Collection</em>.");
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getHeadingText () {
		$repository =& $this->getRepository();
		return _("Import Assets to the")
		." <em>".$repository->getDisplayName()."</em> "
		._(" Collection");
	}

	function buildContent () {
		$dr =& $this->getRepository();
		$harmoni =& Harmoni::instance();
		$centerPane =& $this->getActionRows();
		
		$repositoryId = $dr->getId();
		$harmoni->request->passthrough("collection_id");
		$cacheName = 'import_asset_wizard_'.$repositoryId->getIdString();
		$this->runWizard($cacheName, $centerPane);

	}
	
	/**
 	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
 	 * @access public
 	 * @since 7/18/05
	 */
	
	function &createWizard () {
		//$repository =& $this->getRepository();
		$wizard =& SimpleWizard::withText(
			"\n<h3>"._("Import type")."</h3>".
			"\n"._("The type of archive to be imported: ").
			"\n<br />[[importtype]]".
			"\n<h3>"._("Select file to upload")."</h3>".
			"\n"._("The archive to be uploaded: ").
			"\n<br />[[filename]]".
			"\n<br /><h3>"._("Stop on any Error: ")."</h3>".
			"\n"._("Do not import subsequent assets after the first asset with error.").
			"\n<br />[[dieonerror]]".
			"<table width='100%' border='0' style='margin-top:20px' >\n" .
			"<tr>\n" .
			"<td align='left' width='50%'>\n" .
			"[[_cancel]]".
			"</td>\n" .
			"<td align='right' width='50%'>\n" .
			"[[_save]]".
			"</td></tr></table>");
		
		// :: Name and Description ::
		//$step =& $wizard->addStep("fileupload", new WizardStep());
		//$step->setDisplayName(_("Archive Type and File Upload"));
		
		$select =& $wizard->addComponent("importtype", new WSelectList());
		$select->addOption("Tab-Delimited", "Tab-Delimited");
		$select->addOption("XML", "XML");
		$select->addOption("Exif", "Exif");
		$select->setValue("Tab-Delimited");
		
		$fileField =& $wizard->addComponent("filename", new WFileUploadField());
		
		$dieOnError =& $wizard->addComponent("dieonerror", new WCheckBox());
		
		$save =& $wizard->addComponent("_save", 
			WSaveButton::withLabel("Import"));
		$cancel =& $wizard->addComponent("_cancel", new WCancelButton());
		//$fileField->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		//$fileField->setErrorRule(new WECRegex("[\\w]+"));
		return $wizard;
	}
	
	/**
	 * moves the file returned by the wizard to a direcotory with a unique
	 * name
	 * 
	 * @param string tmpPath
	 * @param string $filename
	 * @return returns the path to the moved file
	 * @access public
	 * @since 7/27/05
	 */
	
	function moveArchive($tmpPath, $filename) {
		$newPath = $tmpPath."0";
		mkdir($newPath);
		
		rename($tmpPath, $newPath.DIRECTORY_SEPARATOR.$filename);
		return $newPath.DIRECTORY_SEPARATOR.$filename;
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 7/27/05
	 */
	
	
	function saveWizard($cacheName) {
		$harmoni =& Harmoni::instance();
		$dr =& $this->getRepository();
		$wizard =& $this->getWizard($cacheName);
		$properties =& $wizard->getAllValues();

		$centerPane =& $this->getActionRows();
		ob_start();
		
		$path = $properties['filename']['tmp_name'];
		$filename = $properties['filename']['name'];
		if ($properties['filename']['name'] == "") {
			print("Please choose a file to upload!");
			$centerPane->add(new Block(ob_get_contents(), 1));
			ob_end_clean();
			return FALSE;
		}	
		$newName = importAction::moveArchive($path, $filename);
		if($properties['dieonerror'] = "on")
			$dieonError = true;
		else $dieonError = false;
		if ($properties['importtype'] == "Tab-Delimited") 
			$importer =& new TabRepositoryImporter($newName, $dr->getId(), $dieonError);
		else if ($properties['importtype'] == "Exif") 
			$importer =& new ExifRepositoryImporter($newName, $dr->getId(), $dieonError);

		if (isset($importer)) {
			$importer->import();
			
			if ($importer->hasErrors()) {
				print("The bad news is that some errors occured during import, they are: <br />");
				$importer->printErrorMessages();
			}
			if ($importer->hasAssets()) {
				print("The good news is that some assets were created during import, they are: <br />");
				$importer->printGoodAssetIds();
			}
		}
		else if ($properties['importtype'] == "XML") { 
			$importer->decompress($newName);
			$importer =& new XMLImporter($newName);
			$importer->parse();
		}

		$centerPane->add(new Block(ob_get_contents(), 1));
		ob_end_clean();
		return TRUE;
	}
		
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getReturnUrl () {
		$repositoryId =& $this->getRepositoryId();
		$harmoni =& Harmoni::instance();
		return $harmoni->request->quickURL("collection", "browse", array("collection_id" => $repositoryId->getIdString()));
	}
	
	/**
	 * Run this Action's wizard and add it to the specified container. Cache
	 * this Action's wizard with the specified cacheName.
	 *
	 * This is the only method that an Action needs to call to run itself, see
	 * {@link editAction::buildContent()} for an example:
	 * <code>
	 * <?php
	 *	...
	 *	
	 *	&#109;**
	 *	 * Build the content for this action
	 *	 * 
	 *	 * @return boolean
	 *	 * @access public
	 *	 * @since 4/26/05
	 *	 *&#109;
	 *	function buildContent () {
	 *		$centerPane =& $this->getCenterPane();
	 *		$assetId =& $this->getAssetId();
	 *		$cacheName = 'edit_asset_wizard_'.$assetId->getIdString();
	 *		
	 *		$this->runWizard ( $cacheName, $centerPane );
	 *	}
	 *	
	 *	...
	 *	?>
	 *	</code>
	 * 
	 * @param string $cacheName The name to cache this Action's Wizard with.
	 * @param object Container $container The container to put the Wizard's layout in.
	 * @return void
	 * @access public
	 * @since 4/28/05
	 */
	function runWizard ( $cacheName, &$container) {
		$wizard =& $this->getWizard($cacheName);
		$harmoni =& Harmoni::instance();
		
		// tell the wizard to GO
		$wizard->go();
		
		$listener =& $wizard->getChild("_savecancel_");
		
		if ($listener->isSaveRequested()) {
			if ($this->saveWizard($cacheName))
				$this->closeWizard($cacheName);
		} 
		else if ($listener->isCancelRequested()) {
			$this->cancelWizard($cacheName);	
		}
		
		if (isset($_SESSION[$cacheName])) $container->add($wizard->getLayout($harmoni), null, null, CENTER, CENTER);
	}
	
}
?>