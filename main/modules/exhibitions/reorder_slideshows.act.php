<?php
/**
 * @since 8/15/06
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * Reorder the slideshows in an exhibition. Assumes that a set exists will 
 * all slideshows in it.
 * 
 * @since 8/15/06
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class reorder_slideshowsAction
	extends MainWindowAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/15/06
	 */
	function isAuthorizedToExecute () {
		// Check that the user can delete this exhibition
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify"), 
					$idManager->getId(RequestContext::value('exhibition_id')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 8/15/06
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to modify this <em>Exhibition</em> or its <em>Slide-Shows</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 8/15/06
	 */
	function getHeadingText () {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$asset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
		return _("Re-Order Slideshows in")." <em>".$asset->getDisplayName()."</em> ";
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 8/15/06
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		
		$exhibitionId =& $idManager->getId(RequestContext::value('exhibition_id'));
		$exhibition =& $repository->getAsset($exhibitionId);
		
		$slideshowId =& $idManager->getId(RequestContext::value('slideshow_id'));
		$slideshowAsset =& $repository->getAsset($slideshowId);
		
		$setManager =& Services::getService("Sets");
		$exhibitionSet =& $setManager->getPersistentSet($exhibitionId);
		$oldPosition = $exhibitionSet->getPosition($slideshowId);
		$newPosition = RequestContext::value('new_position');
		
		// Out of range Error Condition
		if ($newPosition < 0 || $newPosition >= $exhibitionSet->count()) {
			// Log the error
			if (Services::serviceRunning("Logging")) {
				$loggingManager =& Services::getService("Logging");
				$log =& $loggingManager->getLogForWriting("Concerto");
				$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType =& new Type("logging", "edu.middlebury", "Error",
								"Errors.");
				
				$item =& new AgentNodeEntryItem("Reorder Slideshows Failed", "Out of range error: Slideshow in the ".$exhibition->getDisplayName()." exhibition could not be moved from position $oldPosition to $newPosition (".$exhibitionSet->count()." items in the set).");
				$item->addNodeId($exhibition->getId());
							
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
		} 
		// Do the reorder
		else {
			$exhibitionSet->moveToPosition($slideshowId, $newPosition);
			
			// Log the action
			if (Services::serviceRunning("Logging")) {
				$loggingManager =& Services::getService("Logging");
				$log =& $loggingManager->getLogForWriting("Concerto");
				$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType =& new Type("logging", "edu.middlebury", "Event_Notice",
								"Normal events.");
				
				$item =& new AgentNodeEntryItem("Reorder Slideshows", "Slideshows in the ".$exhibition->getDisplayName()." exhibition have been reorderd.");
				$item->addNodeId($exhibition->getId());
							
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			
			// Remove any missing slideshows
			$slideshowsIdStrings = array();
			$slideshows =& $exhibition->getAssets();
			while ($slideshows->hasNext()) {
				$slideshow =& $slideshows->next();
				$slideshowId =& $slideshow->getId();
				$slideshowsIdStrings[] = $slideshowId->getIdString();
			}
			$itemsToRemove = array();
			$exhibitionSet->reset();
			while ($exhibitionSet->hasNext()) {
				$itemId =& $exhibitionSet->next();
				if (!in_array($itemId->getIdString(), $slideshowsIdStrings))
					$itemsToRemove[] = $itemId;
			}
			foreach ($itemsToRemove as $id)
				$exhibitionSet->removeItem($id);
		}
	
		RequestContext::locationHeader(
			$harmoni->request->quickURL("exhibitions", "browse_exhibition", array(
				"exhibition_id" => $exhibitionId->getIdString())));
	}
}

?>