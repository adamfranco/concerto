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
	$wizard =& new Wizard(_("Edit a Collection"));
	$_SESSION['edit_collection_wizard_'.$harmoni->pathInfoParts[2]] =& $wizard;
	
	// :: Step One ::
	$stepOne =& $wizard->createStep(_("Name & Description"));
	
	// Create the properties.
	$displayNameProp =& $stepOne->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
	$displayNameProp->setDefaultValue($dr->getDisplayName());
	$displayNameProp->setErrorString(" <span style='color: f00'>* "._("The name must not start with a space.")."</span>");
	
	$descriptionProp =& $stepOne->createProperty("description", new RegexValidatorRule(".*"));
	$descriptionProp->setDefaultValue($dr->getDescription());
	
	// Create the step text
	ob_start();
	print "\n<h2>"._("Name")."</h2>";
	print "\n"._("The Name for this <em>Collection</em>: ");
	print "\n<br><input type='text' name='display_name' value=\"[[display_name]]\">[[display_name|Error]]";
	print "\n<h2>"._("Description")."</h2>";
	print "\n"._("The Description for this <em>Collection</em>: ");
	print "\n<br><textarea name='description'>[[description]]</textarea>[[description|Error]]";
	print "\n<div style='width: 400px'> &nbsp; </div>";
	$stepOne->setText(ob_get_contents());
	ob_end_clean();
	
	
	// :: Schema Selection ::
	$selectStep =& $wizard->createStep(_("Schema Selection"));
	
	// get an iterator of all InfoStructures
	$infoStructures =& $dr->getInfoStructures();
	$setManager =& Services::getService("Sets");
	$set =& $setManager->getSet($id);
	
	ob_start();
	print "<h2>"._("Select Cataloging Schemata")."</h2>";
	print "\n<p>"._("Select which cataloging schemata you wish to appear during <em>Asset</em> creation and editing. <em>Assets</em> can hold data in any of the schemata, but only the ones selected here will be availible when adding new data.")."</p>";
	print "\n<p>"._("If none of the schemata listed below fit your needs, please click the button below to save your changes and create a new schema.")."</p>";
	print "\n<input type='submit' name='create_schema' value='"._("Save Changes and Create a new Schema")."'>";

	
	
	print "\n<br><table border='1'>";
	print "\n\t<tr>";
	print "\n\t<th>"._("Display Name")."</th>";
	print "\n\t<th>"._("Description")."</th>";
	print "\n\t<th>"._("Order/Position")."</th>";
	print "\n\t</tr>";
	
	// Get the number of info structures
	$numInfoStructures = 0;
	while ($infoStructures->hasNext()) {
		$infoStructure =& $infoStructures->next();
		$numInfoStructures++;
	}
	
	$infoStructures =& $dr->getInfoStructures();
	while ($infoStructures->hasNext()) {
		$infoStructure =& $infoStructures->next();
		$infoStructureId =& $infoStructure->getId();
		
		// Create the properties.
		// 'in set' property
		$property =& $selectStep->createProperty("schema_".$infoStructureId->getIdString(), new RegexValidatorRule(".*"), FALSE);
		if ($set->isInSet($infoStructureId))
			$property->setDefaultValue(1);
		else
			$property->setDefaultValue(0);
		
		// Order property
		$property =& $selectStep->createProperty("schema_".$infoStructureId->getIdString()."_position", new RegexValidatorRule(".*"), FALSE);
		if ($set->isInSet($infoStructureId))
			$property->setDefaultValue($set->getPosition($infoStructureId)+1);
		else
			$property->setDefaultValue(0);
		
		print "\n<tr><td valign='top'>";
		print "\n\t<input type='checkbox' name='schema_".$infoStructureId->getIdString()."' value='1' [['schema_".$infoStructureId->getIdString()."' == TRUE|checked='checked'|]]>";
		print "\n\t<strong>".$infoStructure->getDisplayName()."</strong>";
		print "\n</td><td valign='top'>\n\t<em>".$infoStructure->getDescription()."</em>";
		print " <a href='".MYURL."/schema/view/".$id->getIdString()."/".$infoStructureId->getIdString()."/".implode("/", $harmoni->pathInfoParts)."?__skip_to_step=2'>more...</a>";
		print "\n</td><td valign='top'>";
		
		print "\n\t<select name='schema_".$infoStructureId->getIdString()."_position'>";
		for ($i=0; $i <= $numInfoStructures; $i++) {
			print "\n\t\t<option value='$i' [['schema_".$infoStructureId->getIdString()."_position' == '$i'|selected='selected'|]]>".(($i)?$i:"")."</option>";
		}
		print "\n\t</select>";
		
		print "\n</td></tr>";
	}
	print "\n</table>";
	
	$selectStep->setText(ob_get_contents());
	ob_end_clean();
}

