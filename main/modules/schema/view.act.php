<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');


ob_start();

// Prepare the return URL so that we can get back to where we were.
$currentPathInfo = array();
for ($i = 4; $i < count($harmoni->pathInfoParts); $i++) {
	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
}
$returnURL = MYURL."/".implode("/",$currentPathInfo);

print "<a href='".$returnURL;
if (count($_GET)) {
	print "?";
	foreach ($_GET as $key => $val) {
		print "&".$key."=".$val;
	}
}
print "'><-- "._("Return")."</a>";


// Print out the InfoStructure Parts.
$drManager =& Services::getService("DR");
$sharedManager =& Services::getService("Shared");
$setManager =& Services::getService("Sets");
$drId =& $sharedManager->getId($harmoni->pathInfoParts[2]);
$infoStructureId =& $sharedManager->getId($harmoni->pathInfoParts[3]);

$dr =& $drManager->getDigitalRepository($drId);
$infoStructure =& $dr->getInfoStructure($infoStructureId);
$set =& $setManager->getSet($infoStructureId);

print "<h3>".$infoStructure->getDisplayName()."</h3>";
print "<em>".$infoStructure->getDescription()."</em>";
print "<br /><strong>"._("Format").":</strong> ".$infoStructure->getFormat()."";

// Print out the infoParts
print "<h4>"._("Elements").":</h4>";
print "\n<table border='1'>";
print "\n<th>"._("Order")."</th>";
print "\n<th>"._("DisplayName")."</th>";
print "\n<th>"._("Description")."</th>";
print "\n<th>"._("IsManditory?")."</th>";
print "\n<th>"._("IsRepeatable?")."</th>";
print "\n<th>"._("IsPopulatedByDR?")."</th>";
print "\n</tr>";
$infoParts =& $infoStructure->getInfoParts();
$partArray = array();
while ($infoParts->hasNext()) {
	$infoPart =& $infoParts->next();
	if ($set->isInSet($infoPart->getId()))
		$partArray[$set->getPosition($infoPart->getId())] =& $infoPart;
	else
		$partArray[] =& $infoPart;
}

ksort($partArray);
foreach (array_keys($partArray) as $key) {
	$infoPart =& $partArray[$key];
	print "\n<tr>";
	print "\n<td>".($key+1)."</td>";
	print "\n<td><strong>".$infoPart->getDisplayName()."</strong></td>";
	print "\n<td><em>".$infoPart->getDescription()."</em></td>";
	print "\n<td>".(($infoPart->isManditory())?"TRUE":"FALSE")."</td>";
	print "\n<td>".(($infoPart->isRepeatable())?"TRUE":"FALSE")."</td>";
	print "\n<td>".(($infoPart->isPopulatedByDR())?"TRUE":"FALSE")."</td>";
	print "\n</tr>";
}
print "\n</table>";

$text =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$text->addComponent(new Content(ob_get_contents()), TOP, LEFT);

$centerPane->addComponent($text, TOP, LEFT);
ob_end_clean();

// return the main layout.
return $mainScreen;