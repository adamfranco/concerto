<?

// Check for our authorization function definitions
if (!defined("AZ_ACCESS"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.collection", true));
if (!defined("AZ_VIEW"))
	throwError(new Error("You must define an id for AZ_VIEW", "concerto.collection", true));

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
	$errorLayout =& new Block("You are not authorized to access this <em>Collection</em>.",3);
	$centerPane->add($errorLayout,"100%" ,null, CENTER, CENTER);
	return $mainScreen;
}

// Our Layout Setup
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout,OTHER,1);
$centerPane->add($actionRows, null, null, CENTER, TOP);



// get the search type.
$typeString = urldecode($harmoni->pathInfoParts[3]);
if (!ereg("^(.+)::(.+)::(.+)$", $typeString, $parts))
	throwError(new Error("Invalid Search Type, '$typeString'", "Concerto::searchresults", true));
$searchType =& new HarmoniType($parts[1], $parts[2], $parts[3]);

// Get the Search criteria
$searchModules =& Services::getService("RepositorySearchModules");
$searchCriteria =& $searchModules->getSearchCriteria($searchType);

// Intro
$introHeader =& new Heading(_("Search results of Assets in the")." <em>".$repository->getDisplayName()."</em> "._("Collection"), 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);

// function links
ob_start();
print _("Collection").": ";
RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
$layout =& new Block(ob_get_contents(), 2);
ob_end_clean();
$actionRows->add($layout, null, null, CENTER, CENTER);

ob_start();
print  "<p>";
print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print  "</p>";

$introText =& new Block(ob_get_contents(), 2);
ob_end_clean();
$actionRows->add($introText, null, null, CENTER, CENTER);

//***********************************
// Get the assets to display
//***********************************
$assets =& $repository->getAssetsBySearch($searchCriteria, $searchType);

//***********************************
// print the results
//***********************************
$resultPrinter =& new IteratorResultPrinter($assets, 2, 6, "printAssetShort", $harmoni);
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);


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
	
	AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
	
	$layout =& new Block(ob_get_contents(), 4);
	ob_end_clean();
	return $layout;
}