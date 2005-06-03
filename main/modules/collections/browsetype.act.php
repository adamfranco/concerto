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
class browsetypeAction 
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
		return _("Browse Collections with Type").": \n<br />".
				urldecode(RequestContext::value('type'));
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
		
		$type =& HarmoniType::stringToType(urldecode(RequestContext::value('type')));

		$repositoryManager =& Services::getService("Repository");
		
		
		// Get the Repositories
		$allRepositories =& $repositoryManager->getRepositoriesByType($type);
		
		// put the repositories into an array and order them.
		// @todo, do authorization checking
		$repositoryArray = array();
		while($allRepositories->hasNext()) {
			$repository =& $allRepositories->next();
			$repositoryArray[$repository->getDisplayName()] =& $repository;
		}
		ksort($repositoryArray);
		
		
		// print the Results
		$resultPrinter =& new ArrayResultPrinter($repositoryArray, 2, 20, "printrepositoryShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout($harmoni);
		$actionRows->add($resultLayout, null, null, CENTER, CENTER);
	}
}


// Callback function for printing repositorys
function printrepositoryShort(& $repository, $harmoni) {
	ob_start();
	
	$repositoryId =& $repository->getId();
	print  "\n\t<strong>".$repository->getDisplayName()."</strong> - "._("ID#").": ".
			$repositoryId->getIdString();
	print  "\n\t<br /><em>".$repository->getDescription()."</em>";	
	print  "\n\t<br />";
	
	RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
	
	$layout =& new Block(ob_get_contents(), 4);
	ob_end_clean();
	return $layout;
}