// Handle saving if requested
if ($wizard->isSaveRequested() || $_REQUEST['create_schema']) {

	// If all properties validate then go through the steps nessisary to
	// save the data.
	if ($wizard->updateLastStep()) {
		$properties =& $wizard->getProperties();
// 		print "Now Saving: ";
// 		printpre($properties);
		
		// Save the DR
		$shared =& Services::getService("Shared");
		$id =& $shared->getId($harmoni->pathInfoParts[2]);
		
		$drManager =& Services::getService("DR"); 
		$dr =& $drManager->getDigitalRepository($id);
		
		$dr->updateDisplayName($properties['display_name']->getValue());
		$dr->updateDescription($properties['description']->getValue());
		
		
	// Save the Schema settings.
		
		// Get the set for this DR
		$setManager =& Services::getService("Sets");
		$set =& $setManager->getSet($id);
		
		// get an iterator of all InfoStructures
		$infoStructures =& $dr->getInfoStructures();
		
		// Store up the positions for later setting after all of the ids have
		// been added to the set and we can do checking to make sure that 
		// the specified positions are valid.
		$positions = array();
		$existingStructures = array();
		$numStructures = 0;
		
		// Go through each InfoStructure
		while ($infoStructures->hasNext()) {
			$infoStructure =& $infoStructures->next();
			$infoStructureId =& $infoStructure->getId();
			
			// If the box is checked, make sure that the ID is in the set
			if ($properties["schema_".$infoStructureId->getIdString()]->getValue()) {
				if (!$set->isInSet($infoStructureId))
					$set->addItem($infoStructureId);
				if ($position = $properties["schema_".$infoStructureId->getIdString()."_position"]->getValue())
					$positions[$position-1] =& $infoStructureId;
				
				// Store some info so that we can check that all structures are valid.
				$existingStructures[] = $infoStructureId->getIdString();
				$numStructures++;
			}
			// Otherwise, remove the ID from the set.
			else {
				if ($set->isInSet($infoStructureId))
					$set->removeItem($infoStructureId);
			}
		}
		
		// Go through the positions and set them all.
		ksort ($positions);
		$countPositions = $set->count();
		foreach (array_keys($positions) as $position) {
			if ($position < 0 || $position >= $countPositions) {
				// move to the last position
				$set->moveToPosition($positions[$position], $countPositions-1);
			} else {
				$set->moveToPosition($positions[$position], $position);
			}
		}
		
		// Remove any infoStructures from the set that may have been removed/
		// made-not-availible by some other application.
		if ($numStructures != $set->count()) {
			$set->reset();
			while($set->hasNext()) {
				$id =& $set->next();
				if (!in_array($id->getIdString(), $existingStructures))
					$set->removeItem($id);
			}
		}
		
		// Unset the wizard
		$wizard = NULL;
		unset ($_SESSION['edit_collection_wizard_'.$id->getIdString()]);
		unset ($wizard);
		
		printpre($dr);
		// Head off to editing our new collection.
		$id =& $dr->getId();
		if ($_REQUEST['create_schema'])
			header("Location: ".MYURL."/schema/create/".$id->getIdString()."/".implode("/",$harmoni->pathInfoParts));
		else
			header("Location: ".MYURL."/collections/namebrowse/");
	}
	
// Handle canceling if requested
} else if ($wizard->isCancelRequested()) {
	$wizard = NULL;
	unset ($_SESSION['edit_collection_wizard_'.$harmoni->pathInfoParts[2]]);
	unset ($wizard);
	header("Location: ".MYURL."/collections/namebrowse/");

// If we have an integer as our 4th path info.
} else if (count($harmoni->pathInfoParts) == 4 
	&& ereg("^[1-9][0-9]*$",$harmoni->pathInfoParts[3]))
	$wizard->goToStep($harmoni->pathInfoParts[3]);

$wizardLayout =& $wizard->getLayout($harmoni);
$centerPane->addComponent($wizardLayout, TOP, CENTER);

// return the main layout.
return $mainScreen;