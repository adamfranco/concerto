<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');

// Create the wizard.
 if ($_SESSION['edit_collection_wizard_'.$harmoni->pathInfoParts[2]]) {
 	$wizard =& $_SESSION['edit_collection_wizard_'.$harmoni->pathInfoParts[2]];
 } else {
 	
 	// Make sure we have a valid DR
	$shared =& Services::getService("Shared");
	$drManager =& Services::getService("DR");
	$id =& $shared->getId($harmoni->pathInfoParts[2]);
	$dr =& $drManager->getDigitalRepository($id);

	// Instantiate the wizard, then add our steps.
	$wizard =& new Wizard(_("Create a Collection"));
	$_SESSION['edit_collection_wizard_'.$harmoni->pathInfoParts[2]] =& $wizard;
	
	
	// :: Scheme Selection ::
	$selectStep =& $wizard->createStep(_("Scheme Selection"));
	
	// get an iterator of all InfoStructures
	$infoStructures =& $dr->getInfoStructures();
	
	$text = "<h2>"._("Select Cataloging Schemes")."</h2>";
	$text .= _("\nSelect which caltaloging schemes you wish to appear during <em>Asset</em> creation and editing. <em>Assets</em> can hold data in any of the schemes, but only the ones selected here will be availible when adding new data.");
	
	while ($infoStructures->hasNext()) {
		$infoStructure =& $infoStructures->next();
		$infoStructureId =& $infoStructure->getId();
		
		// Create the properties.
		$property =& $selectStep->createProperty("scheme_".$infoStructureId->getIdString(), "Regex");
		$property->setExpression(".*");
		$property->setDefaultValue(0);
		
		$text .= "\n<p>\n<input type='checkbox' name='scheme_".$infoStructureId->getIdString()."' value=\"[[scheme_".$infoStructureId->getIdString()."]]\">";
		$text .= "\n<strong>".$infoStructure->getDisplayName()."</strong>";
		$text .= "\n<br><em>".$infoStructure->getDescription()."</em>\n</p>";
	}
	$selectStep->setText($text);


	// :: Scheme Creation ::
	$selectStep =& $wizard->createStep(_("Scheme Creation"));
	
	// get an iterator of all InfoStructures
	$infoStructures =& $dr->getInfoStructures();
	
	$text = "<h2>"._("Create a new Cataloging Scheme")."</h2>";
	$text .= _("\nSelect which caltaloging schemes you wish to appear during <em>Asset</em> creation and editing. <em>Assets</em> can hold data in any of the schemes, but only the ones selected here will be availible when adding new data.");
	
	while ($infoStructures->hasNext()) {
		$infoStructure =& $infoStructures->next();
		$infoStructureId =& $infoStructure->getId();
		
		// Create the properties.
		$property =& $selectStep->createProperty("scheme_".$infoStructureId->getIdString(), "Regex");
		$property->setExpression(".*");
		$property->setDefaultValue(0);
		
		$text .= "\n<p>\n<input type='checkbox' name='scheme_".$infoStructureId->getIdString()."' value=\"[[scheme_".$infoStructureId->getIdString()."]]\">";
		$text .= "\n<strong>".$infoStructure->getDisplayName()."</strong>";
		$text .= "\n<br><em>".$infoStructure->getDescription()."</em>\n</p>";
	}
	$selectStep->setText($text);
}

if ($_REQUEST['save'] || $_REQUEST['save_link']) {
	// If all properties validate then go through the steps nessisary to
	// save the data.
	if ($wizard->updateLastStep()) {
		$properties =& $wizard->getProperties();
		print "Now Saving: ";
		printpre($properties);
		
		// Create the dr and get its id.
		$drManager =& Services::getService("DR");
		$dr =& $drManager->createDigitalRepository(
							$properties['display_name']->getValue(),
							$properties['description']->getValue());
		
		// Unset the wizard
		$wizard = NULL;
		unset ($_SESSION['edit_collection_wizard_'.$id->getIdString()]);
		unset ($wizard);
		
		// Head off to editing our new collection.
		$id =& $dr->getId();
		header(header("Location: ".MYURL."/collection/edit/".$id->getIdString()."/"));
	}
	
} else if ($_REQUEST['cancel'] || $_REQUEST['cancel_link']) {
	$wizard = NULL;
	unset ($_SESSION['edit_collection_wizard_'.$harmoni->pathInfoParts[2]]);
	unset ($wizard);
	header(header("Location: ".MYURL."/collections/namebrowse/"));
	
} else if ($_REQUEST['next'] && $wizard->hasNext())
	$wizard->next();

else if ($_REQUEST['previous'] && $wizard->hasPrevious())
	$wizard->previous();

else if ($_REQUEST['go_to_step'])
	$wizard->goToStep($_REQUEST['go_to_step']);

$wizardLayout =& $wizard->getLayout($harmoni);
$centerPane->addComponent($wizardLayout, TOP, CENTER);

// return the main layout.
return $mainScreen;