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
		return _("Collections");
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
		print "<p>";
		print _("<em>Collections</em> are containers for <em>Assets</em>. <em>Assets</em> can in turn contain other Assets. Each collection can have its own cataloging schema.");
		print "</p>\n<ul>";
		print "\n\t<li><a href='";
			print $harmoni->request->quickURL("collections", "namebrowse");
			print "'>";
		print _("Browse <em>Collections</em> by Name");
		print "</a></li>";
		print "\n\t<li><a href='";
			print $harmoni->request->quickURL("collections", "typebrowse");
			print "'>";
		print _("Browse <em>Collections</em> by Type");
		print "</a></li>";
		print "\n\t<li><a href='";
			print $harmoni->request->quickURL("collections", "search");
			print "'>";
		print _("Search <em>Collections</em> for <em> Assets</em>");
		print "</a></li>";
		print "</ul>\n<p>";
		print _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print "</p>";
		
		// If the user is authorized, allow them to create a new collection.
		// @todo - add authorization.
		print "\n<ul>\n<li><a href='";
			print $harmoni->request->quickURL("collection", "create");
			print "'>";
		print _("Create a new <em>Collection</em>");
		print "</a>\n</li>\n</ul>";
		
		$actionRows->add(new Block(ob_get_contents(), 3), "100%", null, CENTER, CENTER);
		ob_end_clean();

	}
}

?>