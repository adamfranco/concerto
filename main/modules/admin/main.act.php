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
$introHeader->addComponent(new Content(_("Admin Tools")));
$actionRows->addComponent($introHeader);

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
ob_start();
print "";
print "</p>\n<ul>";
print "\n\t<li><a href='".MYURL."/agents/group_membership/'>";
print _("Edit Group Membership");
print "</a></li>";
print "\n\t<li><a href='".MYURL."/authorization/choose_agent/'>";
print _("Edit authorizations");
print "</a></li>";
print "</ul>\n<p>";

$introText->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($introText);

// return the main layout.
return $mainScreen;