<?php
/**
 * @package concerto.modules.exhibition
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

// Get the Repository
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");
$repositoryId =& $idManager->getId($harmoni->request->get('collection_id'));
$repository =& $repositoryManager->getRepository($repositoryId);


// Check that the user can access this collection
$authZ =& Services::getService("AuthZ");
$idManager =& Services::getService("Id");
if (!$authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.access"), $repositoryId)) {
	$errorLayout =& new Block(_("You are not authorized to access this <em>Exhibition</em>."),2);
	$centerPane->add($errorLayout, null, null, CENTER, CENTER);
	return $mainScreen;
}

// Our Layout Setup
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);
$centerPane->add($actionRows, null, null, CENTER, CENTER);

// Intro
$introHeader =& new Heading(_("Search Assets in the").
             " <em>".$repository->getDisplayName()."</em> "._("Exhibition"), 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);

// function links
ob_start();
print _("Exhibition").": ";
RepositoryPrinter::printRepositoryFunctionLinksExh($harmoni, $repository);
$layout =& new Block(ob_get_contents, 2);
ob_end_clean();
$actionRows->add($layout, null, null, CENTER, CENTER);

ob_start();
print  "<p>";
print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print  "</p>";

$introText =& new Block(ob_get_contents, 2);
ob_end_clean();
$actionRows->add($introText, null, null, CENTER, CENTER);


// Print out the search types

ob_start();

$searchModules =& Services::getService("RepositorySearchModules");
$searchTypes =& $repository->getSearchTypes();
while ($searchTypes->hasNext()) {
	$searchType =& $searchTypes->next();

	$typeString = $searchType->getDomain()
					."::".$searchType->getAuthority()
					."::".$searchType->getKeyword();
	print "\n<h3>".$typeString."</h3>";
	print "\n".$searchModules->createSearchForm($searchType, MYURL."/exhibition/searchresults/".$repositoryId->getIdString()."/".urlencode($typeString)."/");
}

$searchFields =& new Block(ob_get_contents, 2);
ob_end_clean();
$actionRows->add($searchFields, null, null, CENTER, CENTER);

// return the main layout.
return $mainScreen;
