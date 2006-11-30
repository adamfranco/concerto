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


class multdeleteAction
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
		$assetIds =& $this->getAssetIds();
		foreach ($assetIds as $id) {
			if (!$authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.delete"), 
					$id))
			{
				return false;
			}
		}
		return true;	
	}
	
	/**
	 * Answer the assetIds passed
	 * 
	 * @return ref array
	 * @access public
	 * @since 5/5/06
	 */
	function &getAssetIds () {
		if (!isset($this->_assetIds)) {
			$idManager =& Services::getService("Id");
			$assetIdStrings = explode(",", RequestContext::value('assets'));
			$this->_assetIds = array();
			foreach ($assetIdStrings as $idString) {
				// ignore whitepace or empty strings
				if (preg_match('/\\S+/', $idString))
					$this->_assetIds[] =& $idManager->getId($idString);
			}
		}
		
		return $this->_assetIds;
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to delete these <em>Assets</em>.");
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Delete Assets");
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
		if (RequestContext::value('collection_id')) {
			$collectionId =& $idManager->getId(RequestContext::value('collection_id'));
			$repository =& $repositoryManager->getRepository($collectionId);
		} else if (RequestContext::value('asset_id')) {
			$parentAssetId =& $idManager->getId(RequestContext::value('asset_id'));
			$parentAsset =& $repositoryManager->getAsset($parentAssetId);
			$repository =& $parentAsset->getRepository();
		} else if (count($this->getAssetIds())) {
			$assetIds = $this->getAssetIds();
			$firstAsset =& $repositoryManager->getAsset(current($assetIds));
			$repository =& $firstAsset->getRepository();
		}
		
		// Log the action
		if (Services::serviceRunning("Logging")) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Concerto");
			$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
		}
		
		$itemsToDelete = array();
		$assetIds =& $this->getAssetIds();
		$status =& new StatusStars(_("Deleting Assets"));
		$status->initializeStatistics(count($assetIds));
		foreach ($assetIds as $id) {
			$asset =& $repository->getAsset($id);
			
			// Record the Tagged item to delete.
			$itemsToDelete[] =& TaggedItem::forId($id, 'concerto');
			
			if (isset($log)) {
				$item =& new AgentNodeEntryItem("Delete Node", 
					"Asset deleted:\n<br/>&nbsp; &nbsp; &nbsp;".$asset->getDisplayName());
				$item->addNodeId($asset->getId());
				$item->addNodeId($repository->getId());
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			$repository->deleteAsset($id);
			$status->updateStatistics();
		}
		
		// Remove this asset from the tagging manager
		$tagManager =& Services::getService('Tagging');
		$tagManager->deleteItems($itemsToDelete, 'concerto');
		
		$harmoni->history->goBack("concerto/asset/delete-return");
	}
}
