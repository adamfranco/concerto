<?

// Check for our authorization function definitions
if (!defined("AZ_ACCESS"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.exhibition", true));
if (!defined("AZ_VIEW"))
	throwError(new Error("You must define an id for AZ_VIEW", "concerto.exhibition", true));

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info.
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');


// Get the Repository
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");
$repositoryId =& $idManager->getId($harmoni->pathInfoParts[2]);
$repository =& $repositoryManager->getRepository($repositoryId);

// Check that the user can access this collection
$authZ =& Services::getService("AuthZ");
$idManager =& Services::getService("Id");
if (!$authZ->isUserAuthorized($idManager->getId(AZ_ACCESS), $repositoryId)) {
	$errorLayout =& new Block(_("You are not authorized to access this <em>Exhibition</em>."),2);
	$centerPane->add($errorLayout, null, null, CENTER, CENTER);
	return $mainScreen;
}



// If the Repository supports searching of root assets, just get those
$hasRootSearch = FALSE;
$rootSearchType =& new HarmoniType("Repository","Harmoni","RootAssets", "");
$searchTypes =& $repository->getSearchTypes();
while ($searchTypes->hasNext()) {
	if ($rootSearchType->isEqual( $searchTypes->next() )) {
		$hasRootSearch = TRUE;
		break;
	}
}

// Our Layout Setup
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);
$centerPane->add($actionRows, null, null, CENTER, CENTER);

// Intro
$introHeader =& new Heading(_("Browse Assets in the").
             " <em>".$repository->getDisplayName()."</em> "._("Exhibition"), 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);

// function links
ob_start();
print _("Exhibition").": ";
RepositoryPrinter::printRepositoryFunctionLinksExh($harmoni, $repository);
$layout =& new Block(ob_get_contents, 3);
ob_end_clean();
$actionRows->add($layout, null, null, CENTER, CENTER);

ob_start();
print  "<p>";
print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print  "</p>";

$introText =& new Block(ob_get_contents, 3);
ob_end_clean();
$actionRows->add($introText, null, null, CENTER, CENTER);

//***********************************
// Get the assets to display
//***********************************
if ($hasRootSearch) {
	$criteria = NULL;
	$assets =& $repository->getAssetsBySearch($criteria, $rootSearchType);
}
// Otherwise, just get all the assets
else {
	$assets =& $asset->getAssets();
}

//***********************************
// print the results
//***********************************
$resultPrinter =& new IteratorResultPrinter($assets, 2, 6, "printAssetShort", $harmoni);
$resultLayout =& $resultPrinter->getLayout($harmoni, "canView");
$actionRows->add($resultLayout, null, null, CENTER, CENTER);


// return the main layout.
return $mainScreen;


// Callback function for printing Assets
function printAssetShort(& $asset, &$harmoni) {
	ob_start();

	$assetId =& $asset->getId();
	print  "\n\t<strong>".$asset->getDisplayName()."</strong> - "._("ID#").": ".
			$assetId->getIdString();
	print  "\n\t<br /><em>".$asset->getDescription()."</em>";
	print  "\n\t<br />";

	AssetPrinter::printAssetFunctionLinksExh($harmoni, $asset);

	$layout =& new Block(ob_get_contents, 3);
	ob_end_clean();
	return $layout;
}

// Callback function for checking authorizations
function canView( & $asset ) {
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");

	if ($authZ->isUserAuthorized($idManager->getId(AZ_ACCESS), $asset->getId())
		|| $authZ->isUserAuthorized($idManager->getId(AZ_VIEW), $asset->getId()))
	{
		return TRUE;
	} else {
		return FALSE;
	}
}