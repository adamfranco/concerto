<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');

// Create the wizard.
 if ($_SESSION['edit_record_wizard_'.$harmoni->pathInfoParts[4]]) {
 	$wizard =& $_SESSION['edit_record_wizard_'.$harmoni->pathInfoParts[4]];
 } else {
 	
 	// Make sure we have a valid DR
	$shared =& Services::getService("Shared");
	$drManager =& Services::getService("DR");
	$drId =& $shared->getId($harmoni->pathInfoParts[2]);
	$assetId =& $shared->getId($harmoni->pathInfoParts[3]);
	$recordId =& $shared->getId($harmoni->pathInfoParts[4]);

	$dr =& $drManager->getDigitalRepository($drId);
	$asset =& $dr->getAsset($assetId);
	$record =& $asset->getInfoRecord($recordId);
	$structure =& $record->getInfoStructure();
	$structureId =& $structure->getId();

	// Instantiate the wizard, then add our steps.
	$wizard =& new Wizard(_("Edit Record"));
	$_SESSION['edit_record_wizard_'.$harmoni->pathInfoParts[4]] =& $wizard;
	
// Go through each of the infoParts and create a step for it. If it is 
// multi-valued, make the step multivalued.
	
	// First get the set for this structure and start with the parts in the set.
	$setManager =& Services::getService("Sets");
	$partSet =& $setManager->getSet($structureId);
	$orderedPartsToPrint = array();
	$partsToPrint = array();
	
	// get the parts and break them up into ordered and unordered arrays.
	$parts =& $structure->getInfoParts();
	while ($parts->hasNext()) {
		$part =& $parts->next();
		$partId =& $part->getId();
		
		if ($partSet->isInSet($partId)) {
			$orderedPartsToPrint[] =& $partId;
		} else {
			$partsToPrint[] =& $partId;
		}
	}

	// create the step for the ordered parts
	$partSet->reset();
	while ($partSet->hasNext()) {
		$partId =& $partSet->next();
		$part =& $structure->getInfoPart($partId);
		
		addPartStep($wizard, $dr, $asset, $record, $structure, $part);
	}
	
	// Print out the parts/fields
	foreach (array_keys($partsToPrint) as $key) {
		$partId =& $partsToPrint[$key];
		$part =& $structure->getInfoPart($partId);
		
		addPartStep($wizard, $dr, $asset, $record, $structure, $part);
	}
}

// Prepare the return URL so that we can get back to where we were.
// $currentPathInfo = array();
// for ($i = 3; $i < count($harmoni->pathInfoParts); $i++) {
// 	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
// }
// $returnURL = MYURL."/".implode("/",$currentPathInfo);
$returnURL = MYURL."/asset/editview/".$harmoni->pathInfoParts[2]."/".$harmoni->pathInfoParts[3]."/";

if ($wizard->isSaveRequested()) {

	// Make sure we have a valid DR
	$shared =& Services::getService("Shared");
	$drManager =& Services::getService("DR");
	$drId =& $shared->getId($harmoni->pathInfoParts[2]);
	$assetId =& $shared->getId($harmoni->pathInfoParts[3]);
	$recordId =& $shared->getId($harmoni->pathInfoParts[4]);

	$dr =& $drManager->getDigitalRepository($drId);
	$asset =& $dr->getAsset($assetId);
	$record =& $asset->getInfoRecord($recordId);
	$structure =& $record->getInfoStructure();
	$structureId =& $structure->getId();
	
	$properties =& $wizard->getProperties();
	
	// Delete the old fields
	$fields =& $record->getInfoFields();
	while ($fields->hasNext()) {
		$field =& $fields->next();
		$fieldId =& $field->getId();
		$record->deleteInfoField($fieldId);
		
	}
	
	// Go through each of the parts and save any values as fields.
	$parts = $structure->getInfoParts();
	while ($parts->hasNext()) {
		$part =& $parts->next();
		$partId =& $part->getId();
		$partType = & $part->getType();
		$valueObjClass =& $partType->getKeyword();
		
		if ($part->isRepeatable()) {
			// Add a field for each property
			foreach (array_keys($properties[$partId->getIdString()]) as $setIndex) {
				$set =& $properties[$partId->getIdString()][$setIndex];
				foreach (array_keys($set) as $propertyKey) {
					$property =& $set[$propertyKey];
					$value =& new $valueObjClass($property->getValue());
					$newField =& $record->createInfoField($partId, $value);
				}
			}
		} else {
			// Add a field for the property
			$property =& $properties[$partId->getIdString()];
			$value =& new $valueObjClass($property->getValue());
			$newField =& $record->createInfoField($partId, $value);
		}
	}
	
	$wizard = NULL;
	unset ($_SESSION['edit_record_wizard_'.$harmoni->pathInfoParts[4]]);
	unset ($wizard);
	
	header("Location: ".$returnURL);
	
} else if ($wizard->isCancelRequested()) {
	$wizard = NULL;
	unset ($_SESSION['edit_record_wizard_'.$harmoni->pathInfoParts[4]]);
	unset ($wizard);
	header("Location: ".$returnURL);
	
}

