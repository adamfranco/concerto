<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout,OTHER,1);
$centerPane->add($actionRows, null, null, CENTER, TOP);

// Intro
$introHeader =& new Heading("Admin Tools", 2);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);


ob_start();
print "\n<ul>";
print "\n\t<li><a href='".MYURL."/agents/group_membership/'>";
print _("Edit Group Membership");
print "</a></li>";
print "\n\t<li><a href='".MYURL."/authorization/browse_authorizations/'>";
print _("Browse authorizations");
print "</a></li>";
print "\n\t<li><a href='".MYURL."/authorization/choose_agent/'>";
print _("Edit authorizations");
print "</a></li>";
print "\n\t<li><a href='".MYURL."/agents/create_agent/'>";
print _("Create User");
print "</a></li>";
print "\n</ul>";

$introText =& new Block(ob_get_contents(),3);
$actionRows->add($introText, "100%", null, CENTER, CENTER);
ob_end_clean();

// return the main layout.
return $mainScreen;