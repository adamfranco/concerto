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


$typeString = urldecode($harmoni->pathInfoParts[2]);
$typeParts = explode(" :: ", $typeString);
$type =& new HarmoniType($typeParts[0],$typeParts[1],$typeParts[2]);


// Intro
$introHeader =& new Heading(_("Browse Collections with Type").": \n<br />".$typeString, 2);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);


$repositoryManager =& Services::getService("Repository");


// Get the Repositories
$allRepositories =& $repositoryManager->getRepositoriesByType($type);

// put the repositories into an array and order them.
// @todo, do authorization checking
$repositoryArray = array();
while($allRepositories->hasNext()) {
	$repository =& $allRepositories->next();
	$repositoryArray[$repository->getDisplayName()] =& $repository;
}
ksort($repositoryArray);

// print the Results
$resultPrinter =& new ArrayResultPrinter($repositoryArray, 2, 20, "printrepositoryShort", $harmoni);
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->add($resultLayout, null, null, CENTER, CENTER);



// return the main layout.
return $mainScreen;


// Callback function for printing repositorys
function printrepositoryShort(& $repository, $harmoni) {
	ob_start();
	
	$repositoryId =& $repository->getId();
	print  "\n\t<strong>".$repository->getDisplayName()."</strong> - "._("ID#").": ".
			$repositoryId->getIdString();
	print  "\n\t<br /><em>".$repository->getDescription()."</em>";	
	print  "\n\t<br />";
	
	RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
	
	$layout =& new Block(ob_get_contents(), 4);
	ob_end_clean();
	return $layout;
}
