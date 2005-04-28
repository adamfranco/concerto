<?php
/**
 * @package concerto.modules.admin
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package concerto.modules.admin
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
		return _("Admin Tools");
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
		
		ob_start();
		print "\n<ul>";
		print "\n\t<li><a href='".MYURL."/agents/group_membership/'>";
		print _("Edit Group Membership");
		print "</a></li>";
		print "\n\t<li><a href='".MYURL."/authorization/browse_authorizations/'>";
		print _("Browse authorizations");
		print "</a></li>";
		print "\n\t<li><a href='".MYURL."/authorization/choose_agent/'>";
		print _("Edit authorizations");
		print "</a></li>";
		print "\n\t<li><a href='".MYURL."/agents/create_agent/'>";
		print _("Create User");
		print "</a></li>";
		print "\n</ul>";
		
		$actionRows->add(new Block(ob_get_contents(), 3), "100%", null, CENTER, CENTER);
		ob_end_clean();
	}
}

?>