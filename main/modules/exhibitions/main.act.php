<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info.
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');


// Our
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);
$centerPane->add($actionRows, null, null, CENTER, CENTER);

// Intro
$introHeader =& new Heading(_("Exhibitions"), 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);


ob_start();
print "";
print "<p>";
print _("<b>Exhibitions</b> are composed of slideshows, which in turn are composed of slides of different types (multimedia, text etc.)") ;
print "</p>\n<ul>";
print "\n\t<li><a href='".MYURL."/exhibitions/namebrowse/'>";
print _("Browse <b>Exhibitions</b> by Name");
print "</a></li>";

// If the user is authorized, allow them to create a new exhibition.
// @todo - add authorization.

print "\n\t<li><a href='".MYURL."/exhibition/create/'>";
print _("Create a new <b>Exhibition</b>");
print "</a>\n</li>\n</ul>";

print "</ul>\n<p>";
print _("Some <b>Collections</b>, <b>Exhibitions</b>, <b>Assets</b>, and <b>Slide-Shows</b> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print "</p>";

/*$type_exh =& new HarmoniType ('System Repositories', 'Concerto', 'Exhibitions',  
			'A Repository for holding Exhibitions, their Slide-Shows and Slides'); 
*/
// If the user is authorized, allow them to create a new exhibition.
// @todo - add authorization.
$introText =& new Block(ob_get_contents(), 3);
ob_end_clean();
$actionRows->add($introText, null, null, CENTER, CENTER);

// return the main layout.
return $mainScreen;
