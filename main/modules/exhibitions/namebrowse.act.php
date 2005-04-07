<?php
/**
 * @package concerto.modules.exhibitions
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
$centerPane =& $harmoni->getAttachedData('centerPane');


// Our
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);
$centerPane->add($actionRows, null, null, CENTER, CENTER);


//create and print header
$introHeader =& new Heading(_("Browse Exhibitions By Name"), 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);



$text = "";
$text .= "<p>";
$text .= _("Below are listed the availible <b>Exhibitions</b>, organized by name.");
$text .= "</p>\n<p>";
$text .= _("Some <b>Collections</b>, <b>Exhibitions</b>, <b>Assets</b>, and <b>Slide-Shows</b> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
$text .= "</p>";

$introText =& new Block($text, 2);
$actionRows->add($introText, null, null, CENTER, CENTER);


// Get the Repositoriess
$repositoryManager =& Services::getService("Repository");
$type_exh =& new HarmoniType ('System Repositories', 'Concerto', 'Exhibitions',  
			'A Repository for holding Exhibitions, their Slide-Shows and Slides'); 
$exRepositories =& $repositoryManager->getRepositoriesByType( &$type_exh);

// put the drs into an array and order them.
// @todo, do authorization checking
$repositoryArray = array();
while($exRepositories->hasNext()) {
	$repository =& $exRepositories->next();
	$repositoryArray[$repository->getDisplayName()] =& $repository;
}
ksort($repositoryArray);

// print the Results
$resultPrinter =& new ArrayResultPrinter($repositoryArray, 2, 20, "printRepositoryShort", $harmoni);
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->add($resultLayout, null, null, CENTER, CENTER);


// return the main layout.
return $mainScreen;


// Callback function for printing Repositories
function printRepositoryShort(& $repository, & $harmoni) {
	ob_start();

	$repositoryId =& $repository->getId();
	print  "\n\t<strong>".$repository->getDisplayName()."</strong> - "._("ID#").": ".
			$repositoryId->getIdString();
	print  "\n\t<br /><em>".$repository->getDescription()."</em>";
	print  "\n\t<br />";

	RepositoryPrinter::printRepositoryFunctionLinksExh($harmoni, $repository);

	$layout =& new Block(ob_get_contents(), 4);
	ob_end_clean();
	return $layout;
}
