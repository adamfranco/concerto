<?php
/**
 * @since 9/14/05
 * @package concerto.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLAssetImporter.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRepositoryImporter.class.php");

/**
 * imports underneath an asset (child assets)
 * 
 * @since 9/14/05
 * @package concerto.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class importAction extends MainWindowAction {
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/08/05
	 */
	function isAuthorizedToExecute () {
		$harmoni =& Harmoni::instance();
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		$harmoni->request->startNamespace('import');

		$return = $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$idManager->getId(RequestContext::value('asset_id')));

		$harmoni->request->endNamespace();
		
		// Check that the user can create an asset here.
		return $return;
	}

	/**
 	 * Return the "unauthorized" string to print
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getUnauthorizedMessage () {
		$harmoni =& Harmoni::Instance();
		$idManager =& Services::getService("Id");
		$rm =& Services::getService("Repository");
		
		$harmoni->request->startNamespace("import");
		
		$repository =& $rm->getRepository($idManager->getId(
			$harmoni->request->get("collection_id")));
		$asset =& $repository->getAsset($idManager->getId(
			$harmoni->request->get("asset_id")));
		
		$harmoni->request->endNamespace();
		
		return _("You are not authorized to import below the <em>".
			$asset->getDisplayName()."</em> Asset in the <em>".
			$repository->getDisplayName()."</em> Collection.");
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getHeadingText () {
		$harmoni =& Harmoni::Instance();
		$idManager =& Services::getService("Id");
		$rm =& Services::getService("Repository");

		$harmoni->request->startNamespace("import");
		$repository =& $rm->getRepository($idManager->getId(
			$harmoni->request->get("collection_id")));
		$asset =& $repository->getAsset($idManager->getId(
			$harmoni->request->get("asset_id")));
		
		$harmoni->request->endNamespace();

		return _("Import data below the <em>".
			$asset->getDisplayName()."</em> Asset in the <em>".
			$repository->getDisplayName()."</em> Collection");
	}

	function buildContent () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("import");
		$harmoni->request->passthrough("collection_id");
		$harmoni->request->passthrough("asset_id");
		
		$centerPane =& $this->getActionRows();

		$authN =& Services::getService("AuthN");
		$authTypes =& $authN->getAuthenticationTypes();
		$uniqueString = "";
		while($authTypes->hasNext()) {
			$authType =& $authTypes->next();
			$id =& $authN->getUserId($authType);
			$uniqueString .= "_".$id->getIdString();
		}

		$cacheName = str_replace('.', '_', 'import_asset_wizard_'.$uniqueString);
		
		$this->runWizard($cacheName, $centerPane);
		
		$harmoni->request->endNamespace();
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
			"\n<h3>"._("File type")."</h3>".
			"\n"._("The type of file to be imported: ").
			"\n<br />[[file_type]]".
			"\n<h3>"._("Import type").
			"\n"._("The type of import to execute: ").
			"\n<br />[[import_type]]".
			"\n<h3>"._("File")."</h3>".
			"\n"._("The file to be imported: ").
			"\n<br />[[filename]]".
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
		
		$select =& $wizard->addComponent("file_type", new WSelectList());
//		$select->addOption("Tab-Delimited", "Tab-Delimited");
		$select->addOption("XML", "XML");
//		$select->addOption("Exif", "Exif");
//		$select->setValue("Tab-Delimited");
		
		$type =& $wizard->addComponent("import_type", new WSelectList());
//		$type->addOption("update", "update");  
// need exceptions for nodes not existing
		$type->addOption("insert", "insert");
		//$type->addOption("replace", "replace");
		
		$fileField =& $wizard->addComponent("filename", new WFileUploadField());
				
		$save =& $wizard->addComponent("_save", 
			WSaveButton::withLabel("Import"));
		$cancel =& $wizard->addComponent("_cancel", new WCancelButton());
		//$fileField->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		//$fileField->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		return $wizard;
	}
	
	/**
	 * moves the file returned by the wizard to a directory with a unique
	 * name
	 * 
	 * @param string
	 * @param string 
	 * @return string
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
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
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
		$newName = $this->moveArchive($path, $filename);
		
		if ($properties['file_type'] == "XML") {
			$repository =& $repositoryManager->getRepository($idManager->getId(
				$harmoni->request->get('collection_id')));
			
			$matrixMaker =& new XMLRepositoryImporter();
			$matrixMaker->doIdMatrix();
			
			// recordstructures into idmatrix
			
			$importer =& XMLAssetImporter::withObject(
				$repository->getAsset($idManager->getId(
				$harmoni->request->get("asset_id"))),
				$newName,
				$properties['import_type']);

			$importer->parseAndImportBelow("asset");
			
			$matrixMaker->dropIdMatrix();
			unset($matrixMaker);
		}
		$centerPane->add(new Block(ob_get_contents(), 1));
		ob_end_clean();
		
		return true;
	}
		
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getReturnUrl () {
		$harmoni =& Harmoni::instance();
		
		$collection_id = $harmoni->request->get("collection_id");
		$asset_id = $harmoni->request->get("asset_id");
		$harmoni->request->forget("collection_id");
		$harmoni->request->forget("asset_id");
		$harmoni->request->endNamespace();
		$url =& $harmoni->request->mkURL("asset", "view",
			array("collection_id" => $collection_id,
			"asset_id" => $asset_id));
		$harmoni->request->startNamespace("import");
		
		return $url->write();
	}
	
}
?>