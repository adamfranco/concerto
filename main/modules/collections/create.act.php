<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Create the wizard.

 if ($_SESSION['create_collection_wizard']) {
 	$wizard =& $_SESSION['create_collection_wizard'];
 } else {

	// Instantiate the wizard, then add our steps.
	$wizard =& new Wizard(_("Create a Collection"));
	$_SESSION['create_collection_wizard'] =& $wizard;
	
	// :: Step One ::
	$stepOne =& $wizard->createStep(_("Name & Description"));
	
	// Create the properties.
	$displayNameProp =& $stepOne->createProperty("display_name", "Regex");
	$displayNameProp->setExpression(".*");
	$displayNameProp->setDefaultValue(_("Default Collection Name"));
	
	$descriptionProp =& $stepOne->createProperty("description", "Regex");
	$descriptionProp->setExpression(".*");
	$descriptionProp->setDefaultValue(_("Default Collection description."));
	
	// Create the step text
	$stepOneText = "\n<h2>"._("Name")."</h2>";
	$stepOneText .= "\n"._("The Name for this <em>Collection</em>: ");
	$stepOneText .= "\n<br><input type='text' name='display_name' value='[[display_name]]'>";
	$stepOneText .= "\n<h2>"._("Description")."</h2>";
	$stepOneText .= "\n"._("The Description for this <em>Collection</em>: ");
	$stepOneText .= "\n<br><textarea name='description'>[[description]]</textarea>";
	$stepOneText .= "\n<div style='width: 400px'> &nbsp; </div>";
	$stepOne->setText($stepOneText);
// 	
// 	// :: Step Two ::
// 	$stepTwo =& $wizard->createStep(_("Select Scheme"));
// 	// Create the properties.
// 	$displayNameProp =& $stepTwo->createProperty("display_name2", "Regex");
// 	$displayNameProp->setExpression(".*");
// 	$displayNameProp->setDefaultValue(_("Default Collection Name2"));
// 	
// 	$stepTwoText = "<h2>"._("Name")."</h2>";
// 	$stepTwoText .= "\n"._("The Name for this <em>Collection</em>: ");
// 	$stepTwoText .= "\n<input type='text' name='display_name2' value='[[display_name2]]'>";
// 	$stepTwo->setText($stepTwoText);

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
		unset ($_SESSION['create_collection_wizard']);
		unset ($wizard);
		
		// Head off to editing our new collection.
		$id =& $dr->getId();
		header(header("Location: ".MYURL."/collection/edit/".$id->getIdString()."/"));
	}
	
} else if ($_REQUEST['cancel'] || $_REQUEST['cancel_link']) {
	$wizard = NULL;
	unset ($_SESSION['create_collection_wizard']);
	unset ($wizard);
	header(header("Location: ".MYURL."/collections/main/"));
	
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