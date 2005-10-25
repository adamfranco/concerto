<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");

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
class multieditAction 
	extends RepositoryAction
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
// 		$authZ =& Services::getService("AuthZ");
// 		$idManager =& Services::getService("Id");
// 		return $authZ->isUserAuthorized(
// 					$idManager->getId("edu.middlebury.authorization.modify"), 
// 					$this->getAssetId());
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
		$harmoni->request->passthrough("collection_id", "asset_id", "assets");
		$centerPane =& $this->getActionRows();
		
		$assetList = RequestContext::value("assets");
		
		$cacheName = 'edit_asset_wizard_'.preg_replace("/[^a-zA-Z0-9]/", "_", $assetList);
				
		// Create the step text
		ob_start();
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
		
		$this->runWizard ( $cacheName, $centerPane );
		
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
	 	
	 	$assets = array();
	 	$assetIds = explode(",", RequestContext::value("assets"));
	 	
	 	foreach ($assetIds as $idString) {
			// @todo Check AuthN
			if (true) {
				$asset =& $repository->getAsset($idManager->getId($idString));
				
				// DisplayName
				if ($results['assetproperties']['display_name']['checked'] == '1'
					&& $asset->getDisplayName() != $results['assetproperties']['display_name']['value']) 
				{
					$asset->updateDisplayName($results['assetproperties']['display_name']['value']);
				}
				
				// Description
				if ($results['assetproperties']['description']['checked'] == '1'
					&& $asset->getDescription() != $results['assetproperties']['description']['value']) 
				{
					$asset->updateDescription($results['assetproperties']['description']['value']);
				}
				
				// Effective Date
				if ($results['assetproperties']['effective_date']['checked'] == '1') {
					$effDate =& $asset->getEffectiveDate();
					$newEffDate =& DateAndTime::fromString($results['assetproperties']['effective_date']['value']);
					if (is_object($effDate) && !$effDate->isEqualTo($newEffDate))
						$asset->updateEffectiveDate($newEffDate);
				}
				
				// Expiration Date
				if ($results['assetproperties']['expiration_date']['checked'] == '1') {
					$expDate =& $asset->getEffectiveDate();
					$newExpDate =& DateAndTime::fromString($results['assetproperties']['expiration_date']['value']);
					if (is_object($expDate) && !$expDate->isEqualTo($newExpDate))
						$asset->updateEffectiveDate($newExpDate);
				}
				
				// Content 
				if ($results['contentstep']['content']['checked'] == '1') {
					$content =& $asset->getContent();
					$newContent =& Blob::withValue($results['contentstep']['content']['value']);
					if (is_object($content) && !$content->isEqualTo($newContent))
						$asset->updateContent($newContent);
				}
				
				// Records
				$repository =& $this->getRepository();
				$repositoryId =& $this->getRepositoryId();
				
				// Get the set of RecordStructures so that we can print them in order.
				$setManager =& Services::getService("Sets");
				$recStructSet =& $setManager->getPersistentSet($repositoryId);
				
				// First, lets go through the info structures listed in the set and print out
				// the info records for those structures in order.
				while ($recStructSet->hasNext()) {
					$recStructId =& $recStructSet->next();
					if ($recStructId->getIdString() == 'FILE')
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
		$multExistString = _("(multiple values exist)");
	
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
	/*********************************************************
	 * Load the assets
	 *********************************************************/
	 	$idManager =& Services::getService("Id");
	 	$repository =& $this->getRepository();
	 	
	 	$assets = array();
	 	$assetIds = explode(",", RequestContext::value("assets"));
	 	
	 	foreach ($assetIds as $idString) {
	 		$assets[] =& $repository->getAsset($idManager->getId($idString));
	 	}
	 	
		
	/*********************************************************
	 *  :: Asset Properties ::
	 *********************************************************/
		$step =& $wizard->addStep("assetproperties", new WizardStep());
		$step->setDisplayName(_("Basic Properties"));
		
	// Display Name
		$vProperty =& $step->addComponent("display_name", new WVerifiedChangeInput);
		$property =& $vProperty->setInputComponent(new WTextField);
		$property->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$property->setSize(50);

		$value = $assets[0]->getDisplayName();
		$multipleExist = FALSE;
		for ($i = 1; $i < count($assets); $i++) {
			if ($assets[$i]->getDisplayName() != $value) {
				$multipleExist = TRUE;
				break;
			}
		}
		if ($multipleExist) {
			$property->setStartingDisplayText($multExistString);
		} else {
	 		$property->setValue($value);
	 		$vProperty->setChecked(true);
	 	}

	// Description	
		$vProperty =& $step->addComponent("description", new WVerifiedChangeInput);
		$property =& $vProperty->setInputComponent(new WTextArea);
		$property->setRows(3);
		$property->setColumns(50);
		
		$value = $assets[0]->getDescription();
		$multipleExist = FALSE;
		for ($i = 1; $i < count($assets); $i++) {
			if ($assets[$i]->getDescription() != $value) {
				$multipleExist = TRUE;
				break;
			}
		}
		if ($multipleExist) {
			$property->setStartingDisplayText($multExistString);
		} else {
	 		$property->setValue($value);
	 		$vProperty->setChecked(true);
	 	}
				
	// Effective Date
		$vProperty =& $step->addComponent("effective_date", new WVerifiedChangeInput);
		$property =& $vProperty->setInputComponent(new WTextField);
	
		$date =& $assets[0]->getEffectiveDate();
		$multipleExist = FALSE;
		for ($i = 1; $i < count($assets); $i++) {
			if (($date && !$value->isEqualTo($assets[$i]->getEffectiveDate()))
				|| (!$date && $assets[$i]->getEffectiveDate()))
			{
				$multipleExist = TRUE;
				break;
			}
		}
		if ($multipleExist) {
			$property->setStartingDisplayText($multExistString);
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
				
		$date =& $assets[0]->getExpirationDate();
		$multipleExist = FALSE;
		for ($i = 1; $i < count($assets); $i++) {
			if (($date && !$value->isEqualTo($assets[$i]->getExpirationDate()))
				|| (!$date && $assets[$i]->getEffectiveDate()))
			{
				$multipleExist = TRUE;
				break;
			}
		}
		if ($multipleExist) {
			$property->setStartingDisplayText($multExistString);
		} else if ($date) {
	 		$date =& $date->asDate();
			$property->setValue($date->yyyymmddString());
			$vProperty->setChecked(true);
	 	} else {
	 		$vProperty->setChecked(true);
	 	}

		
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
		
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		
		
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
			if ($recStructId->getIdString() == 'FILE')
				continue;
			
			$recStruct =& $repository->getRecordStructure($recStructId);
			
			$step =& $wizard->addStep($recStructId->getIdString(), new WizardStep());
			$step->setDisplayName($recStruct->getDisplayName());
			
			ob_start();
			print "\n<table class='edit_table' cellspacing='0'>";
			
			$partStructs =& $recStruct->getPartStructures();
			while ($partStructs->hasNext()) {
				$partStruct =& $partStructs->next();
				$partStructId =& $partStruct->getId();
			
			// PartStructure
				if (!$partStruct->isRepeatable()) {
					$property =& $step->addComponent(str_replace(".", "_", $partStructId->getIdString()),
						new WVerifiedChangeInput);
					$property =& $property->setInputComponent(new WTextField);
	// 				$property->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
	// 				$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
					$property->setSize(50);
					
				// Part Values
					$value = NULL;
					$hasNullParts = FALSE;
					$multipleExist = FALSE;
					for ($i = 0; $i < count($assets); $i++) {
						$records =& $assets[$i]->getRecordsByRecordStructure($recStructId);
						
						while ($records->hasNext()) {
							$record =& $records->next();
							$parts =& $record->getPartsByPartStructure($partStructId);
							
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
						
						if ($multipleExist)
								break;
					}
					
					if ($multipleExist) {
						$property->setStartingDisplayText($multExistString);
					} else if ($value) {
						$property->setChecked(true);
						$property->setValue($value->asString());
					} else {
						$property->setChecked(true);
					}
				}

				// If the part is repeatable, then we will build a list of all parts,
				// keeping track of which ones are had by all, and which ones are had
				// just by some.
				else {
					
				// Build lists of all values.
					$values = array();
					$valueCounts = array();
					$numRecords = 0;
					for ($i = 0; $i < count($assets); $i++) {
						$records =& $assets[$i]->getRecordsByRecordStructure($recStructId);
						
						while ($records->hasNext()) {
							$record =& $records->next();
							$numRecords++;
							$parts =& $record->getPartsByPartStructure($partStructId);
							
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
					}
					
				// Make a component for each of values
					$repeatableProperty =& $step->addComponent(str_replace(".", "_", $partStructId->getIdString()),
						new WNewOnlyEditableRepeatableComponentCollection());
					$repeatableProperty->setStartingNumber(0);
							
					$property =& $repeatableProperty->addComponent('partvalue',
							new WVerifiedChangeInput);
					
					$property =& $property->setInputComponent(new WTextField);
					$property->setSize(50);
// 					$property->setReadOnly(true);
					
					ob_start();
					print "\n\t\t\t<div>";
					print "[[partvalue]]";
					print "\n\t\t\t</div>";
					$repeatableProperty->setElementLayout(ob_get_contents());
					ob_end_clean();
					
					for ($i = 0; $i < count($values); $i++) {
						$valueCollection = array();
						$valueCollection['partvalue'] = array();
						
						if ($valueCounts[$i] == $numRecords)
							$valueCollection['partvalue']['checked'] = TRUE;
						else 
							$valueCollection['partvalue']['checked'] = FALSE;
						
						$valueCollection['partvalue']['value'] = $values[$i]->asString();
						$repeatableProperty->addValueCollection($valueCollection);
					}					
				}
				
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
		
			$step->setContent(ob_get_contents());
			ob_end_clean();
		}
		
	/*********************************************************
	 *  :: Content ::
	 *********************************************************/
		$step =& $wizard->addStep("contentstep", new WizardStep());
		$step->setDisplayName(_("Content")." ("._("optional").")");
		
		$vProperty =& $step->addComponent("content", new WVerifiedChangeInput);
		$property =& $vProperty->setInputComponent(new WTextArea);
		$property->setRows(20);
		$property->setColumns(70);
		
		$content =& $assets[0]->getContent();
		$multipleExist = FALSE;
		for ($i = 1; $i < count($assets); $i++) {
			if ($content->isEqualTo($assets[$i]->getContent())) {
				$multipleExist = TRUE;
				break;
			}
		}
		if ($multipleExist) {
			$property->setStartingDisplayText($multExistString);
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
		
		$wizard->initialState = $wizard->getAllValues();
	
		return $wizard;
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
			if (!$this->inWizArray($partStrVal, 'value', $partResults)) {
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
			$valueStr = $valueArray['partvalue']['value'];
			
			if ($checked && !in_array($valueStr, $partValsHandled)) {
				$part =& $record->createPart($partStructId, String::withvalue($valueArray['partvalue']['value']));
				
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
				if ($j == $key && $subComponentArray == $val)
					return TRUE;
				else if (is_array($subComponentArray) 
					&& isset($subComponentArray[$key])
					&& $subComponentArray[$key] == $val)
				{
					return TRUE;
				}
			}
		}
		return FALSE;
	}
}