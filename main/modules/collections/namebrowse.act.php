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
$text .= _("<em>Collections</em> are containers for <em>Assets</em>. <em>Assets</em> can in turn contain other Assets. Each collection can have its own cataloging schema.");
$text .= " ";
$text .= _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
$text .= "</p>";

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$introText->addComponent(new Content($text));
$actionRows->addComponent($introText);


// Get the DRs
$dr =& Services::getService("DR");
$allDRs =& $dr->getDigitalRepositories();

// put the drs into an array and order them.
// @todo, do authorization checking
$drArray = array();
while($allDRs->hasNext()) {
	$dr =& $allDRs->next();
	$drArray[$dr->getDisplayName] =& $dr;
}
ksort($drArray);

// Print out the DRs
$text = "\n<ul>";
if (count($drArray)) {
	foreach (array_keys($drArray) as $name) {
		$dr =& $drArray[$name];
		$drId =& $dr->getId();
		$text .= "\n<li>";
		$text .= "\n\t<strong>".$dr->getDisplayName()."</strong> - ".
				$drId->getIdString()." - <em>".$dr->getDescription()."</em>";
		$text .= "\n</li>";
	}
	
} else {
	$text .= "\n<li>\n\t"._("No <em>Collections</em> are availible.")."\n</li>";
}
$text .= "\n</ul>";

$drBlock =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$drBlock->addComponent(new Content($text));
$actionRows->addComponent($drBlock);


// return the main layout.
return $mainScreen;