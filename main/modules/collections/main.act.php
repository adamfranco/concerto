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
$text = "";
//$text .= "\n<img src='".MYPATH."/main/modules/home/flower.jpg' alt='A flower. &copy;2003 Adam Franco - Creative Commons Attribution-ShareAlike 1.0 - http://creativecommons.org/licenses/by-sa/1.0/' align='right' style='margin: 10px;' />";
$text .= "<p>";
$text .= _("<em>Collections</em> are containers for <em>Assets</em>. <em>Assets</em> can in turn contain other Assets. Each collection can have its own cataloging schema.");
$text .= "</p>\n<ul>";
$text .= "\n\t<li><a href='".MYURL."/collections/namebrowse/'>";
$text .= _("Browse <em>Collections</em> by Name");
$text .= "</a></li>";
$text .= "\n\t<li><a href='".MYURL."/collections/typebrowse/'>";
$text .= _("Browse <em>Collections</em> by Type");
$text .= "</a></li>";
$text .= "\n\t<li><a href='".MYURL."/collections/search/'>";
$text .= _("Search <em>Collections</em> for <em> Assets</em>");
$text .= "</a></li>";
$text .= "</ul>\n<p>";
$text .= _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
$text .= "</p>";

// If the user is authorized, allow them to create a new collection.
// @todo - add authorization.
$text .= "\n<ul>\n<li><a href='".MYURL."/collection/create/'>";
$text .= _("Create a new <em>Collection</em>");
$text .= "</a>\n</li>\n</ul>";

$introText->addComponent(new Content($text));
$actionRows->addComponent($introText);

// return the main layout.
return $mainScreen;