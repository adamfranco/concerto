<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info.
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');


// Our
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);
$centerPane->add($actionRows, null, null, CENTER, CENTER);

// Get the Repository
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");

// get the search type.
$typeString = urldecode($harmoni->pathInfoParts[2]);
if (!ereg("^(.+)::(.+)::(.+)$", $typeString, $parts))
	throwError(new Error("Invalid Search Type, '$typeString'", "Concerto::searchresults", true));
$searchType =& new HarmoniType($parts[1], $parts[2], $parts[3]);

// Get the Search criteria
$searchModules =& Services::getService("RepositorySearchModules");
$searchCriteria =& $searchModules->getSearchCriteria($searchType);

// Intro
$introHeader =& new Heading(_("Search results of Slide shows in all Exhibitions"), 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);

ob_start();
print  "<p>";
print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print  "</p>";

$introText =& new Block(ob_get_contents(), 3);
ob_end_clean();
$actionRows->add($introText, null, null, CENTER, CENTER);
//***********************************
// Get the assets to display
//***********************************
$assetArray = array();
// Go through all the repositories. if they support the searchType,
// run the search on them.
$repositories =& $repositoryManager->getRepositories();
while ($repositories->hasNext()) {
	$repository =& $repositories->next();
	$assets =& $repository->getAssetsBySearch($searchCriteria, $searchType);

	// add the results to our total results
	while ($assets->hasNext()) {
		$assetArray[] =& $assets->next();
	}
}

//***********************************
// print the results
//***********************************
$resultPrinter =& new ArrayResultPrinter($assetArray, 2, 6, "printAssetShort", $harmoni);
$resultLayout =& $resultPrinter->getLayout($harmoni);
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

	AssetPrinter::printAssetFunctionLinks($harmoni, $asset);

	$layout =& new Block(ob_get_contents, 4);
	ob_end_clean();
	return $layout;
}