<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Welcome to Concerto")));
$actionRows->addComponent($introHeader);

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$text = _("Concerto is a digital assets management tool developed at Middlebury College.");
$text .= " <a href='".MYURL."/home/welcome/'>Welcome</a>";
$introText->addComponent(new Content($text));
$actionRows->addComponent($introText);

// return the main layout.
return $mainScreen;