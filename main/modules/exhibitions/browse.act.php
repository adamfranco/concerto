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
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

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
	//===== Create Link =====//
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"), 
			$exhibitionRepositoryId)) {
			print  "\n<p>";
			print "\n\t<a href='".$harmoni->request->quickURL(
				"exhibitions","create_exhibition")."'>".
				_("Create a new <em>Exhibition</em>")."</a>";
	//===== Import Link =====//
// 			$harmoni->request->startNamespace("import");
// 			print  "\t|\t<a href='".
// 				$harmoni->request->quickURL("exhibitions", "import_exhibition").
// 				"'>".
// 				_("Import <em>Exhibition(s)</em>").
// 				"</a>";
// 			$harmoni->request->endNamespace();
			print  "\n</p>";
		}
		
		$introText =& new Block(ob_get_contents(), STANDARD_BLOCK);
		ob_end_clean();
		$actionRows->add($introText, "100%", null, CENTER, CENTER);
		
		//***********************************
		// Get the assets to display
		//***********************************
		if ($hasRootSearch) {
			$criteria = NULL;
			
			$searchProperties =& new HarmoniProperties(
					Type::fromString("repository::harmoni::order"));
			$searchProperties->addProperty("order", $arg = "DisplayName");
			unset($arg);
			$searchProperties->addProperty("direction", $arg = "ASC");
			unset($arg);
			
			$assets =& $repository->getAssetsBySearch($criteria, $rootSearchType, $searchProperties);
		} 
		// Otherwise, just get all the assets
		else {
			$assets =& $repository->getAssets();
		}
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter =& new IteratorResultPrinter($assets, 1, 20, "printAssetShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout("canView");
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
	}
}


// Callback function for printing Assets
function printAssetShort(&$asset, &$harmoni) {
	ob_start();
	
	$assetId =& $asset->getId();
	print  "\n\t<div style='font-weight: bold' title='"._("ID#").": ".
			$assetId->getIdString()."'>".$asset->getDisplayName()."</div>";
	$description =& HtmlString::withValue($asset->getDescription());
	$description->trim(100);
	print  "\n\t<div style='font-size: smaller;'>".$description->asString()."</div>";	
	
	ExhibitionPrinter::printFunctionLinks($asset);
	
	$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($assetId);
	if ($thumbnailURL !== FALSE) {
		
		print "\n\t<br /><a href='";
		print $harmoni->request->quickURL("asset", "view", array('asset_id' => $assetId->getIdString()));
		print "'>";
		print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' />";
		print "\n\t</a>";
	}
	
	$layout =& new Block(ob_get_contents(), EMPHASIZED_BLOCK);
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