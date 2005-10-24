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
				
		$properties =& $wizard->getAllValues();
		printpre($properties);
		exit;
// 			
// 		// Update the name and description
// 		$asset->updateDisplayName($properties['namedescstep']['display_name']);
// 		$asset->updateDescription($properties['namedescstep']['description']);
// 		$content =& Blob::withValue($properties['contentstep']['content']);
// 		$asset->updateContent($content);
// 		
// 		
// 		// Update the effective/expiration dates
// 		if ($properties['datestep']['effective_date'])
// 			$asset->updateEffectiveDate(
// 				DateAndTime::fromString($properties['datestep']['effective_date']));
// 		if ($properties['datestep']['expiration_date'])
// 			$asset->updateExpirationDate(
// 				DateAndTime::fromString($properties['datestep']['expiration_date']));
		
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
	
		return $wizard;
	}
}