<?php
require_once("/home/cshubert/public_html/importer/domit/xml_domit_include.php");

//require_once(MYDIR."/domit/xml_domit_include.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/XMLRepositoryImporter.class.php");
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
			"<table width='100%' border='0' cellpadding='0' cellspacing='2'>\n" .
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
		
		$save =& $wizard->addComponent("_save", new WSaveButton());
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
		printpre($properties['filename']);
		
		$path = $properties['filename']['tmp_name'];
		$filename = $properties['filename']['name'];
		$newName = importAction::moveArchive($path, $filename);
		
		if ($properties['importtype'] == "Tab-Delimited") 
			$importer =& new TabRepositoryImporter($newName, $dr->getId());
		else if ($properties['importtype'] == "XML") 
			$importer =& new XMLRepositoryImporter($newName, $dr->getId());
		else if ($properties['importtype'] == "Exif") 
			$importer =& new ExifRepositoryImporter($newName, $dr->getId());
		
		if ($importer->isDataValid())
			$importer->import();
		else {
			print <<<END
<h1>Holy jeepers, Wilson! The data wasn't the right format!</h1>
END;
		}
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
}
?>
