<?

$authN =& Services::getService("AuthN");

// dethenticate. :-)
$authN->destroyAuthentication();

// Send us back to where we were
$currentPathInfo = array();
for ($i = 2; $i < count($harmoni->pathInfoParts); $i++) {
	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
}

header("Location: ".MYURL."/".implode("/",$currentPathInfo));