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
require_once(MYDIR."/main/library/printers/CollectionsPrinter.static.php");

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
class namebrowseAction 
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
		return _("Browse Collections By Name");
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
		
		ob_start();
		
		CollectionsPrinter::printFunctionLinks();
		
		print "<p>";
		print _("Below are listed the availible <em>Collections</em>, organized by name.");
		print "</p>\n<p>";
		print _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print "</p>";
		
		$actionRows->add(new Block(ob_get_contents(), 3), "100%", null, CENTER, CENTER);
		ob_end_clean();
		
		
		// Get the Repositoriess
		$repositoryManager =& Services::getService("Repository");
		$allRepositories =& $repositoryManager->getRepositories();
		
		$exhibitionRepositoryType =& new Type ('System Repositories', 
											'edu.middlebury.concerto', 'Exhibitions');
		
		// put the drs into an array and order them.
		// @todo, do authorization checking
		$repositoryArray = array();
		$repositoryTitles = array();
		while($allRepositories->hasNext()) {
			$repository =& $allRepositories->next();

			// include all but Exhibitions repository.
			if (!$exhibitionRepositoryType->isEqual($repository->getType())) {
				$id =& $repository->getId();
				$repositoryTitles[$id->getIdString()] = $repository->getDisplayName();
				$repositoryArray[$id->getIdString()] =& $repository;
			}
		}
		array_multisort($repositoryTitles, SORT_ASC, SORT_STRING, $repositoryArray);
		
		// print the Results
		$resultPrinter =& new ArrayResultPrinter($repositoryArray, 2, 20, "printRepositoryShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout($harmoni);
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
	}
}


// Callback function for printing Repositories
function printRepositoryShort(& $repository, & $harmoni) {
	ob_start();
	
	$repositoryId =& $repository->getId();
	print  "\n\t<strong>".$repository->getDisplayName()."</strong> - "._("ID#").": ".
			$repositoryId->getIdString();
	print  "\n\t<br /><em>".$repository->getDescription()."</em>";	
	print  "\n\t<br />";
	
	RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
	$xLayout =& new XLayout();
	$layout =& new Container($xLayout, BLOCK, 4);
	$layout2 =& new Block(ob_get_contents(), 2);
	$layout->add($layout2, null, null, CENTER, CENTER);
	ob_end_clean();
	return $layout;
}

?>