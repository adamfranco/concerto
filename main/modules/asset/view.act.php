<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

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
		printRecord($record);
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

function printRecord(& $record) {	
	$infoStructure =& $record->getInfoStructure();
	$structureId =& $infoStructure->getId();
	
	// Print out the fields parts for this structure
	$setManager =& Services::getService("Sets");
	$partSet =& $setManager->getSet($structureId);
	$orderedFieldsToPrint = array();
	$fieldsToPrint = array();
	
	// get the fields and break them up into ordered and unordered arrays.
	$fields =& $record->getInfoFields();
	while ($fields->hasNext()) {
		$field =& $fields->next();
		$part =& $field->getInfoPart();
		$partId =& $part->getId();
		
		if ($partSet->isInSet($partId)) {
			if (!is_array($orderedFieldsToPrint[$partId->getIdString()]))
				$orderedFieldsToPrint[$partId->getIdString()] = array();
			$orderedFieldsToPrint[$partId->getIdString()][] =& $field;
		} else {
			if (!is_array($fieldsToPrint[$partId->getIdString()]))
				$fieldsToPrint[$partId->getIdString()] = array();
			$fieldsToPrint[$partId->getIdString()][] =& $field;
		}
	}
	
	// Print out the ordered parts/fields
	while ($partSet->hasNext()) {
		$partId =& $partSet->next();
		$fieldsArray =& $orderedFieldsToPrint[$partId->getIdString()];
		foreach (array_keys($fieldsArray) as $key) {
			printField($fieldsArray[$key]);
		}
	}
	
	// Print out the parts/fields
	foreach (array_keys($fieldsToPrint) as $partIdString) {
		$fieldsArray =& $fieldsToPrint[$partIdString];
		foreach (array_keys($fieldsArray) as $key) {
			printField($fieldsArray[$key]);
		}
	}
}

function printField(& $field) {
	$part =& $field->getInfoPart();
	print "\n<strong>".$part->getDisplayName().":</strong> \n";
	$value =& $field->getValue();
	print $value->toString();
	print "\n<br />";
}