<?

$isAuthenticated = FALSE;
$authN =& Services::getService("AuthN");

// authenticate.
$authTypes =& $authN->getAuthenticationTypes();
while ($authTypes->hasNext()) {
	$authType =& $authTypes->next();
	
	// Try authenticating with this type
	$authN->authenticateUser($authType);
	
	// If they are authenticated, quit
	if ($authN->isUserAuthenticated($authType)) {
		$isAuthenticated = TRUE;
		break;
	}
}

if ($isAuthenticated) {
	// Send us back to where we were
	$currentPathInfo = array();
	for ($i = 2; $i < count($harmoni->pathInfoParts); $i++) {
		$currentPathInfo[] = $harmoni->pathInfoParts[$i];
	}
	
	header("Location: ".MYURL."/".implode("/",$currentPathInfo));
}

// if we weren't sucessfull, we will be sent to the failed login
// action automatically.