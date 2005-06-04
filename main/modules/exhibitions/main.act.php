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
class mainAction
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
    return _("Exhibitions");
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
    $repositoryManager =& Services::getService("Repository");
    $idManager =& Services::getService("Id");
    $iterator = $repositoryManager->getRepositoriesByType(new HarmoniType ('System Repositories', 'Concerto', 'Exhibitions','A Repository for holding Exhibitions, their Slide-Shows and Slides'));
    $repository =& $iterator->nextRepository();
    $repositoryId =& $repository->getId();
    
    ob_start();
    print "<p>";
    print _("<em>Exhibitions</em> are containers for <em>Slideshows</em> and Assets. <em>Assets</em> can in turn contain other Assets.");
    print "</p>\n<ul>";
    print "\n\t<li><a href='";
    print $harmoni->request->quickURL("exhibitions", "namebrowse");
    print "'>";
    print _("Browse <em>Exhibitions</em> by Name");
    print "</a></li>";
    print "\n\t<li><a href='";
    print $harmoni->request->quickURL("exhibitions", "search");
    print "'>";
    print _("Search <em>Exhibitions</em> for <em> Assets</em>");
    print "</a></li>";
    print "</ul>\n<p>";
    print _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
    print "</p>";

    $url =& $harmoni->request->mkURL();
    $url->setValue("exhibitions_id", $repositoryId->getIdString());
    $url->setModuleAction("exhibition", "add");
    print "\n\t<li><a href='".$url->write()."'>";

//    print $harmoni->request->quickURL("exhibition", "add", "".$repositoryId->getIdString()."");
  //  print "'>";
    //    print "\n<ul>\n<li><a href='".MYURL."/exhibition/add/".$repositoryId->getIdString()."'>";
    print _("Create a new <em>Exhibition</em>");
    print "</a></li>";
    print "</ul>\n";
    $actionRows->add(new Block(ob_get_contents(), 3), "100%", null, CENTER, CENTER);
    ob_end_clean();

  }
  }

?>



