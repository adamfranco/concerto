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
	function printAssetFunctionLinks (& $harmoni, &$asset, $repositoryId = NULL, $assetNum = 0) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		$assetId =& $asset->getId();
		if ($repositoryId === NULL) {
			$repository =& $asset->getRepository();
			$repositoryId =& $repository->getId();
		}
		
		$links = array();
		
		$actionString = $harmoni->getCurrentAction();
	//===== View Links =====/
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$assetId)) {
		// If we are in an asset, the viewer should contain the asset followed
		// by slides for each of its children
			if (ereg("^asset\..*$", $actionString))	{
				$xmlModule = 'asset';
				$xmlAssetIdString = $harmoni->request->get("asset_id");
				
				if ($harmoni->request->get("asset_id") ==
						$assetId->getIdString())
					$xmlStart = 0;
				else
					$xmlStart = $assetNum;
			} 
		// Otherwise, the viewer should contain the asset allong with slides
		// for the other assets in the collection.
			else {
				$xmlModule = 'collection';
				$xmlAssetIdString = $assetId->getIdString();
				$xmlStart = $assetNum - 1;
			}
		//===== Viewer Link =====//
			ob_start();
			print "<a href='#' onclick='Javascript:window.open(";
			print '"'.VIEWER_URL."?&amp;source=";
			print urlencode($harmoni->request->quickURL($xmlModule, "browsexml",
						array("collection_id" => $repositoryId->getIdString(),
						"asset_id" => $xmlAssetIdString,
						RequestContext::name("limit_by") => RequestContext::value("limit_by"),
						RequestContext::name("type") => RequestContext::value("type"),
						RequestContext::name("searchtype") => RequestContext::value("searchtype"),
						RequestContext::name("searchstring") => RequestContext::value("searchstring"))));
			print '&amp;start='.$xmlStart.'", ';
			print '"'.htmlentities($asset->getDisplayName()).'", ';
			print '"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500"';
			print ")'>";
			print _("View")."</a>";			
			$links[] = ob_get_contents();
			ob_end_clean();
		//===== Details Link =====//
			if ($actionString != "asset.view") {
				$links[] = "<a href='".$harmoni->request->quickURL(
					"asset", "view",
					array("collection_id" => $repositoryId->getIdString(),
					"asset_id" => $assetId->getIdString()))."'>";
				$links[count($links) - 1] .= _("Details")."</a>";
			} else
				$links[] = _("details");
		//===== Export Link =====//
			$harmoni->request->startNamespace('export');
			$links[] = "<a href='".$harmoni->request->quickURL(
				"asset", "export",
				array("collection_id" => $repositoryId->getIdString(),
				"asset_id" => $assetId->getIdString()))."'>";
			$links[count($links) - 1] .= _("Export")."</a>";
			$harmoni->request->endNamespace();
		}
	//===== Browse Link =====//
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.access"),
				$assetId)) {
			$children =& $asset->getAssets();
			if ($children->hasNext()) {
				if ($actionString != "asset.browse" ||
						$assetId->getIdString() != 
						$harmoni->request->get('asset_id')) {
					$links[] = "<a href='".
						$harmoni->request->quickURL("asset", "browse", 
						array("collection_id" => $repositoryId->getIdString(),
						"asset_id" => $assetId->getIdString()))."'>";
					$links[count($links) - 1] .= _("Browse")."</a>";
				} else
					$links[] = _("browse");
			}
		}
	//===== Edit Link =====//
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"),
				$assetId)) {
			if ($actionString != "asset.editview") {
				$links[] = "<a href='".$harmoni->request->quickURL(
					"asset", "editview", 
					array("collection_id" => $repositoryId->getIdString(), 
					"asset_id" => $assetId->getIdString()))."'>";
				$links[count($links) - 1] .= _("Edit")."</a>";
			} else
				$links[] = _("edit");
		}
	//===== Delete Link =====//
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.delete"),
				$assetId)) {
			$harmoni->history->markReturnURL("concerto/asset/delete-return");
			ob_start();
			print "<a href='Javascript:deleteAsset(\"".$assetId->getIdString().
				"\", \"".$repositoryId->getIdString()."\", \"".
				$harmoni->request->quickURL("asset", "delete",
				array("collection_id" => $repositoryId->getIdString(),
				"asset_id" => $assetId->getIdString()))."\");'>";
			print _("Delete")."</a>";
			$links[] = ob_get_contents();
			ob_end_clean();
			
			print "\n<script type='text/javascript'>\n//<![CDATA[";
			print "\n	function deleteAsset(assetId, repositoryId, url) {";
			print "\n		if (confirm(\""._("Are you sure you want to delete this Asset?")."\")) {";
			print "\n			window.location = url;";
			print "\n		}";
			print "\n	}";
			print "\n//]]>\n</script>\n";
		}
	//===== Add Child Asset Link =====//
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$assetId)) {
			if (ereg("^asset\..*$", $actionString) && 
					$harmoni->request->get("asset_id") == 
					$assetId->getIdString()) {
				$links[] = "<a href='".$harmoni->request->quickURL(
					"asset", "add",
					array("collection_id" => $repositoryId->getIdString(),
					"parent" => $assetId->getIdString()))."'>";
				$links[count($links) - 1] .= _("Add Child <em>Asset</em>").
					"</a>";
	//===== Import Link =====//
				$harmoni->request->startNamespace("import");
				$links[] = "<a href='".$harmoni->request->quickURL(
					"asset", "import",
					array("collection_id" => $repositoryId->getIdString(), 
					"asset_id" => $assetId->getIdString()))."'>".
					_("Import Child <em>Asset(s)</em>")."</a>";
				$harmoni->request->endNamespace();
			}
		}
	//===== Basket Link =====//
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$assetId)) {
			$harmoni->request->startNamespace("basket");
			ob_start();
			print "<a href='".$harmoni->request->quickURL("basket", "add",
				array("asset_id" => $assetId->getIdString()));
			print "' title='". _('add to basket')."'>";
			print "<img src='".POLYPHONY_PATH."/main/library/Basket/icons/basketplus.png' height='25px' border='0' alt='"._('Add to <em>Basket</em>')."' />";
			print "</a>";
			
			$links[] = ob_get_contents();
			ob_end_clean();
			$harmoni->request->endNamespace();
			$harmoni->history->markReturnURL("polyphony/basket",
				$harmoni->request->mkURLWithPassthrough());
		}
		print  implode("\n\t | ", $links);
	}
	
	/**
	 * Answer a GUI component that contains controls for editing all of the
	 * selected Assets.
	 * 
	 * @return object Component
	 * @access public
	 * @since 10/19/05
	 */
	function getMultiEditOptionsBlock () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("AssetMultiEdit");
		
		ob_start();
		$idManager =& Services::getService("Id");
		
		$checkboxName = RequestContext::name("asset");
		
		$editMultiURL = str_replace("&amp;", "&", 
			$harmoni->request->quickURL("asset", "multiedit"));
		$editSingleURL = str_replace("&amp;", "&", 
			$harmoni->request->quickURL("asset", "edit"));
		
		$pleaseSelectString = _("Please select some Assets.");
		print<<<END

