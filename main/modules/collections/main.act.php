<?php
/**
 * @package concerto.modules.collections
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

		ob_start();
		print "";
		//print "\n<img src='".MYPATH."/main/modules/home/flower.jpg' alt='A flower. &copy;2003 Adam Franco - Creative Commons Attribution-ShareAlike 1.0 - http://creativecommons.org/licenses/by-sa/1.0/' align='right' style='margin: 10px;' />";
		print "<p>";
		print _("<em>Collections</em> are containers for <em>Assets</em>. <em>Assets</em> can in turn contain other Assets. Each collection can have its own cataloging schema.");
		print "</p>\n<ul>";
		print "\n\t<li><a href='".MYURL."/collections/namebrowse/'>";
		print _("Browse <em>Collections</em> by Name");
		print "</a></li>";
		print "\n\t<li><a href='".MYURL."/collections/typebrowse/'>";
		print _("Browse <em>Collections</em> by Type");
		print "</a></li>";
		print "\n\t<li><a href='".MYURL."/collections/search/'>";
		print _("Search <em>Collections</em> for <em> Assets</em>");
		print "</a></li>";
		print "</ul>\n<p>";
		print _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print "</p>";
		
		// If the user is authorized, allow them to create a new collection.
		// @todo - add authorization.
		print "\n<ul>\n<li><a href='".MYURL."/collection/create/'>";
		print _("Create a new <em>Collection</em>");
		print "</a>\n</li>\n</ul>";
		
		$actionRows->add(new Block(ob_get_contents(), 3), "100%", null, CENTER, CENTER);
		ob_end_clean();

	}
}

?>