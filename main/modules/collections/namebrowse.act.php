<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout,OTHER,1);
$centerPane->add($actionRows, null, null, CENTER, TOP);

// Intro
$introHeader =& new Heading("Browse Collections By Name", 2);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);

$text = "";
$text .= "<p>";
$text .= _("Below are listed the availible <em>Collections</em>, organized by name.");
$text .= "</p>\n<p>";
$text .= _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
$text .= "</p>";

$introText =& new Block($text,3);
$actionRows->add($introText, "100%", null, CENTER, CENTER);


// Get the Repositoriess
$repositoryManager =& Services::getService("Repository");
$allRepositories =& $repositoryManager->getRepositories();

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
$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);


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
	$xLayout =& new XLayout();
	$layout =& new Container($xLayout, BLOCK, 4);
	$layout2 =& new Block(ob_get_contents(), 2);
	$layout->add($layout2, null, null, CENTER, CENTER);
	ob_end_clean();
	return $layout;
}