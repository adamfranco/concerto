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
		return TRUE;
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
			"\n<h3>"._("File type")."</h3>".
			"\n"._("The type of file to be imported: ").
			"\n<br />[[file_type]]".
			"\n<br />\n<br />"._("Is this file an archive?").
			"\n<br />[[is_archived]]".
			"\n<h3>"._("Import type")."</h3>".
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
		
		$archive =& $wizard->addComponent("is_archived", 
			WCheckBox::withLabel("isArchived?"));
		
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
		
		
		
		if ($properties['file_type'] == "XML") {
			$importer =& new XMLImporter();
		if ($properties['is_archived'] == TRUE) {
				$directory = $importer->decompress($newName);
				$importer =& XMLImporter::withFile($directory."/metadata.xml", 
					$properties['import_type']);

			}
			else
				$importer =& XMLImporter::withFile($newName,
					$properties['import_type']);
					
			$importer->parseAndImportBelow();
			
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
		return $harmoni->request->quickURL("admin", "main");
	}
	
}
?>