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
		print "\n\t<hr/>";
		print  "\n\t<strong>"._("Child Assets").":</strong>";		
		$actionRows->add(new Block(ob_get_contents(), 3), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		
		//***********************************
		// Get the assets to display
		//***********************************
		$assets =& $asset->getAssets();
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter =& new IteratorResultPrinter($assets, 3, 6, "printAssetShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
	}
}

// Callback function for printing Assets
function printAssetShort(& $asset, &$harmoni, $num) {
	ob_start();
	
	$assetId =& $asset->getId();
	print  "\n\t<strong>".$asset->getDisplayName()."</strong> - "._("ID#").": ".
			$assetId->getIdString();
	print  "\n\t<br /><em>".$asset->getDescription()."</em>";	
	print  "\n\t<br />";
	
	AssetPrinter::printAssetFunctionLinks($harmoni, $asset, NULL, $num);
	
	$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($assetId);
	if ($thumbnailURL !== FALSE) {
		
		print "\n\t<br /><a href='";
		print $harmoni->request->quickURL("asset", "view", array('asset_id' => $assetId->getIdString()));
		print "'>";
		print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' />";
		print "\n\t</a>";
	}
	
	$xLayout =& new XLayout();
	$layout =& new Container($xLayout, BLOCK, 4);
	$layout2 =& new Block(ob_get_contents(), 3);
	$layout->add($layout2, null, null, CENTER, CENTER);
	//$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	return $layout;
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