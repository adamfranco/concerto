<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');

// Create the wizard.
 if ($_SESSION['add_asset_wizard_'.$harmoni->pathInfoParts[2]]) {
 	$wizard =& $_SESSION['add_asset_wizard_'.$harmoni->pathInfoParts[2]];
 } else {
 	
 	// Make sure we have a valid DR
	$shared =& Services::getService("Shared");
	$drManager =& Services::getService("DR");
	$drId =& $shared->getId($harmoni->pathInfoParts[2]);
// 	$assetId =& $shared->getId($harmoni->pathInfoParts[3]);

	$dr =& $drManager->getDigitalRepository($drId);
// 	$asset =& $dr->getAsset($assetId);

	// Instantiate the wizard, then add our steps.
	$wizard =& new Wizard(_("Add Asset to the ")."<em>".$dr->getDisplayName()."</em> "._("Collection"));
	$_SESSION['add_asset_wizard_'.$harmoni->pathInfoParts[2]] =& $wizard;
	
	
	
	// :: Name and Description ::
	$step =& $wizard->createStep(_("Name & Description"));
	
	// Create the properties.
	$displayNameProp =& $step->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
	$displayNameProp->setDefaultValue(_("Default Asset Name"));
	$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
	
	$descriptionProp =& $step->createProperty("description", new RegexValidatorRule(".*"));
	$descriptionProp->setDefaultValue(_("Default Asset description."));
	
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
	
	

	// :: Type Step ::
	$step =& $wizard->createStep(_("Type"));
	// Create the properties.
	$property =& $step->createProperty("option_type", new RegexValidatorRule(".*"));
	$property->setDefaultValue(_("NONE"));
	
	$property =& $step->createProperty("type_domain", new RegexValidatorRule(".*"));
	$property->setDefaultValue(_("Concerto"));
	
	$property =& $step->createProperty("type_authority", new RegexValidatorRule(".*"));
	$property->setDefaultValue(_("Concerto"));
	
	$property =& $step->createProperty("type_keyword", new RegexValidatorRule(".*"));
	$property->setDefaultValue(_("Generic Asset"));
	
	$property =& $step->createProperty("type_description", new RegexValidatorRule(".*"));
	$property->setDefaultValue(_("This is an <em>Asset</em> of unspecified type."));
	
	// create the text
	ob_start();
	print "<h2>"._("Type")."</h2>";
	print "\n"._("All <em>Assets</em> have an immutable type. This type can be used to catagorize <em>Assets</em>, but is not necessary.");
	print "\n<br />Select a type here or use the fields below to create a new one:";
	print "\n<select name='option_type'>";
	print "\n\t<option value='NONE' [['option_type'=='NONE'|selected|]]>Use Fields Below...</option>";
	$assetTypes =& $dr->getAssetTypes();
	while ($assetTypes->hasNext()) {
		$assetType =& $assetTypes->next();
		$typeKey = urlencode($assetType->getDomain()."::".$assetType->getAuthority()."::".$assetType->getKeyword());
		print "\n\t<option value='".$typeKey."' [['option_type'=='".$typeKey."'|selected|]]>"
			.$assetType->getDomain()."::".$assetType->getAuthority()."::".$assetType->getKeyword()
			."</option>";
	}
	print "\n</select>";
	print "\n<br />";
	print "\n<br />";
	print "\n<table>";
	print "\n\t<tr>\n\t\t<td>";
	print "<strong>"._("Domain").": </strong>";
	print "\n\t\t</td>";
	print "\n\t\t<td>";
	print "\n<input type='text' name='type_domain' value=\"[[type_domain]]\">";
	print "\n\t\t</td>\n\t</tr>";
	print "\n\t<tr>\n\t\t<td>";
	print "<strong>"._("Authority").": </strong>";
	print "\n\t\t</td>";
	print "\n\t\t<td>";
	print "\n<input type='text' name='type_authority' value=\"[[type_authority]]\">";
	print "\n\t\t</td>\n\t</tr>";
	print "\n\t<tr>\n\t\t<td>";
	print "<strong>"._("Keyword").": </strong>";
	print "\n\t\t</td>";
	print "\n\t\t<td>";
	print "\n<input type='text' name='type_keyword' value=\"[[type_keyword]]\">";
	print "\n\t\t</td>\n\t</tr>";
	print "\n\t<tr>\n\t\t<td>";
	print "<strong>"._("Description").": </strong>";
	print "\n\t\t</td>";
	print "\n\t\t<td>";
	print "\n<textarea name='type_description'>[[type_description]]</textarea>";
	print "\n\t\t</td>\n\t</tr>";
	print "\n</table>";
	$step->setText(ob_get_contents());
	ob_end_clean();
	
	
	// :: Content ::
	$step =& $wizard->createStep(_("Content")." ("._("optional").")");
	
	$property =& $step->createProperty("content", new RegexValidatorRule(".*"));
	
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
//	$property->setDefaultValue();
	$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");

	$property =& $step->createProperty("expiration_date", new RegexValidatorRule("^(([0-9]{4,8}))?$"));
//	$property->setDefaultValue();
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
	
	
	
	// :: Parent ::
	$step =& $wizard->createStep(_("Parent")." ("._("optional").")");
	
	// Create the properties.
	$property =& $step->createProperty("parent", new AlwaysTrueValidatorRule);
	$property->setDefaultValue("NONE");
	
	// Create the step text
	ob_start();
	print "\n<h2>"._("Parent <em>Asset</em>")."</h2>";
	print "\n"._("Select one of the <em>Assets</em> below if you wish to make this new asset a child of another asset: ");
	print "\n<br><select name='parent'>";
	print "\n\t<option value='NONE' [[parent=='NONE'|checked|]]>None</option>";
	$assets =& $dr->getAssets();
	while ($assets->hasNext()) {
		$asset =& $assets->next();
		$assetId =& $asset->getId();
		print "\n\t<option value='".$assetId->getIdString()."' [[parent=='".$assetId->getIdString()."'|checked|]]>Id: ".$assetId->getIdString()." - ".$asset->getDisplayName()."</option>";
	}
		
	print "\n</select>";
	
	$step->setText(ob_get_contents());
	ob_end_clean();
}

