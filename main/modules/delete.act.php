<?
require_once (OKI2."osid/repository/RepositoryManager.php");
require_once (HARMONI."oki2/repository/HarmoniRepository.class.php");

$harmoni->ActionHandler->execute("window", "screen");
	$mainScreen =& $harmoni->getAttachedData('mainScreen');
	$statusBar =& $harmoni->getAttachedData('statusBar');
	$centerPane =& $harmoni->getAttachedData('centerPane');
// Check for our authorization function definitions
if (!defined("AZ_DELETE"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.exhibition", true));

// Get the Repository
//$x=& Services::startService("HarmoniRepositoryManager");
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");
$repositoryId =& $idManager->getId($harmoni->pathInfoParts[2],$harmoni->pathInfoParts[2]);
//$repository =& $repositoryManager->getRepository($repositoryId);
print("mata e ".$repositoryId);
// Check that the user can delete this asset
/*
$authZ =& Services::getService("AuthZ");
$idManager =& Services::getService("Id");
 if (!$authZ->isUserAuthorized($idManager->getId(AZ_DELETE), $repositoryId)) {
	// Get the Layout compontents. See core/modules/moduleStructure.txt
	// for more info.
	$harmoni->ActionHandler->execute("window", "screen");
	$mainScreen =& $harmoni->getAttachedData('mainScreen');
	$centerPane =& $harmoni->getAttachedData('centerPane');

	$errorLayout =& new SingleContentLayout;
	$errorLayout->addComponent(new Content(_("You are not authorized to delete this <em>Repository</em>."), MIDDLE, CENTER));
	$centerPane->addComponent($errorLayout, MIDDLE, CENTER);
 //return $mainScreen;
}*/
  



// Delete the repository

$repositoryManager->deleteRepository($repositoryId);

// Head back to where we were
$retURL = MYURL."/";
$retURL .= "exhibitions/namebrowse/";

/*
  if($repositoryId='NULL'){
	$returnURL .= "mama"."/";
	}else{
	$returnURL .= $repositoryId."/";
}
*/

header("Location: ".$retURL);
return $mainScreen;
