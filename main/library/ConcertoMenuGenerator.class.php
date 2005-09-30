<?php
/**
 * @package concerto.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

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

class ConcertoMenuGenerator {

	/**
	 * Generates a menu layout based on the current action.
	 * @param string $actionString A dotted-pair action string of the form
	 *		"module.action" .
	 * @return object MenuLayout
	 */
	function &generateMainMenu($harmoni) {
		
		$harmoni =& Harmoni::instance();
		
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		
		$mainMenu =& new Menu(new YLayout(), 1);

	// :: Home ::
		$mainMenu_item1 =& new MenuItemLink(
			"<span style='font-size: large'>"._("Home")."</span>", 
			$harmoni->request->quickURL("home", "welcome"), 
			($module == "home" && $action == "welcome")?TRUE:FALSE, 1);
		$mainMenu->add($mainMenu_item1, "100%", null, LEFT, CENTER);


		$mainMenu_item2 =& new MenuItemLink(
			"<span style='font-size: large'>"._("Collections")."</span>", 
			$harmoni->request->quickURL("collections", "namebrowse"), 
			($module == "collections" && $action == "main")?TRUE:FALSE,1);
		$mainMenu->add($mainMenu_item2, "100%", null, LEFT, CENTER);
		
		// Collection browse links.
		// Just show if we are not in a particular collection.
		if (ereg("collection(s)?|asset", $module)) {			
			// Collection root
			if (ereg("^(collection|asset)$", $module)) {
				// Name browse
				$mainMenu_item5 =& new MenuItemLink("<span style='font-size: small'> - - ".
						_("Collection")."</span>", 
						$harmoni->request->quickURL("collection", "browse",
							array('collection_id' => $harmoni->request->get('collection_id'))),
						($module == "collection")?TRUE:FALSE,1
				);
				$mainMenu->add($mainMenu_item5, "100%", null, LEFT, CENTER);
			}
		}
		
		$mainMenu_item6 =& new MenuItemLink("<span style='font-size: large'>"._("Exhibitions")."</span>", 
			$harmoni->request->quickURL("exhibitions", "browse"), 
			(ereg("^exhibition.*",$module))?TRUE:FALSE,1);
		$mainMenu->add($mainMenu_item6, "100%", null, LEFT, CENTER);
		
		foreach (array_keys($_SESSION) as $key) {
			if (ereg("^add_slideshow_wizard_(.+)", $key, $matches)) {
				$exhibitionAssetId = $matches[1];
	
				$item =& new MenuItemLink("<span style='font-size: medium'> - ".
						_("SlideShow")."</span>", 
						$harmoni->request->quickURL("exhibitions", "add_slideshow", 
							array("exhibition_id" => $exhibitionAssetId)), 
						($module == "exhibitions" && $action == "add_slideshow" && RequestContext::value("exhibition_id") == $exhibitionAssetId)?TRUE:FALSE,1
				);
				$mainMenu->add($item, "100%", null, LEFT, CENTER);
			}
		}
		
		$mainMenu_item7 =& new MenuItemLink("<span style='font-size: large'>"._("Admin Tools")."</span>", 
			$harmoni->request->quickURL("admin", "main"), 
			(ereg("^admin$",$module))?TRUE:FALSE, 1);
		$mainMenu->add($mainMenu_item7, "100%", null, LEFT, CENTER);
	
		return $mainMenu;
	}
}

?>