<script type='text/javascript'>
// <![CDATA[
	
	function checkAllAssets() {
		var assetElements = document.getElementsByName('$checkboxName');
		for (var i = 0; i < assetElements.length; i++) {
			if (!assetElements[i].disabled)
				assetElements[i].checked = true;
		}
	}
	
	function uncheckAllAssets() {
		var assetElements = document.getElementsByName('$checkboxName');
		for (var i = 0; i < assetElements.length; i++) {
			assetElements[i].checked = false;
		}
	}
	
	function editCheckedAssets() {
		var editMultiURL = '$editMultiURL';
		var editSingleURL = '$editSingleURL';
		var assetList = '&assets=';
		var assetElements = document.getElementsByName('$checkboxName');
		var numChecked = 0;
		
		for (var i = 0; i < assetElements.length; i++) {
			if (!assetElements[i].disabled && assetElements[i].checked == true) {
				if (numChecked > 0)
					assetList += ',';
				assetList += assetElements[i].value;
				numChecked++;
			}
		}
		
		if (numChecked > 1)
			window.location = editMultiURL + assetList;
		else if (numChecked == 1)
			window.location = editSingleURL + assetList;
		else
			alert('$pleaseSelectString');
	}
	
// ]]>
</script>
END;
		
		print "<input type='button' onclick='checkAllAssets();'";
		print "value='"._("Check All")."'/>";
		
		print "\n<br/><input type='button' onclick='uncheckAllAssets();'";
		print "value='"._("Un-Check All")."'/>";
		
		print "\n<br/><input type='button' onclick='editCheckedAssets();'";
		print "value='"._("Edit Checked")."'/>";
		
		$block = new Block(ob_get_contents(), 4);
		ob_end_clean();
		$harmoni->request->endNamespace();
		return $block;
	}
}
?>