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

abstract class CollectionsPrinter {
		
	/**
	 * Print links for the various functions that are possible to do.
	 * 
	 * @return void
	 * @access public
	 * @date 8/6/04
	 * @static
	 */
	static function printFunctionLinks () {
		$links = array();
		$harmoni = Harmoni::instance();
		$actionString = $harmoni->getCurrentAction();
	//===== NameBrowse Link =====//
		if ($actionString != "collections.namebrowse" ) {
			$links[] = "<a href='"
				.$harmoni->request->quickURL("collections", "namebrowse")
				."'>"
				._("Browse")
				."</a>";
		} else {
			$links[] = _("Browse");
		}
	//===== TypeBrowse Link =====//
		if ($actionString != "collections.typebrowse" ) {
			$links[] = "<a href='"
				.$harmoni->request->quickURL("collections", "typebrowse")
				."'>"
				._("Browse by Type")
				."</a>";
		} else {
			$links[] = _("browse by type");
		}
	//===== Search Link =====//
		if ($actionString != "collections.search" ) {
			$links[] = "<a href='"
				.$harmoni->request->quickURL("collections", "search")
				."'>"
				._("Search")
				."</a>";
		} else {
			$links[] = _("search");
		}	
	//===== Create Link =====//
		require_once(MYDIR."/main/modules/collection/create.act.php");
		$createAction = new createAction;
		if ($createAction->isAuthorizedToExecute()) {
// 		if (true) {
			$links[] = "<a href='"
				.$harmoni->request->quickURL("collection", "create")
				."'>"
				._("Create a new <em>Collection</em>")
				."</a>";
	//===== Import Link =====//
			$links[] = "<a href='".
				$harmoni->request->quickURL("collections", "import").
				"'>".
				_("Import <em>Collection(s)</em>").
				"</a>";
		}
		print  implode("\n\t | ", $links);
	}
}
?>