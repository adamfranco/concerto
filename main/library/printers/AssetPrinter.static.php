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
	function printAssetFunctionLinks (& $harmoni, &$asset, $repositoryId = NULL, $assetNum = 0, $includeEditDelete = true) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		$assetId =& $asset->getId();
		if ($repositoryId === NULL) {
			$repository =& $asset->getRepository();
			$repositoryId =& $repository->getId();
		}
		
		$links = array();
		
		/*********************************************************
		 * Parameters to pass on through our links
		 *********************************************************/
		// If we are in an asset, the viewer should contain the asset followed
		// by slides for each of its children
		$actionString = $harmoni->getCurrentAction();
		if (ereg("^asset\..*$", $actionString))	{
			$xmlModule = 'asset';
			$xmlAction = 'browsexml';
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
			$xmlAction = 'browse_outline_xml';
			$xmlAssetIdString = $assetId->getIdString();
			$xmlStart = $assetNum - 1;
		}
		$params = array("collection_id" => $repositoryId->getIdString(),
					"asset_id" => $xmlAssetIdString,
					RequestContext::name("starting_number") => RequestContext::value("starting_number"),
					RequestContext::name("limit_by_type") => RequestContext::value("limit_by_type"),
					RequestContext::name("order") => RequestContext::value("order"),
					RequestContext::name("direction") => RequestContext::value("direction"),
					RequestContext::name("type") => RequestContext::value("type"),
					RequestContext::name("searchtype") => RequestContext::value("searchtype"));
						
		if (RequestContext::value("searchtype")) {
			$searchModuleManager =& Services::getService("RepositorySearchModules");
			foreach ($searchModuleManager->getCurrentValues(Type::fromString(RequestContext::value("searchtype"))) as $key => $value) {
				$params[$key] = $value;
			}
		}
		
		// if we are limiting by type
		if (RequestContext::value("limit_by_type") == 'true') {
			$types =& $repository->getAssetTypes();
			$selectedTypes = array();
			while ($types->hasNext()) {
				$type =& $types->next();
				if (RequestContext::value("type___".Type::typeToString($type)) == 'true')
					$params[RequestContext::name("type___".Type::typeToString($type))] = 
						RequestContext::value("type___".Type::typeToString($type));
			}
		}
		
		// Authorization Icon
		print AuthZPrinter::getAZIcon($assetId);
		print " &nbsp; ";
		
		
	//===== View Links =====/
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$assetId)) {
		
		//===== Viewer Link =====//
			// Add the options panel script to the header
			$outputHandler =& $harmoni->getOutputHandler();
			$outputHandler->setHead($outputHandler->getHead()
			."\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/AssetOptionsPanel.js'></script>"
			."\n\t\t<link rel='stylesheet' type='text/css' href='".MYPATH."/javascript/AssetOptionsPanel.css' />");
			
			ob_start();
			$viewerUrl = VIEWER_URL."?&amp;source=";
			$viewerUrl .= urlencode($harmoni->request->quickURL($xmlModule, $xmlAction, $params));
			$viewerUrl .= '&amp;start='.$xmlStart;
			
			print "<a href='#' onclick=\"Javascript:AssetOptionsPanel.run('".$repositoryId->getIdString()."', '".$assetId->getIdString()."', this, [";
			$toShow = array();
			if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$assetId)) 
			{
				$toShow[] = "'view'";
			}
			
			if ($authZ->isUserAuthorizedBelow(
				$idManager->getId("edu.middlebury.authorization.view"),
				$assetId)) 
			{
				$children =& $asset->getAssets();
				if ($children->hasNext()) {
					if ($actionString != "asset.browse" ||
							$assetId->getIdString() != 
							$harmoni->request->get('asset_id')) 
					{
						$toShow[] = "'browse'";
					}
				}
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
				// If we are viewing the asset and we delete it, we can't return
				// to viewing it.
				if (ereg("^asset\..*$", $actionString) && 
						$harmoni->request->get("asset_id") == 
						$assetId->getIdString())
				{
					$deleteParams = $params;
					unset ($deleteParams['asset_id']);
					$harmoni->history->markReturnURL("concerto/asset/delete-return",
						$harmoni->request->mkURL('collection', 'browse', $deleteParams));
				} 
				// otherwise, go bact to where we are.
				else {
					$harmoni->history->markReturnURL("concerto/asset/delete-return",
						$harmoni->request->mkURL(null, null, $params));
				}
				
				$toShow[] = "'delete'";
			}
			
			if ($authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.add_children"),
					$assetId)) 
			{
				$toShow[] = "'add_children'";
			}
			
			print implode(", ", $toShow);
			print "], '".$viewerUrl."'); return false;\">"._("Options...")."</a> ";
					
			$links[] = ob_get_clean();

		//===== Details Link =====//
