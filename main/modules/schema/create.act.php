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
	$displayNameProp =& $stepOne->createProperty("schema_display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
	$displayNameProp->setDefaultValue("Default Schema Name");
	$displayNameProp->setErrorString(" <span style='color: f00'>* "._("The name must not start with a space.")."</span>");

	$formatProp =& $stepOne->createProperty("schema_format", new RegexValidatorRule(".*"));
	$formatProp->setDefaultValue("Plain Text - UTF-8 encoding");
	
	$descriptionProp =& $stepOne->createProperty("schema_description", new RegexValidatorRule(".*"));
	$descriptionProp->setDefaultValue("Default Schema description.");
	
	
	// Create the step text
	ob_start();
	print "\n<h2>"._("Name")."</h2>";
	print "\n"._("The Name for this Schema: ");
	print "\n<br><input type='text' name='schema_display_name' value=\"[[schema_display_name]]\">[[schema_display_name|Error]]";
	print "\n<h2>"._("Description")."</h2>";
	print "\n"._("The Description for this Schema: ");
	print "\n<br><textarea name='schema_description'>[[schema_description]]</textarea>[[schema_description|Error]]";
	print "\n<h2>"._("Format")."</h2>";
	print "\n"._("The format of data that is entered into the fields: ");
	print "\n<br /><em>"._("'Plain Text - ASCII encoding', 'XML', etc.")."</em>";
	print "\n<br><input type='text' name='schema_format' value=\"[[schema_format]]\" size='25'>[[schema_format|Error]]";
	print "\n<div style='width: 400px'> &nbsp; </div>";
	$stepOne->setText(ob_get_contents());
	ob_end_clean();
		
	
	// :: Add Elements ::
	$elementStep =& $wizard->addStep(new MultiValuedWizardStep(_("Add Elements")));
	$_SESSION['create_schema_wizard_'.$harmoni->pathInfoParts[2]."element_step"] =& $elementStep;
	
	$property =& $elementStep->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
	$property->setDefaultValue("");
	$property->setErrorString(" <span style='color: f00'>* "._("The name must not start with a space.")."</span>");
	
	$property =& $elementStep->createProperty("description", new RegexValidatorRule(".*"));
	$property->setDefaultValue("");
	
	$property =& $elementStep->createProperty("type", new RegexValidatorRule(".*"));
	$property->setDefaultValue("DR/Harmoni/string");
	
	$property =& $elementStep->createProperty("mandatory", new RegexValidatorRule(".*"));
	$property->setDefaultValue("FALSE");
	
	$property =& $elementStep->createProperty("repeatable", new RegexValidatorRule(".*"));
	$property->setDefaultValue("FALSE");
	
	$property =& $elementStep->createProperty("populatedbydr", new RegexValidatorRule(".*"));
	$property->setDefaultValue("FALSE");
	
	// We don't have any InfoParts yet, so we can't get them.
	
	ob_start();
	print "<h2>"._("Add New Elements")."</h2>";
	print "\n<p>"._("If none of the schemata listed below fit your needs, please click the button below to save your changes and create a new schema.")."</p>";
	
	print "\n<table border=\"0\">";
		print "\n<tr><td>";
			print "DisplayName: ";
		print "\n</td><td>";
			print "<input type='text' name='display_name' value=\"[[display_name]]\">[[display_name|Error]]";
		print "\n</td></tr>";
		print "\n<tr><td>";
			print "Description: ";
		print "\n</td><td>";
			print "<textarea name=\"description\" rows=\"3\" cols=\"25\">[[description]]</textarea>[[description|Error]]";
		print "\n</td></tr>";
		print "\n<tr><td>";
			print "Select a Type... ";
		print "\n</td><td>";
			print "\n<select name=\"type\">";
			// We are going to assume that all InfoStructures have the same InfoPartTypes
			// in this digitalRepository. This will allow us to list InfoPartTypes before
			// the InfoStructure is actually created.
			$infoStructures =& $dr->getInfoStructures();
			if (!$infoStructures->hasNext())
				throwError(new Error("No InfoStructures availible.", "Concerto"));
			
			// just get the fist one to use:
			$infoStructure =& $infoStructures->next();
			$types =& $infoStructure->getInfoPartTypes();
			while ($types->hasNext()) {
				$type =& $types->next();
				$typeString = urlencode($type->getDomain())."/".urlencode($type->getAuthority())."/".urlencode($type->getKeyword());
				print "\n<option value=\"".$typeString."\" [['type'=='".$typeString."'| selected|]]>";
				print $type->getDomain()." :: ".$type->getAuthority()." :: ".$type->getKeyword();
				print "</option>";
			}
			print "\n</select>[[type|Error]]";
		print "\n</td></tr>";

		print "\n<tr><td>";
			print "isMandatory? ";
		print "\n</td><td>";
			print "<input type=\"radio\" name='mandatory' value='TRUE' [['mandatory'=='TRUE'| checked|]] />TRUE / ";
			print "<input type=\"radio\" name='mandatory' value='FALSE' [['mandatory'=='FALSE'| checked|]] /> FALSE";
		print "\n</td></tr>";
		
		print "\n<tr><td>";
			print "isRepeatable? ";
		print "\n</td><td>";
			print "<input type=\"radio\" name='repeatable' value='TRUE' [['repeatable'=='TRUE'| checked|]] />TRUE / ";
			print "<input type=\"radio\" name='repeatable' value='FALSE' [['repeatable'=='FALSE'| checked|]] /> FALSE";
		print "\n</td></tr>";
		
		print "\n<tr><td>";
			print "isPopulatedByDR? ";
		print "\n</td><td>";
			print "<input type=\"radio\" name='populatedbydr' value='TRUE' [['populatedbydr'=='TRUE'| checked|]] />TRUE / ";
			print "<input type=\"radio\" name='populatedbydr' value='FALSE' [['populatedbydr'=='FALSE'| checked|]] /> FALSE";
		print "\n</td></tr>";
		
		print "</table>";
	
	print "\n<br />[Buttons]";
	print "\n<hr>";
	print "Elements Added:";
	print "\n<table>[List]<tr><td valign='top'>[ListButtons]<br />[ListMoveButtons]</td>";
	print "<td style='padding-bottom: 20px'>";
	print "<strong>DisplayName:</strong> [[display_name]]";
	print "<br /><strong>Description:</strong> [[description]]";
	print "<br /><strong>Type:</strong> [[type]]";
	print "<br /><strong>isMandatory:</strong> [[mandatory]]";
	print "<br /><strong>isRepeatable:</strong> [[repeatable]]";
	print "<br /><strong>isPopulatedByDR:</strong> [[populatedbydr]]";
	print "</td></tr>[/List]</table>";

	$elementStep->setText(ob_get_contents());
	ob_end_clean();
}

// If we've added a new element, add it to the list.


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