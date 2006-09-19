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
class typebrowseAction 
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
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.access"), 
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
		return _("Browse Assets in the")
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

		// function links
		ob_start();
		print _("Collection").": ";
		RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
		$layout =& new Block(ob_get_contents(), 3);
		ob_end_clean();
		$actionRows->add($layout, "100%", null, LEFT, CENTER);
		
		$repositoryManager =& Services::getService("Repository");
		
		// Get all the types
		$types =& $repository->getAssetTypes();
		// put the drs into an array and order them.
		$typeArray = array();
		while($types->hasNext()) {
			$type =& $types->next();
			$typeArray[$type->getDomain()." ".$type->getAuthority()." ".$type->getKeyword()] =& $type;
		}
		ksort($typeArray);
		
		// print the Results
		$resultPrinter =& new ArrayResultPrinter($typeArray, 2, 20, "printTypeShort", $repository->getId());
		$resultPrinter->addLinksStyleProperty(new MarginTopSP("10px"));
		$resultLayout =& $resultPrinter->getLayout();
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
	}
}


// Callback function for printing Repositories
function printTypeShort(& $type, & $repositoryId) {
	ob_start();
	
	$typeString = HarmoniType::typeToString($type, " :: ");
	
	$harmoni =& Harmoni::instance();
	
	print "<a href='".$harmoni->request->quickURL("collection", "browsetype", array("collection_id" => $repositoryId->getIdString(), "asset_type" => urlencode($typeString)))."'>";
	print "\n\t<strong>";
	print $typeString;
	print "</strong>";
	print "</a>";
	
	$layout =& new Block(ob_get_contents(), 4);
	ob_end_clean();
	return $layout;
}