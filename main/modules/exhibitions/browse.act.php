<?php
/**
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/library/printers/ExhibitionPrinter.static.php");

/**
 * 
 * 
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class browseAction 
	extends MainWindowAction
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
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Browse all Exhibitions");
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
		
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		
		$exhibitionRepositoryId =& $idManager->getId(
				"edu.middlebury.concerto.exhibition_repository");
		$repository =& $repositoryManager->getRepository($exhibitionRepositoryId);
		
		// If the Repository supports searching of root assets, just get those
		$hasRootSearch = FALSE;
		$rootSearchType =& new HarmoniType("Repository","edu.middlebury.harmoni","RootAssets", "");
		$searchTypes =& $repository->getSearchTypes();
		while ($searchTypes->hasNext()) {
			if ($rootSearchType->isEqual( $searchTypes->next() )) {
				$hasRootSearch = TRUE;
				break;
			}
		}
		
		ob_start();
		print  "<p>";
		print  _("Some <em>Exhibitions</em> and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print  "</p>";
		
		$authZ =& Services::getService("AuthZ");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"), 
			$exhibitionRepositoryId)) 
		{
			print  "\n<p>";
			print "\n\t<a href='";
			print $harmoni->request->quickURL("exhibitions","create_exhibition");
			print "'>"._("Create a new Exhibition")."</a>";
			print  "\n</p>";
		}
		
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
		$resultPrinter =& new IteratorResultPrinter($assets, 3, 6, "printAssetShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
	}
}


// Callback function for printing Assets
function printAssetShort(&$asset, &$harmoni) {
	ob_start();
	
	$assetId =& $asset->getId();
	print  "\n\t<strong>".$asset->getDisplayName()."</strong>";
	print  "\n\t<br /><em>".$asset->getDescription()."</em>";	
	print  "\n\t<br />";
	
	ExhibitionPrinter::printFunctionLinks($asset);
	
	$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($assetId);
	if (!is_null($thumbnailURL)) {
		
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