<?

// Check for our authorization function definitions
if (!defined("AZ_DELETE"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.collection", true));

// Get the Repository
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");
$assetId =& $idManager->getId($harmoni->pathInfoParts[3]);
$asset =& $repositoryManager->getAsset($assetId);
$repository =& $asset->getRepository();
$repositoryId =& $repository->getId();

// Check that the user can delete this asset
$authZ =& Services::getService("AuthZ");
$idManager =& Services::getService("Id");
if (!$authZ->isUserAuthorized($idManager->getId(AZ_DELETE), $assetId)) {
	// Get the Layout compontents. See core/modules/moduleStructure.txt
	// for more info. 
	$harmoni->ActionHandler->execute("window", "screen");
	$mainScreen =& $harmoni->getAttachedData('mainScreen');
	$centerPane =& $harmoni->getAttachedData('centerPane');

	$errorLayout =& new Block(_("You are not authorized to delete this <em>Asset</em> here."),2);
	$centerPane->add($errorLayout, null, null, CENTER, CENTER);
	return $mainScreen;
}

// Delete the asset
$repository->deleteAsset($assetId);

// Head back to where we were
$returnURL = MYURL."/";
for($i = 4; $i < count($harmoni->pathInfoParts); $i++) {
	$returnURL .= $harmoni->pathInfoParts[$i]."/";
}

header("Location: ".$returnURL);