if ($wizard->isSaveRequested()) {

	// Make sure we have a valid DR
	$shared =& Services::getService("Shared");
	$drManager =& Services::getService("DR");
	$drId =& $shared->getId($harmoni->pathInfoParts[2]);

	$dr =& $drManager->getDigitalRepository($drId);
	
	$properties =& $wizard->getProperties();
	
	
	// Get the type from the select if one is specified
	if ($properties['option_type']->getValue() != 'NONE') {
		$typeString = urldecode($properties['option_type']->getValue());
		$typeparts = explode("::", $typeString);
		$assetType = new HarmoniType($typeparts[0], $typeparts[1], $typeparts[2]);
	} 
	// Otherwise, Generate the type from the specified fields
	else {
		$assetType = new HarmoniType($properties['type_domain']->getValue(), 
									$properties['type_authority']->getValue(), 
									$properties['type_keyword']->getValue(), 
									$properties['type_description']->getValue());
	}
	
 	$asset =& $dr->createAsset($properties['display_name']->getValue(), 
 								$properties['description']->getValue(), 
 								$assetType);
 	$assetId =& $asset->getId();
 	
 	$content =& new Blob($properties['content']->getValue());
	$asset->updateContent($content);
 	
 	// Update the effective/expiration dates
 	if ($properties['effective_date']->getValue())
	 	$asset->updateEffectiveDate(new Time($properties['effective_date']->getValue()));
	if ($properties['expiration_date']->getValue())
	 	$asset->updateExpirationDate(new Time($properties['expiration_date']->getValue()));
	
	// Add our parent if we have specified one.
	if ($properties['parent']->getValue() 
		&& $properties['parent']->getValue() != 'NONE') 
	{
		$parentId =& $shared->getId($properties['parent']->getValue());
		$parentAsset =& $dr->getAsset($parentId);
		$parentAsset->addAsset($assetId);
	}
	
	$wizard = NULL;
	unset ($_SESSION['add_asset_wizard_'.$harmoni->pathInfoParts[2]]);
	unset ($wizard);
	
	$returnURL = MYURL."/asset/editview/".$drId->getIdString()."/".$assetId->getIdString();
	
	header("Location: ".$returnURL);
	
} else if ($wizard->isCancelRequested()) {	

	// Prepare the return URL so that we can get back to where we were.
	// $currentPathInfo = array();
	for ($i = 3; $i < count($harmoni->pathInfoParts); $i++) {
		$currentPathInfo[] = $harmoni->pathInfoParts[$i];
	}
	$returnURL = MYURL."/".implode("/",$currentPathInfo);
	
	$wizard = NULL;
	unset ($_SESSION['add_asset_wizard_'.$harmoni->pathInfoParts[2]]);
	unset ($wizard);
	header("Location: ".$returnURL);
	
}

$wizardLayout =& $wizard->getLayout($harmoni);
$centerPane->addComponent($wizardLayout, TOP, CENTER);

// return the main layout.
return $mainScreen;