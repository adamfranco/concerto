<?

/**
 * The MenuGenerator class is a static class used for the generation of Menus in
 * Concerto.
 * @package concerto.display
 * @author Adam Franco
 * @access public
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
		$parts = explode(".", $actionString);
		$module = $harmoni->pathInfoParts[0];
		$action = $harmoni->pathInfoParts[1];
		
		$mainMenu =& new Menu(new YLayout(), 1);


$mainMenu_item1 =& new MenuItemLink("<span style='font-size: large'>"._("Home")."</span>", 
				MYURL."/home/welcome/", 
				($module == "home" && $action == "welcome")?TRUE:FALSE,1);
				
$mainMenu->add($mainMenu_item1, "100%", null, LEFT, CENTER);


$mainMenu_item2 =& new MenuItemLink("<span style='font-size: large'>"._("Collections")."</span>", 
				MYURL."/collections/main/", 
				($module == "collections" && $action == "main")?TRUE:FALSE,1);

$mainMenu->add($mainMenu_item2, "100%", null, LEFT, CENTER);
// Collection browse links.
// Just show if we are not in a particular collection.
if (ereg("collection(s)?|asset", $module)) {
			// Name browse
			$mainMenu_item3 =& new MenuItemLink("<span style='font-size: medium'> - ".
					_("By Name")."</span>", 
					MYURL."/collections/namebrowse/", 
					($module == "collections" && $action == "namebrowse")?TRUE:FALSE,1
			);
			$mainMenu->add($mainMenu_item3, "100%", null, LEFT, CENTER);
			
			// Type browse
			$mainMenu_item4 =& new MenuItemLink("<span style='font-size: medium'> - ".
					_("By Type")."</span>", 
					MYURL."/collections/typebrowse/", 
					($module == "collections" && ($action == "typebrowse" || $action == "browsetype"))?TRUE:FALSE,1
			);
			$mainMenu->add($mainMenu_item4, "100%", null, LEFT, CENTER);
			if (ereg("^(collection|asset)$", $module)) {
				// Name browse
				$mainMenu_item5 =& new MenuItemLink("<span style='font-size: small'> - - ".
						_("Collection")."</span>", 
						MYURL."/collection/browse/".$harmoni->pathInfoParts[2]."/", 
						($module == "collection")?TRUE:FALSE,1
				);
				$mainMenu->add($mainMenu_item5, "100%", null, LEFT, CENTER);
			}
		}
		
$mainMenu_item6 =& new MenuItemLink("<span style='font-size: large'>"._("Exhibitions")."</span>", 
				MYURL."/exhibitions/main/", 
				(ereg("^exhibition.*",$module))?TRUE:FALSE,1);
$mainMenu_item7 =& new MenuItemLink("<span style='font-size: large'>"._("Admin Tools")."</span>", 
				MYURL."/admin/main/", 
				(ereg("^admin$",$module))?TRUE:FALSE, 1);

			

$mainMenu->add($mainMenu_item6, "100%", null, LEFT, CENTER);
$mainMenu->add($mainMenu_item7, "100%", null, LEFT, CENTER);
	
		return $mainMenu;
	}
}

?>