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
	
	// First get the set for this structure and start with the parts in the set.
	$setManager =& Services::getService("Sets");
	$partSet =& $setManager->getSet($structureId);
	
	$moduleManager =& Services::getService("InOutModules");
	// if we are dealing with ordered parts, order them
	if ($partSet->count()) {
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
	
		$allParts =& array_merge($orderedPartsToPrint, $partsToPrint);
		$moduleManager->createWizardStepsForParts($record, $wizard, $allParts);
	}
	
	// Otherwise just add steps for all parts.
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
	
	$moduleManager =& Services::getService("InOutModules");
	
	$moduleManager->updateFromWizard($record, $wizard);
	
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