<?php
/**
 * @since 9/14/05
 * @package concerto.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRepositoryImporter.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/FilesOnlyRepositoryImporter.class.php");

/**
 * Imports assets into a collection in concerto
 * 
 * @since 9/14/05
 * @package concerto.collection
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
			$idManager->getId(RequestContext::value('collection_id')));

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
		
		$harmoni->request->endNamespace();
		
		return _("You are not authorized to import into the <em>".
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
		
		$harmoni->request->endNamespace();

		return _("Import data into the <em>".$repository->getDisplayName().
			"</em> Collection");
	}

	function buildContent () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("import");
		$harmoni->request->passthrough("collection_id");
		
		$centerPane =& $this->getActionRows();
		$cacheName = 'import_concerto_data_wizard_'.
			$harmoni->request->get('collection_id');
		
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
		$harmoni =& Harmoni::Instance();
		$wizard =& SimpleWizard::withText(
			"<table border='0' style='margin-top:20px' >\n" .
			"\n<tr><td><h3>"._("File type:")."</h3></td></tr>".
			"\n<tr><td>"._("The type of file to be imported: ")."</td>".
			"\n<td>[[file_type]]</td></tr>".
// 			"\n<tr><td colspan='2'>"._("If Exif click ")."<a href=\"".
// 			$harmoni->request->quickURL("collection", "exifschema").
// 			"\">"._("here")."</a>".
// 			_(" to customize your schema (Suggested)")."</td></tr>".
			"\n<tr><td>"._("Is this file an archive? ")."</td>".
			"\n<td>[[is_archived]] (Files-Only, Tab-Delimited and Exif must be Archived)</td></tr>".
			"\n<tr><td><h3>"._("Import type:")."</h3></td></tr>".
			"\n<tr><td>"._("The type of import to execute: ")."</td>".
			"\n<td>[[import_type]]</td></tr>".
			"\n<tr><td><h3>"._("File:")."</h3></td></tr>".
			"\n<tr><td>"._("The file to be imported: ")."</td>".
			"\n<td>[[filename]]</td>".
			"<tr>\n" .
			"<td align='left'>\n" .
			"[[_cancel]]".
			"</td>\n" .
			"<td align='right'>\n" .
			"[[_save]]".
			"</td></tr></table>");
				
		$select =& $wizard->addComponent("file_type", new WSelectList());
		$select->addOption("XML", "XML");
		$select->addOption("Tab-Delimited", "Tab-Delimited");
		$select->addOption("Exif", "Exif");
		$select->addOption("FilesOnly", "Files Only (no metadata)");
		$select->setValue("FilesOnly");

		$archive =& $wizard->addComponent("is_archived", 
			WCheckBox::withLabel("is Archived"));
		$archive->setChecked(true);

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
		$properties = $wizard->getAllValues();

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
//===== THIS ARRAY DEFINES THINGS THAT SHOULD NOT BE IMPORTED =====// 
		$array = array("FILE", "FILE_DATA", "FILE_NAME", "MIME_TYPE",
		"THUMBNAIL_DATA", "THUMBNAIL_MIME_TYPE", "FILE_SIZE", "DIMENSIONS",
		"THUMBNAIL_DIMENSIONS", 	
		"edu.middlebury.harmoni.repository.asset_content", 
		"edu.middlebury.harmoni.repository.asset_content.Content");
		
		
		$repository =& $repositoryManager->getRepository(
					$idManager->getId($harmoni->request->get('collection_id')));
		
//===== Exif and Tab-Delim Importers are special =====//
		if ($properties['file_type'] == "Tab-Delimited") 
			$importer =& new TabRepositoryImporter($newName, $repository->getId(), false);
		else if ($properties['file_type'] == "Exif") 
			$importer =& new ExifRepositoryImporter($newName, $repository->getId(), false);
		else if ($properties['file_type'] == "FilesOnly") 
			$importer =& new FilesOnlyRepositoryImporter($newName, $repository->getId(), false);
		if (isset($importer))
			$importer->import();						
//===== Done with special "RepositoryImporters" =====//

		if ($properties['file_type'] == "XML") {
			if ($properties['is_archived'] == TRUE) {
				//	define an empty importer for decompression
				$importer =& new XMLImporter($array);
				$directory = $importer->decompress($newName);
				unset($importer);
				$importer =& XMLRepositoryImporter::withObject(
					$array,
					$repository,
					$directory."/metadata.xml",
					$properties['import_type']);
			}
			else // not compressed, only one xml file
				$importer =& XMLRepositoryImporter::withObject($array,
					$repositoryManager->getRepository($idManager->getId(
					$harmoni->request->get('collection_id'))), $newName,
					$properties['import_type']);
			$importer->parseAndImportBelow("asset", 100);
		}		
		if ($importer->hasErrors()) {
		// something happened so tell the end user
			$importer->printErrorMessages();
	
			$centerPane->add(new Block(ob_get_contents(), 1));
			ob_end_clean();
			return FALSE;
		}
		// clean and clear
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
		$harmoni =& Harmoni::instance();

		$id = $harmoni->request->get("collection_id");
		$harmoni->request->forget("collection_id");
		$harmoni->request->endNamespace();
		$url =& $harmoni->request->mkURL("collection", "browse",
			array("collection_id" => $id));
		$harmoni->request->startNamespace("import");
		
		return $url->write();
	}
	
}
?>