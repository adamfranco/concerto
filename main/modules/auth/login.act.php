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
} else {

	// if we weren't sucessfull, head to the failed login action if for 
	// some reason we are not sent there automatically.
	$harmoni->forward($harmoni->LoginHandler->getFailedLoginAction());
}