// 			if ($actionString != "asset.view") {
// 				$links[] = "<a href='".$harmoni->request->quickURL(
// 					"asset", "view",
// 					array("collection_id" => $repositoryId->getIdString(),
// 					"asset_id" => $assetId->getIdString()))."'>";
// 				$links[count($links) - 1] .= _("Details")."</a>";
// 			} else
// 				$links[] = _("Details");
		//===== Export Link =====//
// 			if (ereg("^asset\..*$", $actionString) && 
// 					$harmoni->request->get("asset_id") == 
// 					$assetId->getIdString()) {
// 				$harmoni->request->startNamespace('export');
// 				$links[] = "<a href='".$harmoni->request->quickURL(
// 					"asset", "export",
// 					array("collection_id" => $repositoryId->getIdString(),
// 					"asset_id" => $assetId->getIdString()))."'>";
// 				$links[count($links) - 1] .= _("Export")."</a>";
// 				$harmoni->request->endNamespace();
// 			}
 		}
	//===== Browse Link =====//
		if ($authZ->isUserAuthorizedBelow(
				$idManager->getId("edu.middlebury.authorization.view"),
				$assetId)) {
			$children =& $asset->getAssets();
			if ($children->hasNext()) {
				if ($actionString != "asset.browse" ||
						$assetId->getIdString() != 
						$harmoni->request->get('asset_id')) {
					$links[] = "<a href='".
						$harmoni->request->quickURL("asset", "browseAsset", 
						array("collection_id" => $repositoryId->getIdString(),
						"asset_id" => $assetId->getIdString()))."'>";
					$links[count($links) - 1] .= _("Browse")."</a>";
				} else
					$links[] = _("Browse");
			}
		}
	//===== Edit Link =====//	
// 		$harmoni->history->markReturnURL("concerto/asset/edit-return",
// 			$harmoni->request->mkURL(null, null, $params));
// 		
// 		if ($includeEditDelete) {
// 			if ($authZ->isUserAuthorized(
// 					$idManager->getId("edu.middlebury.authorization.modify"),
// 					$assetId)) {
// 				if ($actionString != "asset.edit") {
// 					$links[] = "<a href='".$harmoni->request->quickURL(
// 						"asset", "edit", 
// 						array("collection_id" => $repositoryId->getIdString(), 
// 						"assets" => $assetId->getIdString()))."'>";
// 					$links[count($links) - 1] .= _("Edit")."</a>";
// 				} else
// 					$links[] = _("Edit");
// 			}
		//===== Delete Link =====//
// 			if ($authZ->isUserAuthorized(
// 					$idManager->getId("edu.middlebury.authorization.delete"),
// 					$assetId)) 
// 			{
// 				// If we are viewing the asset and we delete it, we can't return
// 				// to viewing it.
// 				if (ereg("^asset\..*$", $actionString) && 
// 						$harmoni->request->get("asset_id") == 
// 						$assetId->getIdString())
// 				{
// 					$deleteParams = $params;
// 					unset ($deleteParams['asset_id']);
// 					$harmoni->history->markReturnURL("concerto/asset/delete-return",
// 						$harmoni->request->mkURL('collection', 'browse', $deleteParams));
// 				} 
// 				// otherwise, go bact to where we are.
// 				else {
// 					$harmoni->history->markReturnURL("concerto/asset/delete-return",
// 						$harmoni->request->mkURL(null, null, $params));
// 				}
// 				ob_start();
// 				print "<a href='Javascript:deleteAsset(\"".$assetId->getIdString().
// 					"\", \"".$repositoryId->getIdString()."\", \"".
// 					$harmoni->request->quickURL("asset", "delete",
// 					array("collection_id" => $repositoryId->getIdString(),
// 					"asset_id" => $assetId->getIdString()))."\");'>";
// 				print _("Delete")."</a>";
// 				$links[] = ob_get_contents();
// 				ob_end_clean();
// 				
// 				print "\n<script type='text/javascript'>\n//<![CDATA[";
// 				print "\n	function deleteAsset(assetId, repositoryId, url) {";
// 				print "\n		if (confirm(\""._("Are you sure you want to delete this Asset?")."\")) {";
// 				print "\n			window.location = url;";
// 				print "\n		}";
// 				print "\n	}";
// 				print "\n//]]>\n</script>\n";
// 			}
		
		//===== Add Child Asset Link =====//
