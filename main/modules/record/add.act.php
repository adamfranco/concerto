<?

// Check for our authorization function definitions
if (!defined("AZ_EDIT"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.collection", true));

// Check that the user can access this collection
$authZ =& Services::getService("AuthZ");
$shared =& Services::getService("Shared");
$id =& $shared->getId($harmoni->pathInfoParts[3]);
if (!$authZ->isUserAuthorized($shared->getId(AZ_EDIT), $id)) {
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
$sharedManager =& Services::getService("Shared");
$assetId =& $sharedManager->getId($harmoni->pathInfoParts[3]);
$asset =& $repositoryManager->getAsset($assetId);
$repository =& $asset->getRepository();
$repositoryId =& $repository->getId();

$recordStructureId =& $sharedManager->getId($_REQUEST['structure']);

$asset->createRecord($recordStructureId);

header("Location: ".$_REQUEST['return_url']);