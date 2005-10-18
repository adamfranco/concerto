<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/AssetAction.class.php");

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
class browseAction 
	extends AssetAction
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
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->passthrough("collection_id");
		$harmoni->request->passthrough("asset_id");
		
		$asset =& $this->getAsset();
		$assetId =& $asset->getId();

		// function links
		ob_start();
		AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
		$layout =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($layout, null, null, CENTER, CENTER);
		
		ob_start();
		print "\n<table width='100%'>\n<tr><td style='text-align: left; vertical-align: top'>";				
		
		print  "\n\t<strong>"._("Title").":</strong> \n<em>".$asset->getDisplayName()."</em>";
		print  "\n\t<br /><strong>"._("Description").":</strong> \n<em>".$asset->getDescription()."</em>";
		print  "\n\t<br /><strong>"._("ID#").":</strong> ".$assetId->getIdString();
	
		$effectDate =& $asset->getEffectiveDate();
		if(is_Object($effectDate)) {
			$effectDate =& $effectDate->asDate();
			print  "\n\t<br /><strong>"._("Effective Date").":</strong> \n<em>".$effectDate->asString()."</em>";
		}
	
		$expirationDate =& $asset->getExpirationDate();
		if(is_Object($expirationDate)) {
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
		$actionRows->add(new Block(ob_get_contents(), 3), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		
		// Limit selection form
		$searchBar =& new Container(new XLayout(), BLOCK, 2);
		$actionRows->add($searchBar, "100%", null, CENTER, CENTER);
		$currentUrl =& $harmoni->request->mkURL();	
		$searchBar->setPreHTML(
			"\n<form action='".$currentUrl->write()."' method='post'>");
		$searchBar->setPostHTML("\n</form");
		
		ob_start();
		print  "\n\t<strong>"._("Child Assets").":</strong>";		
		$searchForm =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$searchBar->add($searchForm, "70%", null, LEFT, TOP);
		
		// view options
		ob_start();
		print "\n<div style='text-align: right'>";
		print "\n\t\t"._("Assets Per Page").": ";
		
		if (isset($_SESSION["assetsPerPage"]))
			$defaultNumPerPage = $_SESSION["assetsPerPage"];
		else
			$defaultNumPerPage = 6;
		
		print "\n\t<select name='".RequestContext::name("num_per_page")."'";
		print " onchange='this.form.submit();'>";			
		for ($i = 1; $i < 20; $i++)
			$this->printSelectOption("num_per_page", $defaultNumPerPage, $i);
		for ($i = 20; $i < 100; $i=$i+10)
			$this->printSelectOption("num_per_page", $defaultNumPerPage, $i);
		for ($i = 100; $i <= 1000; $i=$i+100)
			$this->printSelectOption("num_per_page", $defaultNumPerPage, $i);
		print "\n\t</select>";
		
		print "\n\t\t"._("Columns").": ";
		
		if (isset($_SESSION["assetColumns"]))
			$defaultCols = $_SESSION["assetColumns"];
		else
			$defaultCols = 3;
		
		print "\n\t<select name='".RequestContext::name("columns")."'";
		print " onchange='this.form.submit();'>";
		for ($i = 1; $i < 20; $i++)
			$this->printSelectOption("columns", $defaultCols, $i);
		print "\n\t</select>";
		print "</div>";
		
		$searchForm =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$searchBar->add($searchForm, "30%", null, RIGHT, TOP);
		
		//***********************************
		// Get the assets to display
		//***********************************
		$assets =& $asset->getAssets();
		
		//***********************************
		// print the results
		//***********************************
		if (RequestContext::value("num_per_page")) {
			$numPerPage = RequestContext::value("num_per_page");
			$_SESSION["assetsPerPage"] = $numPerPage;
		} else if (isset($_SESSION["assetsPerPage"]))
			$numPerPage = $_SESSION["assetsPerPage"];
		else
			$numPerPage = $defaultNumPerPage;
			
		if (RequestContext::value("columns")) {
			$columns = RequestContext::value("columns");
			$_SESSION["assetColumns"] = $columns;
		} else if (isset($_SESSION["assetColumns"]))
			$columns = $_SESSION["assetColumns"];
		else
			$columns = $defaultCols;
		
		$resultPrinter =& new IteratorResultPrinter($assets, $columns, $numPerPage, "printAssetShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
	}
	
	/**
	 * Print out a select list option
	 * 
	 * @param string $fieldname
	 * @param string $default
	 * @param string $value
	 * @return void
	 * @access public
	 * @since 10/18/05
	 */
	function printSelectOption ( $fieldname, $default, $value ) {
		print "\n\t\t<option value='".$value."'";
		if (RequestContext::value($fieldname) == $value
			|| (!RequestContext::value($fieldname)
				&& $value == $default)) 
		{
			print " selected='selected'";
		}
		print ">".$value."</option>";
	}
}

// Callback function for printing Assets
function printAssetShort(& $asset, &$harmoni, $num) {
	$container =& new Container(new YLayout, BLOCK, 4);
	$fillContainerSC =& new StyleCollection("*.fillcontainer", "fillcontainer", "Fill Container", "Elements with this style will fill their container.");
	$fillContainerSC->addSP(new HeightSP("85%"));
// 	$fillContainerSC->addSP(new WidthSP("100%"));
// 	$fillContainerSC->addSP(new BorderSP("3px", "solid", "#F00"));
	$container->addStyle($fillContainerSC);
	
	$centered =& new StyleCollection("*.centered", "centered", "Centered", "Centered Text");
	$centered->addSP(new TextAlignSP("center"));	
	
	ob_start();
	$assetId =& $asset->getId();
	print "\n\t<strong>".$asset->getDisplayName()."</strong>";
	print "\n\t<br/>"._("ID#").": ".$assetId->getIdString();
	print  "\n\t<br /><em>".$asset->getDescription()."</em>";	
	print  "\n\t<br />";
	
	$component =& new Block(ob_get_contents(), 2);
	ob_end_clean();
	$container->add($component, "100%", null, LEFT, TOP);
	
	$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($assetId);
	if ($thumbnailURL !== FALSE) {
		ob_start();
		print "\n\t<a href='";
		print $harmoni->request->quickURL("asset", "view", array('asset_id' => $assetId->getIdString()));
		print "'>";
		print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' />";
		print "\n\t</a>";
		$component =& new Block(ob_get_contents(), 2);
		$component->addStyle($centered);
		ob_end_clean();
		$container->add($component, "100%", null, CENTER, CENTER);
	}
	
	ob_start();
	AssetPrinter::printAssetFunctionLinks($harmoni, $asset, NULL, $num);
	$component =& new Block(ob_get_contents(), 2);
	$component->addStyle($centered);
	ob_end_clean();
	$container->add($component, "100%", null, CENTER, BOTTOM);
	
	return $container;
}

// Callback function for checking authorizations
function canView( & $asset ) {
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	
	if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.access"), $asset->getId())
		|| $authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $asset->getId()))
	{
		return TRUE;
	} else {
		return FALSE;
	}
}