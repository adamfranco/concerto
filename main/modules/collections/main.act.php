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
$introHeader->addComponent(new Content(_("Collections")));
$actionRows->addComponent($introHeader);

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
ob_start();
print "";
//print "\n<img src='".MYPATH."/main/modules/home/flower.jpg' alt='A flower. &copy;2003 Adam Franco - Creative Commons Attribution-ShareAlike 1.0 - http://creativecommons.org/licenses/by-sa/1.0/' align='right' style='margin: 10px;' />";
print "<p>";
print _("<em>Collections</em> are containers for <em>Assets</em>. <em>Assets</em> can in turn contain other Assets. Each collection can have its own cataloging schema.");
print "</p>\n<ul>";
print "\n\t<li><a href='".MYURL."/collections/namebrowse/'>";
print _("Browse <em>Collections</em> by Name");
print "</a></li>";
print "\n\t<li><a href='".MYURL."/collections/typebrowse/'>";
print _("Browse <em>Collections</em> by Type");
print "</a></li>";
print "\n\t<li><a href='".MYURL."/collections/search/'>";
print _("Search <em>Collections</em> for <em> Assets</em>");
print "</a></li>";
print "</ul>\n<p>";
print _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print "</p>";

// If the user is authorized, allow them to create a new collection.
// @todo - add authorization.
print "\n<ul>\n<li><a href='".MYURL."/collection/create/'>";
print _("Create a new <em>Collection</em>");
print "</a>\n</li>\n</ul>";

$introText->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($introText);

// return the main layout.
return $mainScreen;