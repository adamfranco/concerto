<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Browse Collections By Name")));
$actionRows->addComponent($introHeader);

$text = "";
$text .= "<p>";
$text .= _("Below are listed the availible <em>Collections</em>, organized by name.");
$text .= "</p>\n<p>";
$text .= _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
$text .= "</p>";

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$introText->addComponent(new Content($text));
$actionRows->addComponent($introText);


// Get the Repositoriess
$repositoryManager =& Services::getService("Repository");
$allRepositories =& $repositoryManager->getDigitalRepositories();

// put the drs into an array and order them.
// @todo, do authorization checking
$repositoryArray = array();
while($allRepositories->hasNext()) {
	$repository =& $allRepositories->next();
	$repositoryArray[$repository->getDisplayName()] =& $repository;
}
ksort($repositoryArray);

// print the Results
$resultPrinter =& new ArrayResultPrinter($repositoryArray, 2, 20, "printRepositoryShort", $harmoni);
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->addComponent($resultLayout);


// return the main layout.
return $mainScreen;


// Callback function for printing Repositories
function printRepositoryShort(& $repository, & $harmoni) {
	ob_start();
	
	$repositoryId =& $repository->getId();
	print  "\n\t<strong>".$repository->getDisplayName()."</strong> - "._("ID#").": ".
			$repositoryId->getIdString();
	print  "\n\t<br /><em>".$repository->getDescription()."</em>";	
	print  "\n\t<br />";
	
	RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
	
	$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	return $layout;
}