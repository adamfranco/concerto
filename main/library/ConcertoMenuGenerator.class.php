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
		
		$menu =& new VerticalMenuLayout(MENU_WIDGET, 2);

	// :: Home ::
		$menu->addComponent(
			new LinkMenuItem(_("Home"), 
				MYURL."/home/welcome/", 
				($module == "home" && $action == "welcome")?TRUE:FALSE)
		);
		
	// :: Collections ::
		// Main Collections link.
		$menu->addComponent(
			new LinkMenuItem(
				_("Collections"), 
				MYURL."/collections/main/", 
				(ereg("^collection.*",$module))?TRUE:FALSE)
		);
		
		// Collection browse links.
		// Just show if we are not in a particular collection.
		if ($module == "collections") {
			// Name browse
			$menu->addComponent(
				new LinkMenuItem(" - ".
					_("By Name"), 
					MYURL."/collections/namebrowse/", 
					($module == "collections" && $action == "namebrowse")?TRUE:FALSE)
			);
			// Name browse
			$menu->addComponent(
				new LinkMenuItem(" - ".
					_("By Type"), 
					MYURL."/collections/typebrowse/", 
					($module == "collections" && $action == "typebrowse")?TRUE:FALSE)
			);		}
		
	// :: Exhibitions ::
		// Main Exhibitions link.
		$menu->addComponent(
			new LinkMenuItem(
			_("Exhibitions"), 
			MYURL."/exhibitions/main/", 
			(ereg("^exhibition.*",$module))?TRUE:FALSE)
		);
		
	
		return $menu;
	}
}

?>