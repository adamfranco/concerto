<?

// Get the DR and Asset
$drManager =& Services::getService("DR");
$sharedManager =& Services::getService("Shared");
$assetId =& $sharedManager->getId($harmoni->pathInfoParts[3]);
$asset =& $drManager->getAsset($assetId);
$dr =& $asset->getDigitalRepository();
$drId =& $dr->getId();

$infoStructureId =& $sharedManager->getId($_REQUEST['structure']);

$asset->createInfoRecord($infoStructureId);

header("Location: ".$_REQUEST['return_url']);