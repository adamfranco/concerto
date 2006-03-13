<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/modules/asset/AssetEditingAction.class.php");

/**
 * An action to edit multiple assets.
 * 
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class multieditAction 
	extends AssetEditingAction
{
	
	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 10/26/05
	 */
	function multieditAction () {
		$this->_init();
				
		$this->_recStructsToIgnore[] = 'FILE';
		
		$assetList = RequestContext::value("assets");
		$this->_cacheName = 'edit_asset_wizard_'.preg_replace("/[^a-zA-Z0-9]/", "_", $assetList);
		$this->_loadAssets(explode(",", RequestContext::value("assets")));
	}
	
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
		foreach( array_keys($this->_assets) as $key) {
			if (!$authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify"), 
					$this->_assets[$key]->getId()))
			{
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to modify one or more of these <em>Assets</em>.");
	}
	
		/**
	 * Answer the asset properties Wizard step
	 * 
	 * @param object Wizard $wizard
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function &getAssetPropertiesStep () {
		/*********************************************************
	 *  :: Asset Properties ::
	 *********************************************************/
		$step =& new WizardStep();
		$step->setDisplayName(_("Basic Properties"));
		
	// Display Name
		$vProperty =& $step->addComponent("display_name", new WVerifiedChangeInput);
		$property =& $vProperty->setInputComponent(new WTextField);
		$property->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$property->setSize(40);

		$value = $this->_assets[0]->getDisplayName();
		$multipleExist = FALSE;
		for ($i = 1; $i < count($this->_assets); $i++) {
			if ($this->_assets[$i]->getDisplayName() != $value) {
				$multipleExist = TRUE;
				break;
			}
		}
		if ($multipleExist) {
			$property->setStartingDisplayText($this->_multExistString);
		} else {
	 		$property->setValue($value);
	 		$vProperty->setChecked(true);
	 	}

	// Description	
		$vProperty =& $step->addComponent("description", new WVerifiedChangeInput);
		$property =& $vProperty->setInputComponent(new WTextArea);
		$property->setRows(3);
		$property->setColumns(40);
		
		$value = $this->_assets[0]->getDescription();
		$multipleExist = FALSE;
		for ($i = 1; $i < count($this->_assets); $i++) {
			if ($this->_assets[$i]->getDescription() != $value) {
				$multipleExist = TRUE;
				break;
			}
		}
		if ($multipleExist) {
			$property->setStartingDisplayText($this->_multExistString);
		} else {
	 		$property->setValue($value);
	 		$vProperty->setChecked(true);
	 	}
				
	// Effective Date
		$vProperty =& $step->addComponent("effective_date", new WVerifiedChangeInput);
		$property =& $vProperty->setInputComponent(new WTextField);
		$property->setSize(40);
		
		if (is_object($this->_assets[0]->getEffectiveDate()))
			$date =& $this->_assets[0]->getEffectiveDate();
		else
			$date = null;
		
		$multipleExist = FALSE;
		for ($i = 1; $i < count($this->_assets); $i++) {
			if (($date && !$date->isEqualTo($this->_assets[$i]->getEffectiveDate()))
				|| (!$date && $this->_assets[$i]->getEffectiveDate()))
			{
				$multipleExist = TRUE;
				break;
			}
		}
		if ($multipleExist) {
			$property->setStartingDisplayText($this->_multExistString);
		} else if ($date) {
	 		$date =& $date->asDate();
			$property->setValue($date->yyyymmddString());
	 		$vProperty->setChecked(true);
	 	} else {
	 		$vProperty->setChecked(true);
	 	}
	
	// Expiration Date
		$vProperty =& $step->addComponent("expiration_date", new WVerifiedChangeInput);
		$property =& $vProperty->setInputComponent(new WTextField);
		$property->setSize(40);
				
		if (is_object($this->_assets[0]->getExpirationDate()))
			$date =& $this->_assets[0]->getExpirationDate();
		else
			$date = null;
		$multipleExist = FALSE;
		for ($i = 1; $i < count($this->_assets); $i++) {
			if (($date && !$date->isEqualTo($this->_assets[$i]->getExpirationDate()))
				|| (!$date && $this->_assets[$i]->getExpirationDate()))
			{
				$multipleExist = TRUE;
				break;
			}
		}
		if ($multipleExist) {
			$property->setStartingDisplayText($this->_multExistString);
		} else if ($date) {
	 		$date =& $date->asDate();
			$property->setValue($date->yyyymmddString());
			$vProperty->setChecked(true);
	 	} else {
	 		$vProperty->setChecked(true);
	 	}
	 	
	 	
		$step->setContent($this->getAssetPropertiesContent());
		return $step;
	}
	
	/**
	 * Update the asset properties based on values from the wizard.
	 * 
	 * @param array $results
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function updateAssetProperties ( &$results, &$asset ) {
		// DisplayName
		if ($results['display_name']['checked'] == '1'
			&& $asset->getDisplayName() != $results['assetproperties']['display_name']['value']) 
		{
			$asset->updateDisplayName($results['display_name']['value']);
		}
		
		// Description
		if ($results['description']['checked'] == '1'
			&& $asset->getDescription() != $results['description']['value']) 
		{
			$asset->updateDescription($results['description']['value']);
		}
		
		// Effective Date
		if ($results['effective_date']['checked'] == '1') {
			$effDate = $asset->getEffectiveDate();
			$newEffDate =& DateAndTime::fromString($results['effective_date']['value']);
			if (is_object($effDate) && !$effDate->isEqualTo($newEffDate))
				$asset->updateEffectiveDate($newEffDate);
		}
		
		// Expiration Date
		if ($results['expiration_date']['checked'] == '1') {
			$expDate = $asset->getEffectiveDate();
			$newExpDate =& DateAndTime::fromString($results['expiration_date']['value']);
			if (is_object($expDate) && !$expDate->isEqualTo($newExpDate))
				$asset->updateEffectiveDate($newExpDate);
		}
	}
	
	/**
	 * Answer a WizardStep for the AssetContent
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 10/26/05
	 */
	function getAssetContentStep () {
		$step =& new WizardStep();
		$step->setDisplayName(_("Content")." ("._("optional").")");
		
		$vProperty =& $step->addComponent("content", new WVerifiedChangeInput);
		$property =& $vProperty->setInputComponent(new WTextArea);
		$property->setRows(20);
		$property->setColumns(70);
		
		$content =& $this->_assets[0]->getContent();
		$multipleExist = FALSE;
		for ($i = 1; $i < count($this->_assets); $i++) {
			if ($content->isEqualTo($this->_assets[$i]->getContent())) {
				$multipleExist = TRUE;
				break;
			}
		}
		if ($multipleExist) {
			$property->setStartingDisplayText($this->_multExistString);
		} else {
			$vProperty->setChecked(true);
	 		$property->setValue($content->asString());
	 	}
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Content")."</h2>";
		print "\n"._("This is an optional place to put content for this <em>Asset</em>. <br />If you would like more structure, you can create new schemas to hold the <em>Asset's</em> data.");
		print "\n<br />[[content]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		return $step;
	}
	
	/**
	 * Update the asset content based on the values from the wizard
	 * 
	 * @param array $results
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function updateAssetContent ( &$results, &$asset ) {
		// Content 
		if ($results['checked'] == '1') {
			$content =& $asset->getContent();
			$newContent =& Blob::withValue($results['value']);
			if (is_object($content) && !$content->isEqualTo($newContent))
				$asset->updateContent($newContent);
		}
	}
	
	/**
	 * Answer a Wizard step for the Record structure passed.
	 * 
	 * @param object RecordStructure $recStruct
	 * @return object WizardStep
	 * @access public
	 * @since 10/26/05
	 */
	function &getRecordStructureStep ( &$recStruct ) {
		$step =& new WizardStep();
		$step->setDisplayName($recStruct->getDisplayName());
		
		$this->addPartStructureComponents($step, $recStruct);
		
		$records =& $this->getRecordsForRecordStructure($recStruct);
		$partStructs =& $recStruct->getPartStructures();
		while ($partStructs->hasNext()) {
			$partStruct =& $partStructs->next();
			$partstructId =& $partStruct->getId();
			$partStructIdString = preg_replace("/[^a-zA-Z0-9:_\-]/", "_", $partstructId->getIdString());
			
			$partComponent =& $step->getChild($partStructIdString);
			
			if ($partStruct->isRepeatable()) {
				$partComponent->setValue(
					$this->getRepeatbleValuesForPartsFromRecords($partStruct, $records));
			} else {
				$partInfo =& $this->getSingleValuedInfoForPartsFromRecords($partStruct, $records);
				if ($partInfo['multiple-exist']) {
					$partComponent->setStartingDisplayText($this->_multExistString);
				} else if ($partInfo['value']) {
					$partComponent->setChecked(true);
					$partComponent->setValue($partInfo['value']);
				} else {
					$partComponent->setChecked(true);
				}
			}
		}
		
		return $step;
	}
	
	/**
	 * Answer a component for a Single-valued Part
	 * 
	 * @param object PartStructure $partStruct
	 * @return object WizardComponent
	 * @access public
	 * @since 10/26/05
	 */
	function &getSingleValuedPartStructComponent ( &$partStruct ) {
		$property =& new WVerifiedChangeInput;
		$property->setInputComponent(
			$this->getComponentForPartStruct($partStruct));
// 		$property->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
// 		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		return $property;
	}
	
	/**
	 * If the part is repeatable, then we will build a list of all parts,
	 * keeping track of which ones are had by all, and which ones are had
	 * just by some.
	 * 
	 * @param object PartStructure $partStruct
	 * @return object WizardComponent
	 * @access public
	 * @since 10/26/05
	 */
	function &getRepeatablePartStructComponent ( &$partStruct ) {
		
	// Make a component for each of values
		$repeatableProperty =& new WNewOnlyEditableRepeatableComponentCollection();
		$repeatableProperty->setStartingNumber(0);
				
		$property =& $repeatableProperty->addComponent('partvalue',
				new WVerifiedChangeInput);
		
		$property =& $property->setInputComponent(
			$this->getComponentForPartStruct($partStruct));
		
		ob_start();
		print "\n\t\t\t<div>";
		print "[[partvalue]]";
		print "\n\t\t\t</div>";
		$repeatableProperty->setElementLayout(ob_get_contents());
		ob_end_clean();
		
		return $repeatableProperty;
	}
	
	/**
	 * Answer the collections of values from the records passed for a given
	 * repetable partstructure
	 * 
	 * @param object PartStructure $partStruct
	 * @param array $records
	 * @return array
	 * @access public
	 * @since 10/27/05
	 */
	function &getRepeatbleValuesForPartsFromRecords( &$partStruct, &$records) {
		$recStruct =& $partStruct->getRecordStructure();
		
	// Build lists of all values.
		$values = array();
		$valueCounts = array();
		$numRecords = 0;
		for ($i = 0; $i < count($records); $i++) {
			$record =& $records[$i];
			$numRecords++;
			$parts =& $record->getPartsByPartStructure($partStruct->getId());
			
			while ($parts->hasNext()) {
				$part =& $parts->next();
				$value =& $part->getValue();
				
				$valueKeyIfExists = false;
				for ($j = 0; $j < count($values); $j++) {
					if ($value->isEqualTo($values[$j])) {
						$valueKeyIfExists = $j;
						break;
					}
				}
				
				if ($valueKeyIfExists === false)
					$valueKeyIfExists = count($values);
				
				$values[$valueKeyIfExists] =& $value;
				if (isset($valueCounts[$valueKeyIfExists]))
					$valueCounts[$valueKeyIfExists]++;
				else
					$valueCounts[$valueKeyIfExists] = 1;
			}
		}
		
		$collections = array();
		for ($i = 0; $i < count($values); $i++) {
			$collections[$i] = array();
			$collections[$i]['partvalue'] = array();
			
			if ($valueCounts[$i] == $numRecords)
				$collections[$i]['partvalue']['checked'] = TRUE;
			else 
				$collections[$i]['partvalue']['checked'] = FALSE;
			
			$collections[$i]['partvalue']['value'] = $values[$i];
		}
		
		return $collections;
	}
		
	/**
	 * Answer an array with the value and a multiple-exist flag for the parts from
	 * the records passed for the given single-valued partstructure
	 * 
	 * @param object PartStructure $partStruct
	 * @param array $records
	 * @return array
	 * @access public
	 * @since 10/27/05
	 */
	function &getSingleValuedInfoForPartsFromRecords( &$partStruct, &$records) {
		$recStruct =& $partStruct->getRecordStructure();
		
	// Build lists of all values.
	// Part Values
		$value = NULL;
		$hasNullParts = FALSE;
		$multipleExist = FALSE;
		for ($i = 0; $i < count($records); $i++) {
			$record =& $records[$i];
			$parts =& $record->getPartsByPartStructure($partStruct->getId());
			
			if (!$parts->hasNext()) {
				$hasNullParts = TRUE;
				
				if ($value === NULL)
					continue;
				else {
					$multipleExist = TRUE;
					break;
				}	
			}
			// Since we are here, we have non-null parts in this record.
			// if others have null parts, then Multiple values exist
			else if ($hasNullParts) {
				$multipleExist = TRUE;
				break;
			}
			
			// Initialize our value or make sure that all are null.
			if ($value === NULL) {
				// If we have a value, initialize to it
				$part =& $parts->next();
				$value =& $part->getValue();
			}
			
			while ($parts->hasNext()) {
				$part =& $parts->next();
				if (!$value->isEqualTo($part->getValue())) {
					$multipleExist = TRUE;
					break;
				}
			}
		
			if ($multipleExist)
				break;
		}
		
		$results = array();
		$results['multiple-exist'] = $multipleExist;
		$results['value'] =& $value;
		return $results;
	}
	
	/**
	 * Answer the step for setting the parent of the asset
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 12/15/05
	 */
	function &getParentStep () {
		// :: Parent ::
		$step =& new WizardStep();
		$step->setDisplayName(_("Parent")." ("._("optional").")");
		
		// Create the properties.
		$vProperty =& $step->addComponent("parent", new WVerifiedChangeInput);
		$property =& $vProperty->setInputComponent(new WSelectList);
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Parent <em>Asset</em>")."</h2>";
		print "\n"._("Optionally select one of the <em>Assets</em> below if you wish to make these assets the children of another asset: ");
		print "\n<br />[[parent]]";
		
		$step->setContent(ob_get_clean());
				
		
		$harmoni =& Harmoni::instance();
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		$multipleValuesExist = false;
		$commonParentId = null;
		$commonParent = null;
		$excluded = array();
		for ($i = 0; $i < count($this->_assets); $i++) {
			$assetId = $this->_assets[$i]->getId();
			$excluded[] = $assetId->getIdString();
			
			$parents =& $this->_assets[$i]->getParents();
			if ($parents->hasNext()) {
				$parent =& $parents->next();
				$parentId =& $parent->getId();
				
				// If we are at the first asset and there is a parent, use it and continue
				if ($i == 0) {
					$commonParentId =& $parentId;
					$commonParent =& $parent;
					continue;
				}
				
				// if we are just now hitting an id after passing assets
				// without parents, then multiple values exist
				if ($i > 0 && $commonParentId == null) {
					$multipleValuesExist = true;
					continue;
				}
				
				// If we have different parent Ids...
				if (!$parentId->isEqual($commonParentId)) {
					$multipleValuesExist = true;
					
					unset($commonParentId);
					$commonParentId = null;
					unset($commonParent);
					$commonParent = null;
					
					continue;
				}
			}
			
			$descendentInfo =& $this->_assets[$i]->getDescendentInfo();
			while ($descendentInfo->hasNext()) {
				$info =& $descendentInfo->next();
				$childId =& $info->getNodeId();
				if (!in_array($childId->getIdString(), $excluded))
					$excluded[] = $childId->getIdString();
			}
		}
		
		
		
		// Check for authorization to remove the existing parent.
		if (!$multipleValuesExist && is_object($commonParentId)) {
			// If we aren't authorized to change the parent, just use it as the only option.
			if ($authZManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.remove_children"),
				$commonParentId))
			{
				$property->addOption("NONE", _("None"));
				$property->addOption(
					$commonParentId->getIdString(), 
					$commonParentId->getIdString()." - ".$commonParent->getDisplayName());
				$property->setValue($commonParentId->getIdString());
				$vProperty->setChecked(true);
			} else {
				$property->addOption(
					$commonParentId->getIdString(), 
					$commonParentId->getIdString()." - ".$commonParent->getDisplayName());
				$property->setValue($commonParentId->getIdString());
				$vProperty->setChecked(true);

				return $step;
			}
		} else if (!$multipleValuesExist) {
			$vProperty->setChecked(true);
			$property->addOption("NONE", _("None"));
			$property->setValue("NONE");
		} 
		// Multiple values exist
		else {
			$property->addOption("", _("(multiple values exist)"));
			$property->setValue("");
		}
		
		$property->_startingDisplay = "";
	
		
		// print options for the rest of the assets
		$repository =& $this->_assets[0]->getRepository();
		$assets =& $repository->getAssets();
		while ($assets->hasNext()) {
			$asset =& $assets->next();
			$assetId =& $asset->getId();
			if ($authZManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$assetId)
				&& (!is_object($commonParentId) || !$assetId->isEqual($commonParentId))
				&& !in_array($assetId->getIdString(), $excluded))
			{
				$property->addOption($assetId->getIdString(), $assetId->getIdString()." - ".$asset->getDisplayName());
			}
		}
		
		return $step;
	}
}