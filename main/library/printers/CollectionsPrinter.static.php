<?php
/**
 * @package concerto.printers
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/**
 * A static printer class for printing common asset info
 *
 * @package concerto.printers
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

class CollectionsPrinter {
		
	/**
	 * Die constructor for static class
	 */
	function CollectionsPrinter () {
		die("Static class CollectionsPrinter can not be instantiated.");
	}
	
	/**
	 * Print links for the various functions that are possible to do.
	 * 
	 * @return void
	 * @access public
	 * @date 8/6/04
	 */
	function printFunctionLinks () {
		$links = array();
		$harmoni =& Harmoni::instance();
		$actionString = $harmoni->getCurrentAction();
		
		if ($actionString != "collections.namebrowse" ) {
			$links[] = "<a href='"
				.$harmoni->request->quickURL("collections", "namebrowse")
				."'>"
				._("browse")
				."</a>";
		} else {
			$links[] = _("browse");
		}
					
		if ($actionString != "collections.typebrowse" ) {
			$links[] = "<a href='"
				.$harmoni->request->quickURL("collections", "typebrowse")
				."'>"
				._("browse by type")
				."</a>";
		} else {
			$links[] = _("browse by type");
		}
		
		if ($actionString != "collections.search" ) {
			$links[] = "<a href='"
				.$harmoni->request->quickURL("collections", "search")
				."'>"
				._("search")
				."</a>";
		} else {
			$links[] = _("search");
		}
		
		// If the user is authorized, allow them to create a new collection.
		require_once(MYDIR."/main/modules/collection/create.act.php");
		if (createAction::isAuthorizedToExecute()) {
			$links[] = "<a href='"
				.$harmoni->request->quickURL("collection", "create")
				."'>"
				._("Create a new <em>Collection</em>")
				."</a>";

			$links[] = "<a href='".
				$harmoni->request->quickURL("collections", "import").
				"'>".
				_("Import a <em>Collection</em>").
				"</a>";
		} else {
			$links[] = _("import");
		}
			
			
/******************* FOR EXPORTING ALL THE COLLECTIONS??? ****************/	
		if (/*admin*/FALSE) {
			$harmoni->request->startNamespace('export');
			if ($actionString != "collections.export") {
				$links[] = "<a href='".$harmoni->request->quickURL(
					"collections", "export")."'>";
				$links[count($links) - 1] .= _("export")."</a>";
			} else {
				$links[] = _("export");
			}
			$harmoni->request->endNamespace();
		}		
		print  implode("\n\t | ", $links);
	}
	
}

?>