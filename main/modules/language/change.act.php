<?

// Set the new language
$langLoc =& Services::getService('Lang');
$langLoc->setLanguage($_REQUEST['language']);

debug::output("Setting the language to ".$_REQUEST['language']);
debug::output("SESSION: ".printpre($_SESSION, TRUE));

// Send us back to where we were
$currentPathInfo = array();
for ($i = 2; $i < count($harmoni->pathInfoParts); $i++) {
	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
}

header("Location: ".MYURL."/".implode("/",$currentPathInfo));