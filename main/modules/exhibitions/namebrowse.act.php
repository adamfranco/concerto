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
 * @package concerto.modules.collection
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
      return _("Browse exhibitions by name ");
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

    ob_start();
    print "<p>";
    print _("Below are listed the availible <em>Exhibitions</em>, organized by name.");
    print "</p>\n<p>";
    print _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
    print "</p>";

    $actionRows->add(new Block(ob_get_contents(), 3), "100%", null, CENTER, CENTER);
    ob_end_clean();
    // Get the Repositoriess
    $repositoryManager =& Services::getService("Repository");
    $iterator = $repositoryManager->getRepositoriesByType(new HarmoniType ('System Repositories', 'Concerto', 'Exhibitions','A Repository for holding Exhibitions, their Slide-Shows and Slides'));
    if(!($iterator->hasNextRepository())){
      print "<p>";
      print _("There are no Exhibitions.");
    }else{
      $repository = $iterator->nextRepository();

            
      //***********************************
      // Get the assets to display
      //***********************************
    	//$assets =& $repository->getAssetsBySearch($criteria, $rootSearchType, $searchProperties = NULL);
	$assets =& $repository->getAssets();
      //***********************************
      // print the results
      //***********************************
	$resultPrinter =& new IteratorResultPrinter($assets, 2, 6, "printAssetShort", $harmoni);
	$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
	$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
    }
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
