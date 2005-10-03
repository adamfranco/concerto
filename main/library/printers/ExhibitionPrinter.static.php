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

class ExhibitionPrinter {
		
	/**
	 * Die constructor for static class
	 */
	function ExhibitionPrinter () {
		die("Static class AssetPrinter can not be instantiated.");
	}
	
	/**
	 * Print links for the various functions that are possible to do with this
	 * Asset.
	 * 
	 * @param object Asset $asset The Asset to print the links for.
	 * @return void
	 * @access public
	 * @date 8/6/04
	 */
	function printFunctionLinks (&$asset, $repositoryId = NULL) {
		$harmoni =& Harmoni::instance();
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		$assetId =& $asset->getId();
		if ($repositoryId === NULL) {
			$repository =& $asset->getRepository();
			$repositoryId =& $repository->getId();
		}
		
		$links = array();
		
		$actionString = $harmoni->getCurrentAction();
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.access"), $asset->getId())) {
// 			$children =& $asset->getAssets();
// 			if ($children->hasNext()) {
				if ($actionString != "exhibitions.browse_exhibition" 
					|| $assetId->getIdString() != $harmoni->request->get('exhibition_id')) 
				{
					$links[] = "<a href='"
						.$harmoni->request->quickURL("exhibitions", "browse_exhibition", 
							array("exhibition_id" => $assetId->getIdString()))
						."'>";
					$links[count($links) - 1] .= _("browse")."</a>";
				} else {
					$links[] = _("browse");
				}
// 			}
			$harmoni->request->startNamespace('export_exhibition');
			if($actionString != "exhibitions.export_exhibition") {
				$links[] = "<a href='".$harmoni->request->quickURL(
					"exhibitions", "export", array("exhibition_id" =>
					$assetId->getIdString()))."'>";
				$links[count($links) - 1] .= _("export")."</a>";
			} else {
				$links[] = _("export");
			}
			$harmoni->request->endNamespace();
		}
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.modify"), $asset->getId())) {
			$harmoni->request->startNamespace('modify_exhibition');
			if ($actionString != "exhibitions.modify_exhibition") {
				$links[] = "<a href='"
					.$harmoni->request->quickURL("exhibitions", "modify_exhibition", 
						array("exhibition_id" => $assetId->getIdString()))
					."'>";
				$links[count($links) - 1] .= _("modify")."</a>";
			} else {
				$links[] = _("modify");
			}
			$harmoni->request->endNamespace();
		}
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.delete"), $asset->getId())) {
			if ($actionString != "exhibitions.delete") {
				$harmoni->history->markReturnURL("concerto/exhibitions/delete-return");
				ob_start();
				print "<a href='Javascript:deleteExhibition(\"".$assetId->getIdString()."\", \"".$harmoni->request->quickURL("exhibitions", "delete", array("exhibition_id" => $assetId->getIdString()))."\");'";
				print ">";
				print _("delete")."</a>";
				
				$links[] = ob_get_contents();
				ob_end_clean();
				
				print "\n<script type='text/javascript'>\n//<![CDATA[";
				print "\n	function deleteExhibition(assetId, url) {";
				print "\n		if (confirm(\""._("Are you sure you want to delete this Exhibition and all of its Slide-Shows?")."\")) {";
				print "\n			window.location = url;";
				print "\n		}";
				print "\n	}";
				print "\n//]]>\n</script>\n";
			} else {
				$links[] = _("delete");
			}
		}
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.add_children"), $asset->getId())) {
// 			if (ereg("^exhibitions\..*$", $actionString) 
// 				&& $harmoni->request->get("exhibition_id") == $assetId->getIdString()) 
// 			{
				$links[] = "<a href='"
					.$harmoni->request->quickURL("exhibitions", "add_slideshow",
						array("exhibition_id" => $assetId->getIdString()))
					."'>";
				$links[count($links) - 1] .= _("add a slideshow")."</a>";
			}
// 		}
		
		print  implode("\n\t | ", $links);
	}
	
	
	
}

?>