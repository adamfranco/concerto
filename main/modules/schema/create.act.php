<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');

// Create the wizard.
 if ($_SESSION['create_schema_wizard_'.$harmoni->pathInfoParts[2]]) {
 	$wizard =& $_SESSION['create_schema_wizard_'.$harmoni->pathInfoParts[2]];
 } else {
 	
 	// Make sure we have a valid DR
	$shared =& Services::getService("Shared");
	$drManager =& Services::getService("DR");
	$id =& $shared->getId($harmoni->pathInfoParts[2]);
	$dr =& $drManager->getDigitalRepository($id);

	// Instantiate the wizard, then add our steps.
	$wizard =& new Wizard(_("Create a Schema"));
	$_SESSION['create_schema_wizard_'.$harmoni->pathInfoParts[2]] =& $wizard;
	
	// :: Step One ::
	$stepOne =& $wizard->createStep(_("Name & Description"));
	
	// Create the properties.
	$displayNameProp =& $stepOne->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
	$displayNameProp->setDefaultValue($dr->getDisplayName());
	$displayNameProp->setErrorString(" <span style='color: f00'>* "._("The name must not start with a space.")."</span>");
	
	$descriptionProp =& $stepOne->createProperty("description", new RegexValidatorRule(".*"));
	$descriptionProp->setDefaultValue($dr->getDescription());
	
	// Create the step text
	$stepOneText = "\n<h2>"._("Name")."</h2>";
	$stepOneText .= "\n"._("The Name for this Schema: ");
	$stepOneText .= "\n<br><input type='text' name='display_name' value=\"[[display_name]]\">[[display_name|Error]]";
	$stepOneText .= "\n<h2>"._("Description")."</h2>";
	$stepOneText .= "\n"._("The Description for this Schema: ");
	$stepOneText .= "\n<br><textarea name='description'>[[description]]</textarea>[[description|Error]]";
	$stepOneText .= "\n<div style='width: 400px'> &nbsp; </div>";
	$stepOne->setText($stepOneText);
	
	
	// :: Add Elements ::
	$elementStep =& $wizard->createStep(_("Add Elements"));
	
	// get an iterator of all InfoStructures
	$infoStructures =& $dr->getInfoStructures();
	
	$text = "<h2>"._("Add a new Element")."</h2>";
	$text .= "\n<p>"._("")."</p>";
	$text .= "\n<p>"._("If none of the schemata listed below fit your needs, please click the button below to save your changes and create a new schema.")."</p>";
	$text .= "\n<input type='submit' name='create_schema' value='"._("Save Changes and Create a new Schema")."'>";
	
	while ($infoStructures->hasNext()) {
		$infoStructure =& $infoStructures->next();
		$infoStructureId =& $infoStructure->getId();
		
		// Create the properties.
		$property =& $elementStep->createProperty("schema_".$infoStructureId->getIdString(), new RegexValidatorRule(".*"), FALSE);
		$property->setDefaultValue(0);
		
		$text .= "\n<p>\n<input type='checkbox' name='schema_".$infoStructureId->getIdString()."' value='1' [[schema_".$infoStructureId->getIdString()."==1|checked='checked'|]]>";
		$text .= "\n<strong>".$infoStructure->getDisplayName()."</strong>";
		$text .= "\n<br><em>".$infoStructure->getDescription()."</em>\n</p>";
	}
	$elementStep->setText($text);
}


// Prepare the return URL so that we can get back to where we were.
$currentPathInfo = array();
for ($i = 3; $i < count($harmoni->pathInfoParts); $i++) {
	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
}
$returnURL = MYURL."/".implode("/",$currentPathInfo);

if ($wizard->isSaveRequested() || $_REQUEST['create_schema']) {
	// If all properties validate then go through the steps nessisary to
	// save the data.
	if ($wizard->updateLastStep()) {
		$properties =& $wizard->getProperties();
		
		// Save the DR
		$shared =& Services::getService("Shared");
		$id =& $shared->getId($harmoni->pathInfoParts[2]);
		
		$drManager =& Services::getService("DR"); 
		$dr =& $drManager->getDigitalRepository($id);
		
		$dr->updateDisplayName($properties['display_name']->getValue());
		$dr->updateDescription($properties['description']->getValue());
		
		// Save the Schema settings.
		// @todo
		
		// Unset the wizard
		$wizard = NULL;
		unset ($_SESSION['create_schema_wizard_'.$harmoni->pathInfoParts[2]]);
		unset ($wizard);
		
		// Head off to editing our new collection.
		header("Location: ".$returnURL);
	}
	
} else if ($wizard->isCancelRequested()) {
	$wizard = NULL;
	unset ($_SESSION['create_schema_wizard_'.$harmoni->pathInfoParts[2]]);
	unset ($wizard);
	header("Location: ".$returnURL);
	
}

$wizardLayout =& $wizard->getLayout($harmoni);
$centerPane->addComponent($wizardLayout, TOP, CENTER);

// return the main layout.
return $mainScreen;