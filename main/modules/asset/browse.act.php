<?

if (!defined("AZ_ACCESS"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.asset", true));

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');


// Check that the user can create an asset here.
$authZ =& Services::getService("AuthZ");
$shared =& Services::getService("Shared");
if (!$authZ->isUserAuthorized($shared->getId(AZ_ACCESS), $shared->getId($harmoni->pathInfoParts[3]))) {
	$errorLayout =& new SingleContentLayout;
	$errorLayout->addComponent(new Content(_("You are not authorized to access this <em>Asset</em> here."), MIDDLE, CENTER));
	$centerPane->addComponent($errorLayout, MIDDLE, CENTER);
	return $mainScreen;
}

// Our Layout Setup
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Get the DR
$drManager =& Services::getService("DR");
$sharedManager =& Services::getService("Shared");
$assetId =& $sharedManager->getId($harmoni->pathInfoParts[3]);
$asset =& $drManager->getAsset($assetId);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Asset").": <em>".$asset->getDisplayName()."</em>"));
$actionRows->addComponent($introHeader);

// function links
ob_start();
AssetPrinter::printAssetFunctionLinks($harmoni, $asset);
$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$layout->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($layout);

ob_start();
print  "<p>";
print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print  "</p>";

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$introText->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($introText);


//***********************************
// Get the assets to display
//***********************************
$assets =& $asset->getAssets();

//***********************************
// print the results
//***********************************
$resultPrinter =& new IteratorResultPrinter($assets, 2, 6, "printAssetShort", $harmoni);
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->addComponent($resultLayout);


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
	
	$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	return $layout;
}