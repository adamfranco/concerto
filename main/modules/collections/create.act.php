<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Create the wizard.

 if ($_SESSION['wizard']) {
 	$wizard =& $_SESSION['wizard'];
 } else {

	// Instantiate the wizard, then add our steps.
	$wizard =& new Wizard(_("Create a Collection"));
	$_SESSION['wizard'] =& $wizard;
	
	// :: Step One ::
	$stepOne =& $wizard->createStep(_("Name & Description"));
	// Create the properties.
	$displayNameProp =& $stepOne->createProperty("display_name", "Regex");
	$displayNameProp->setExpression(".*");
	$displayNameProp->setDefaultValue(_("Default Collection Name"));
	
	$stepOneText = "<h2>"._("Name")."</h2>";
	$stepOneText .= "\n"._("The Name for this <em>Collection</em>: ");
	$stepOneText .= "\n<input type='text' name='display_name' value='[[display_name]]'>";
	$stepOne->setText($stepOneText);
	
	// :: Step Two ::
	$stepTwo =& $wizard->createStep(_("Name & Description 2"));
	// Create the properties.
	$displayNameProp =& $stepTwo->createProperty("display_name2", "Regex");
	$displayNameProp->setExpression(".*");
	$displayNameProp->setDefaultValue(_("Default Collection Name2"));
	
	$stepTwoText = "<h2>"._("Name")."</h2>";
	$stepTwoText .= "\n"._("The Name for this <em>Collection</em>: ");
	$stepTwoText .= "\n<input type='text' name='display_name2' value='[[display_name2]]'>";
	$stepTwo->setText($stepTwoText);

}

if ($_REQUEST['save']) {
	// If all properties validate then go through the steps nessisary to
	// save the data.
	if ($wizard->updateLastStep()) {
		$properties =& $wizard->getProperties();
		print "Now Saving: ";
		printpre($properties);
	}
	
} else if ($_REQUEST['cancel']) {
	$wizard = NULL;
	unset ($_SESSION['wizard']);
	unset ($wizard);
	header(header("Location: ".MYURL."/collections/main/"));
	
} else if ($_REQUEST['next'] && $wizard->hasNext())
	$wizard->next();

else if ($_REQUEST['previous'] && $wizard->hasPrevious())
	$wizard->previous();

$wizardLayout =& $wizard->getLayout($harmoni);
$centerPane->addComponent($wizardLayout, TOP, CENTER);

// return the main layout.
return $mainScreen;