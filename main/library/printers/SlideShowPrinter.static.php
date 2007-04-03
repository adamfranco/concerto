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
		die("Static class SlideShowPrinter can not be instantiated.");
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
		
		// Authorization Icon
		print AuthZPrinter::getAZIcon($assetId);
		print " &nbsp; ";
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId())) {
			$viewertheme = 'black';
			ob_start();
			print "<a onclick='Javascript:window.open(";
			print '"'.VIEWER_URL."?&source=";
			print urlencode($harmoni->request->quickURL("exhibitions", "slideshowOutlineXml", 
						array("slideshow_id" => $assetId->getIdString())));
			print '", ';
			print '"'.$asset->getDisplayName().'", ';
			print '"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500"';
			print ")'>";
			print _("View")."</a>";
			
			$links[] = ob_get_contents();
			ob_end_clean();
			
// 			$links[] = "<a href='"
// 					.$harmoni->request->quickURL("exhibitions", "slideshowxml", 
// 						array("slideshow_id" => $assetId->getIdString()))
// 					."'>"._("view xml (debug)")."</a>";
			
			// Add the options panel script to the header
			if (!defined('ASSET_PANEL_LOADED')) {
				$outputHandler =& $harmoni->getOutputHandler();
				$outputHandler->setHead($outputHandler->getHead()
				."\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/AssetOptionsPanel.js'></script>"
				."\n\t\t<link rel='stylesheet' type='text/css' href='".MYPATH."/javascript/AssetOptionsPanel.css' />");
				define('ASSET_PANEL_LOADED', true);
			}
			if (!defined('SLIDESHOW_PANEL_LOADED')) {
				$outputHandler =& $harmoni->getOutputHandler();
				$outputHandler->setHead($outputHandler->getHead()
				."\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/SlideshowOptionsPanel.js'></script>");
				define('SLIDESHOW_PANEL_LOADED', true);
			}
			
			ob_start();
			$viewerUrl = VIEWER_URL."?&amp;source=";
			$viewerUrl .= urlencode($harmoni->request->quickURL("exhibitions", "slideshowOutlineXml", array("slideshow_id" => $assetId->getIdString())));
// 			$viewerUrl .= '&amp;start='.$xmlStart;
			
			$parents =& $asset->getParents();
			$exhibition =& $parents->next();
			$exhibitionId =& $exhibition->getId();
			
			print "<a href='#' onclick=\"Javascript:SlideshowOptionsPanel.run('".$exhibitionId->getIdString()."', '".$assetId->getIdString()."', this, [";
			$toShow = array();
			if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$assetId)) 
			{
				$toShow[] = "'view'";
			}
			
			if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"),
				$assetId)) 
			{
				$toShow[] = "'edit'";
			}
			
			if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.delete"),
				$assetId)) 
			{
				$deleteParams = array('exhibition_id' => RequestContext::value('exhibition_id'));
				
				// If we are viewing the asset and we delete it, we can't return
				// to viewing it.
				if (ereg("^slideshow\..*$", $actionString) && 
						$harmoni->request->get("slideshow_id") == 
						$assetId->getIdString())
				{
					$harmoni->history->markReturnURL("concerto/slideshow/delete-return",
						$harmoni->request->mkURL('exhibition', 'browse', $deleteParams));
				} 
				// otherwise, go bact to where we are.
				else {
					$harmoni->history->markReturnURL("concerto/slideshow/delete-return",
						$harmoni->request->mkURL(null, null, $deleteParams));
				}
				
				$toShow[] = "'delete'";
			}
			
			print implode(", ", $toShow);
			print "], '".$viewerUrl."'); return false;\">"._("Options...")."</a> ";
					
			$links[] = ob_get_clean();
		}
		
// 		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId())) {
// 			if ($actionString != "exhibitions.browseSlideshow") {
// 				$links[] = "<a href='".$harmoni->request->quickURL(
// 					"exhibitions", "browseSlideshow",
// 					array("asset_id" => $assetId->getIdString())).
// 					"'>";
// 				$links[count($links) - 1] .= _("Browse")."</a>";
// 			} else {
// 				$links[] = _("Browse");
// 			}
// 		}
// 		
// 		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.modify"), $asset->getId())) {
// 			$harmoni->request->startNamespace('modify_slideshow');
// 			if ($actionString != "exhibitions.modify_slideshow") {
// 				$links[] = "<a href='".$harmoni->request->quickURL(
// 					"exhibitions", "modify_slideshow",
// 					array("slideshow_id" => $assetId->getIdString())).
// 					"'>";
// 				$links[count($links) - 1] .= _("Edit")."</a>";
// 			} else {
// 				$links[] = _("Edit");
// 			}
// 			$harmoni->request->endNamespace();
// 		}
// 		
// 		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.delete"), $asset->getId())) {
// 			if ($actionString != "exhibitions.delete") {
// 				$harmoni->history->markReturnURL("concerto/exhibitions/delete-return");
// 				ob_start();
// 				print "<a href='Javascript:deleteSlideShow(\"".$assetId->getIdString()."\", \"".$harmoni->request->quickURL("exhibitions", "delete_slideshow", array("exhibition_id" => RequestContext::value('exhibition_id'), "slideshow_id" => $assetId->getIdString()))."\");'";
// 				print ">";
// 				print _("Delete")."</a>";
// 				
// 				$links[] = ob_get_contents();
// 				ob_end_clean();
// 				
// 				print "\n<script type='text/javascript'>\n//<![CDATA[";
// 				print "\n	function deleteSlideShow(assetId, url) {";
// 				print "\n		if (confirm(\""._("Are you sure you want to delete this Slide-Show?")."\")) {";
// 				print "\n			window.location = url;";
// 				print "\n		}";
// 				print "\n	}";
// 				print "\n//]]>\n</script>\n";
// 			} else {
// 				$links[] = _("Delete");
// 			}
// 		}
		
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.modify"), $asset->getId())) {
			if ($actionString != "exhibitions.browse_slideshow") {
				$setManager =& Services::getService("Sets");
				$parents =& $asset->getParents();
				$exhibition =& $parents->next();
				$exhibitionId =& $exhibition->getId();
				$exhibitionSet =& $setManager->getPersistentSet($exhibitionId);
				$position = $exhibitionSet->getPosition($assetId);
				
				$url = $harmoni->request->quickURL(
							"exhibitions", "reorder_slideshows", array(
								"exhibition_id" => $exhibitionId->getIdString(),
								"slideshow_id" => $assetId->getIdString(),
								"new_position" => "XXXXXX"));
				
				ob_start();
				print "\n<select name='reorder_".$assetId->getIdString()."'";
				print " onchange='";
				print ' var url = "'.str_replace("&amp;", "&", $url).'"; ';
				print 'window.location = url.replace(/XXXXXX/, this.value);';
				print "'>";
				for ($i = 0; $i < $exhibitionSet->count(); $i++) {
					print "\n\t<option value='".$i."'";
					if ($i == $position)
						print " selected='selected'";
					print ">".($i + 1)."</option>";
				}
				print "\n</select>";
				
				$links[] = ob_get_clean();
			}
		}
		
		print  implode("\n\t | ", $links);
	}
}
?>