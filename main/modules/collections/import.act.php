<?php
/**
 * @since 10/31/05
 * @package concerto.collections
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLRepositoryImporter.class.php");

/**
 * Imports collections into concerto
 * 
 * @since 10/31/05
 * @package concerto.collections
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
			$idManager->getId("edu.middlebury.concerto.collections_root"));

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
		return _("You are not authorized to import a <em>Collection</em> into Concerto.");
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getHeadingText () {
		return _("Import a <em>Collection</em> into Concerto");
	}

	function buildContent () {
		$harmoni =& Harmoni::instance();
		$centerPane =& $this->getActionRows();

		$authN =& Services::getService("AuthN");
		$authTypes =& $authN->getAuthenticationTypes();
		$uniqueString = "";
		while($authTypes->hasNext()) {
			$authType =& $authTypes->next();
			$id =& $authN->getUserId($authType);
			$uniqueString .= "_".$id->getIdString();
		}

		$cacheName = str_replace('.', '_', 'import_collection_wizard_'.$uniqueString);
		
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
		$wizard =& SimpleWizard::withText(
			"<table border='0' style='margin-top:20px' >\n" .
			"\n<tr><td><h3>"._("File type:")."</h3></td></tr>".
			"\n<tr><td>"._("The type of file to be imported: ")."</td>".
			"\n<td>[[file_type]]</td></tr>".
			"\n<tr><td>"._("Is this file an archive? ")."</td>".
			"\n<td>[[is_archived]]</td></tr>".
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
//		$select->addOption("Tab-Delimited", "Tab-Delimited");
		$select->addOption("XML", "XML");
//		$select->addOption("Exif", "Exif");
//		$select->setValue("Tab-Delimited");
		
		$archive =& $wizard->addComponent("is_archived", 
			WCheckBox::withLabel("is Archived"));

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
		$array = array(
			"REMOTE_FILE", 
			"FILE_URL", 
			"FILE", 
			"FILE_DATA", 
			"FILE_NAME", 
			"MIME_TYPE",
			"THUMBNAIL_DATA", 
			"THUMBNAIL_MIME_TYPE", 
			"FILE_SIZE", 
			"DIMENSIONS",
			"THUMBNAIL_DIMENSIONS", 
			"edu.middlebury.harmoni.repository.asset_content", 		
			"edu.middlebury.harmoni.repository.asset_content.Content", 
			"edu.middlebury.concerto.exhibition_repository",
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure",
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id",
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.text_position",
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.display_metadata");
		$hasErrors = false;
		if ($properties['file_type'] == "XML") {
		//	define an empty importer for decompression
			if ($properties['is_archived'] == TRUE) {
				$importer =& new XMLImporter($array);
				$directory = $importer->decompress($newName);
								
				// something happened so tell the end user
				if ($importer->hasErrors()) {
					$importer->printErrorMessages();
					$hasErrors = true;
				}
				
				unset($importer);
				$dir = opendir($directory);
				
				while ($file = readdir($dir)) // each folder is a collection
					if (is_dir($directory."/".$file) && $file != "." && $file != "..") {
						$importer =& XMLRepositoryImporter::withFile(
							$array,
							$directory."/".$file."/metadata.xml", 
							$properties['import_type']);
						$importer->parseAndImport("asset");
						
						// something happened so tell the end user
						if ($importer->hasErrors()) {
							$importer->printErrorMessages();
							$hasErrors = true;
						}
						unset($importer);
					}
					closedir($dir);
					// Unlink the directory
					shell_exec(' rm -R '.$directory);
			}
			else {// not compressed, only one xml file
				$importer =& XMLRepositoryImporter::withFile($array, $newName,
					$properties['import_type']);
				$importer->parseAndImport("asset");
				// something happened so tell the end user
				if ($importer->hasErrors()) {
					$importer->printErrorMessages();
					$hasErrors = true;
				}
			}
			
		}
		unlink($newName);
		
		if ($hasErrors) {	
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
		return $harmoni->request->quickURL("collections", "namebrowse");
	}	
}
?>