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
		// Check for our authorization function definitions
		if (!defined("AZ_ACCESS"))
			throwError(new Error("You must define an id for AZ_ACCESS", "concerto.collection", true));
		if (!defined("AZ_VIEW"))
			throwError(new Error("You must define an id for AZ_VIEW", "concerto.collection", true));
		
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId(AZ_ACCESS), 
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
		$resultPrinter =& new ArrayResultPrinter($typeArray, 2, 20, "printTypeShort", $repositoryId);
		$resultLayout =& $resultPrinter->getLayout($harmoni);
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
	}
}


// Callback function for printing Repositories
function printTypeShort(& $type, & $repositoryId) {
	ob_start();
	
	$typeString = $type->getDomain()." :: " .$type->getAuthority()." :: ".$type->getKeyword();

	print "<a href='".MYURL."/collection/browsetype/".$repositoryId->getIdString()."/".urlencode($typeString)."'>";
	print "\n\t<strong>";
	print $typeString;
	print "</strong>";
	print "</a>";
	
	$layout =& new Block(ob_get_contents(), 4);
	ob_end_clean();
	return $layout;
}