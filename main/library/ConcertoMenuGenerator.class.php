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
	function & generateMainMenu($actionString) {
		$parts = explode(".", $actionString);
		$module = $parts[0];
		$action = $parts[1];
		
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
			// Name browse
			$menu->addComponent(
				new LinkMenuItem("<span style='font-size: medium'> - ".
					_("By Type")."</span>", 
					MYURL."/collections/typebrowse/", 
					($module == "collections" && $action == "typebrowse")?TRUE:FALSE)
			);
			if (ereg("^(collection|asset)$", $module)) {
				// Name browse
				$menu->addComponent(
					new LinkMenuItem("<span style='font-size: small'> - - ".
						_("Collection")."</span>", 
						MYURL."/collection/browse/".$part[2]."/", 
						($module == "collection" && $action == "browse")?TRUE:FALSE)
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