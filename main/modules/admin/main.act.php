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
		
		
		$actionRows->add(new Heading(_("CourseManagement"), 2));
		
		
		
		
		ob_start();
		print "\n<ul>";
		$authN =& Services::getService("AuthN");
		$authNTypesIterator =& $authN->getAuthenticationTypes();
		if($authNTypesIterator->hasNextType()){
			$authNType1 =& $authNTypesIterator->nextType();
			//hopefully the first one is the right one to choose.
			$id =& $authN->getUserId($authNType1);
			print "\n\t<li><a href='".$harmoni->request->quickURL("agents","edit_agent_details", array("agentId"=>$id->getIdString()))."'>";
			print _("My Profile");
			print "</a></li>";		
		}

		print "\n\t<li><a href='".$harmoni->request->quickURL("agents","agent_search")."'>";
		print _("Search Agents");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","course_search")."'>";
		print _("Search Courses");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","createcourse")."'>";
		print _("New Course");
		print "</a></li>";
		/*print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","searchcoursesection")."'>";
		print _("Search Course Sections");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","createcanonicalcourse")."'>";
		print _("Create Canonical Course");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","browsecanonicalcourse")."'>";
		print _("Browse Canonical Courses");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","searchcanonicalcourse")."'>";
		print _("Search Canonical Courses");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","createcourseoffering")."'>";
		print _("Create Course Offering");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","browsecourseoffering")."'>";
		print _("Browse Course Offerings");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","searchcourseoffering")."'>";
		print _("Search Course Offerings");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","createcoursesection")."'>";
		print _("Create Course Section");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","browsecoursesection")."'>";
		print _("Browse Course Sections");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","searchcoursesection")."'>";
		print _("Search Course Sections");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","createnewtype")."'>";
		print _("Add New Type");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","createnewterm")."'>";
		print _("Add New Term");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","suck_it_up")."'>";
		print _("Suck It Up by Term");
		print "</a></li>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("coursemanagement","suck_by_agent")."'>";
		print _("Suck It Up by Agent");
		print "</a></li>";
		print "\n</ul>";*/
		
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
			if (defined('ENABLE_RESET') && ENABLE_RESET
				&& $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.delete"),
					$idManager->getId("edu.middlebury.authorization.root"))) 
			{
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
		
		$actionRows->add(new Heading(_("Logs"), 2));
		
		ob_start();
		print "\n<ul>";
		print "\n\t<li><a href='".$harmoni->request->quickURL("logs","browse")."'>";
		print _("Browse Logs");
		print "</a></li>";
		print "\n</ul>";
		
		$introText =& new Block(ob_get_contents(),2);
		$actionRows->add($introText, "100%", null, CENTER, CENTER);
		ob_end_clean();
	}
}

?>
