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

   function printRepositoryFunctionLinksExh (& $harmoni, & $repository) {
		if (!defined("AZ_ACCESS"))
			throwError(new Error("You must define an id for AZ_ACCESS", "concerto.exhibition", true));
		if (!defined("AZ_VIEW"))
			throwError(new Error("You must define an id for AZ_VIEW", "concerto.exhibition", true));
		if (!defined("AZ_EDIT"))
			throwError(new Error("You must define an id for AZ_EDIT", "concerto.exhibition", true));
		if (!defined("AZ_DELETE"))
			throwError(new Error("You must define an id for AZ_DELETE", "concerto.exhibition", true));
		if (!defined("AZ_ADD_CHILDREN"))
			throwError(new Error("You must define an id for AZ_ADD_CHILDREN", "concerto.exhibition", true));

		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$repositoryId =& $repository->getId();

		$links = array();

		$actionString = $harmoni->getCurrentAction();

		if ($authZ->isUserAuthorized($idManager->getId(AZ_ACCESS), $repositoryId)) {
			if ($actionString != "exhibition.browse") {
				$links[] = "<a href='".MYURL."/exhibition/browse/".$repositoryId->getIdString()."/'>";
				$links[count($links) - 1] .= _("browse")."</a>";
			} else {
				$links[] = _("browse");
			}

			if ($actionString != "exhibition.search") {
				$links[] = "<a href='".MYURL."/exhibition/search/".$repositoryId->getIdString()."/'>";
				$links[count($links) - 1] .= _("search")."</a>";
			} else {
				$links[] = _("search");
			}
    }

		if ($authZ->isUserAuthorized($idManager->getId(AZ_EDIT), $repositoryId)) {
			$links[] = "<a href='".MYURL."/exhibition/edit/".$repositoryId->getIdString()."/'>";
			$links[count($links) - 1] .= _("edit")."</a>";
		}

   	if ($authZ->isUserAuthorized($idManager->getId(AZ_DELETE), $repositoryId)) {
			if ($actionString != "exhibition.delete") {
				ob_start();
				print "<a href='Javascript:deleteExhibition".$repositoryId."();'";
				print ">";
				print _("delete")."</a>";

				print "\n<script type='text/javascript'>\n//<![CDATA[";
				print "\n	function deleteExhibition".$repositoryId."() {";
				print "\n	var url;";
				print "\n		url = '".MYURL."/exhibition/delete/".$repositoryId->getIdString()."/";
				if (ereg("^exhibition\..*$", $actionString))
					print "exhibition/browse/".$repositoryId->getIdString()."/';";
				else
					print implode("/", $harmoni->pathInfoParts)."/';";
				print "\n		if (confirm(\""._("Are you sure you want to delete this Exhibition?")."\")) {";
				print "\n			window.location = url;";
				print "\n		}";
				print "\n	}";
				print "\n//]]>\n</script>";

				$links[] = ob_get_contents();
				ob_end_clean();
			} else {
				$links[] = _("delete");
			}
		}

	 	if ($authZ->isUserAuthorized($idManager->getId(AZ_ADD_CHILDREN), $repositoryId)) {
			$links[] = "<a href='".MYURL."/asset/add/".$repositoryId->getIdString()."/".implode("/",$harmoni->pathInfoParts)."'>";
			$links[count($links) - 1] .= _("add asset")."</a>";
		}

		print  implode("\n\t | ", $links);
	}

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
		
// 		$links[] = "<a href='".MYURL."/collection/view/".$repositoryId->getIdString()."/'>";
// 		$links[count($links) - 1] .= _("view")."</a>";
		
		$actionString = $harmoni->getCurrentAction();
		
		if ($authZ->isUserAuthorized($idManager->getId(AZ_ACCESS), $repositoryId)) {
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
		}
		
		if ($authZ->isUserAuthorized($idManager->getId(AZ_EDIT), $repositoryId)) {
			$links[] = "<a href='".MYURL."/collection/edit/".$repositoryId->getIdString()."/'>";
			$links[count($links) - 1] .= _("edit")."</a>";
		}
	 	
	 	if ($authZ->isUserAuthorized($idManager->getId(AZ_ADD_CHILDREN), $repositoryId)) {
			$links[] = "<a href='".MYURL."/asset/add/".$repositoryId->getIdString()."/".implode("/",$harmoni->pathInfoParts)."'>";
			$links[count($links) - 1] .= _("add asset")."</a>";
		}
		
		print  implode("\n\t | ", $links);
	}
	
	
	
}

?>
