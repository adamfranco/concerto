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
$drId =& $sharedManager->getId($harmoni->pathInfoParts[2]);
$dr =& $drManager->getDigitalRepository($drId);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Browse Assets in the")." <em>".$dr->getDisplayName()."</em> "._("Collection")));
$actionRows->addComponent($introHeader);

ob_start();
print  "<p>";
print  _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
print  "</p>";

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$introText->addComponent(new Content(ob_get_contents()));
ob_end_clean();
$actionRows->addComponent($introText);

// Get the assets to display
$assets =& $dr->getAssets();

$resultPrinter =& new IteratorResultPrinter($assets, 2, 4, "printAssetShort");
$resultLayout =& $resultPrinter->getLayout($harmoni);
$actionRows->addComponent($resultLayout);


// return the main layout.
return $mainScreen;

function printAssetShort(& $asset) {
	ob_start();
	
	$assetId =& $asset->getId();
	print  "\n\t<strong>".$asset->getDisplayName()."</strong> - "._("ID#").": ".
			$assetId->getIdString();
	print  "\n\t<br><em>".$asset->getDescription()."</em>";
//		print  "<div style='font-size: smaller'><br>";
//		print  $assetType->getDomain() ."::". $assetType->getAuthority() ."::". $assetType->getKeyword() ."<br><em>".$assetType->getDescription()."</em></span>";
	
	// @todo User AuthZ to decide if we should print links.
	print  "\n\t<br>";
	$links = array();
	
	$links[] = "<a href='".MYURL."/asset/view/".$assetId->getIdString()."/'>";
	$links[count($links) - 1] .= _("view")."</a>";
	
	$children =& $asset->getAssets();
	if ($children->hasNext()) {
		$links[] = "<a href='".MYURL."/asset/browse/".$assetId->getIdString()."/'>";
		$links[count($links) - 1] .= _("browse")."</a>";
		
		$links[] = "<a href='".MYURL."/asset/typebrowse/".$assetId->getIdString()."/'>";
		$links[count($links) - 1] .= _("browse by type")."</a>";
	}
	
// 	$links[] = "<a href='".MYURL."/asset/edit/".$assetId->getIdString()."/'>";
// 	$links[count($links) - 1] .= _("edit")."</a>";
	
	print  implode("\n\t | ", $links);
	
	$layout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 3);
	$layout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	return $layout;
}