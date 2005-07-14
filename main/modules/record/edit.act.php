<?php
/**
 * @package concerto.modules.record
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/AssetAction.class.php");

/**
 * 
 * 
 * @package concerto.modules.record
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class editAction 
	extends AssetAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify"), 
					$this->getAssetId());
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to edit this <em>Asset</em>.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$centerPane =& $this->getActionRows();
		$idManager =& Services::getService("Id");
		$recordId =& $idManager->getId($harmoni->request->get("record_id"));
		$cacheName = 'edit_record_wizard_'.$recordId->getIdString();
		
		$this->runWizard ( $cacheName, $centerPane );
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function &createWizard () {
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$recordId =& $idManager->getId($harmoni->request->get("record_id"));
		$asset =& $this->getAsset();
		$record =& $asset->getRecord($recordId);
		$structure =& $record->getRecordStructure();
		$structureId =& $structure->getId();
	
		// Instantiate the wizard, then add our steps.
		$wizard =& new Wizard(_("Edit Record"));
		
		// First get the set for this structure and start with the partStructure in the set.
		$setManager =& Services::getService("Sets");
		$partStructureSet =& $setManager->getSet($structureId);
		
		$moduleManager =& Services::getService("InOutModules");
		// if we are dealing with ordered partStructures, order them
		if ($partStructureSet->count()) {
			$orderedPartStructuresToPrint = array();
			$partStructuresToPrint = array();
			$allPartStructures = array();
			
			// get the partStructures and break them up into ordered and unordered arrays.
			$partStructures =& $structure->getPartStructures();
			while ($partStructures->hasNext()) {
				$partStructure =& $partStructures->next();
				$partStructureId =& $partStructure->getId();
				
				$allPartStructures[] =& $partStructure;
				
				if ($partStructureSet->isInSet($partStructureId)) {
					$orderedPartStructuresToPrint[] =& $partStructureId;
				} else {
					$partStructuresToPrint[] =& $partStructureId;
				}
			}
			
			$moduleManager->createWizardStepsForPartStructures($record, $wizard, $allPartStructures);
		}
		
		// Otherwise just add steps for all partStructures.
		else {
			$moduleManager->createWizardSteps($record, $wizard);
		}
		
		return $wizard;
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		$wizard =& $this->getWizard($cacheName);
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$recordId =& $idManager->getId($harmoni->request->get('repository_id'));
		$asset =& $this->getAsset();
		$record =& $asset->getRecord($recordId);
		
		$record =& $asset->getRecord($recordId);
		$moduleManager =& Services::getService("InOutModules");
		$moduleManager->updateFromWizard($record, $wizard);
		
		return TRUE;
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->history->getReturnURL("concerto/asset/editview");
		return $url->write();
	}
}