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
    return _("Search Slideshows and Assets in all Exhibitions");
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
    //    $harmoni =& $this->getHarmoni();
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
    
    $iterator = $repositoryManager->getRepositoriesByType(new HarmoniType ('System Repositories', 'Concerto', 'Exhibitions','A Repository for holding Exhibitions, their Slide-Shows and Slides'));
    if(!($iterator->hasNextRepository())){
      print _("There are no Exhibitions to search in");
    }else{
      
      $repository =& $iterator->nextRepository();
      $searchTypes =& $repository->getSearchTypes();
      while ($searchTypes->hasNext()) {
	$searchType =& $searchTypes->next();

	$typeString = $searchType->getDomain()
	  ."::".$searchType->getAuthority()
	  ."::".$searchType->getKeyword();

	if (!$searchArray[$typeString])
	  $searchArray[$typeString] =& $searchType;
      }
    }

    // print out the types
    foreach (array_keys($searchArray) as $typeString) {
      $searchType =& $searchArray[$typeString];
      print "\n<h3>".$typeString."</h3>";
      print "\n".$searchModules->createSearchForm($searchType, MYURL."/exhibitions/searchresults/".urlencode($typeString)."/");
    }


    $actionRows->add(new Block(ob_get_contents(), 3), "100%", null, LEFT, CENTER);
    ob_end_clean();
  }
}

?>

