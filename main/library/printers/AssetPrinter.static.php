<?php

/**
 * A static printer class for printing common asset info
 * 
 * @package concerto.printers
 * @version $Id$
 * @date $Date$
 * @copyright 2004 Middlebury College
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
		$shared =& Services::getService("Shared");
		
		$assetId =& $asset->getId();
		if ($repositoryId === NULL) {
			$repository =& $asset->getDigitalRepository();
			$repositoryId =& $repository->getId();
		}
		
		$links = array();
		
		$actionString = $harmoni->getCurrentAction();
		
		if ($authZ->isUserAuthorized($shared->getId(AZ_VIEW), $asset->getId())) {
			if ($actionString != "asset.view") {
				$links[] = "<a href='".MYURL."/asset/view/".$repositoryId->getIdString()."/".$assetId->getIdString()."/'>";
				$links[count($links) - 1] .= _("view")."</a>";
			} else {
				$links[] = _("view");
			}
		}
		
		if ($authZ->isUserAuthorized($shared->getId(AZ_ACCESS), $asset->getId())) {
			$children =& $asset->getAssets();
			if ($children->hasNext()) {
				if ($actionString != "asset.browse" || $assetId->getIdString() != $harmoni->pathInfoParts[3]) {
					$links[] = "<a href='".MYURL."/asset/browse/".$repositoryId->getIdString()."/".$assetId->getIdString()."/'>";
					$links[count($links) - 1] .= _("browse")."</a>";
				} else {
					$links[] = _("browse");
				}
				
	// 			if ($actionString != "asset.typebrowse") {
	// 				$links[] = "<a href='".MYURL."/asset/typebrowse/".$assetId->getIdString()."/'>";
	// 				$links[count($links) - 1] .= _("browse by type")."</a>";
	// 			} else {
	// 				$links[] = _("browse by type");
	// 			}
			}
		}
		
		if ($authZ->isUserAuthorized($shared->getId(AZ_EDIT), $asset->getId())) {
			if ($actionString != "asset.editview") {
				$links[] = "<a href='".MYURL."/asset/editview/".$repositoryId->getIdString()."/".$assetId->getIdString()."/'>";
				$links[count($links) - 1] .= _("edit")."</a>";
			} else {
				$links[] = _("edit");
			}
		}
		
		if ($authZ->isUserAuthorized($shared->getId(AZ_DELETE), $asset->getId())) {
			if ($actionString != "asset.delete") {
				ob_start();
				print "<a href='Javascript:deleteAsset".$assetId->getIdString()."From".$repositoryId->getIdString()."();'";
				print ">";
				print _("delete")."</a>";
				
				print "\n<script type='text/javascript'>\n//<![CDATA[";
				print "\n	function deleteAsset".$assetId->getIdString()."From".$repositoryId->getIdString()."() {";
				print "\n	var url;";
				print "\n		url = '".MYURL."/asset/delete/".$repositoryId->getIdString()."/".$assetId->getIdString()."/";
				if (ereg("^asset\..*$", $actionString))
					print "collection/browse/".$repositoryId->getIdString()."/';";
				else
					print implode("/", $harmoni->pathInfoParts)."/';";
				print "\n		if (confirm(\""._("Are you sure you want to delete this Asset?")."\")) {";
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
		
		if ($authZ->isUserAuthorized($shared->getId(AZ_ADD_CHILDREN), $asset->getId())) {
			if (ereg("^asset\..*$", $actionString) && $harmoni->pathInfoParts[3] == $assetId->getIdString()) {
				$links[] = "<a href='".MYURL."/asset/addchild/".$repositoryId->getIdString()."/".$assetId->getIdString()."/'>";
				$links[count($links) - 1] .= _("add child asset")."</a>";
			}
		}
		
		print  implode("\n\t | ", $links);
	}
	
	
	
}

?>