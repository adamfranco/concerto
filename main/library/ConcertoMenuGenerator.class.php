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
	function & generateMainMenu($harmoni) {
		$parts = explode(".", $actionString);
		$module = $harmoni->pathInfoParts[0];
		$action = $harmoni->pathInfoParts[1];
		
		$menu =& new VerticalMenuLayout(MENU_WIDGET, 1);

	// :: Home ::
		$menu->addComponent(
			new LinkMenuItem("<span style='font-size: large'>"._("Home")."</span>", 
				MYURL."/home/welcome/", 
				($module == "home" && $action == "welcome")?TRUE:FALSE)
		);
		
	// :: Collections ::
		// Main Collections link.
		$menu->addComponent(
			new LinkMenuItem(
				"<span style='font-size: large'>"._("Collections")."</span>", 
				MYURL."/collections/main/", 
				($module == "collections" && $action == "main")?TRUE:FALSE)
		);
		
		// Collection browse links.
		// Just show if we are not in a particular collection.
		if (ereg("collection(s)?|asset", $module)) {
			// Name browse
			$menu->addComponent(
				new LinkMenuItem("<span style='font-size: medium'> - ".
					_("By Name")."</span>", 
					MYURL."/collections/namebrowse/", 
					($module == "collections" && $action == "namebrowse")?TRUE:FALSE)
			);
			// Type browse
			$menu->addComponent(
				new LinkMenuItem("<span style='font-size: medium'> - ".
					_("By Type")."</span>", 
					MYURL."/collections/typebrowse/", 
					($module == "collections" && ($action == "typebrowse" || $action == "browsetype"))?TRUE:FALSE)
			);
			if (ereg("^(collection|asset)$", $module)) {
				// Name browse
				$menu->addComponent(
					new LinkMenuItem("<span style='font-size: small'> - - ".
						_("Collection")."</span>", 
						MYURL."/collection/browse/".$harmoni->pathInfoParts[2]."/", 
						($module == "collection")?TRUE:FALSE)
				);
			}
		}
		
	// :: Exhibitions ::
		// Main Exhibitions link.
		$menu->addComponent(
			new LinkMenuItem(
			"<span style='font-size: large'>"._("Exhibitions")."</span>", 
			MYURL."/exhibitions/main/", 
			(ereg("^exhibition.*",$module))?TRUE:FALSE)
		);
		
	
		return $menu;
	}
}

?>