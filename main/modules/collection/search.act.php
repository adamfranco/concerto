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
class searchAction 
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
		return $authZ->isUserAuthorizedBelow(
					$idManager->getId("edu.middlebury.authorization.view"), 
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
		return _("Search Assets in the")
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
		$repositoryId =& $this->getRepositoryId();

		// function links
		ob_start();
		print _("Collection").": ";
		RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
		$layout =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($layout, null, null, CENTER, CENTER);
		
		ob_start();
		print  "<p>";
		print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print  "</p>";
		
		$introText =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($introText, null, null, CENTER, CENTER);
		
		
		// Print out the search types
		
		ob_start();
		
		$searchModules =& Services::getService("RepositorySearchModules");
		$searchTypes =& $repository->getSearchTypes();
		while ($searchTypes->hasNext()) {
			$searchType =& $searchTypes->next();
			
			$typeString = $searchType->getDomain()
							."::".$searchType->getAuthority()
							."::".$searchType->getKeyword();
			print "\n<h3>".$typeString."</h3>";
			print "\n".$searchModules->createSearchForm($repository, $searchType, $harmoni->request->quickURL("collection", "searchresults", array("collection_id" => $repositoryId->getIdString(), "asset_type" => urlencode($typeString))));
		}
		
		$searchFields =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($searchFields, "100%", null, LEFT, CENTER);
	}
}

?>