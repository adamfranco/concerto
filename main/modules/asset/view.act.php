<?

if (!defined("AZ_VIEW"))
	throwError(new Error("You must define an id for AZ_VIEW", "concerto.asset", true));

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');
 
// Check that the user can create an asset here.
$authZ =& Services::getService("AuthZ");
$shared =& Services::getService("Shared");
if (!$authZ->isUserAuthorized($shared->getId(AZ_VIEW), $shared->getId($harmoni->pathInfoParts[3]))) {
	$errorLayout =& new SingleContentLayout;
	$errorLayout->addComponent(new Content(_("You are not authorized to view this <em>Asset</em> here."), MIDDLE, CENTER));
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
$dr =& $asset->getDigitalRepository();
$drId =& $dr->getId();

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

// Columns for Description and thumbnail.
$contentCols =& new ColumnLayout;
$actionRows->addComponent($contentCols);

	// Description and dates
	ob_start();
	$assetId =& $asset->getId();
	print  "\n\t<strong>"._("Description").":</strong> \n<em>".$asset->getDescription()."</em>";
	print  "\n\t<br><strong>"._("ID#").":</strong> ".$assetId->getIdString();

	$effectDate =& $asset->getEffectiveDate();
	print  "\n\t<br><strong>"._("Effective Date").":</strong> \n<em>".$effectDate->toString()."</em>";

	$expirationDate =& $asset->getExpirationDate();
	print  "\n\t<br><strong>"._("Expiration Date").":</strong> \n<em>".$expirationDate->toString()."</em>";

	$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
	$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	$contentCols->addComponent($layout);
	
	// Thumbnail
	ob_start();
// 	$thumbnailFields =& $asset->getInfoFieldByPart($_SESSION['concerto_config']['thumbnail_part_id']);
// 	while ($fields->hasNext()) {
// 		$field =& $fields->next();
// 		$value =& $field->getValue();
// 		print "\n<img src='".$value->toString()."'>\n<br />";
// 	}
	$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	$contentCols->addComponent($layout);


//***********************************
// Info Records
//***********************************
ob_start();
$printedRecordIds = array();

// Get the set of InfoStructures so that we can print them in order.
$setManager =& Services::getService("Sets");
$structSet =& $setManager->getSet($drId);

// First, lets go through the info structures listed in the set and print out
// the info records for those structures in order.
while ($structSet->hasNext()) {
	$structureId =& $structSet->next();
	$records =& $asset->getInfoRecords($structureId);
	while ($records->hasNext()) {
		$record =& $records->next();
		$recordId =& $record->getId();
		$printedRecordIds[] = $recordId->getIdString();

		print "<hr>";
		printRecord($drId, $assetId, $record);
	}	
}

$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$layout->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($layout);


//***********************************
// Content
//	If we can, we may want to print the content here.
// 	@todo Add some sniffing of content so that we can either put it in if
// 	it is text, image, etc, or do otherwise with it if it is some other form
// 	of data.
//***********************************
$content =& $asset->getContent();
if ($string = $content->toString()) {
	ob_start();
	print ($string);
	$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	$actionRows->addComponent($layout);
}

//***********************************
// return the main layout.
return $mainScreen;
//***********************************


//***********************************
// Function Definitions
//***********************************

function printRecord(& $drId, &$assetId, & $record) {	
	$infoStructure =& $record->getInfoStructure();
	$structureId =& $infoStructure->getId();
	
	// Print out the fields parts for this structure
	$setManager =& Services::getService("Sets");
	$partSet =& $setManager->getSet($structureId);
	
	$partsArray = array();
	// Print out the ordered parts/fields
	$partSet->reset();
	while ($partSet->hasNext()) {
		$partId =& $partSet->next();
		$partsArray[] =& $infoStructure->getInfoPart($partId);
	}
	// Get the rest of the parts (the unordered ones);
	$partIterator =& $infoStructure->getInfoParts();
	while ($partIterator->hasNext()) {
		$part =& $partIterator->next();
		if (!$partSet->isInSet($part->getId()))
			$partsArray[] =& $part;
	}
	
	$moduleManager =& Services::getService("InOutModules");
	print $moduleManager->generateDisplayForFields($drId, $assetId, $record, $partsArray);
}