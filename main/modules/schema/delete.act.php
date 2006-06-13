<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
require_once(MYDIR."/main/library/abstractActions/RecordStructureAction.class.php");
require_once(POLYPHONY."/main/library/Importer/StatusStars.class.php");


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
	extends RecordStructureAction
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
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$recordStructure =& $this->getRecordStructure();
		$recordStructureId =& $this->getRecordStructureId();
		if (method_exists($recordStructure, 'createPartStructure')
			&& 	((preg_match("/^Repository::.+$/i", $recordStructureId->getIdString())
					&& $authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.delete_rec_struct"), 
						$this->getRepositoryId()))
				|| $authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.delete_rec_struct"), 
						$idManager->getId("edu.middlebury.authorization.root"))))
		{
			return true;
		} else
			return false;
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to delete this <em>Schema</em>.");
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$recordStructure =& $this->getRecordStructure();
		return _("Delete Schema")." <em>".$recordStructure->getDisplayName()."</em> ";
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
		
		$recordStructureId =& $this->getRecordStructureId();
		$recordStructure =& $this->getRecordStructure();
		$recordStructureIdString = $recordStructureId->getIdString();
		$repositoryId =& $this->getRepositoryId();
		$repository =& $this->getRepository();
		
		// Log the action
		if (Services::serviceRunning("Logging")) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Concerto");
			$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item =& new AgentNodeEntryItem("Delete RecordStructure", "RecordStructure deleted:\n<br/>&nbsp; &nbsp; &nbsp;".$recordStructure->getDisplayName());
			$item->addNodeId($recordStructureId);
			$item->addNodeId($repositoryId);
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		$setManager =& Services::getService("Sets");
		$set =& $setManager->getPersistentSet($repositoryId);
		if ($set->isInSet($recordStructureId))
			$set->removeItem($recordStructureId);
			
		$repository->deleteRecordStructure($recordStructureId, 
			new StatusStars(_("Deleting Schema and associated Records")));
				
		$url = $harmoni->history->getReturnUrl("concerto/schema/delete-return/".$recordStructureIdString);
		$unescapedurl = preg_replace("/&amp;/", "&", $url);
		$label = _("Return");
		print <<< END
<script type='text/javascript'>
/* <![CDATA[ */
	
	window.location = '$unescapedurl';
	
/* ]]> */
</script>
<a href='$url'>$label</a>

END;
		exit();
	}
}
