<?php

/**
 * A static printer class for printing common repository info
 * 
 * @package concerto.printers
 * @version $Id$
 * @date $Date$
 * @copyright 2004 Middlebury College
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
		// @todo User AuthZ to decide if we should print links.
		$repositoryId =& $repository->getId();
		
		$links = array();
		
// 		$links[] = "<a href='".MYURL."/collection/view/".$repositoryId->getIdString()."/'>";
// 		$links[count($links) - 1] .= _("view")."</a>";
		
		$actionString = $harmoni->getCurrentAction();
		
		if ($actionString != "collection.browse") {
			$links[] = "<a href='".MYURL."/collection/browse/".$repositoryId->getIdString()."/'>";
			$links[count($links) - 1] .= _("browse")."</a>";
		} else {
			$links[] = _("browse");
		}
		
		if ($actionString != "collection.typebrowse") {
			$links[] = "<a href='".MYURL."/collection/typebrowse/".$repositoryId->getIdString()."/'>";
			$links[count($links) - 1] .= _("browse by type")."</a>";
		} else {
			$links[] = _("browse by type");
		}
		
		if ($actionString != "collection.search") {
			$links[] = "<a href='".MYURL."/collection/search/".$repositoryId->getIdString()."/'>";
			$links[count($links) - 1] .= _("search")."</a>";
		} else {
			$links[] = _("search");
		}
		
	 	$links[] = "<a href='".MYURL."/collection/edit/".$repositoryId->getIdString()."/'>";
	 	$links[count($links) - 1] .= _("edit")."</a>";
	 	
	 	$links[] = "<a href='".MYURL."/asset/add/".$repositoryId->getIdString()."/".implode("/",$harmoni->pathInfoParts)."'>";
	 	$links[count($links) - 1] .= _("add asset")."</a>";
		
		print  implode("\n\t | ", $links);
	}
	
	
	
}

?>