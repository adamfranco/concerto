<?
// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info.
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');


// Determine paramenters
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

//create and print header
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Browse Exhibitions By Name")));
$actionRows->addComponent($introHeader);

$text = "";
$text .= "<p>";
$text .= _("Below are listed the availible <b>Exhibitions</b>, organized by name.");
$text .= "</p>\n<p>";
$text .= _("Some <b>Collections</b>, <b>Exhibitions</b>, <b>Assets</b>, and <b>Slide-Shows</b> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
$text .= "</p>";

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$introText->addComponent(new Content($text));
$actionRows->addComponent($introText);


// Get the Repositoriess
$repositoryManager =& Services::getService("Repository");
$type_exh =& new HarmoniType ('System Repositories', 'Concerto', 'Exhibitions',  
			'A Repository for holding Exhibitions, their Slide-Shows and Slides'); 
$exRepositories =& $repositoryManager->getRepositoriesByType( &$type_exh);

// put the drs into an array and order them.
// @todo, do authorization checking
$repositoryArray = array();
while($exRepositories->hasNext()) {
	$repository =& $exRepositories->next();
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

	RepositoryPrinter::printRepositoryFunctionLinksExh($harmoni, $repository);

	$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	return $layout;
}
