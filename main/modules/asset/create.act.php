<?php
/**
 * @since 10/23/07
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");

require_once(POLYPHONY."/main/library/RepositoryImporter/FilesOnlyRepositoryImporter.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/ExifRepositoryImporter.class.php");

/**
 * Create a new Asset from a file (similar to importing a single file), or 
 * give a button to create a generic asset.
 * 
 * @since 10/23/07
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class createAction
	extends RepositoryAction
{
		
	/**
	 * @var array $saveMessages;  
	 * @access protected
	 * @since 10/23/07
	 */
	protected $saveMessages = array();
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 10/23/07
	 */
	function isAuthorizedToExecute () {
		// Check that the user can create an asset here.
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		 
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$this->getRepositoryId());
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 10/23/07
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create an <em>Asset</em> here.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 10/23/07
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("collection_id");
		
		$centerPane =$this->getActionRows();
		$repositoryId =$this->getRepositoryId();
		$cacheName = 'create_asset_wizard_'.$repositoryId->getIdString();
		
		$this->runWizard ( $cacheName, $centerPane );
	}
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 10/23/07
	 */
	function getHeadingText () {
		$repository =$this->getRepository();
	
		return _("Add Asset to the ")."<em>".$repository->getDisplayName()."</em> "._("Collection");
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 10/23/07
	 */
	function createWizard () {
		$repository =$this->getRepository();
		
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleWizard::withText("<div>\n" .
				"[[cancel]]\n" .
				"</div>\n" .
				"<hr/>\n" .
				"<div>[[file]]</div>".
				"<div>[[asset_kind]]</div>".
				"\n");
		
		$wizard->addComponent("cancel", new WCancelButton);
		
		$file = $wizard->addComponent("file", new WFileUploadField);
		$file->setStartingDisplay(_("Choose a file"), 0);
		
		$assetKind = $wizard->addComponent("asset_kind", new WSaveWithChoiceButtonList);
		
		$assetKind->addOption("from_file", _("Create From File &raquo;"), "<br/><br/>");		
		$assetKind->addOption("generic", _("Create a generic Asset &raquo;"));
		
		$parent = $wizard->addComponent("parent", new WHiddenField);
		if (RequestContext::value("parent"))
			$parent->setValue(RequestContext::value("parent"));
	
		return $wizard;
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 10/24/07
	 */
	function saveWizard ( $cacheName ) {
		$wizard = $this->getWizard($cacheName);
		$properties = $wizard->getAllValues();
		
		/*********************************************************
		 * Create generic
		 *********************************************************/
		if ($properties['asset_kind'] == 'generic') {
			$this->addGeneric = true;
			$this->parentAsset = $properties['parent'];
			return true;
		}
		
		
		/*********************************************************
		 * Asset from file
		 *********************************************************/
		try {
			if ($properties['file']['name'] == "")
				throw new Exception("Please choose a file to upload.");
			
			$path = $properties['file']['tmp_name'];
			$filename = $properties['file']['name'];
			$newName = $this->moveArchive($path, $filename);
			
			
			// check the file type:
			$mime = Services::getService("MIME");
			$mimeType = $mime->getMimeTypeForFileName(basename($filename));
			
			// Load Exif metadata from JPEGS
			if ($mimeType == "image/jpeg") {
				$importer = new ExifRepositoryImporter($newName, $this->getRepositoryId(), false);
			} 
			// Just create an asset from the file.
			else {
				$importer = new FilesOnlyRepositoryImporter($newName, $this->getRepositoryId(), false);
			}
			
				
			if ($properties['parent']) {
				$repository = $this->getRepository();
				$idManager = Services::getService("Id");
				$this->parentAsset = $properties['parent'];
				
				$importer->setParent(
					$repository->getAsset(
						$idManager->getId($properties['parent'])));
			}
		
			ob_start();
			$importer->import(false);
			$importerOutput = ob_get_clean();
			
			if ($importer->hasErrors()) {
				ob_start();
				$importer->printErrorMessages();
				throw new Exception(ob_get_clean());
			}
			
		} catch (Exception $e) {
			unlink($newName);
			rmdir(dirname($newName));
			
			$centerPane->add(new Block($e->getMessage(), 1));
			
			return false;
		}
		
		unlink($newName);
		rmdir(dirname($newName));
		return true;
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
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/07
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
		$repositoryId = $this->getRepositoryId();
		if (isset($this->addGeneric) && $this->addGeneric == true) 
			return $harmoni->request->quickURL("asset", "add", array(
				"collection_id" => $repositoryId->getIdString(),
				"parent" => $this->parentAsset));
		
		if (isset($this->parentAsset) && $this->parentAsset) 
			return $harmoni->request->quickURL("asset", "browseAsset", array(
				"collection_id" => $repositoryId->getIdString(),
				"asset_id" => $this->parentAsset));
		
		return $harmoni->request->quickURL("collection", "browse", array(
				"collection_id" => $repositoryId->getIdString()));
	}
	
}

?>