<?

// Check for our authorization function definitions
if (!defined("AZ_EDIT"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.collection", true));

// Check that the user can access this collection
$authZ =& Services::getService("AuthZ");
$idManager =& Services::getService("Id");
$id =& $idManager->getId($harmoni->pathInfoParts[3]);
if (!$authZ->isUserAuthorized($idManager->getId(AZ_EDIT), $id)) {
	// Get the Layout compontents. See core/modules/moduleStructure.txt
	// for more info. 
	$harmoni->ActionHandler->execute("window", "screen");
	$mainScreen =& $harmoni->getAttachedData('mainScreen');
	$centerPane =& $harmoni->getAttachedData('centerPane');
	$errorLayout =& new SingleContentLayout;
	$errorLayout->addComponent(new Content(_("You are not authorized to edit this <em>Asset</em>."), MIDDLE, CENTER));
	$centerPane->addComponent($errorLayout, MIDDLE, CENTER);
	return $mainScreen;
}

// Get the Repository and Asset
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");
$assetId =& $idManager->getId($harmoni->pathInfoParts[3]);
$recordId =& $idManager->getId($harmoni->pathInfoParts[4]);
$asset =& $repositoryManager->getAsset($assetId);
$repository =& $asset->getRepository();
$repositoryId =& $repository->getId();

$asset->deleteRecord($recordId);

$returnURL = MYURL."/asset/editview/".$harmoni->pathInfoParts[2]."/".$harmoni->pathInfoParts[3]."/";
header("Location: ".$returnURL);