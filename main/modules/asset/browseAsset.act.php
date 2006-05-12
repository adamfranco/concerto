<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/modules/collection/browse.act.php");
require_once(HARMONI."GUIManager/StyleProperties/MinHeightSP.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

/**
 * 
 * 
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class browseAssetAction 
	extends browseAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.access"), 
					$this->getAssetId());
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to access this <em>Asset</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$asset =& $this->getAsset();
		return _("Browsing Asset")." <em>".$asset->getDisplayName()."</em> ";
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		
		$this->registerDisplayProperties();
		
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->passthrough("collection_id");
		$harmoni->request->passthrough("asset_id");
		
		$asset =& $this->getAsset();
		$assetId =& $asset->getId();

		// function links
		ob_start();
		AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
		$layout =& new Block(ob_get_contents(), STANDARD_BLOCK);
		ob_end_clean();
		$actionRows->add($layout, null, null, CENTER, CENTER);
		
		ob_start();
		print "\n<table width='100%'>\n<tr><td style='text-align: left; vertical-align: top'>";				
		
		print "\n\t<strong>"._("Title").":</strong> \n<em>".$asset->getDisplayName()."</em>";
		print "\n\t<br /><strong>"._("Description").":</strong>";
		$description =& HtmlString::withValue($asset->getDescription());
		$description->clean();
		print  "\n\t<div style='font-size: smaller;'>".$description->asString()."</div>";
		print "\n\t<br /><strong>"._("ID#").":</strong> ".$assetId->getIdString();
	
		
		if(is_object($asset->getEffectiveDate())) {
			$effectDate =& $asset->getEffectiveDate();
			$effectDate =& $effectDate->asDate();
			print  "\n\t<br /><strong>"._("Effective Date").":</strong> \n<em>".$effectDate->asString()."</em>";
		}
	
		
		if(is_object($asset->getExpirationDate())) {
			$expirationDate =& $asset->getExpirationDate();
			$expirationDate =& $expirationDate->asDate();
			print  "\n\t<br /><strong>"._("Expiration Date").":</strong> \n<em>".$expirationDate->asString()."</em>";
		}
		
		
		print "\n</td><td style='text-align: right; vertical-align: top'>";
		
		
		$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($assetId);
	if ($thumbnailURL !== FALSE) {
			print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' align='right' />";
		}
		
		print "\n</td></tr></table>";
// 		print "\n\t<hr/>";
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		
		$searchBar =& new Container(new YLayout(), BLOCK, STANDARD_BLOCK);
		$actionRows->add($searchBar, "100%", null, CENTER, CENTER);
		
		
		//***********************************
		// Get the assets to display
		//***********************************
		$assets =& $asset->getAssets();
		
		//***********************************
		// print the results
		//***********************************
		$params = array();
		$params["collection_id"] = RequestContext::value("collection_id");
		$params[RequestContext::name("limit_by_type")] = RequestContext::value("limit_by_type");
		$params[RequestContext::name("type")] = RequestContext::value("type");
		$params[RequestContext::name("searchtype")] = RequestContext::value("searchtype");
		if (isset($selectedSearchType)) {
			$searchModuleManager =& Services::getService("RepositorySearchModules");
			foreach ($searchModuleManager->getCurrentValues($selectedSearchType) as $key => $value) {
				$params[$key] = $value;
			}
		}
		if (isset($selectedTypes) && count($selectedTypes)) {
			foreach(array_keys($selectedTypes) as $key) {
				$params[RequestContext::name("type___".Type::typeToString($selectedTypes[$key]))] = 
					RequestContext::value("type___".Type::typeToString($selectedTypes[$key]));
			}
		}
		
		
		$resultPrinter =& new IteratorResultPrinter($assets, $_SESSION["asset_columns"], $_SESSION["assets_per_page"], "printAssetShort", $params);
		
		$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
		$resultLayout->setPreHTML("<form id='AssetMultiEditForm' name='AssetMultiEditForm' action='' method='post'>");
		$resultLayout->setPostHTML("</form>");
		
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		
		
		
		/*********************************************************
		 * Display options
		 *********************************************************/
		$currentUrl =& $harmoni->request->mkURL();	
		$searchBar->setPreHTML(
			"\n<form action='".$currentUrl->write()."' method='post'>");
		$searchBar->setPostHTML("\n</form");
		
		ob_start();
		print  "\n\t<strong>"._("Child Assets").":</strong>";
		$searchBar->add(new UnstyledBlock(ob_get_clean()), null, null, LEFT, TOP);
		
		$searchBar->add($this->getDisplayOptions($resultPrinter), null, null, LEFT, TOP);
	}
}