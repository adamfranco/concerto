<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our rows
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Get the Repository
$repositoryManager =& Services::getService("Repository");
$sharedManager =& Services::getService("Shared");
$repositoryId =& $sharedManager->getId($harmoni->pathInfoParts[2]);
$repository =& $repositoryManager->getRepository($repositoryId);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Browse Assets in the")." <em>".$repository->getDisplayName()."</em> "._("Collection")._(" by Type")));
$actionRows->addComponent($introHeader);

// function links
ob_start();
print _("Collection").": ";
RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$layout->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($layout);

$repositoryManager =& Services::getService("Repository");

// Get all the types
$types =& $repository->getAssetTypes();
// put the drs into an array and order them.
$typeArray = array();
while($types->hasNext()) {
	$type =& $types->next();
	$typeArray[$type->getDomain()." ".$type->getAuthority()." ".$type->getKeyword()] =& $type;
}
ksort($typeArray);

// print the Results
$resultPrinter =& new ArrayResultPrinter($typeArray, 2, 20, "printTypeShort", $repositoryId);
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->addComponent($resultLayout);

// return the main layout.
return $mainScreen;


// Callback function for printing Repositories
function printTypeShort(& $type, & $repositoryId) {
	ob_start();
	
	$typeString = $type->getDomain()." :: " .$type->getAuthority()." :: ".$type->getKeyword();

	print "<a href='".MYURL."/collection/browsetype/".$repositoryId->getIdString()."/".urlencode($typeString)."'>";
	print "\n\t<strong>";
	print $typeString;
	print "</strong>";
	print "</a>";
	
	$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	return $layout;
}