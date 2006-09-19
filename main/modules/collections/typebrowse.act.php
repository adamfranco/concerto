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
		$harmoni =& Harmoni::instance();
		
		ob_start();
		
		CollectionsPrinter::printFunctionLinks();
		
		print "<p>";
		print _("Below are listed the availible <em>Collections</em>, organized by type, then name.");
		print "</p>\n<p>";
		print _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print "</p>";
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, CENTER, CENTER);
		ob_end_clean();
		
		$exhibitionRepositoryType =& new Type ('System Repositories', 
											'edu.middlebury.concerto', 'Exhibitions');
		
		$repositoryManager =& Services::getService("Repository");
		
		// Get all the types
		$types =& $repositoryManager->getRepositoryTypes();
		// put the drs into an array and order them.
		$typeArray = array();
		while($types->hasNext()) {
			$type =& $types->next();
			
			// include all but Exhibitions repository.
			if (!$exhibitionRepositoryType->isEqual($type))
				$typeArray[HarmoniType::typeToString($type)] =& $type;
		}
		ksort($typeArray);
		
		// print the Results
		$resultPrinter =& new ArrayResultPrinter($typeArray, 2, 20, "printTypeShort");
		$resultPrinter->addLinksStyleProperty(new MarginTopSP("10px"));
		$resultLayout =& $resultPrinter->getLayout();
		$actionRows->add($resultLayout, null, null, CENTER, CENTER);
	}
}


// Callback function for printing Repositories
function printTypeShort(& $type) {
	ob_start();
	$harmoni =& Harmoni::instance();
	print "<a href='";
	print $harmoni->request->quickURL('collections', 'browsetype', 
			array('type' => urlencode(HarmoniType::typeToString($type))));
	print "'>";
	print "\n\t<strong>";
	print HarmoniType::typeToString($type, " :: ");
	print "</strong>";
	print "</a>";
	
	$block =& new Block(ob_get_contents(), STANDARD_BLOCK);
	ob_end_clean();
	return $block;
}