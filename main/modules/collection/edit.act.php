<?php
/**
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

// Check for our authorization function definitions
if (!defined("AZ_EDIT"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.collection", true));

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');

// Check that the user can access this collection
$authZ =& Services::getService("AuthZ");
$idManager =& Services::getService("Id");
$id =& $idManager->getId($harmoni->pathInfoParts[2]);
if (!$authZ->isUserAuthorized($idManager->getId(AZ_EDIT), $id)) {
	$errorLayout =& new Block("You are not authorized to edit this <em>Collection</em>.",3);
	$centerPane->add($errorLayout,"100%" ,null, CENTER, CENTER);
	return $mainScreen;
}


// Create the wizard.
 if ($_SESSION['edit_collection_wizard_'.$harmoni->pathInfoParts[2]]) {
 	$wizard =& $_SESSION['edit_collection_wizard_'.$harmoni->pathInfoParts[2]];
 } else {
 	
 	// Make sure we have a valid Repository
	$idManager =& Services::getService("Id");
	$repositoryManager =& Services::getService("Repository");
	$id =& $idManager->getId($harmoni->pathInfoParts[2]);
	$repository =& $repositoryManager->getRepository($id);

	// Instantiate the wizard, then add our steps.
	$wizard =& new Wizard(_("Edit a Collection"));
	$_SESSION['edit_collection_wizard_'.$harmoni->pathInfoParts[2]] =& $wizard;
	
	// :: Step One ::
	$stepOne =& $wizard->createStep(_("Name &amp; Description"));
	
	// Create the properties.
	$displayNameProp =& $stepOne->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
	$displayNameProp->setDefaultValue($repository->getDisplayName());
	$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
	
	$descriptionProp =& $stepOne->createProperty("description", new RegexValidatorRule(".*"));
	$descriptionProp->setDefaultValue($repository->getDescription());
	
	// Create the step text
	ob_start();
	print "\n<h2>"._("Name")."</h2>";
	print "\n"._("The Name for this <em>Collection</em>: ");
	print "\n<br /><input type='text' name='display_name' value=\"[[display_name]]\" />[[display_name|Error]]";
	print "\n<h2>"._("Description")."</h2>";
	print "\n"._("The Description for this <em>Collection</em>: ");
	print "\n<br /><textarea name='description' rows='5' cols='30'>[[description]]</textarea>[[description|Error]]";
	print "\n<div style='width: 400px'> &nbsp; </div>";
	$stepOne->setText(ob_get_contents());
	ob_end_clean();
	
	
	// :: Schema Selection ::
	$selectStep =& $wizard->createStep(_("Schema Selection"));
	
	// get an iterator of all RecordStructures
	$recordStructures =& $repository->getRecordStructures();
	$setManager =& Services::getService("Sets");
	$set =& $setManager->getSet($id);
	
	ob_start();
	print "<h2>"._("Select Cataloging Schemata")."</h2>";
	print "\n<p>"._("Select which cataloging schemata you wish to appear during <em>Asset</em> creation and editing. <em>Assets</em> can hold data in any of the schemata, but only the ones selected here will be availible when adding new data.")."</p>";
	print "\n<p>"._("If none of the schemata listed below fit your needs, please click the button below to save your changes and create a new schema.")."</p>";
	print "\n<input type='submit' name='create_schema' value='"._("Save Changes and Create a new Schema")."' />";

	
	
	print "\n<br /><table border='1'>";
	print "\n\t<tr>";
	print "\n\t<th>"._("Display Name")."</th>";
	print "\n\t<th>"._("Description")."</th>";
	print "\n\t<th>"._("Order/Position")."</th>";
	print "\n\t</tr>";
	
	// Get the number of info structures
	$numRecordStructures = 0;
	while ($recordStructures->hasNext()) {
		$recordStructure =& $recordStructures->next();
		$numRecordStructures++;
	}
	
	$recordStructures =& $repository->getRecordStructures();
	while ($recordStructures->hasNext()) {
		$recordStructure =& $recordStructures->next();
		$recordStructureId =& $recordStructure->getId();
		
		// Create the properties.
		// 'in set' property
		$property =& $selectStep->createProperty("schema_".$recordStructureId->getIdString(), new RegexValidatorRule(".*"), FALSE);
		if ($set->isInSet($recordStructureId))
			$property->setDefaultValue(1);
		else
			$property->setDefaultValue(0);
		
		// Order property
		$property =& $selectStep->createProperty("schema_".$recordStructureId->getIdString()."_position", new RegexValidatorRule(".*"), FALSE);
		if ($set->isInSet($recordStructureId))
			$property->setDefaultValue($set->getPosition($recordStructureId)+1);
		else
			$property->setDefaultValue(0);
		
		print "\n<tr><td valign='top'>";
		print "\n\t<input type='checkbox' name='schema_".$recordStructureId->getIdString()."' value='1' [['schema_".$recordStructureId->getIdString()."' == TRUE|checked='checked'|]] />";
		print "\n\t<strong>".$recordStructure->getDisplayName()."</strong>";
		print "\n</td><td valign='top'>\n\t<em>".$recordStructure->getDescription()."</em>";
		print " <a href='".MYURL."/schema/view/".$id->getIdString()."/".$recordStructureId->getIdString()."/".implode("/", $harmoni->pathInfoParts)."?__skip_to_step=2'>more...</a>";
		print "\n</td><td valign='top'>";
		
		print "\n\t<select name='schema_".$recordStructureId->getIdString()."_position'>";
		for ($i=0; $i <= $numRecordStructures; $i++) {
			print "\n\t\t<option value='$i' [['schema_".$recordStructureId->getIdString()."_position' == '$i'|selected='selected'|]]>".(($i)?$i:"")."</option>";
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
		
		// Save the Repository
		$idManager =& Services::getService("Id");
		$id =& $idManager->getId($harmoni->pathInfoParts[2]);
		
		$repositoryManager =& Services::getService("Repository"); 
		$repository =& $repositoryManager->getRepository($id);
		
		$repository->updateDisplayName($properties['display_name']->getValue());
		$repository->updateDescription($properties['description']->getValue());
		
		
	// Save the Schema settings.
		
		// Get the set for this Repository
		$setManager =& Services::getService("Sets");
		$set =& $setManager->getSet($id);
		
		// get an iterator of all RecordStructures
		$recordStructures =& $repository->getRecordStructures();
		
		// Store up the positions for later setting after all of the ids have
		// been added to the set and we can do checking to make sure that 
		// the specified positions are valid.
		$positions = array();
		$existingStructures = array();
		$numStructures = 0;
		
		// Go through each RecordStructure
		while ($recordStructures->hasNext()) {
			$recordStructure =& $recordStructures->next();
			$recordStructureId =& $recordStructure->getId();
			
			// If the box is checked, make sure that the ID is in the set
			if ($properties["schema_".$recordStructureId->getIdString()]->getValue()) {
				if (!$set->isInSet($recordStructureId))
					$set->addItem($recordStructureId);
				if ($position = $properties["schema_".$recordStructureId->getIdString()."_position"]->getValue())
					$positions[$position-1] =& $recordStructureId;
				
				// Store some info so that we can check that all structures are valid.
				$existingStructures[] = $recordStructureId->getIdString();
				$numStructures++;
			}
			// Otherwise, remove the ID from the set.
			else {
				if ($set->isInSet($recordStructureId))
					$set->removeItem($recordStructureId);
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
		
		// Remove any RecordStructures from the set that may have been removed/
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
		
		// Head off to editing our new collection.
		$id =& $repository->getId();
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
$centerPane->add($wizardLayout, null, null, CENTER, TOP);

// return the main layout.
return $mainScreen;