$wizardLayout =& $wizard->getLayout($harmoni);
$centerPane->addComponent($wizardLayout, TOP, CENTER);

// return the main layout.
return $mainScreen;



/**
 * Add a step for an infoPart
 * 
 * @param object Wizard $wizard The wizard to add the step to.
 * @param object DigitalRepository $dr The dr.
 * @param object Asset $asset The asset the record is in.
 * @param object Record $record The Record to modify.
 * @param object InfoStructure $structure The structure the part belongs to.
 * @param object InfoPart $part The part to add the step for.
 * @return void
 * @access public
 * @date 8/30/04
 */
function addPartStep (& $wizard, & $dr, & $asset, & $record, & $structure, & $part) {
	
	$partId =& $part->getId();
	$partType =& $part->getType();
	$fields =& $record->getInfoFields();
	
	// Create the step
	if ($part->isRepeatable()) {
		$step =& $wizard->addStep(new MultiValuedWizardStep($part->getDisplayName(), strval($partId->getIdString())));
	} else {
		$step =& $wizard->createStep($part->getDisplayName());
	}
	
	// Switch for any special part types
	switch (TRUE) {
		
		// default Works for most field types
		default:
			$property =& $step->createProperty(strval($partId->getIdString()),
								new AlwaysTrueValidatorRule,
								$part->isMandatory());
			
			ob_start();
			print "\n<em>".$part->getDescription()."</em>\n<hr />";
			print "\n<br /><strong>".$part->getDisplayName()."</strong>:";
			print " <input type='text'";
			print " name='".$partId->getIdString()."'";
			print " value='[[".$partId->getIdString()."]]'> ";
			print " [[".$partId->getIdString()."|Error]]";
			if ($part->isRepeatable()) {
				print "\n<br />[Buttons] <em>"._("Click here to save the value above.")."</em>";
				print "\n<br /><hr />";
				print _("Already Added:");
				print "\n<table>";
				print "[List]\n<tr>";
				print "\n<td valign='top'>[ListButtons]<br />[ListMoveButtons]</td>";
				print "\n<td style='padding-bottom: 20px'>";
				print "\n\t<strong>".$part->getDisplayName().":</strong>"
					." [[".$partId->getIdString()."]]";
				print "</td>\n</tr>[/List]\n</table>";
			}
			$step->setText(ob_get_contents());
			ob_end_clean();
			
			// If we have fields, load their values as the defaults.
			while ($fields->hasNext()) {
				$field =& $fields->next();
				$currentPart =& $field->getInfoPart();
				
				if ($partId->isEqual($currentPart->getId())) {
					$valueObj =& $field->getValue();
					$property->setValue($valueObj->toString());
					if ($part->isRepeatable()) {
						$step->saveCurrentPropertiesAsNewSet();
					} else {
						break;
					}
				}
			}
	}
}