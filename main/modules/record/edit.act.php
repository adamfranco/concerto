<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');

// Check for our authorization function definitions
if (!defined("AZ_EDIT"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.collection", true));

// Check that the user can access this collection
$authZ =& Services::getService("AuthZ");
$idManager =& Services::getService("Id");
$id =& $idManager->getId($harmoni->pathInfoParts[3]);
if (!$authZ->isUserAuthorized($idManager->getId(AZ_EDIT), $id)) {
	$errorLayout =& new SingleContentLayout;
	$errorLayout->addComponent(new Content(_("You are not authorized to edit this <em>Asset</em>."), MIDDLE, CENTER));
	$centerPane->addComponent($errorLayout, MIDDLE, CENTER);
	return $mainScreen;
}

// Create the wizard.
 if ($_SESSION['edit_record_wizard_'.$harmoni->pathInfoParts[4]]) {
 	$wizard =& $_SESSION['edit_record_wizard_'.$harmoni->pathInfoParts[4]];
 } else {
 	
 	// Make sure we have a valid Repository
	$idManager =& Services::getService("Id");
	$repositoryManager =& Services::getService("Repository");
	$repositoryId =& $idManager->getId($harmoni->pathInfoParts[2]);
	$assetId =& $idManager->getId($harmoni->pathInfoParts[3]);
	$recordId =& $idManager->getId($harmoni->pathInfoParts[4]);

	$repository =& $repositoryManager->getRepository($repositoryId);
	$asset =& $repository->getAsset($assetId);
	$record =& $asset->getRecord($recordId);
	$structure =& $record->getRecordStructure();
	$structureId =& $structure->getId();

	// Instantiate the wizard, then add our steps.
	$wizard =& new Wizard(_("Edit Record"));
	$_SESSION['edit_record_wizard_'.$harmoni->pathInfoParts[4]] =& $wizard;
	
	// First get the set for this structure and start with the partStructure in the set.
	$setManager =& Services::getService("Sets");
	$partStructureSet =& $setManager->getSet($structureId);
	
	$moduleManager =& Services::getService("InOutModules");
	// if we are dealing with ordered partStructures, order them
	if ($partStructureSet->count()) {
		$orderedPartStructuresToPrint = array();
		$partStructuresToPrint = array();
		
		// get the partStructures and break them up into ordered and unordered arrays.
		$partStructures =& $structure->getPartStructures();
		while ($partStructures->hasNext()) {
			$partStructure =& $partStructures->next();
			$partStructureId =& $partStructure->getId();
			
			if ($partStructureSet->isInSet($partStructureId)) {
				$orderedPartStructuresToPrint[] =& $partStructureId;
			} else {
				$partStructuresToPrint[] =& $partStructureId;
			}
		}
	
		$allPartStructures =& array_merge($orderedPartStructuresToPrint, $partStructuresToPrint);
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
$returnURL = MYURL."/asset/editview/".$harmoni->pathInfoParts[2]."/".$harmoni->pathInfoParts[3]."/";

if ($wizard->isSaveRequested()) {

	// Make sure we have a valid Repository
	$idManager =& Services::getService("Id");
	$repositoryManager =& Services::getService("Repository");
	$repositoryId =& $idManager->getId($harmoni->pathInfoParts[2]);
	$assetId =& $idManager->getId($harmoni->pathInfoParts[3]);
	$recordId =& $idManager->getId($harmoni->pathInfoParts[4]);

	$repository =& $repositoryManager->getRepository($repositoryId);
	$asset =& $repository->getAsset($assetId);
	$record =& $asset->getRecord($recordId);
	
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