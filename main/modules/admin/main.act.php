<?php
/**
 * @package concerto.modules.admin
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
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		
		$actionRows->add(new Heading(_("Agents &amp; Groups"), 2));
		
		ob_start();
		print "\n<ul>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("agents","create_agent")."'>";
		print _("Create User");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("agents","group_browse")."'>";
		print _("Browse Agents and Groups");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("agents","group_membership")."'>";
		print _("Edit Group Membership");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("agents","edit_agents")."'>";
		print _("Edit Agents");
		print "</a></li>";
		print "\n</ul>";
		
		$introText =& new Block(ob_get_contents(),2);
		$actionRows->add($introText, "100%", null, CENTER, CENTER);
		ob_end_clean();
		
		
		
		$actionRows->add(new Heading(_("Authorizations"), 2));
		
		ob_start();
		print "\n<ul>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("authorization","browse_authorizations")."'>";
		print _("Browse Authorizations");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("authorization","choose_agent")."'>";
		print _("Edit Agent Authorizations &amp; Details");
		print "</a></li>";
		print "\n</ul>";
		
		$introText =& new Block(ob_get_contents(),2);
		$actionRows->add($introText, "100%", null, CENTER, CENTER);
		ob_end_clean();
		
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"),
			$idManager->getId("edu.middlebury.authorization.root"))) {
		
			$actionRows->add(new Heading(_("Development"), 2));
			
			ob_start();
			print "\n<ul>";
			if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.delete"),
				$idManager->getId("edu.middlebury.authorization.root"))) {
				print "\n\t<li><a href='".$harmoni->request->quickURL(
					"admin","main", array('reset_concerto' => 'TRUE'))."'>";
				print _("Reset Concerto");
				print "</a></li>";
			}
			if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$idManager->getId("edu.middlebury.authorization.root"))) {
				print "\n\t<li><a href='".$harmoni->request->quickURL("admin", 
					"import")."'>";
				print _("Import");
				print "</a></li>";
			}
			print "\n\t<li><a href='".$harmoni->request->quickURL("admin", 
				"export")."'>";
			print _("Export");
			print "</a></li>";
			print "\n</ul>";
			
			$introText =& new Block(ob_get_contents(), 2);
			$actionRows->add($introText, "100%", null, CENTER, CENTER);
			ob_end_clean();
		}
	}
}

?>
