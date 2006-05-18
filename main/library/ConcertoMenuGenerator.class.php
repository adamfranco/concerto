<?php
/**
 * @package concerto.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
require_once(dirname(__FILE__)."/abstractActions/AssetAction.class.php");
require_once(HARMONI."GUIManager/Components/MenuItem.class.php");

/**
 * The MenuGenerator class is a static class used for the generation of Menus in
 * Concerto.
 *
 * @author Adam Franco
 *
 * @package concerto.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

class ConcertoMenuGenerator 
	extends AssetAction
{

	/**
	 * Generates a menu layout based on the current action.
	 * @param string $actionString A dotted-pair action string of the form
	 *		"module.action" .
	 * @return object MenuLayout
	 */
	function &generateMainMenu() {
		
		$harmoni =& Harmoni::instance();
		
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		
		$mainMenu =& new Menu(new YLayout(), 1);

	// :: Home ::
		$mainMenu_item1 =& new MenuItemLink(
			_("Home"), 
			$harmoni->request->quickURL("home", "welcome"), 
			($module == "home" && $action == "welcome")?TRUE:FALSE, 1);
		$mainMenu->add($mainMenu_item1, "100%", null, LEFT, CENTER);


		$mainMenu_item2 =& new MenuItemLink(
			_("Collections"),
			$harmoni->request->quickURL("collections", "namebrowse"), 
			($module == "collections")?TRUE:FALSE,1);
		$mainMenu->add($mainMenu_item2, "100%", null, LEFT, CENTER);
		
		// Collection browse links.
		// Just show if we are not in a particular collection.
		if (ereg("collection(s)?|asset", $module)) {			
			// Collection root
			if (ereg("^(collection|asset)$", $module)) {
				// Repository Link
				$repository =& $this->getRepository();
				if ($repository)
					$linkTitle = $repository->getDisplayName();
				else
					$linkTitle = _("Collection");
				$mainMenu->add(
					new MenuItemLink(
						$linkTitle,
						$harmoni->request->quickURL("collection", "browse",
							array('collection_id' => $harmoni->request->get('collection_id'))),
						($module == "collection")?TRUE:FALSE, 2), 
					"100%", null, LEFT, CENTER);
					
				// Asset Link
				$asset =& $this->getAsset();
				if ($asset) {
					$assets = array();
					$assets[] =& $asset;
					$this->addFirstParents($asset, $assets);
					
					$j = 0;
					for ($i = count($assets) - 1; $i >= 0 ; $i--) {
						$assetId =& $assets[$i]->getId();
						$mainMenu->add(
							new MenuItemLink(
								$assets[$i]->getDisplayName(),
								$harmoni->request->quickURL("asset", "browseAsset",
									array('asset_id' => $assetId->getIdString())),
								($module == "asset")?TRUE:FALSE, $j+3), 
							"100%", null, LEFT, CENTER);
						$j++;
					}
				}
			}
		}
		
		$mainMenu_item6 =& new MenuItemLink(
			_("Exhibitions"),
			$harmoni->request->quickURL("exhibitions", "browse"), 
			($module == 'exhibitions' && $action == 'browse')?TRUE:FALSE,1);
		$mainMenu->add($mainMenu_item6, "100%", null, LEFT, CENTER);
		
		// Exhibition browse links.
		// Just show if we are not in a particular collection.
		if ($module == 'exhibitions' && $action != 'browse') {
			$idManager =& Services::getService("Id");
			$repositoryManager =& Services::getService("Repository");
			
			if (RequestContext::value('slideshow_id'))
				$assetId =& $idManager->getId(RequestContext::value('slideshow_id'));
			else if (RequestContext::value('asset_id'))
				$assetId =& $idManager->getId(RequestContext::value('asset_id'));
			else if (RequestContext::value('exhibition_id'))
				$assetId =& $idManager->getId(RequestContext::value('exhibition_id'));
			else
				$assetId = false;
				
			// Asset Link
			if ($assetId) {
				$exhibitionRepositoryId =& $idManager->getId(
						"edu.middlebury.concerto.exhibition_repository");
				$exhibitionRepository =& $repositoryManager->getRepository($exhibitionRepositoryId);
				$asset =& $exhibitionRepository->getAsset($assetId);

				$assets = array();
				$assets[] =& $asset;
				$this->addFirstParents($asset, $assets);
				
				$slideshowType = new HarmoniType("exhibitions", 
										"edu.middlebury.concerto", 
										"slideshow", 
										"Slide-Shows are ordered collections of slides that contain captions and may reference media Assets.");
				$exhibitionType = new Type("Asset Types",
							"edu.middlebury.concerto",
							"Exhibition",
							"Exhibition Assets are containers for Slideshows.");
							
				$j = 0;
				for ($i = count($assets) - 1; $i >= 0 ; $i--) {
					$assetId =& $assets[$i]->getId();
					if ($slideshowType->isEqual($assets[$i]->getAssetType())) {
						$mainMenu->add(
							new MenuItemLink(
								$assets[$i]->getDisplayName(),
								$harmoni->request->quickURL("exhibitions", 'browseSlideshow',
									array('asset_id' => $assetId->getIdString())),
								($action == "browseSlideshow")?TRUE:FALSE, $j+3), 
							"100%", null, LEFT, CENTER);
					} else if ($exhibitionType->isEqual($assets[$i]->getAssetType())) {
						$mainMenu->add(
							new MenuItemLink(
								$assets[$i]->getDisplayName(),
								$harmoni->request->quickURL("exhibitions", 'browse_exhibition',
									array('exhibition_id' => $assetId->getIdString())),
								($action == "browse_exhibition")?TRUE:FALSE, $j+3), 
							"100%", null, LEFT, CENTER);
					} else {
						$mainMenu->add(
							new MenuItemLink(
								$assets[$i]->getDisplayName(),
								'',
								FALSE, $j+3), 
							"100%", null, LEFT, CENTER);
					}
					$j++;
				}
			}
		}
		
		foreach (array_keys($_SESSION) as $key) {
			if (ereg("^add_slideshow_wizard_(.+)", $key, $matches)) {
				$exhibitionAssetId = $matches[1];
	
				$item =& new MenuItemLink(
						_("SlideShow"),
						$harmoni->request->quickURL("exhibitions", "add_slideshow", 
							array("exhibition_id" => $exhibitionAssetId)), 
						($module == "exhibitions" && $action == "add_slideshow" && RequestContext::value("exhibition_id") == $exhibitionAssetId)?TRUE:FALSE, 2
				);
				$mainMenu->add($item, "100%", null, LEFT, CENTER);
			}
		}
	
	
		return $mainMenu;
	}
	
	/**
	 * Answer the first parent of an asset or false
	 * 
	 * @param object Asset
	 * @return mixed object or false
	 * @access public
	 * @since 5/15/06
	 */
	function addFirstParents ( &$asset, &$assetArray ) {
		$parents =& $asset->getParents();
		if ($parents->hasNext()) {
			$parent =& $parents->next();
			$assetArray[] =& $parent;
			$this->addFirstParents($parent, $assetArray);
		}
	}
}

?>