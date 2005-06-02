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
 * A static printer class for printing common repository info
 *
 * @package concerto.printers
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

class RepositoryPrinter {
		
	/**
	 * Die constructor for static class
	 */
	function RepositoryPrinter () {
		die("Static class RepositoryPrinter can not be instantiated.");
	}
	
	/**
	 * Print links for the various functions that are possible to do with this
	 * Repository.
	 * 
	 * @param object Repository $repository The Repository to print the links for.
	 * @return void
	 * @access public
	 * @date 8/6/04
	 */
	function printRepositoryFunctionLinks (& $harmoni, & $repository) {
		if (!defined("AZ_ACCESS"))
			throwError(new Error("You must define an id for AZ_ACCESS", "concerto.collection", true));
		if (!defined("AZ_VIEW"))
			throwError(new Error("You must define an id for AZ_VIEW", "concerto.collection", true));
		if (!defined("AZ_EDIT"))
			throwError(new Error("You must define an id for AZ_EDIT", "concerto.collection", true));
		if (!defined("AZ_DELETE"))
			throwError(new Error("You must define an id for AZ_DELETE", "concerto.collection", true));
		if (!defined("AZ_ADD_CHILDREN"))
			throwError(new Error("You must define an id for AZ_ADD_CHILDREN", "concerto.collection", true));
		
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$repositoryId =& $repository->getId();
		
		$links = array();
		
		$actionString = $harmoni->getCurrentAction();
		$url =& $harmoni->request->mkURL();	
		$url->setValue("collection_id", $repositoryId->getIdString());
		
		if ($authZ->isUserAuthorized($idManager->getId(AZ_ACCESS), $repositoryId)) {
			
			if ($actionString != "collection.browse") {
				$url->setModuleAction("collection", "browse");
				$links[] = "<a href='".$url->write()."'>";
				$links[count($links) - 1] .= _("browse")."</a>";
			} else {
				$links[] = _("browse");
			}
			
			if ($actionString != "collection.typebrowse") {
				$url->setModuleAction("collection", "typebrowse");
				$links[] = "<a href='".$url->write()."'>";
				$links[count($links) - 1] .= _("browse by type")."</a>";
			} else {
				$links[] = _("browse by type");
			}
			
			if ($actionString != "collection.search") {
				$url->setModuleAction("collection", "search");
				$links[] = "<a href='".$url->write()."'>";
				$links[count($links) - 1] .= _("search")."</a>";
			} else {
				$links[] = _("search");
			}
		}
		
		if ($authZ->isUserAuthorized($idManager->getId(AZ_EDIT), $repositoryId)) {
			$url->setModuleAction("collection", "edit");
				$links[] = "<a href='".$url->write()."'>";
			$links[count($links) - 1] .= _("edit")."</a>";
		}
	 	
	 	if ($authZ->isUserAuthorized($idManager->getId(AZ_ADD_CHILDREN), $repositoryId)) {
			$url->setModuleAction("asset", "add");
				$links[] = "<a href='".$url->write()."'>";
			$links[count($links) - 1] .= _("add asset")."</a>";
		}
		
		print  implode("\n\t | ", $links);
	}
	
	
	
}

?>