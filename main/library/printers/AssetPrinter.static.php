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

class AssetPrinter {
		
	/**
	 * Die constructor for static class
	 */
	function AssetPrinter () {
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
	function printAssetFunctionLinks (& $harmoni, & $asset, $repositoryId = NULL) {
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
			if ($actionString != "asset.view") {
				$links[] = "<a href='"
					.$harmoni->request->quickURL("asset", "view",
						array("collection_id" => $repositoryId->getIdString(),
						"asset_id" => $assetId->getIdString()))
					."'>";
				$links[count($links) - 1] .= _("view")."</a>";
			} else {
				$links[] = _("view");
			}
		}
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.access"), $asset->getId())) {
			$children =& $asset->getAssets();
			if ($children->hasNext()) {
				if ($actionString != "asset.browse" 
					|| $assetId->getIdString() != $harmoni->request->get('asset_id')) 
				{
					$links[] = "<a href='"
						.$harmoni->request->quickURL("asset", "browse", 
							array("collection_id" => $repositoryId->getIdString(),
							"asset_id" => $assetId->getIdString()))
						."'>";
					$links[count($links) - 1] .= _("browse")."</a>";
				} else {
					$links[] = _("browse");
				}
			}
		}
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.modify"), $asset->getId())) {
			if ($actionString != "asset.editview") {
				$links[] = "<a href='"
					.$harmoni->request->quickURL("asset", "editview", 
						array("collection_id" => $repositoryId->getIdString(), 
						"asset_id" => $assetId->getIdString()))
					."'>";
				$links[count($links) - 1] .= _("edit")."</a>";
			} else {
				$links[] = _("edit");
			}
		}
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.delete"), $asset->getId())) {
			if ($actionString != "asset.delete") {
				$harmoni->history->markReturnURL("concerto/asset/delete-return");
				ob_start();
				print "<a href='Javascript:deleteAsset(\"".$assetId->getIdString()."\", \"".$repositoryId->getIdString()."\", \"".$harmoni->request->quickURL("asset", "delete", array("collection_id" => $repositoryId->getIdString(), "asset_id" => $assetId->getIdString()))."\");'";
				print ">";
				print _("delete")."</a>";
				
				$links[] = ob_get_contents();
				ob_end_clean();
				
				print "\n<script type='text/javascript'>\n//<![CDATA[";
				print "\n	function deleteAsset(assetId, repositoryId, url) {";
				print "\n		if (confirm(\""._("Are you sure you want to delete this Asset?")."\")) {";
				print "\n			window.location = url;";
				print "\n		}";
				print "\n	}";
				print "\n//]]>\n</script>\n";
			} else {
				$links[] = _("delete");
			}
		}
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.add_children"), $asset->getId())) {
			if (ereg("^asset\..*$", $actionString) 
				&& $harmoni->request->get("asset_id") == $assetId->getIdString()) 
			{
				$links[] = "<a href='"
					.$harmoni->request->quickURL("asset", "add",
						array("collection_id" => $repositoryId->getIdString(),
						"parent" => $assetId->getIdString()))
					."'>";
				$links[count($links) - 1] .= _("add child asset")."</a>";
			}
		}
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId())) {
			$harmoni->request->startNamespace("basket");
			$links[] = "<a href='"
				.$harmoni->request->quickURL("basket", "add",
					array("asset_id" => $assetId->getIdString()))
				."'>";
			$links[count($links) - 1] .= _("add to basket")."</a>";
			$harmoni->request->endNamespace();
			$harmoni->history->markReturnURL("polyphony/basket", $harmoni->request->mkURLWithPassthrough());
		}
		
		print  implode("\n\t | ", $links);
	}
	
	
	
}

?>