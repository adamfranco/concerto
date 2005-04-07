<?php
/**
 * @package concerto.modules.collections
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
$actionRows =& new Container($yLayout,OTHER,1);
$centerPane->add($actionRows, null, null, CENTER, TOP);


// Get the Repository
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");

// Intro
$introHeader =& new Heading("Search Assets in all Collections", 2);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);

ob_start();
print  "<p>";
print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print  "</p>";

$introText =& new Block(ob_get_contents(),3);
$actionRows->add($introText, "100%", null, CENTER, CENTER);
ob_end_clean();



// Print out the search types

ob_start();

// Get all the drs and all of their search types
$searchModules =& Services::getService("RepositorySearchModules");
$searchArray = array();

$repositories =& $repositoryManager->getRepositories();
while ($repositories->hasNext()) {
	$repository =& $repositories->next();
	$searchTypes =& $repository->getSearchTypes();
	while ($searchTypes->hasNext()) {
		$searchType =& $searchTypes->next();
		
		$typeString = $searchType->getDomain()
						."::".$searchType->getAuthority()
						."::".$searchType->getKeyword();
		
		if (!$searchArray[$typeString])
			$searchArray[$typeString] =& $searchType;
	}
}

// print out the types
foreach (array_keys($searchArray) as $typeString) {
	$searchType =& $searchArray[$typeString];
	print "\n<h3>".$typeString."</h3>";
	print "\n".$searchModules->createSearchForm($searchType, MYURL."/collections/searchresults/".urlencode($typeString)."/");
}

$searchFields =& new Block(ob_get_contents(), 3);
ob_end_clean();
$actionRows->add($searchFields, "100%", null, LEFT, CENTER);

// return the main layout.
return $mainScreen;