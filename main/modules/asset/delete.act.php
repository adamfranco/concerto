<?

// Get the DR
$drManager =& Services::getService("DR");
$sharedManager =& Services::getService("Shared");
$assetId =& $sharedManager->getId($harmoni->pathInfoParts[3]);
$asset =& $drManager->getAsset($assetId);
$dr =& $asset->getDigitalRepository();
$drId =& $dr->getId();

// Delete the asset
$dr->deleteAsset($assetId);

// Head back to where we were
$returnURL = MYURL."/";
for($i = 4; $i < count($harmoni->pathInfoParts); $i++) {
	$returnURL .= $harmoni->pathInfoParts[$i]."/";
}

header("Location: ".$returnURL);