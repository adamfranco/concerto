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


// Print out the RecordStructure Parts.
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");
$setManager =& Services::getService("Sets");
$repositoryId =& $idManager->getId($harmoni->pathInfoParts[2]);
$recordStructureId =& $idManager->getId($harmoni->pathInfoParts[3]);

$repository =& $repositoryManager->getRepository($repositoryId);
$recordStructure =& $repository->getRecordStructure($recordStructureId);
$set =& $setManager->getSet($recordStructureId);

print "<h3>".$recordStructure->getDisplayName()."</h3>";
print "<em>".$recordStructure->getDescription()."</em>";
print "<br /><strong>"._("Format").":</strong> ".$recordStructure->getFormat()."";

// Print out the PartStructures
print "<h4>"._("Elements").":</h4>";
print "\n<table border='1'>";
print "\n<th>"._("Order")."</th>";
print "\n<th>"._("DisplayName")."</th>";
print "\n<th>"._("Description")."</th>";
print "\n<th>"._("IsMandatory?")."</th>";
print "\n<th>"._("IsRepeatable?")."</th>";
print "\n<th>"._("IsPopulatedByRepository?")."</th>";
print "\n</tr>";
$partStructures =& $recordStructure->getPartStructures();
$partStructureArray = array();
while ($partStructures->hasNext()) {
	$partStructure =& $partStructures->next();
	if ($set->isInSet($partStructure->getId()))
		$partStructureArray[$set->getPosition($partStructure->getId())] =& $partStructure;
	else
		$partStructureArray[] =& $partStructure;
}

ksort($partStructureArray);
foreach (array_keys($partStructureArray) as $key) {
	$partStructure =& $partStructureArray[$key];
	print "\n<tr>";
	print "\n<td>".($key+1)."</td>";
	print "\n<td><strong>".$partStructure->getDisplayName()."</strong></td>";
	print "\n<td><em>".$partStructure->getDescription()."</em></td>";
	print "\n<td>".(($partStructure->isMandatory())?"TRUE":"FALSE")."</td>";
	print "\n<td>".(($partStructure->isRepeatable())?"TRUE":"FALSE")."</td>";
	print "\n<td>".(($partStructure->isPopulatedByRepository())?"TRUE":"FALSE")."</td>";
	print "\n</tr>";
}
print "\n</table>";

$text =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$text->addComponent(new Content(ob_get_contents()), TOP, LEFT);

$centerPane->addComponent($text, TOP, LEFT);
ob_end_clean();

// return the main layout.
return $mainScreen;