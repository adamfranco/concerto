<?

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
$shared =& Services::getService("Shared");
$id =& $shared->getId($harmoni->pathInfoParts[3]);
if (!$authZ->isUserAuthorized($shared->getId(AZ_EDIT), $id)) {
	$errorLayout =& new SingleContentLayout;
	$errorLayout->addComponent(new Content(_("You are not authorized to edit this <em>Asset</em>."), MIDDLE, CENTER));
	$centerPane->addComponent($errorLayout, MIDDLE, CENTER);
	return $mainScreen;
}

// Create the wizard.
 if ($_SESSION['edit_asset_wizard_'.$harmoni->pathInfoParts[3]]) {
 	$wizard =& $_SESSION['edit_asset_wizard_'.$harmoni->pathInfoParts[3]];
 } else {
 	
 	// Make sure we have a valid DR
	$shared =& Services::getService("Shared");
	$drManager =& Services::getService("DR");
	$drId =& $shared->getId($harmoni->pathInfoParts[2]);
	$assetId =& $shared->getId($harmoni->pathInfoParts[3]);

	$dr =& $drManager->getDigitalRepository($drId);
	$asset =& $dr->getAsset($assetId);

	// Instantiate the wizard, then add our steps.
	$wizard =& new Wizard(_("Edit Asset"));
	$_SESSION['edit_asset_wizard_'.$harmoni->pathInfoParts[3]] =& $wizard;
	
	
	// :: Name and Description ::
	$step =& $wizard->createStep(_("Name & Description"));
	
	// Create the properties.
	$displayNameProp =& $step->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
	$displayNameProp->setDefaultValue($asset->getDisplayName());
	$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
	
	$descriptionProp =& $step->createProperty("description", new RegexValidatorRule(".*"));
	$descriptionProp->setDefaultValue($asset->getDescription());
	
	// Create the step text
	ob_start();
	print "\n<h2>"._("Name")."</h2>";
	print "\n"._("The Name for this <em>Asset</em>: ");
	print "\n<br><input type='text' name='display_name' value=\"[[display_name]]\">[[display_name|Error]]";
	print "\n<h2>"._("Description")."</h2>";
	print "\n"._("The Description for this <em>Asset</em>: ");
	print "\n<br><textarea name='description'>[[description]]</textarea>[[description|Error]]";
	print "\n<div style='width: 400px'> &nbsp; </div>";
	$step->setText(ob_get_contents());
	ob_end_clean();
	
	
	// :: Content ::
	$step =& $wizard->createStep(_("Content")." ("._("optional").")");
	
	$property =& $step->createProperty("content", new RegexValidatorRule(".*"));
	$content =& $asset->getContent();
	
	if ($content->toString())
		$property->setDefaultValue($content->toString());
	
	// Create the step text
	ob_start();
	print "\n<h2>"._("Content")."</h2>";
	print "\n"._("This is an optional place to put content for this <em>Asset</em>. <br />If you would like more structure, you can create new schemas to hold the <em>Asset's</em> data.");
	print "\n<br><textarea name='content' cols='50' rows='20'>[[content]]</textarea>[[content|Error]]";
	print "\n<div style='width: 400px'> &nbsp; </div>";
	$step->setText(ob_get_contents());
	ob_end_clean();
	
	
	
	// :: Effective/Expiration Dates ::
	$step =& $wizard->createStep(_("Effective Dates")." ("._("optional").")");
	
	// Create the properties.
	$property =& $step->createProperty("effective_date", new RegexValidatorRule("^(([0-9]{4,8}))?$"));
	$date =& $asset->getEffectiveDate();
	$property->setDefaultValue($date->getYear()
		.(($date->getMonth()<10)?"0".intval($date->getMonth()):$date->getMonth())
		.(($date->getDay()<10)?"0".intval($date->getDay()):$date->getDay()));
	$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");

	$property =& $step->createProperty("expiration_date", new RegexValidatorRule("^(([0-9]{4,8}))?$"));
	$date =& $asset->getExpirationDate();
	$property->setDefaultValue($date->getYear()
		.(($date->getMonth()<10)?"0".intval($date->getMonth()):$date->getMonth())
		.(($date->getDay()<10)?"0".intval($date->getDay()):$date->getDay()));
	$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
	
	// Create the step text
	ob_start();
	print "\n<h2>"._("Effective Date")."</h2>";
	print "\n"._("The date that this <em>Asset</em> becomes effective: ");
	print "\n<br><input type='text' name='effective_date' value=\"[[effective_date]]\">[[effective_date|Error]]";
	
	print "\n<h2>"._("Expiration Date")."</h2>";
	print "\n"._("The date that this <em>Asset</em> expires: ");
	print "\n<br><input type='text' name='expiration_date' value=\"[[expiration_date]]\">[[expiration_date|Error]]";
	$step->setText(ob_get_contents());
	ob_end_clean();
}

// Prepare the return URL so that we can get back to where we were.
// $currentPathInfo = array();
// for ($i = 3; $i < count($harmoni->pathInfoParts); $i++) {
// 	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
// }
// $returnURL = MYURL."/".implode("/",$currentPathInfo);
$returnURL = MYURL."/asset/editview/".$harmoni->pathInfoParts[2]."/".$harmoni->pathInfoParts[3]."/";

if ($wizard->isSaveRequested()) {

	// Make sure we have a valid DR
	$shared =& Services::getService("Shared");
	$drManager =& Services::getService("DR");
	$drId =& $shared->getId($harmoni->pathInfoParts[2]);
	$assetId =& $shared->getId($harmoni->pathInfoParts[3]);

	$dr =& $drManager->getDigitalRepository($drId);
	$asset =& $dr->getAsset($assetId);
	
	$properties =& $wizard->getProperties();
	
	// Update the name and description
	$asset->updateDisplayName($properties['display_name']->getValue());
	$asset->updateDescription($properties['description']->getValue());
	$content =& new Blob($properties['content']->getValue());
	$asset->updateContent($content);
	
	
	// Update the effective/expiration dates
 	if ($properties['effective_date']->getValue())
	 	$asset->updateEffectiveDate(new Time($properties['effective_date']->getValue()));
	if ($properties['expiration_date']->getValue())
	 	$asset->updateExpirationDate(new Time($properties['expiration_date']->getValue()));
	
	// Add our parent if we have specified one.
// 	if ($properties['parent']->getValue()) {
// 		$parentId =& $shared->getId($properties['parent']->getValue());
// 		$parentAsset =& $dr->getAsset($parentId);
// 		$parentAsset->addAsset($assetId);
// 	}
	
	
	$wizard = NULL;
	unset ($_SESSION['edit_asset_wizard_'.$harmoni->pathInfoParts[3]]);
	unset ($wizard);
	
	header("Location: ".$returnURL);
	
} else if ($wizard->isCancelRequested()) {
	$wizard = NULL;
	unset ($_SESSION['edit_asset_wizard_'.$harmoni->pathInfoParts[3]]);
	unset ($wizard);
	header("Location: ".$returnURL);
	
}

$wizardLayout =& $wizard->getLayout($harmoni);
$centerPane->addComponent($wizardLayout, TOP, CENTER);

// return the main layout.
return $mainScreen;