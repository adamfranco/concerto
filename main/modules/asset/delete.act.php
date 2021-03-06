<?php
/**
 * @package concerto.modules.asset
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
 * @package concerto.modules.asset
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
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.delete"), 
					$idManager->getId(RequestContext::value('asset_id')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to delete this <em>Asset</em> or its <em>Assets</em>.");
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
					RequestContext::value('collection_id')));
		$asset =$repository->getAsset(
				$idManager->getId(RequestContext::value('asset_id')));
		return _("Delete Asset")." <em>".$asset->getDisplayName()."</em> ";
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
					RequestContext::value('collection_id')));
		$asset =$repository->getAsset(
			$idManager->getId(RequestContext::value('asset_id')));
			
		// Remove this asset from the tagging manager
		$tagManager = Services::getService('Tagging');
		$tagManager->deleteItems(TaggedItem::forId($asset->getId(), 'concerto'));
		
		// Log the action
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log =$loggingManager->getLogForWriting("Concerto");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item = new AgentNodeEntryItem("Delete Node", "Asset deleted:\n<br/>&nbsp; &nbsp; &nbsp;".$asset->getDisplayName());
			$item->addNodeId($asset->getId());
			$item->addNodeId($repository->getId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		$repository->deleteAsset(
				$idManager->getId(RequestContext::value('asset_id')));
		
		$harmoni->history->goBack("concerto/asset/delete-return");
	}
}
