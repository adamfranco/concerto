<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our Layout Setup
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Get the Repository
$repositoryManager =& Services::getService("Repository");
$sharedManager =& Services::getService("Shared");

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Search Assets in all Collections")));
$actionRows->addComponent($introHeader);

ob_start();
print  "<p>";
print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print  "</p>";

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$introText->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($introText);


// Print out the search types

ob_start();

// Get all the drs and all of their search types
$searchModules =& Services::getService("RepositorySearchModules");
$searchArray = array();

$repositories =& $repositoryManager->getDigitalRepositories();
while ($repositories->hasNext()) {
	$repository =& $repositories->next();
	$searchTypes =& $repository->getSearchTypes();
	while ($searchTypes->hasNext()) {
		$searchType =& $searchTypes->next();
		
		$typeString = $searchType->getDomain()
						."::".$searchType->getAuthority()
						."::".$searchType->getKeyword();
		
		if (!$searchArray[$typeString])
			$searchArray[$typeString] =& $searchType;
	}
}

// print out the types
foreach (array_keys($searchArray) as $typeString) {
	$searchType =& $searchArray[$typeString];
	print "\n<h3>".$typeString."</h3>";
	print "\n".$searchModules->createSearchForm($searchType, MYURL."/collections/searchresults/".urlencode($typeString)."/");
}

$searchFields =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$searchFields->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($searchFields);

// return the main layout.
return $mainScreen;