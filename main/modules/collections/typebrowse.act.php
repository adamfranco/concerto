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
$introHeader =& new Heading("Browse Collections By Type", 2);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);

$text = "";
$text .= "<p>";
$text .= _("Below are listed the availible <em>Collections</em>, organized by type, then name.");
$text .= "</p>\n<p>";
$text .= _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
$text .= "</p>";

$introText =& new Block($text,3);
$actionRows->add($introText, "100%", null, CENTER, CENTER);

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
$actionRows->add($resultLayout, null, null, CENTER, CENTER);

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
	
	$xLayout =& new XLayout();
	$layout =& new Container($xLayout, BLOCK, 4);
	$layout2 =& new Block(ob_get_contents(), 2);
	$layout->add($layout2, null, null, CENTER, CENTER);
	ob_end_clean();
	return $layout;
}