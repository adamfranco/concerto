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
	$errorLayout =& new Block(_("You are not authorized to edit this <em>Asset</em>."),2);
	$centerPane->add($errorLayout, "100%", null,CENTER, CENTER);
	return $mainScreen;
}

// Get the Repository and Asset
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");
$assetId =& $idManager->getId($harmoni->pathInfoParts[3]);
$asset =& $repositoryManager->getAsset($assetId);
$repository =& $asset->getRepository();
$repositoryId =& $repository->getId();

$recordStructureId =& $idManager->getId($_REQUEST['structure']);

$asset->createRecord($recordStructureId);

header("Location: ".$_REQUEST['return_url']);