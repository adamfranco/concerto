<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

$typeString = urldecode($harmoni->pathInfoParts[2]);
$typeParts = explode(" :: ", $typeString);
$type =& new HarmoniType($typeParts[0],$typeParts[1],$typeParts[2]);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Browse Collections with Type").": \n<br>".$typeString));
$actionRows->addComponent($introHeader);

$drManager =& Services::getService("DR");


// Get the DRs
$allDRs =& $drManager->getDigitalRepositoriesByType($type);

// put the drs into an array and order them.
// @todo, do authorization checking
$drArray = array();
while($allDRs->hasNext()) {
	$dr =& $allDRs->next();
	$drArray[$dr->getDisplayName()] =& $dr;
}
ksort($drArray);

// print the Results
$resultPrinter =& new ArrayResultPrinter($drArray, 2, 20, "printDRShort", $harmoni);
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->addComponent($resultLayout);



// return the main layout.
return $mainScreen;


// Callback function for printing DRs
function printDRShort(& $dr, $harmoni) {
	ob_start();
	
	$drId =& $dr->getId();
	print  "\n\t<strong>".$dr->getDisplayName()."</strong> - "._("ID#").": ".
			$drId->getIdString();
	print  "\n\t<br><em>".$dr->getDescription()."</em>";	
	print  "\n\t<br>";
	
	RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $dr);
	
	$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	return $layout;
}