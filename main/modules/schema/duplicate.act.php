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


class duplicateAction
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
						$idManager->getId("edu.middlebury.authorization.convert_rec_struct"), 
						$repositoryId))
				|| $authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.convert_rec_struct"), 
						$idManager->getId("edu.middlebury.authorization.root"))))
		{
			if (RequestContext::value('copy_records') == 'true') {
				if ($authZManager->isUserAuthorized(
							$idManager->getId("edu.middlebury.authorization.modify"), 
							$this->getRepositoryId()))
				{
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
		
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
		return _("You are not authorized to duplicate this <em>Schema</em>.");
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
		return _("Duplicating Schema")." <em>".$recordStructure->getDisplayName()."</em> ";
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
		
		$newRecordStructure =& $repository->duplicateRecordStructure(
			$recordStructureId,
			((RequestContext::value('copy_records') == 'true')?true:false),
			null,
			false,
			new StatusStars(_("Duplicating Schema and associated Records")));
		
		// Log the action
		if (Services::serviceRunning("Logging")) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Concerto");
			$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item =& new AgentNodeEntryItem("Duplicate RecordStructure", "RecordStructure copied from: \n<br/>&nbsp; &nbsp; &nbsp;".$recordStructure->getDisplayName()."\n<br/>to:\n<br/>&nbsp; &nbsp; &nbsp;".$newRecordStructure->getDisplayName());
			$item->addNodeId($repositoryId);
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		$setManager =& Services::getService("Sets");
		$set =& $setManager->getPersistentSet($repositoryId);
		if ($set->isInSet($recordStructureId))
			$set->addItem($newRecordStructure->getId());
		
		
		
		$url = $harmoni->history->getReturnUrl(
			"concerto/schema/duplicate-return/".$recordStructureIdString);
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
