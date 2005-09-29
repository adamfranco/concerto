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
			"\n<h3>"._("Import type")."</h3>".
			"\n"._("The type of file to be imported: ").
			"\n<br />[[importtype]]".
			"\n<h3>"._("Select file to import")."</h3>".
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
		
		$select =& $wizard->addComponent("importtype", new WSelectList());
//		$select->addOption("Tab-Delimited", "Tab-Delimited");
		$select->addOption("XML", "XML");
//		$select->addOption("Exif", "Exif");
//		$select->setValue("Tab-Delimited");
		
		$fileField =& $wizard->addComponent("filename", new WFileUploadField());
				
		$save =& $wizard->addComponent("_save", 
			WSaveButton::withLabel("Import"));
		$cancel =& $wizard->addComponent("_cancel", new WCancelButton());
		//$fileField->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		//$fileField->setErrorRule(new WECRegex("[\\w]+"));
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
		
		if ($properties['importtype'] == "XML") 
			$importer =& new XMLImporter($newName);

		$importer->parse();
	
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
		return $harmoni->request->quickURL("admin", "main");
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