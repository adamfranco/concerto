<?php
/**
 * @package concerto.modules.collection
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
 

// Our Layout Setup
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout,OTHER,1);
$centerPane->add($actionRows, null, null, CENTER, TOP);

// Get the Repository
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");
$repositoryId =& $idManager->getId($harmoni->pathInfoParts[2]);
$repository =& $repositoryManager->getRepository($repositoryId);

// Intro
$introHeader =& new Heading("Browse Assets in the <em>".$repository->getDisplayName()."</em> Collection", 2);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);

// function links
ob_start();
print _("Collection").": ";
RepositoryPrinter::printRepositoryFunctionLinks($harmoni, $repository);
$layout =& new Block(ob_get_contents(), 3);
ob_end_clean();
$actionRows->add($layout, "100%", null, LEFT, CENTER);

$repositoryManager =& Services::getService("Repository");

// Get all the types
$types =& $repository->getAssetTypes();
// put the drs into an array and order them.
$typeArray = array();
while($types->hasNext()) {
	$type =& $types->next();
	$typeArray[$type->getDomain()." ".$type->getAuthority()." ".$type->getKeyword()] =& $type;
}
ksort($typeArray);

// print the Results
$resultPrinter =& new ArrayResultPrinter($typeArray, 2, 20, "printTypeShort", $repositoryId);
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);

// return the main layout.
return $mainScreen;


// Callback function for printing Repositories
function printTypeShort(& $type, & $repositoryId) {
	ob_start();
	
	$typeString = $type->getDomain()." :: " .$type->getAuthority()." :: ".$type->getKeyword();

	print "<a href='".MYURL."/collection/browsetype/".$repositoryId->getIdString()."/".urlencode($typeString)."'>";
	print "\n\t<strong>";
	print $typeString;
	print "</strong>";
	print "</a>";
	
	$layout =& new Block(ob_get_contents(), 4);
	ob_end_clean();
	return $layout;
}