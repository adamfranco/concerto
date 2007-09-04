<?php
/**
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/delete.act.php");

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
class delete_slideshowAction 
	extends deleteAction
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
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.delete"), 
					$idManager->getId(RequestContext::value('slideshow_id')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to delete this <em>Slideshow</em> or its <em>Slides</em>.");
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$idManager = Services::getService("Id");
		$repositoryManager = Services::getService("Repository");
		$repository =$repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$asset =$repository->getAsset(
				$idManager->getId(RequestContext::value('slideshow_id')));
		return _("Delete Slideshow")." <em>".$asset->getDisplayName()."</em> ";
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =$this->getActionRows();
		$harmoni = Harmoni::instance();
		
		$idManager = Services::getService("Id");
		$repositoryManager = Services::getService("Repository");
		$repository =$repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		
		$asset =$repository->getAsset(
			$idManager->getId(RequestContext::value('slideshow_id')));
		
		// Remove it from its set.
		$exhibitionId =$idManager->getId(RequestContext::value('exhibition_id'));		
		$setManager = Services::getService("Sets");
		$exhibitionSet =$setManager->getPersistentSet($exhibitionId);
		$exhibitionSet->removeItem($asset->getId());
		
		// Log the action
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log =$loggingManager->getLogForWriting("Concerto");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item = new AgentNodeEntryItem("Delete Node", "Slideshow deleted:\n<br/>&nbsp; &nbsp; &nbsp;".$asset->getDisplayName());
			$item->addNodeId($asset->getId());
			$item->addNodeId($idManager->getId(RequestContext::value('exhibition_id')));
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		$repository->deleteAsset(
				$idManager->getId(RequestContext::value('slideshow_id')));

		RequestContext::locationHeader($harmoni->request->quickURL(
			"exhibitions", "browse_exhibition",
			array("exhibition_id" => RequestContext::value('exhibition_id'))));
	}
}