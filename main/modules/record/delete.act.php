<?

// Get the DR and Asset
$drManager =& Services::getService("DR");
$sharedManager =& Services::getService("Shared");
$assetId =& $sharedManager->getId($harmoni->pathInfoParts[3]);
$recordId =& $sharedManager->getId($harmoni->pathInfoParts[4]);
$asset =& $drManager->getAsset($assetId);
$dr =& $asset->getDigitalRepository();
$drId =& $dr->getId();

$asset->deleteInfoRecord($recordId);

$returnURL = MYURL."/asset/editview/".$harmoni->pathInfoParts[2]."/".$harmoni->pathInfoParts[3]."/";
header("Location: ".$returnURL);