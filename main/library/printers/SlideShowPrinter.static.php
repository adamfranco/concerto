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

class SlideShowPrinter {
		
	/**
	 * Die constructor for static class
	 */
	function SlideShowPrinter () {
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
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId())) {
			$links[] = "<a href='"
					.$harmoni->request->quickURL("asset", "view", 
						array("asset_id" => $assetId->getIdString()))
					."'>";
				$links[count($links) - 1] .= _("view as asset (temporary)")."</a>";
		}
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId())) {
			$viewertheme = 'black';
			ob_start();
			print "<a href='Javascript:openViewer(";
			print '"'.$asset->getDisplayName().'", ';
			print '"'.VIEWER_URL."?&vtheme=".$viewertheme."&inputtype=concerto_slideshow&input=".$assetId->getIdString().'"';
			print ")'>";
			print _("view")."</a>";
			
			$links[] = ob_get_contents();
			ob_end_clean();
			
			print "\n<script type='text/javascript'>\n//<![CDATA[";
			print "\n	function openViewer(name, url) {";
			print "\n		var win = window.open(\"\",name,";
			print "\"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500\");";
			print "\n		win.document.location=url;";
			print "\n		win.focus();";
			print "\n	}";
			print "\n//]]>\n</script>\n";

			if ($actionString != "exhibitions.view_slideshow" 
				|| $assetId->getIdString() != $harmoni->request->get('slideshow_id')) 
			{
				$links[] = "<a href='"
					.$harmoni->request->quickURL("exhibitions", "view_slideshow", 
						array("slideshow_id" => $assetId->getIdString()))
					."'>";
				$links[count($links) - 1] .= _("view thumbnails")."</a>";
			} else {
				$links[] = _("view thumbnails");
			}
		}
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.modify"), $asset->getId())) {
			$harmoni->request->startNamespace('modify_slideshow');
			if ($actionString != "exhibitions.modify_slideshow") {
				$links[] = "<a href='"
					.$harmoni->request->quickURL("exhibitions", "modify_slideshow", 
						array("slideshow_id" => $assetId->getIdString()))
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
				print "<a href='Javascript:deleteSlideShow(\"".$assetId->getIdString()."\", \"".$harmoni->request->quickURL("exhibitions", "delete", array("exhibition_id" => $assetId->getIdString()))."\");'";
				print ">";
				print _("delete")."</a>";
				
				$links[] = ob_get_contents();
				ob_end_clean();
				
				print "\n<script type='text/javascript'>\n//<![CDATA[";
				print "\n	function deleteSlideShow(assetId, url) {";
				print "\n		if (confirm(\""._("Are you sure you want to delete this Slide-Show?")."\")) {";
				print "\n			window.location = url;";
				print "\n		}";
				print "\n	}";
				print "\n//]]>\n</script>\n";
			} else {
				$links[] = _("delete");
			}
		}
		
		print  implode("\n\t | ", $links);
	}
	
	
	
}

?>