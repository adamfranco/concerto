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
$introHeader->addComponent(new Content(_("Browse Collections By Type")));
$actionRows->addComponent($introHeader);

$text = "";
$text .= "<p>";
$text .= _("Below are listed the availible <em>Collections</em>, organized by type, then name.");
$text .= "</p>\n<p>";
$text .= _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
$text .= "</p>";

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$introText->addComponent(new Content($text));
$actionRows->addComponent($introText);

$repositoryManager =& Services::getService("Repository");

// Get all the types
$types =& $repositoryManager->getRepositoryTypes();
// put the drs into an array and order them.
$typeArray = array();
while($types->hasNext()) {
	$type =& $types->next();
	$typeArray[$type->getDomain()." ".$type->getAuthority()." ".$type->getKeyword()] =& $type;
}
ksort($typeArray);

// print the Results
$resultPrinter =& new ArrayResultPrinter($typeArray, 2, 20, "printTypeShort");
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->addComponent($resultLayout);

// return the main layout.
return $mainScreen;


// Callback function for printing Repositories
function printTypeShort(& $type) {
	ob_start();
	
	$typeString = $type->getDomain()." :: " .$type->getAuthority()." :: ".$type->getKeyword();

	print "<a href='".MYURL."/collections/browsetype/".urlencode($typeString)."'>";
	print "\n\t<strong>";
	print $typeString;
	print "</strong>";
	print "</a>";
	
	$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	return $layout;
}