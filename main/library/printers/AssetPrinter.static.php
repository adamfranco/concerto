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
		// @todo User AuthZ to decide if we should print links.
		$assetId =& $asset->getId();
		$links = array();
		
		$actionString = $harmoni->getCurrentAction();
		
		if ($actionString != "asset.view") {
			$links[] = "<a href='".MYURL."/asset/view/".$assetId->getIdString()."/'>";
			$links[count($links) - 1] .= _("view")."</a>";
		} else {
			$links[] = _("view");
		}
		
		$children =& $asset->getAssets();
		if ($children->hasNext()) {
			if ($actionString != "asset.browse") {
				$links[] = "<a href='".MYURL."/asset/browse/".$assetId->getIdString()."/'>";
				$links[count($links) - 1] .= _("browse")."</a>";
			} else {
				$links[] = _("browse");
			}
			
			if ($actionString != "asset.typebrowse") {
				$links[] = "<a href='".MYURL."/asset/typebrowse/".$assetId->getIdString()."/'>";
				$links[count($links) - 1] .= _("browse by type")."</a>";
			} else {
				$links[] = _("browse by type");
			}
		}
		
		if (ereg("^asset\.$", $actionString) && $harmoni->pathInfoParts[3] == $assetId->getIdString()) {
			if ($repositoryId === NULL) {
				$repository =& $asset->getDigitalRepository();
				$repositoryId =& $repository->getId();
			}
			$links[] = "<a href='".MYURL."/asset/add/".$repositoryId->getIdString()."/".$assetId->getIdString()."/'>";
			$links[count($links) - 1] .= _("add child asset")."</a>";
		}
		
		print  implode("\n\t | ", $links);
	}
	
	
	
}

?>