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
 
// Get the DR
$drManager =& Services::getService("DR");
$sharedManager =& Services::getService("Shared");
$drId =& $sharedManager->getId($harmoni->pathInfoParts[2]);
$dr =& $drManager->getDigitalRepository($drId);


// Check that the user can access this collection
$authZ =& Services::getService("AuthZ");
$shared =& Services::getService("Shared");
if (!$authZ->isUserAuthorized($shared->getId(AZ_ACCESS), $drId)) {
	$errorLayout =& new SingleContentLayout;
	$errorLayout->addComponent(new Content(_("You are not authorized to access this <em>Collection</em>."), MIDDLE, CENTER));
	$centerPane->addComponent($errorLayout, MIDDLE, CENTER);
	return $mainScreen;
}

// Our Layout Setup
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Search Assets in the")." <em>".$dr->getDisplayName()."</em> "._("Collection")));
$actionRows->addComponent($introHeader);

// function links
ob_start();
print _("Collection").": ";
RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $dr);
$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$layout->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($layout);

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

$searchModules =& Services::getService("DRSearchModules");
$searchTypes =& $dr->getSearchTypes();
while ($searchTypes->hasNext()) {
	$searchType =& $searchTypes->next();
	
	$typeString = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
	print "\n<h3>".$typeString."</h3>";
	print "\n".$searchModules->createSearchForm($searchType, MYURL."/collection/searchresults/".$drId->getIdString()."/".urlencode($typeString)."/");
}

$searchFields =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$searchFields->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($searchFields);

// return the main layout.
return $mainScreen;