<?php
/**
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");

/**
 * 
 * 
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class browseAction 
	extends RepositoryAction
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
					$this->getRepositoryId());
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to access this <em>Collection</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$repository =& $this->getRepository();
		return _("Browse Assets in the")
			." <em>".$repository->getDisplayName()."</em> "
			._(" Collection");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$repository =& $this->getRepository();

		
		// If the Repository supports searching of root assets, just get those
		$hasRootSearch = FALSE;
		$rootSearchType =& new HarmoniType("Repository","Harmoni","RootAssets", "");
		$searchTypes =& $repository->getSearchTypes();
		while ($searchTypes->hasNext()) {
			if ($rootSearchType->isEqual( $searchTypes->next() )) {
				$hasRootSearch = TRUE;
				break;
			}
		}		
		
		// function links
		ob_start();
		print _("Collection").": ";
		RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
		$layout =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($layout, "100%", null, CENTER, CENTER);
		
		ob_start();
		print  "<p>";
		print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print  "</p>";
		
		$introText =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($introText, "100%", null, CENTER, CENTER);
		
		//***********************************
		// Get the assets to display
		//***********************************
		if ($hasRootSearch) {
			$criteria = NULL;
			$assets =& $repository->getAssetsBySearch($criteria, $rootSearchType, $searchProperties = NULL);
		} 
		// Otherwise, just get all the assets
		else {
			$assets =& $asset->getAssets();
		}
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter =& new IteratorResultPrinter($assets, 2, 6, "printAssetShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
	}
}


// Callback function for printing Assets
function printAssetShort(& $asset, &$harmoni) {
	ob_start();
	
	$assetId =& $asset->getId();
	print  "\n\t<strong>".$asset->getDisplayName()."</strong> - "._("ID#").": ".
			$assetId->getIdString();
	print  "\n\t<br /><em>".$asset->getDescription()."</em>";	
	print  "\n\t<br />";
	
	AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
	
	$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($assetId);
	if (!is_null($thumbnailURL)) {
		print "\n\t<br /><img src='$thumbnailURL' alt='Thumbnail Image' />";
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