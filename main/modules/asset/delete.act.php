<?

// Check for our authorization function definitions
if (!defined("AZ_DELETE"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.collection", true));

// Get the DR
$drManager =& Services::getService("DR");
$sharedManager =& Services::getService("Shared");
$assetId =& $sharedManager->getId($harmoni->pathInfoParts[3]);
$asset =& $drManager->getAsset($assetId);
$dr =& $asset->getDigitalRepository();
$drId =& $dr->getId();

// Check that the user can delete this asset
$authZ =& Services::getService("AuthZ");
$shared =& Services::getService("Shared");
if (!$authZ->isUserAuthorized($shared->getId(AZ_DELETE), $assetId)) {
	// Get the Layout compontents. See core/modules/moduleStructure.txt
	// for more info. 
	$harmoni->ActionHandler->execute("window", "screen");
	$mainScreen =& $harmoni->getAttachedData('mainScreen');
	$centerPane =& $harmoni->getAttachedData('centerPane');

	$errorLayout =& new SingleContentLayout;
	$errorLayout->addComponent(new Content(_("You are not authorized to delete this <em>Asset</em>."), MIDDLE, CENTER));
	$centerPane->addComponent($errorLayout, MIDDLE, CENTER);
	return $mainScreen;
}

// Delete the asset
$dr->deleteAsset($assetId);

// Head back to where we were
$returnURL = MYURL."/";
for($i = 4; $i < count($harmoni->pathInfoParts); $i++) {
	$returnURL .= $harmoni->pathInfoParts[$i]."/";
}

header("Location: ".$returnURL);