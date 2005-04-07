<?php
/**
 * @package concerto.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

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
$introHeader =& new Heading("Welcome to Concerto", 2);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);


$text = "";
//$text .= "\n<img src='".MYPATH."/main/modules/home/flower.jpg' alt='A flower. &copy;2003 Adam Franco - Creative Commons Attribution-ShareAlike 1.0 - http://creativecommons.org/licenses/by-sa/1.0/' align='right' style='margin: 10px;' />";
$text .= "<p>";
$text .= _("<strong>Concerto</strong> is a digital assets management tool developed at Middlebury College.");
$text .= "</p>\n<p>";
$text .= _("The two main parts of <strong>Concerto</strong> are the <em>Collections</em> of digital <em>Assets</em> and the <em>Exhibitions</em> of <em>Slide-Shows</em>. Click on the links to the left to start exploring <strong>Concerto</strong>.");
$text .= "</p>\n<p>";
$text .= _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
$text .= "</p>";

$introText =& new Block($text,3);
$actionRows->add($introText, "100%", null, CENTER, CENTER);


return $mainScreen;