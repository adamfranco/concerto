<?

// Check for our authorization function definitions
if (!defined("AZ_ADD_CHILDREN"))
	throwError(new Error("You must define an id for AZ_ADD_CHILDREN", "concerto.exhibition", true));

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info.
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');


// Check that the user can create a collection here.
$authZ =& Services::getService("AuthZ");
$idManager =& Services::getService("Id");
if (!$authZ->isUserAuthorized($idManager->getId(AZ_ADD_CHILDREN), $idManager->getId(REPOSITORY_NODE_ID))) {
	$errorLayout =& new SingleContentLayout;
	$errorLayout->addComponent(new Content(_("You are not authorized to create an <em>Exhibition</em>."), MIDDLE, CENTER));
	$centerPane->addComponent($errorLayout, MIDDLE, CENTER);
	return $mainScreen;
}

// Create the wizard.

 if ($_SESSION['create_exhibition_wizard']) {
 	$wizard =& $_SESSION['create_exhibition_wizard'];
 } else {

	// Instantiate the wizard, then add our steps.
	$wizard =& new Wizard(_("Create an Exhibition"));
	$_SESSION['create_exhibition_wizard'] =& $wizard;

	// :: Step One ::
	$stepOne =& $wizard->createStep(_("Name & Description"));

	// Create the properties.
	$displayNameProp =& $stepOne->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
	$displayNameProp->setDefaultValue(_("Default Exhibition Name"));
	$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");

	$descriptionProp =& $stepOne->createProperty("description", new RegexValidatorRule(".*"));
	$descriptionProp->setDefaultValue(_("Default Exhibition description."));

	// Create the step text
	$stepOneText = "\n<h2>"._("Name")."</h2>";
	$stepOneText .= "\n"._("The Name for this <em>Exhibition</em>: ");
	$stepOneText .= "\n<br /><input type='text' name='display_name' value=\"[[display_name]]\" />[[display_name|Error]]";
	$stepOneText .= "\n<h2>"._("Description")."</h2>";
	$stepOneText .= "\n"._("The Description for this <em>Exhibition</em>: ");
	$stepOneText .= "\n<br /><textarea name='description'>[[description]]</textarea>[[description|Error]]";
	$stepOneText .= "\n<div style='width: 400px'> &nbsp; </div>";
	$stepOne->setText($stepOneText);

	// :: Step Two ::
/*
$stepTwo =& $wizard->createStep(_("Owner & Keywords"));
	// Create the properties.
	$property =& $stepTwo->createProperty("keywords", new RegexValidatorRule(".*"));
	$property->setDefaultValue(_("Exhibition"));
	$property =& $stepTwo->createProperty("Owner", new RegexValidatorRule("^[^ ]{1}.*$"));
	$property->setDefaultValue(_("Anonymous"));
	

	$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The owner shouldn't start with a space.")."</span>");

	// create the text
	$stepTwoText = "<h2>"._("Type")."</h2>";
	$stepTwoText .= "\n"._("All <b>exhibitions</b> may have an owner and some describing keywords.");
	$stepTwoText .= "\n<table>";
	$stepTwoText .= "\n\t<tr>\n\t\t<td>";
	$stepTwoText .= "<strong>"._("Keywords").": </strong>";
	$stepTwoText .= "\n\t\t</td>";
	$stepTwoText .= "\n\t\t<td>";
  $stepTwoText .= "\<br /><textarea name='keywords'>[[keywords]]</textarea>[[keywords|Error]]
                        \n<div style='width: 400px'> &nbsp; </div>";
	$stepTwoText .= "\n\t\t</td>\n\t</tr>";
	$stepTwoText .= "\n\t<tr>\n\t\t<td>";
  $stepTwoText .= "<strong>"._("Owner").": </strong>";
	$stepTwoText .= "\n\t\t</td>";
	$stepTwoText .= "\n\t\t<td>";
	$stepTwoText .= "\n<input type='text' name='Owner' value=\"[[Owner]]\" />";
	$stepTwoText .= "\n\t\t</td>\n\t</tr>";
	$stepTwoText .= "\n</table>";

	$stepTwo->setText($stepTwoText);
*/
}

// Handle saving if requested
if ($wizard->isSaveRequested()) {

	// If all properties validate then go through the steps nessisary to
	// save the data.
	if ($wizard->updateLastStep()) {
		$properties =& $wizard->getProperties();

		// Create the repository and get its id.
		$repositoryManager =& Services::getService("Repository");
    $t =& new HarmoniType ('System Repositories', 'Concerto', 'Exhibitions',
			'A Repository for holding Exhibitions, their Slide-Shows and Slides');
   //$asset=& new HarmoniAsset ()
    $repository =& $repositoryManager->createRepository(
							$properties['display_name']->getValue(),
							$properties['description']->getValue(),
              $t);

		// Unset the wizard
		$wizard = NULL;
		unset ($_SESSION['create_exhibition_wizard']);
		unset ($wizard);

		// Head off to editing our new collection.
		$id =& $repository->getId();
		header(header("Location: ".MYURL."/exhibition/edit/".$id->getIdString()."/?__skip_to_step=2"));
	}

// Handle canceling if requested
} else if ($wizard->isCancelRequested()) {
	$wizard = NULL;
	unset ($_SESSION['create_exhibition_wizard']);
	unset ($wizard);
	header("Location: ".MYURL."/exhibitions/main/");
}

$wizardLayout =& $wizard->getLayout($harmoni);
$centerPane->add($wizardLayout, null, null, CENTER, CENTER);

// return the main layout.
return $mainScreen;
