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
	
	// :: Step One ::
	$stepOne =& $wizard->createStep(_("Name & Description"));
	
	// Create the properties.
	$displayNameProp =& $stepOne->createProperty("display_name", "Regex");
	$displayNameProp->setExpression(".*");
	$displayNameProp->setDefaultValue($dr->getDisplayName());
	
	$descriptionProp =& $stepOne->createProperty("description", "Regex");
	$descriptionProp->setExpression(".*");
	$descriptionProp->setDefaultValue($dr->getDescription());
	
	// Create the step text
	$stepOneText = "\n<h2>"._("Name")."</h2>";
	$stepOneText .= "\n"._("The Name for this <em>Collection</em>: ");
	$stepOneText .= "\n<br><input type='text' name='display_name' value=\"[[display_name]]\">";
	$stepOneText .= "\n<h2>"._("Description")."</h2>";
	$stepOneText .= "\n"._("The Description for this <em>Collection</em>: ");
	$stepOneText .= "\n<br><textarea name='description'>[[description]]</textarea>";
	$stepOneText .= "\n<div style='width: 400px'> &nbsp; </div>";
	$stepOne->setText($stepOneText);
	
	
	// :: Schema Selection ::
	$selectStep =& $wizard->createStep(_("Schema Selection"));
	
	// get an iterator of all InfoStructures
	$infoStructures =& $dr->getInfoStructures();
	
	$text = "<h2>"._("Select Cataloging Schemata")."</h2>";
	$text .= _("\nSelect which cataloging schemata you wish to appear during <em>Asset</em> creation and editing. <em>Assets</em> can hold data in any of the schemata, but only the ones selected here will be availible when adding new data.");
	
	while ($infoStructures->hasNext()) {
		$infoStructure =& $infoStructures->next();
		$infoStructureId =& $infoStructure->getId();
		
		// Create the properties.
		$property =& $selectStep->createProperty("schema_".$infoStructureId->getIdString(), "Regex", FALSE);
		$property->setExpression(".*");
		$property->setDefaultValue(0);
		
		$text .= "\n<p>\n<input type='checkbox' name='schema_".$infoStructureId->getIdString()."' value='1' [[schema_".$infoStructureId->getIdString()."==1|checked='checked'|]]>";
		$text .= "\n<strong>".$infoStructure->getDisplayName()."</strong>";
		$text .= "\n<br><em>".$infoStructure->getDescription()."</em>\n</p>";
	}
	$selectStep->setText($text);

// 
// 	// :: Schema Creation ::
// 	
// 	// The createInfoStructure() method is not defined in the DR OSID. It has been
// 	// added to the Harmoni implimentation in order to provide enhanced 
// 	// functionality in dynamically creating info structures.
// 	if (method_exists($dr, "createInfoStructure")) {
// 	
// 		$createStep =& $wizard->createStep(_("Schema Creation"));
// 		
// 		$text = "<h2>"._("Create a new Cataloging Schema")."</h2>";
// 		$text .= _("\nSelect which caltaloging schemata you wish to appear during <em>Asset</em> creation and editing. <em>Assets</em> can hold data in any schema, but only the ones selected here will be availible when adding new data.");
// 		
// 		while ($infoStructures->hasNext()) {
// 			$infoStructure =& $infoStructures->next();
// 			$infoStructureId =& $infoStructure->getId();
// 			
// 			// Create the properties.
// 			$property =& $createStep->createProperty("schema_".$infoStructureId->getIdString(), "Regex");
// 			$property->setExpression(".*");
// 			$property->setDefaultValue(0);
// 			
// 			$text .= "\n<p>\n<input type='checkbox' name='schema_".$infoStructureId->getIdString()."' value=\"[[schema_".$infoStructureId->getIdString()."]]\">";
// 			$text .= "\n<strong>".$infoStructure->getDisplayName()."</strong>";
// 			$text .= "\n<br><em>".$infoStructure->getDescription()."</em>\n</p>";
// 		}
// 		$createStep->setText($text);
// 	}
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