<?php
/**
 * @package concerto.modules.user
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
 * @package concerto.modules.user
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
	 * @since 10/24/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/05
	 */
	function getHeadingText () {
		return _("User Tools");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();

		$actionRows->add(new Block("<span style='font-size: larger'><b>".
			_("Authentication")."</b></span>" , 3));
		
		ob_start();
		print "\n<ul>".
			"\n\t<li><a href='".
			$harmoni->request->quickURL("user", "change_password")."'>".
			_("Change ConcertoDB Password").
			"</li>";
			
		$introText =& new Block(ob_get_contents(),3);
		$actionRows->add($introText, "100%", null, CENTER, CENTER);
		ob_end_clean();
		// end of authN links
		
	}
}
?>