// 			if ($authZ->isUserAuthorized(
// 					$idManager->getId("edu.middlebury.authorization.add_children"),
// 					$assetId)) {
// 				if (ereg("^asset\..*$", $actionString) && 
// 						$harmoni->request->get("asset_id") == 
// 						$assetId->getIdString()) {
// 					$links[] = "<a href='".$harmoni->request->quickURL(
// 						"asset", "add",
// 						array("collection_id" => $repositoryId->getIdString(),
// 						"parent" => $assetId->getIdString()))."'>";
// 					$links[count($links) - 1] .= _("Add Child <em>Asset</em>").
// 						"</a>";
// 		//===== Import Link =====//
// 	// 				$harmoni->request->startNamespace("import");
// 	// 				$links[] = "<a href='".$harmoni->request->quickURL(
// 	// 					"asset", "import",
// 	// 					array("collection_id" => $repositoryId->getIdString(), 
// 	// 					"asset_id" => $assetId->getIdString()))."'>".
// 	// 					_("Import Child <em>Asset(s)</em>")."</a>";
// 	// 				$harmoni->request->endNamespace();
// 				}
// 			}
// 		}
		
	//===== Basket Link =====//
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$assetId)) 
		{
			
			$basket =& Basket::instance();
			$links[] = $basket->getAddLink($assetId);
			
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
		
		$harmoni->history->markReturnURL("concerto/asset/delete-return");
		$harmoni->history->markReturnURL("concerto/asset/edit-return");
		
		$harmoni->request->startNamespace("AssetMultiEdit");
		
		ob_start();
		
// 		print "<input type='button' onclick='checkAllAssets();'";
// 		print "value='"._("Check All")."'/>";
// 		
// 		print "\n<br/><input type='button' onclick='uncheckAllAssets();'";
// 		print "value='"._("Un-Check All")."'/>";
// 		
// 		print "\n<br/><input type='button' onclick='editCheckedAssets();'";
// 		print "value='"._("Edit Checked")."'/>";
// 		
// 		print "\n<br/><input type='button' onclick='addCheckedAssetsToBasket();'";
// 		print "value='"._("Add Checked To Basket")."'/>";
		
		print "\n<select onchange=' ";
		print 'eval(this.value); ';
		print 'this.value="return";';
		print "'>";
		print "\n\t<option selected='selected' value='return'>"._("Commands...")."</option>";
		print "\n\t<optgroup label='"._("Select")."'>";
		print "\n\t\t<option value='checkAllAssets();'>";
		print _("Check All")."</option>";
		print "\n\t\t<option value='uncheckAllAssets();'>";
		print _("Un-Check All")."</option>";
		print "\n\t</optgroup>";
		print "\n\t<optgroup label='"._("Selection")."'>";
		print "\n\t<option value='addCheckedAssetsToBasket();'>";
		print _("Add Checked To Selection")."</option>";
		print "\n\t<option value='Basket.empty();'>";
		print _("Empty Selection")."</option>";	
		print "\n\t</optgroup>";
		print "\n\t<optgroup label='"._("Modify")."'>";
		print "\n\t<option value='editCheckedAssets();'>";
		print _("Edit Checked")."</option>";
		print "\n\t<option value='deleteCheckedAssets();'>";
		print _("Delete Checked")."</option>";
		print "\n\t</optgroup>";		
		print "\n</select>";
		
		$idManager =& Services::getService("Id");
		
		$checkboxName = RequestContext::name("asset");
		
		$editMultiURL = str_replace("&amp;", "&", 
			$harmoni->request->quickURL("asset", "multiedit"));
		$editSingleURL = str_replace("&amp;", "&", 
			$harmoni->request->quickURL("asset", "edit"));
		
		$deleteMultiURL = str_replace("&amp;", "&", 
			$harmoni->request->quickURL("asset", "multdelete"));
		
		$pleaseSelectStringEdit = _("Please check some Assets to edit.");
		$pleaseSelectStringDelete = _("Please check some Assets to delete.");
		$pleaseSelectStringBasket = _("Please check some Assets to add to the selection.");
		$confirmDeleteString = _("Are you sure that you wish to permenantly delete these assets?");
		$unauthorizedStringEdit = _("You are not authorized to modify XXXXXX of the Assets you have checked, they have been unchecked.");
		$unauthorizedStringDelete = _("You are not authorized to delete XXXXXX of the Assets you have checked, they have been unchecked.");
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
		var numUnauthorized = 0;
		
		for (var i = 0; i < assetElements.length; i++) {
			if (!assetElements[i].disabled && assetElements[i].checked == true) 
			{
				var authFields = document.getElementsByName(assetElements[i].name + '_can_modify_' + assetElements[i].value);
				if (authFields[0].value != 'true')
				{
					assetElements[i].checked = false;
					numUnauthorized++;
					continue;
				}
				
				if (numChecked > 0)
					assetList += ',';
				assetList += assetElements[i].value;
				numChecked++;
			}
		}
		
		if (numUnauthorized > 0) {
			var message = "$unauthorizedStringEdit";
			alert(message.replace(/XXXXXX/, numUnauthorized));
		}
		
		if (numChecked > 1)
			window.location = editMultiURL + assetList;
		else if (numChecked == 1)
			window.location = editSingleURL + assetList;
		else
			alert('$pleaseSelectStringEdit');
	}
	
	function deleteCheckedAssets() {
		var deleteMultiURL = '$deleteMultiURL';
		var assetList = '&assets=';
		var assetElements = document.getElementsByName('$checkboxName');
		var numChecked = 0;
		var numUnauthorized = 0;
		
		for (var i = 0; i < assetElements.length; i++) {
			if (!assetElements[i].disabled && assetElements[i].checked == true) {
				
				var authFields = document.getElementsByName(assetElements[i].name + '_can_delete_' + assetElements[i].value);
				if (authFields[0].value != 'true')
				{
					assetElements[i].checked = false;
					numUnauthorized++;
					continue;
				}
				
				if (numChecked > 0)
					assetList += ',';
				assetList += assetElements[i].value;
				numChecked++;
			}
		}
		
		if (numUnauthorized > 0) {
			var message = "$unauthorizedStringDelete";
			alert(message.replace(/XXXXXX/, numUnauthorized));
		}
		
		if (numChecked < 1)
			alert('$pleaseSelectStringDelete');
		
		else if (confirm('$confirmDeleteString'))
			window.location = deleteMultiURL + assetList;
	}

	/**
	 * New Adding of checked to the basket. Now uses the basket's own javascript
	 * for adding assets
	 * 
	 * @return void
	 * @access public
	 * @since 5/2/06
	 */
	function addCheckedAssetsToBasket() {
		var assetList = new Array;
		var assetElements = document.getElementsByName('$checkboxName');
		
		for (var i = 0; i < assetElements.length; i++) {
			if (!assetElements[i].disabled && assetElements[i].checked == true) {				
				assetList.push(assetElements[i].value);
				assetElements[i].checked = false;
			}
		}
		
		
		if (assetList.length >= 1)
			Basket.addAssets(assetList);
		else
			alert('$pleaseSelectStringBasket');
	}
		
		
	
// ]]>
</script>
END;
		
		$block = new Block(ob_get_contents(), HIGHLIT_BLOCK);
		ob_end_clean();
		$harmoni->request->endNamespace();
		return $block;
	}
}
?>