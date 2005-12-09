<?php
/**
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/library/printers/ExhibitionPrinter.static.php");

/**
 * 
 * 
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class deleteAction 
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
		// Check that the user can delete this exhibition
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.delete"), 
					$idManager->getId(RequestContext::value('exhibition_id')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to delete this <em>Exhibition</em> or its <em>Slide-Shows</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$asset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
		return _("Delete Exhibition")." <em>".$asset->getDisplayName()."</em> ";
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
		
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
	
		$repository->deleteAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
	
		RequestContext::locationHeader(
			$harmoni->request->quickURL("exhibitions", "browse"));
	}
}