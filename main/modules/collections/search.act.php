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
class searchAction 
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
		return _("Search Assets in all Collections");
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
		
		ob_start();
		print  "<p>";
		print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print  "</p>";
		
		$actionRows->add(new Block(ob_get_contents(),3), "100%", null, CENTER, CENTER);
		ob_end_clean();
		
		// Print out the search types
		ob_start();
		
		// Get all the drs and all of their search types
		$searchModules =& Services::getService("RepositorySearchModules");
		$searchArray = array();
		
		$repositories =& $repositoryManager->getRepositories();
		while ($repositories->hasNext()) {
			$repository =& $repositories->next();
			$searchTypes =& $repository->getSearchTypes();
			while ($searchTypes->hasNext()) {
				$searchType =& $searchTypes->next();
				
				$typeString = HarmoniType::typeToString($searchType);
				
				if (!isset($searchArray[$typeString]))
					$searchArray[$typeString] =& $searchType;
			}
		}
		
		// print out the types
		foreach (array_keys($searchArray) as $typeString) {
			$searchType =& $searchArray[$typeString];
			print "\n<h3>".$typeString."</h3>";
			
			$harmoni =& Harmoni::instance();
			print "\n".$searchModules->createSearchForm($searchType, 
				$harmoni->request->quickURL("collections", "searchresults",
					array("search_type" => urlencode($typeString))));
		}
		
		
		$actionRows->add(new Block(ob_get_contents(), 3), "100%", null, LEFT, CENTER);
		ob_end_clean();
	}
}

?>