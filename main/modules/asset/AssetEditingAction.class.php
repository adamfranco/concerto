<?php
/**
 * @since 10/26/05
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");

/**
 * This is an abstract class which contains methods for editing assets.
 * its child classes will handle the setting up of which assets to edit.
 * 
 * @since 10/26/05
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class AssetEditingAction
	extends RepositoryAction
{
	
	/**
	 * Initialize an instance of the object
	 * 
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function _init () {
		$this->_recStructsToIgnore = array();
		$this->_multExistString = _("(multiple values exist)");
	}
	
	/**
	 * Load the assets for the array of Id-strings passed
	 * 
	 * @param array $assetIdStrings
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function _loadAssets ($assetIdStrings) {
		$idManager =& Services::getService("Id");
	 	$repository =& $this->getRepository();
	 	
	 	$this->_assets = array();
	 	
	 	foreach ($assetIdStrings as $idString) {
	 		$this->_assets[] =& $repository->getAsset($idManager->getId($idString));
	 	}
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
		$harmoni->request->passthrough("collection_id", "asset_id", "assets");
		$centerPane =& $this->getActionRows();
				
		$this->addTableStyles();
		
		$this->runWizard ( $this->_cacheName, $centerPane );
		
// 		printpre($_REQUEST);
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
// 		$asset =& $this->getAsset();
		$wizard =& $this->getWizard($cacheName);
				
		$results =& $wizard->getAllValues();
		$initialState =& $wizard->initialState;
				
		// Go through all of the assets and update all of the values if they have
		// changed.
		$idManager =& Services::getService("Id");
	 	$repository =& $this->getRepository();
	 	
	 	
	 	// Records
		$repository =& $this->getRepository();
		$repositoryId =& $this->getRepositoryId();
		
		// Get the set of RecordStructures so that we can print them in order.
		$setManager =& Services::getService("Sets");
		$recStructSet =& $setManager->getPersistentSet($repositoryId);
		
		$authZMan =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
	 		 	
	 	foreach (array_keys($this->_assets) as $key) {
	 		$asset =& $this->_assets[$key];
			if ($authZMan->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify"), 
					$asset->getId()))
			{
				printpre("<hr>".$asset->getDisplayName());
				
				$this->updateAssetProperties($results['assetproperties'], $asset);
				$this->updateAssetContent($results['contentstep']['content'], $asset);
				
				// First, lets go through the info structures listed in the set and print out
				// the info records for those structures in order.
				$recStructSet->reset();
				while ($recStructSet->hasNext()) {
					$recStructId =& $recStructSet->next();
					if (in_array($recStructId->getIdString(), $this->_recStructsToIgnore))
						continue;
					
					if ($this->hasChangedParts($results, $initialState, $recStructId)) {
						$this->updateAssetRecords($results, $initialState, $recStructId, $asset);
					}
					
				}
			}
	 	}
		
		
// 		printpre($results);
// 		exit;
		
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
		$repositoryId =& $this->getRepositoryId();
		$harmoni =& Harmoni::instance();
		
		if ($assetIdString = RequestContext::value("asset_id")) {
			return $harmoni->request->quickURL("asset", "browse", 
					array("collection_id" => $repositoryId->getIdString(), 
					"asset_id" => $assetIdString));
		} else {
			return $harmoni->request->quickURL("collection", "browse", 
					array("collection_id" => $repositoryId->getIdString()));
		}
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
	
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
	/*********************************************************
	 * :: Asset Properties ::
	 *********************************************************/
		$wizard->addStep("assetproperties", $this->getAssetPropertiesStep());
		
	/*********************************************************
	 *  :: Record Structures ::
	 *********************************************************/
	 	$repository =& $this->getRepository();
	 	$repositoryId =& $this->getRepositoryId();
	 	
	 	// Get the set of RecordStructures so that we can print them in order.
		$setManager =& Services::getService("Sets");
		$recStructSet =& $setManager->getPersistentSet($repositoryId);
		
		// First, lets go through the info structures listed in the set and print out
		// the info records for those structures in order.
		while ($recStructSet->hasNext()) {
			$recStructId =& $recStructSet->next();
			if (in_array($recStructId->getIdString(), $this->_recStructsToIgnore))
				continue;
			
			$recStruct =& $repository->getRecordStructure($recStructId);
			
			$wizard->addStep($recStructId->getIdString(), $this->getRecordStructureStep($recStruct));
			
		}
		
	/*********************************************************
	 *  :: Content ::
	 *********************************************************/
		$wizard->addStep("contentstep", $this->getAssetContentStep());
		
		
		$wizard->initialState = $wizard->getAllValues();
	
		return $wizard;
	}
		
	/**
	 * Add needed styles to the pages head section
	 * 
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function addTableStyles () {
		$harmoni =& Harmoni::instance();
		$style = "
		<style type='text/css'>			
			.edit_table td, th {
// 				border-top: 1px solid;
				padding: 5px;
				padding-bottom: 20px;
				vertical-align: top;
				text-align: left;
			}
			
			.edit_table .mod {
				vertical-align: middle;
				text-align: center;
			}
			
			.desc {
				border-top: 0px solid;
				vertical-align: bottom;
				text-align: center;
				padding-bottom: 5px;
				font-style: italic;
			}
			
		</style>
		";
		$outputHandler =& $harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead().$style);
	}
	
	/**
	 * Answer the content for the AssetPropertiesStep
	 * 
	 * @return string
	 * @access public
	 * @since 10/27/05
	 */
	function getAssetPropertiesContent () {
		print "\n<table class='edit_table' cellspacing='0'>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>";
		print "\n\t\t\t"._("Name");
// 		print "\n"._("The Name for this <em>Asset</em>: ");
		print "\n\t\t</th>";
		print "\n\t\t<td>";
		print "\n\t\t\t[[display_name]]";
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>";
		print "\n\t\t\t"._("Description");
// 		print "\n"._("The Description for this <em>Asset</em>: ");
		print "\n\t\t</th>";
		print "\n\t\t<td>";
		print "\n\t\t\t[[description]]";
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>";
		print "\n\t\t\t"._("Effective Date");
// 		print "\n"._("The date that this <em>Asset</em> becomes effective: ");
		print "\n\t\t</th>";
		print "\n\t\t<td>";
		print "\n\t\t\t[[effective_date]]";
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>";
		print "\n\t\t\t"._("Expiration Date");
// 		print "\n"._("The date that this <em>Asset</em> expires: ");
		print "\n\t\t</th>";
		print "\n\t\t<td>";
		print "\n\t\t\t[[expiration_date]]";
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n</table>";
		$text = ob_get_contents();
		ob_end_clean();
		
		return $text;
	}
	
	/**
	 * Add a table of PartStructure components to the given component.
	 * 
	 * @param object WizardComponent
	 * @return void
	 * @access public
	 * @since 10/27/05
	 */
	function addPartStructureComponents ( &$parentComponent, &$recStruct ) {
		ob_start();
		print "\n<table class='edit_table' cellspacing='0'>";
		
		$partStructs =& $recStruct->getPartStructures();
		while ($partStructs->hasNext()) {
			$partStruct =& $partStructs->next();
			$partStructId =& $partStruct->getId();
		
		// PartStructure
			if (!$partStruct->isRepeatable()) {
				$component =& $this->getSingleValuedPartStructComponent($partStruct);
			} else {
				$component =& $this->getRepeatablePartStructComponent($partStruct);				
			}
			
			$parentComponent->addComponent(
				str_replace(".", "_", $partStructId->getIdString()),
				$component);
			
			
			print "\n\t<tr>";
			print "\n\t\t<th>";
			print "\n\t\t\t".$partStruct->getDisplayName();
	// 		print "\n"._("The Name for this <em>Asset</em>: ");
			print "\n\t\t</th>";
			print "\n\t\t<td>";
			print "\n\t\t\t[[".str_replace(".", "_", $partStructId->getIdString())."]]";
			print "\n\t\t</td>";
			print "\n\t</tr>";
		}
	
		print "\n</table>";
	
		$parentComponent->setContent(ob_get_contents());
		ob_end_clean();
	}
	
	/**
	 * Answer the records in the assets for the given record Structure
	 * 
	 * @param object RecordStructure $recStruct
	 * @return array
	 * @access public
	 * @since 10/27/05
	 */
	function &getRecordsForRecordStructure ( &$recStruct ) {
		$records = array();
		for ($i = 0; $i < count($this->_assets); $i++) {
			$recordIterator =& $this->_assets[$i]->getRecordsByRecordStructure(
				$recStruct->getId());
			
			while ($recordIterator->hasNext()) {
				$records[] =& $recordIterator->next();
			}
		}
		
		return $records;
	}
	
	/**
	 * Answer the component needed to modify a given PartStructure
	 * 
	 * @param object PartStructure $partStruct
	 * @return object WizardComponent
	 * @access public
	 * @since 10/26/05
	 */
	function &getComponentForPartStruct ( &$partStruct ) {
		$partStructType =& $partStruct->getType();
		
		// get the datamanager data type
		$dataType = $partStructType->getKeyword();
		
		// get the correct component for this data type
		$component =& PrimitiveIOManager::createComponent($dataType);

		$hasMethods =& HasMethodsValidatorRule::getRule("setSize");
		if ($hasMethods->check($component))
			$component->setSize(40);
		
		return $component;
	}
	
	/**
	 * Answer true if some of the parts were changed in this record
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Id $recStructId
	 * @return boolean
	 * @access public
	 * @since 10/24/05
	 */
	function hasChangedParts ( &$results, &$initialState, &$recStructId ) {
		if ($results[$recStructId->getIdString()] != $initialState[$recStructId->getIdString()])
			return TRUE;
		
		return FALSE;
	}
	
	/**
	 * Update the records for the recStructId for the asset based on the results
	 * from the wizard.
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Id $recStructId
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateAssetRecords (&$results, &$initialState, &$recStructId, &$asset) {
		printpre("<hr>updateAssetRecords:".$asset->getDisplayName());
		$records =& $asset->getRecordsByRecordStructure($recStructId);
		if (!$records->hasNext()) {
			$record =& $asset->createRecord($recStructId);
			$this->updateRecord($results, $initialState, $record);
		} else {
			while ($records->hasNext()) {
				$this->updateRecord($results, $initialState, $records->next());
			}
		}
	}
	
	/**
	 * Update the given record to reflect the values changed in the wizard
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateRecord (&$results, &$initialState, &$record) {
		$recStruct =& $record->getRecordStructure();

		$recStructId =& $recStruct->getId();
		$recStructIdString = str_replace(".", "_", $recStructId->getIdString());
		
		$partStructs =& $recStruct->getPartStructures();
		while ($partStructs->hasNext()) {
			$partStruct =& $partStructs->next();
			
			$partStructId = $partStruct->getId();
			$partStructIdString = str_replace(".", "_", $partStructId->getIdString());
			
			if ($partStruct->isRepeatable()) {
				$this->updateRepeatablePart(
					$results[$recStructIdString][$partStructIdString], 
					$initialState[$recStructIdString][$partStructIdString], 
					$partStruct, $record);
			} else {
				$this->updateSingleValuedPart(
					$results[$recStructIdString][$partStructIdString],
					$initialState[$recStructIdString][$partStructIdString], 
					$partStruct, $record);
			}
		}
	}
	
	/**
	 * Update the given single valued part to reflect the value changed in the wizard
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateSingleValuedPart (&$partResults, &$partInitialState, 
		&$partStruct, &$record) 
	{
		$partStructId = $partStruct->getId();
		
		$partStructType =& $partStruct->getType();
		$valueObjClass =& $partStructureType->getKeyword();
		
		if ($partResults['checked'] == '1'
			&& ($partInitialState['checked'] =='0'
				|| $partResults['value'] != $partInitialState['value']))
		{
			$parts =& $record->getPartsByPartStructure($partStructId);
			$part =& $parts->next();
			$part->updateValue(String::withvalue($partResults['value']));
		}
	}
	
	/**
	 * Update the given repeatable part to reflect the value changed in the wizard.
	 *
	 * For "Value from Wizard" = wizVal and  "value originally in Part" = partVal
	 *	- If a partVal exists and is equal to a wizVal, leave it alone
	 *	- If a partVal exists, but is not equal to any wizVals, remove it.
	 *	- If a wizVal exists, but no partVals equal to it exist, add a new Part
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateRepeatablePart (&$partResults, &$partInitialState, 
		&$partStruct, &$record) 
	{
		$partStructId = $partStruct->getId();
		$partValsHandled = array();
		
		$parts =& $record->getPartsByPartStructure($partStructId);
		while ($parts->hasNext()) {
			$part =& $parts->next();
			$partVal =& $part->getValue();
			$partStrVal = $partVal->asString();
			
			// Check for existance in the results.
			// if the value is not in the results, remove the part and continue.
			if (!$this->inWizArray($partVal, 'value', $partResults)) {
				$record->deletePart($part->getId());
				$partValsHandled[] = $partStrVal;
				
				$partId =& $part->getId();
				printpre("\tDeleting Part: Id: ".$partId->getIdString()." Value: ".$partStrVal);
				
				continue;
			}
			
			// If the value is in the wizard results, do nothing
			$partValsHandled[] = $partStrVal;
			
			$partId =& $part->getId();
			printpre("\tIgnoring Part: Id: ".$partId->getIdString()." Value: ".$partStrVal);
			
			continue;
		}
		
		// Go through all of the Wizard result values. If any of them haven't
		// been handled and need to be, add them.
		foreach ($partResults as $key => $valueArray) {
			$checked = ($valueArray['partvalue']['checked'] == '1')?true:false;
			$valueStr = $valueArray['partvalue']['value']->asString();
			
			if ($checked && !in_array($valueStr, $partValsHandled)) {
				$part =& $record->createPart($partStructId, $valueArray['partvalue']['value']);
				
				$partId =& $part->getId();
				printpre("\tAdding Part: Id: ".$partId->getIdString()." Value: ".$partStrVal);
			}
		}
	}
	
	/**
	 * Answer true if the wizard results array contains the value for the 
	 * specified key.
	 * 
	 * @param string $val
	 * @param string $key
	 * @param array $parentArray
	 * @return boolean
	 * @access public
	 * @since 10/25/05
	 */
	function inWizArray ($val, $key, $parentArray) {
		foreach ( $parentArray as $i => $componentArray ) {
			foreach ($componentArray as $j => $subComponentArray) {
				if ($j == $key && $val->isEqualTo($subComponentArray))
					return TRUE;
				else if (is_array($subComponentArray) 
					&& isset($subComponentArray[$key])
					&& $val->isEqualTo($subComponentArray[$key]))
				{
					return TRUE;
				}
			}
		}
		return FALSE;
	}
}

?>