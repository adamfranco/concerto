<?php
/**
 * @package concerto.modules.record
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/../AssetAction.class.php");

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
		// Check for our authorization function definitions
		if (!defined("AZ_EDIT"))
			throwError(new Error("You must define an id for AZ_EDIT", "concerto.collection", true));
		if (!defined("AZ_VIEW"))
			throwError(new Error("You must define an id for AZ_VIEW", "concerto.collection", true));
		
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId(AZ_EDIT), 
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
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return '';
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& $this->getHarmoni();
		$centerPane =& $this->getCenterPane();
		$asset =& $this->getAsset();
		$assetId =& $this->getAssetId();
		$repositoryId =& $this->getRepositoryId();
		$idManager =& Services::getService("Id");
		$recordId =& $idManager->getId($harmoni->pathInfoParts[4]);
		
		// Create the wizard.
		 if ($_SESSION['edit_record_wizard_'.$recordId->getIdString()]) {
			$wizard =& $_SESSION['edit_record_wizard_'.$recordId->getIdString()];
		 } else {
		 
			$record =& $asset->getRecord($recordId);
			$structure =& $record->getRecordStructure();
			$structureId =& $structure->getId();
		
			// Instantiate the wizard, then add our steps.
			$wizard =& new Wizard(_("Edit Record"));
			$_SESSION['edit_record_wizard_'.$recordId->getIdString()] =& $wizard;
			
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
		}
		
		// Prepare the return URL so that we can get back to where we were.
		// $currentPathInfo = array();
		// for ($i = 3; $i < count($harmoni->pathInfoParts); $i++) {
		// 	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
		// }
		// $returnURL = MYURL."/".implode("/",$currentPathInfo);
		$returnURL = MYURL."/asset/editview/".$repositoryId->getIdString()."/".$assetId->getIdString()."/";
		
		if ($wizard->isSaveRequested()) {
		
			$record =& $asset->getRecord($recordId);
			
			$moduleManager =& Services::getService("InOutModules");
			
			$moduleManager->updateFromWizard($record, $wizard);
			
			$wizard = NULL;
			unset ($_SESSION['edit_record_wizard_'.$recordId->getIdString()]);
			unset ($wizard);
			
			header("Location: ".$returnURL);
			
		} else if ($wizard->isCancelRequested()) {
			$wizard = NULL;
			unset ($_SESSION['edit_record_wizard_'.$recordId->getIdString()]);
			unset ($wizard);
			header("Location: ".$returnURL);
			
		}
		
		$wizardLayout =& $wizard->getLayout($harmoni);
		$centerPane->add($wizardLayout, null, null, CENTER, CENTER);
	}
}