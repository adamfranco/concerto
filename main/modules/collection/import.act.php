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
require_once(POLYPHONY."/main/library/RepositoryImporter/ExifRepositoryImporter.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/TabRepositoryImporter.class.php");


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
		$harmoni = Harmoni::instance();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
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
		$harmoni = Harmoni::Instance();
		$idManager = Services::getService("Id");
		$rm = Services::getService("Repository");
		
		$harmoni->request->startNamespace("import");
		
		$repository =$rm->getRepository($idManager->getId(
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
		$harmoni = Harmoni::Instance();
		$idManager = Services::getService("Id");
		$rm = Services::getService("Repository");
		
		$harmoni->request->startNamespace("import");
		
		$repository =$rm->getRepository($idManager->getId(
			$harmoni->request->get("collection_id")));
		
		$harmoni->request->endNamespace();

		return _("Import data into the <em>".$repository->getDisplayName().
			"</em> Collection");
	}

	function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("import");
		$harmoni->request->passthrough("collection_id");
		
		$centerPane =$this->getActionRows();
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
	function createWizard () {
		$harmoni = Harmoni::Instance();
		$wizard = SimpleWizard::withText(
			"<table border='0' style='margin-top:20px' >\n" .
			
			"\n<tr><td><h3>"._("Source data type:")."</h3></td>".
			"\n<td style='font-size: small;'>".("* Please see the <strong>Help</strong> below for more information.")."</td></tr>".
			
			"\n<tr><td>"._("The format of data to be imported: ")."</td>".
			"\n<td>[[file_type]]</td></tr>".
// 			"\n<tr><td colspan='2'>"._("If Exif click ")."<a href=\"".
// 			$harmoni->request->quickURL("collection", "exifschema").
// 			"\">"._("here")."</a>".
// 			_(" to customize your schema (Suggested)")."</td></tr>".
			
			"\n<tr><td>"._("Is this file a Zip/GZip/BZip/Tar archive?<br/>If checked, the archive will be decompressed and all files in it will be imported.")."</td>".
			"\n<td>[[is_archived]]</td></tr>".
			
			"\n<tr><td><h3>"._("Import type:")."</h3></td></tr>".
			"\n<tr><td>"._("The type of import to execute: ")."</td>".
			"\n<td>[[import_type]]</td></tr>".
			
			"\n<tr><td><h3>"._("File:")."</h3></td></tr>".
			"\n<tr><td>"._("The Zip/GZip/BZip/Tar/XML achive file or a single file to be imported: ")."</td>".
			"\n<td>[[filename]]</td></tr>".
			
			"\n<tr><td><h3>"._("Parent Asset:")."</h3></td></tr>".
			"\n<tr><td>"._("If specified, all Assets imported will be placed below this parent. ")."</td>".
			"\n<td>[[parent]]</td></tr>".
			
			"<tr>\n" .
			"<td align='left'>\n" .
			"[[_cancel]]".
			"</td>\n" .
			"<td align='right'>\n" .
			"[[_save]]".
			"</td></tr></table>");
				
		$select =$wizard->addComponent("file_type", new WSelectList());
		$select->addOption("XML", "XML");
		$select->addOption("Tab-Delimited", "Tab-Delimited");
		$select->addOption("Exif", "Exif");
		$select->addOption("FilesOnly", "Files Only (no metadata)");
		$select->setValue("FilesOnly");

		$archive =$wizard->addComponent("is_archived", 
			WCheckBox::withLabel("is Archived"));
		$archive->setChecked(true);

		$type =$wizard->addComponent("import_type", new WSelectList());
//		$type->addOption("update", "update");  
// need exceptions for nodes not existing
		$type->addOption("insert", "insert");
		//$type->addOption("replace", "replace");
		
		$fileField =$wizard->addComponent("filename", new WFileUploadField());
		
		$parent =$wizard->addComponent("parent", new WSelectList());
		$parent->addOption("", "none");
		$rootAssets =$this->getRootAssets();
		while ($rootAssets->hasNext()) {
			$this->addAssetOption($parent, $rootAssets->next());
		}
		if (RequestContext::value('parent')) {
			$parent->setValue(RequestContext::value('parent'));
		}
				
		$save =$wizard->addComponent("_save", 
			WSaveButton::withLabel("Import"));
		$cancel =$wizard->addComponent("_cancel", new WCancelButton());
		//$fileField->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		//$fileField->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		return $wizard;
	}
	
	/**
	 * Add an asset option to a WSelectList recursively
	 * 
	 * @param object $field
	 * @param object $asset
	 * @return void
	 * @access public
	 * @since 4/2/07
	 */
	function addAssetOption ($field, $asset, $depth = 0) {
		$assetId =$asset->getId();
		
		ob_start();
		for ($i = 0; $i <= $depth; $i++)
			print "-";
		
		print " ".$asset->getDisplayName();
		print " (".$assetId->getIdString().")";
		
		$field->addOption($assetId->getIdString(), ob_get_clean());
		
		$children =$asset->getAssets();
		while ($children->hasNext()) {
			$this->addAssetOption($field, $children->next(), $depth + 1);
		}
	}
	
	/**
	 * Answer the root assets in the current repository
	 * 
	 * @return object Iterator
	 * @access public
	 * @since 4/2/07
	 */
	function getRootAssets () {
		$harmoni = Harmoni::Instance();
		$idManager = Services::getService("Id");
		$rm = Services::getService("Repository");
		
		$harmoni->request->startNamespace("import");
		
		$repository =$rm->getRepository($idManager->getId(
			$harmoni->request->get("collection_id")));
		
		$harmoni->request->endNamespace();
		
		$criteria = NULL;
		$searchProperties = new HarmoniProperties(
					HarmoniType::fromString("repository::harmoni::order"));
		$searchProperties->addProperty("order", $orderBy = 'DisplayName');
		$searchProperties->addProperty("direction", $direction = 'ASC');
		unset($orderBy, $direction);
		
		$assets =$repository->getAssetsBySearch(
			$criteria, 
			new HarmoniType("Repository","edu.middlebury.harmoni","RootAssets", ""), 
			$searchProperties);
		
		return $assets;
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
		$harmoni = Harmoni::instance();
		$idManager = Services::getService("Id");
		$repositoryManager = Services::getService("Repository");
		$wizard =$this->getWizard($cacheName);
		$properties = $wizard->getAllValues();

		$centerPane =$this->getActionRows();
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
		$this->_ignore = array(
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
			"edu.middlebury.harmoni.repository.asset_content.Content");
		
		
		$repository =$repositoryManager->getRepository(
					$idManager->getId($harmoni->request->get('collection_id')));
		
		$startTime = DateAndTime::now();
		$this->_hasErrors = false;
		
//===== Exif and Tab-Delim Importers are special =====//
		if ($properties['file_type'] == "Tab-Delimited") 
			$importer = new TabRepositoryImporter($newName, $repository->getId(), false);
		else if ($properties['file_type'] == "Exif") 
			$importer = new ExifRepositoryImporter($newName, $repository->getId(), false);
		else if ($properties['file_type'] == "FilesOnly") 
			$importer = new FilesOnlyRepositoryImporter($newName, $repository->getId(), false);
		if (isset($importer)) {
			if ($properties['parent']) {
				$importer->setParent(
					$repository->getAsset(
						$idManager->getId($properties['parent'])));
			}
			
			$importer->import($properties['is_archived']);	
			
			// something happened so tell the end user
			if ($importer->hasErrors()) {
				$importer->printErrorMessages();
				$this->_hasErrors = true;
			}
		}
		
//===== Done with special "RepositoryImporters" =====//

		if ($properties['file_type'] == "XML") {			
			if ($properties['is_archived'] == TRUE) {
				//	define an empty importer for decompression
				$importer = new XMLImporter($this->_ignore);
				$directory = $importer->decompress($newName);
				unset($importer);
				
				$this->_repository =$repository;
				$this->_importType = $properties['import_type'];
				
				if ($properties['parent']) {
					$parent =$repository->getAsset(
							$idManager->getId($properties['parent']));
				} else {
					$parent = null;
				}
				
				$this->importDirectoriesDown($directory, $parent);
			}
			else {
				// not compressed, only one xml file
				$importer = XMLRepositoryImporter::withObject($this->_ignore,
					$repositoryManager->getRepository($idManager->getId(
					$harmoni->request->get('collection_id'))), $newName,
					$properties['import_type']);
				
				if ($properties['parent']) {
					$importer->setParent(
						$repository->getAsset(
							$idManager->getId($properties['parent'])));
				}

				
				$importer->parseAndImportBelow("asset", 100);
				
				// something happened so tell the end user
				if ($importer->hasErrors()) {
					$importer->printErrorMessages();
					$this->_hasErrors = true;
				}
				unset($importer);
			}
		}
		
		// Unlink the directory
		if (isset($directory) && $directory)
			$this->deleteRecursive($directory);
		if (file_exists($newName))
			unlink($newName);
		
		if ($this->_hasErrors) {	
			$centerPane->add(new Block(ob_get_contents(), 1));
			ob_end_clean();
			return FALSE;
		}
		
		// Update any newly modified assets' tags
		$searchProperties = new HarmoniProperties(
					new Type("repository", "harmoni", "order"));
		$searchProperties->addProperty("order", $order = "ModificationDate");
		$searchProperties->addProperty("direction", $direction = "DESC");
		$assets =$repository->getAssetsBySearch(
				$criteria = '*',
				new Type("Repository", "edu.middlebury.harmoni", "DisplayName"),
				$searchProperties);
				
		$systemAgentId =$idManager->getId('system:concerto');
		$tagGenerator = StructuredMetaDataTagGenerator::instance();	
		
		while ($assets->hasNext()) {
			$asset =$assets->next();
			if ($startTime->isGreaterThan($asset->getModificationDate()))
				break;
			
			$tagGenerator->regenerateTagsForAsset($asset, $systemAgentId,
			'concerto', $repository->getId());
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
		$harmoni = Harmoni::instance();

		$id = $harmoni->request->get("collection_id");
		$harmoni->request->forget("collection_id");
		$harmoni->request->endNamespace();
		$url =$harmoni->request->mkURL("collection", "browse",
			array("collection_id" => $id));
		$harmoni->request->startNamespace("import");
		
		return $url->write();
	}
	
	/**
	 * Import a directories that contain metadata.xml files
	 * 
	 * @param string $directory
	 * @param optional object Asset $parentAsset
	 * @return void
	 * @access public
	 * @since 12/7/06
	 */
	function importDirectory ($directory, $parentAsset = null) {
		$importer = XMLRepositoryImporter::withObject(
			$this->_ignore,
			$this->_repository,
			$directory."/metadata.xml",
			$this->_importType);
			
		if ($parentAsset) {
			$importer->setParent($parentAsset);
		}
		$importer->parseAndImportBelow("asset", 100);
		
		// something happened so tell the end user
		if ($importer->hasErrors()) {
			$importer->printErrorMessages();
			$this->_hasErrors = true;
		}
		unset($importer);
	}
	
	/**
	 * Answer true if the directory can be imported
	 * 
	 * @param string $directory
	 * @return boolean
	 * @access public
	 * @since 12/7/06
	 */
	function hasImport ($directory) {
		$dir = opendir($directory);
		while ($file = readdir($dir)) {
			if ($file == "metadata.xml") {
				closedir($dir);
				return true;
			}
		}
		closedir($dir);
		return false;
	}
	
	/**
	 * Import the current directory or any valid subdirectories.
	 * 
	 * @param string $directory
	 * @param optional object Asset $parentAsset
	 * @return boolean
	 * @access public
	 * @since 12/7/06
	 */
	function importDirectoriesDown ($directory, $parentAsset = null) {
		if ($this->hasImport($directory))
			$this->importDirectory($directory, $parentAsset);
		
		$dir = opendir($directory);
		while ($file = readdir($dir)) {
			if (is_dir($directory."/".$file) && $file != "." && $file != "..") {
				$this->importDirectoriesDown($directory."/".$file, $parentAsset);
			}
		}
		closedir($dir);
	}
	
	/**
	 * Recursively delete a directory
	 * 
	 * @param string $path
	 * @return void
	 * @access protected
	 * @since 1/18/08
	 */
	protected function deleteRecursive ($path) {
		if (is_dir($path)) {
			$entries = scandir($path);
			foreach ($entries as $entry) {
				if ($entry != '.' && $entry != '..') {
					$this->deleteRecursive($path.DIRECTORY_SEPARATOR.$entry);
				}
			}
			rmdir($path);
		} else {
			unlink($path);
		}
	}
	
}
?>