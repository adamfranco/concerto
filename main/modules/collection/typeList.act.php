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
require_once(HARMONI."GUIManager/StyleProperties/TextAlignSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MinHeightSP.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");
require_once(POLYPHONY."/main/library/RepositorySearchModules/RepositorySearchModuleManager.class.php");
require_once(HARMONI."oki2/shared/MultiIteratorIterator.class.php");


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
class typeListAction 
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
		print _("You are not authorized to access this <em>Collection</em>.");
		exit;
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
		$repository =& $this->getRepository();
			
		print "\n\t<table border='0'>";
		print "\n\t\t<tr>";
		$i = 0;
		$types =& $repository->getAssetTypes();
		while ($types->hasNext()) {
			print "\n\t\t\t<td>";
			$type =& $types->next();
			print "\n\t\t\t\t<input type='checkbox'";
			print " name='".RequestContext::name("type___".Type::typeToString($type))."'";
			print " value='true'";
			print "/>".$type->getKeyword()."";
			print "\n\t\t\t<td>";
			$i++;
			if (($i % 4) == 0)
				print "\n\t\t</tr>\n\t\t<tr>";
		}
		print "\n\t\t</tr>";
		print "\n\t</table>";
		exit;
	}
}