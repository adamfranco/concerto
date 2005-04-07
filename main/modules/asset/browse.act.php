<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

if (!defined("AZ_ACCESS"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.asset", true));

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');


// Check that the user can create an asset here.
$authZ =& Services::getService("AuthZ");
$idManager =& Services::getService("Id");
if (!$authZ->isUserAuthorized($idManager->getId(AZ_ACCESS), $idManager->getId($harmoni->pathInfoParts[3]))) {
	$errorLayout =& new Block(_("You are not authorized to create an <em>Asset</em> here."),2);
	$centerPane->add($errorLayout, null, null, CENTER, CENTER);
	return $mainScreen;
}

// Our Layout Setup
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);
$centerPane->add($actionRows, null, null, CENTER, TOP);

// Get the Repository
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");
$assetId =& $idManager->getId($harmoni->pathInfoParts[3]);
$asset =& $repositoryManager->getAsset($assetId);

// Intro
$introHeader =& new Heading(_("Asset").": <em>".$asset->getDisplayName()."</em>", 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);

// function links
ob_start();
AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
$layout =& new Block(ob_get_contents(), 2);
ob_end_clean();
$actionRows->add($layout, null, null, CENTER, CENTER);

ob_start();
print  "<p>";
print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print  "</p>";

$introText =& new Block(ob_get_contents(), 2);
ob_end_clean();
$actionRows->add($introText, "100%", null, LEFT, CENTER);


//***********************************
// Get the assets to display
//***********************************
$assets =& $asset->getAssets();

//***********************************
// print the results
//***********************************
$resultPrinter =& new IteratorResultPrinter($assets, 2, 6, "printAssetShort", $harmoni);
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);


// return the main layout.
return $mainScreen;


// Callback function for printing Assets
function printAssetShort(& $asset, &$harmoni) {
	ob_start();
	
	$assetId =& $asset->getId();
	print  "\n\t<strong>".$asset->getDisplayName()."</strong> - "._("ID#").": ".
			$assetId->getIdString();
	print  "\n\t<br /><em>".$asset->getDescription()."</em>";	
	print  "\n\t<br />";
	
	AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
	
	$layout =& new Block(ob_get_contents(), 4);
	ob_end_clean();
	return $layout;
}