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
		$part =& $infoStructure->getInfoPart($partId);
		
		addPartStep($wizard, $dr, $asset, $record, $structure, $part);
	}
	
	// Print out the parts/fields
	foreach (array_keys($partsToPrint) as $key) {
		$partId =& $partsToPrint[$key];
		$part =& $infoStructure->getInfoPart($partId);
		
		addPartStep($wizard, $dr, $asset, $record, $structure, $part);
	}
}

// Prepare the return URL so that we can get back to where we were.
$currentPathInfo = array();
for ($i = 3; $i < count($harmoni->pathInfoParts); $i++) {
	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
}
$returnURL = MYURL."/".implode("/",$currentPathInfo);

if ($wizard->isSaveRequested()) {
		
} else if ($wizard->isCancelRequested()) {
	$wizard = NULL;
	unset ($_SESSION['edit_record_wizard_'.$harmoni->pathInfoParts[4]]);
	unset ($wizard);
	header("Location: ".$returnURL."?__skip_to_step=2");
	
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
	if ($part->getRepeatable()) {
		$step =& $wizard->addStep(new MultiValuedWizardStep($part->getDisplayName()));
	} else {
		$step =& $wizard->createStep($part->getDisplayName());
	}
}