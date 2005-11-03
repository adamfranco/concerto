<?php
/**
 * @since 9/14/05
 * @package concerto.admin
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

//require_once(MYDIR."/domit/xml_domit_include.php");
//require_once(POLYPHONY."/main/library/RepositoryImporter/XMLRepositoryImporter.class.php");
//require_once(POLYPHONY."/main/library/RepositoryImporter/TabRepositoryImporter.class.php");
//require_once(POLYPHONY."/main/library/RepositoryImporter/ExifRepositoryImporter.class.php");
//require_once(HARMONI."utilities/MIMETypes.class.php");
//require_once(HARMONI."utilities/Dearchiver.class.php");

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");

/**
 * imports data into concerto (of many types)
 * 
 * @since 9/14/05
 * @package concerto.admin
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
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");

		return $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$idManager->getId("edu.middlebury.authorization.root"));
	}

	/**
 	 * Return the "unauthorized" string to print
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to import into <em>Concerto</em>.");
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getHeadingText () {
		return _("Import data into <em>Concerto</em>");
	}

	function buildContent () {
		$harmoni =& Harmoni::instance();
		$centerPane =& $this->getActionRows();
		
		$cacheName = 'import_concerto_data_wizard';
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
		
		// :: Name and Description ::
		//$step =& $wizard->addStep("fileupload", new WizardStep());
		//$step->setDisplayName(_("Archive Type and File Upload"));
		
		$select =& $wizard->addComponent("file_type", new WSelectList());
//		$select->addOption("Tab-Delimited", "Tab-Delimited");
		$select->addOption("XML", "XML");
//		$select->addOption("Exif", "Exif");
//		$select->setValue("Tab-Delimited");
		
		$archive =& $wizard->addComponent("is_archived", 
			WCheckBox::withLabel("is Archived"));
		
		$type =& $wizard->addComponent("import_type", new WSelectList());
		$type->addOption("update", "update");
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
		
		$array = array("FILE", "FILE_DATA", "FILE_NAME", "MIME_TYPE",
		"THUMBNAIL_DATA", "THUMBNAIL_MIME_TYPE", "FILE_SIZE", "DIMENSIONS",
		"THUMBNAIL_DIMENSIONS", 	
		"edu.middlebury.harmoni.repository.asset_content", 
		"edu.middlebury.harmoni.repository.asset_content.Content",
		"edu.middlebury.concerto.exhibition_repository");

		
		if ($properties['file_type'] == "XML") {
			$importer =& new XMLImporter($array);
		if ($properties['is_archived'] == TRUE) {
				$directory = $importer->decompress($newName);
				$importer =& XMLImporter::withFile($array, 
					$directory."/metadata.xml", 
					$properties['import_type']);
			}
			else
				$importer =& XMLImporter::withFile($array, $newName,
					$properties['import_type']);
					
			$importer->parseAndImportBelow();
			
		}

		if ($importer->hasErrors()) {
			$importer->printErrorMessages();
	
			$centerPane->add(new Block(ob_get_contents(), 1));
			ob_end_clean();
			return FALSE;
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

		$harmoni =& Harmoni::instance();
		return $harmoni->request->quickURL("admin", "main");
	}
	
}
?>