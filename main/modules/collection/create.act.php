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
	$stepOneText .= "\n<br><input type='text' name='display_name' value=\"[[display_name]]\">";
	$stepOneText .= "\n<h2>"._("Description")."</h2>";
	$stepOneText .= "\n"._("The Description for this <em>Collection</em>: ");
	$stepOneText .= "\n<br><textarea name='description'>[[description]]</textarea>";
	$stepOneText .= "\n<div style='width: 400px'> &nbsp; </div>";
	$stepOne->setText($stepOneText);
	
	// :: Step Two ::
	$stepTwo =& $wizard->createStep(_("Type"));
	// Create the properties.
	$property =& $stepTwo->createProperty("type_domain", "Regex");
	$property->setExpression(".*");
	$property->setDefaultValue(_("Collections"));
	
	$property =& $stepTwo->createProperty("type_authority", "Regex");
	$property->setExpression(".*");
	$property->setDefaultValue(_("Concerto"));
	
	$property =& $stepTwo->createProperty("type_keyword", "Regex");
	$property->setExpression(".*");
	$property->setDefaultValue(_("Generic Collection"));
	
	$property =& $stepTwo->createProperty("type_description", "Regex");
	$property->setExpression(".*");
	$property->setDefaultValue(_("This is a <em>Collection</em> of unspecified type."));
	
	// create the text
	$stepTwoText = "<h2>"._("Type")."</h2>";
	$stepTwoText .= "\n"._("All <em>Collections</em> have an immutable type. This type can be used to catagorize <em>Collections</em>, but is not necessary.");
	$stepTwoText .= "\n<table>";
	$stepTwoText .= "\n\t<tr>\n\t\t<td>";
	$stepTwoText .= "<strong>"._("Domain").": </strong>";
	$stepTwoText .= "\n\t\t</td>";
	$stepTwoText .= "\n\t\t<td>";
	$stepTwoText .= "\n<input type='text' name='type_domain' value=\"[[type_domain]]\">";
	$stepTwoText .= "\n\t\t</td>\n\t</tr>";
	$stepTwoText .= "\n\t<tr>\n\t\t<td>";
	$stepTwoText .= "<strong>"._("Authority").": </strong>";
	$stepTwoText .= "\n\t\t</td>";
	$stepTwoText .= "\n\t\t<td>";
	$stepTwoText .= "\n<input type='text' name='type_authority' value=\"[[type_authority]]\">";
	$stepTwoText .= "\n\t\t</td>\n\t</tr>";
	$stepTwoText .= "\n\t<tr>\n\t\t<td>";
	$stepTwoText .= "<strong>"._("Keyword").": </strong>";
	$stepTwoText .= "\n\t\t</td>";
	$stepTwoText .= "\n\t\t<td>";
	$stepTwoText .= "\n<input type='text' name='type_keyword' value=\"[[type_keyword]]\">";
	$stepTwoText .= "\n\t\t</td>\n\t</tr>";
	$stepTwoText .= "\n\t<tr>\n\t\t<td>";
	$stepTwoText .= "<strong>"._("Description").": </strong>";
	$stepTwoText .= "\n\t\t</td>";
	$stepTwoText .= "\n\t\t<td>";
	$stepTwoText .= "\n<textarea name='type_description'>[[type_description]]</textarea>";
	$stepTwoText .= "\n\t\t</td>\n\t</tr>";
	$stepTwoText .= "\n</table>";
	$stepTwo->setText($stepTwoText);

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
		$type =& new HarmoniType($properties['type_domain']->getValue(),
								$properties['type_authority']->getValue(),
								$properties['type_keyword']->getValue(),
								$properties['type_description']->getValue());
		$dr =& $drManager->createDigitalRepository(
							$properties['display_name']->getValue(),
							$properties['description']->getValue(), $type);
		
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