<?php
/**
 * @package concerto.modules.collections
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

/**
 * 
 * 
 * @package concerto.modules.collections
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class searchresultsAction 
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
		return _("Search results of Assets in all Collections");
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

		// Get the Repository
		$repositoryManager =& Services::getService("Repository");
		$idManager =& Services::getService("Id");
		
		// get the search type.
		$searchType =& HarmoniType::fromString(urldecode(
    	$harmoni->request->get('search_type')));
		
		// Get the Search criteria
		$searchModules =& Services::getService("RepositorySearchModules");
		$searchCriteria =& $searchModules->getSearchCriteria($repository, $searchType);
		
		ob_start();
		print  "<p>";
		print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print  "</p>";
		
		$introText =& new Block(ob_get_contents(), 2);
		ob_end_clean();
		$actionRows->add($introText, "100%", null, CENTER, CENTER);
		
		//***********************************
		// Get the assets to display
		//***********************************
		$assetArray = array();
		// Go through all the repositories. if they support the searchType,
		// run the search on them.
		$repositories =& $repositoryManager->getRepositories();
		while ($repositories->hasNext()) {
			$repository =& $repositories->next();
			$assets =& $repository->getAssetsBySearch($searchCriteria, $searchType, $searchProperties = NULL);
			
			// add the results to our total results
			while ($assets->hasNext()) {
				$assetArray[] =& $assets->next();
			}
		}
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter =& new ArrayResultPrinter($assetArray, 2, 6, "printAssetShort", $harmoni);
		$resultPrinter->addLinksStyleProperty(new MarginTopSP("10px"));
		$resultLayout =& $resultPrinter->getLayout();
		$actionRows->add($resultLayout, null, null, CENTER, CENTER);

	}
}

// Callback function for printing Assets
function printAssetShort(& $asset, &$harmoni) {
	ob_start();
	
	$assetId =& $asset->getId();
	print  "\n\t<strong>".$asset->getDisplayName()."</strong> - "._("ID#").": ".
			$assetId->getIdString();
	$description =& HtmlString::withValue($asset->getDescription());
	$description->trim(25);
	print  "\n\t<div style='font-size: smaller;'>".$description->asString()."</div>";	
	
	AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
	
	$layout =& new Block(ob_get_contents(), 3);
	ob_end_clean();
	return $layout;
}