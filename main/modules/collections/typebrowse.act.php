<?php
/**
 * @package concerto.modules.collections
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/../MainWindowAction.class.php");

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
class typebrowseAction 
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
		return _("Browse Collections By Type");
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
		$harmoni =& $this->getHarmoni();
		
		ob_start();
		print "<p>";
		print _("Below are listed the availible <em>Collections</em>, organized by type, then name.");
		print "</p>\n<p>";
		print _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print "</p>";
		
		$actionRows->add(new Block(ob_get_contents(),3), "100%", null, CENTER, CENTER);
		ob_end_clean();
		
		$repositoryManager =& Services::getService("Repository");
		
		// Get all the types
		$types =& $repositoryManager->getRepositoryTypes();
		// put the drs into an array and order them.
		$typeArray = array();
		while($types->hasNext()) {
			$type =& $types->next();
			$typeArray[$type->getDomain()." ".$type->getAuthority()." ".$type->getKeyword()] =& $type;
		}
		ksort($typeArray);
		
		// print the Results
		$resultPrinter =& new ArrayResultPrinter($typeArray, 2, 20, "printTypeShort");
		$resultLayout =& $resultPrinter->getLayout($harmoni);
		$actionRows->add($resultLayout, null, null, CENTER, CENTER);
	}
}


// Callback function for printing Repositories
function printTypeShort(& $type) {
	ob_start();
	
	$typeString = $type->getDomain()." :: " .$type->getAuthority()." :: ".$type->getKeyword();

	print "<a href='".MYURL."/collections/browsetype/".urlencode($typeString)."'>";
	print "\n\t<strong>";
	print $typeString;
	print "</strong>";
	print "</a>";
	
	$xLayout =& new XLayout();
	$layout =& new Container($xLayout, BLOCK, 4);
	$layout2 =& new Block(ob_get_contents(), 2);
	$layout->add($layout2, null, null, CENTER, CENTER);
	ob_end_clean();
	return $layout;
}