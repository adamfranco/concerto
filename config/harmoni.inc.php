<?
// :: Version: $Id$


// :: set up the $harmoni object :: 
	$harmoni->config->set("useAuthentication",true);
	$harmoni->config->set("defaultModule","home");
	$harmoni->config->set("defaultAction","welcome");
	$harmoni->config->set("charset","utf-8");
	$harmoni->config->set("outputHTML",true);
	$harmoni->config->set("sessionName","AUTHN");
	$harmoni->config->set("sessionUseCookies",true);
	$harmoni->config->set("sessionCookiePath","/");
	$harmoni->config->set("sessionCookieDomain","middlebury.edu");

// :: setup the ActionHandler ::
	function callback_action(&$harmoni) {
		return $harmoni->pathInfoParts[0] . "." . $harmoni->pathInfoParts[1];
	}
	$harmoni->setActionCallbackFunction("callback_action");
	$harmoni->ActionHandler->setActionsType(ACTIONS_FLATFILES,".act.php");
	$harmoni->ActionHandler->setModulesLocation(realpath(MYDIR."/main/modules"),MODULES_FOLDERS);

// :: Set up the database connection ::
	$dbHandler=&Services::requireService("DBHandler");
	$dbID = $dbHandler->addDatabase( new MySQLDatabase("localhost","AuthN","test","test") );
	$dbHandler->pConnect($dbID);
	unset($dbHandler); // done with that for now

// :: Set up the SharedManager as this is required for the ID service ::
	Services::startService("Shared", $dbID, "AuthN");


// :: Set up the Authentication and Login Handlers ::
	$harmoni->LoginHandler->setFailedLoginAction("auth.fail_redirect");
	$harmoni->LoginHandler->addNoAuthActions("auth.logout",
											"auth.fail",
											"auth.login",
											"language.change",
											"window.screen",
											"home.welcome"
											);
	
	//printpre($GLOBALS);
	
	Services::startService("AuthN", $dbID, "AuthN");
	
	#########################
	# HANDLE AUTHENTICATION #
	# A) authenticated      #
	# B) not authenticated  #
	# C) attempting log in  #
	#########################
	
	Services::startService("Authentication");
	Services::startService("DBHandler");
	
	// :: get all the services we need ::
	$authHandler =& Services::getService("Authentication");
	
	// :: set up the DBAuthenticationMethod options ::
	$options =& new DBMethodOptions;
	$options->set("databaseIndex",$dbID);
	$options->set("tableName", "AuthN.user");
	$options->set("usernameField", "username");
	$options->set("passwordField", "password");
	
	// :: create the DBAuthenticationMethod with the above options ::
	$dbAuthMethod =& new DBAuthenticationMethod($options);
	
	// :: add it to the handler ::
	$authHandler->addMethod("dbAuth",0,$dbAuthMethod);


// :: Layout and Theme Setup ::
	Services::registerService("Themes", "ThemeHandler");
	Services::startService("Themes");
	$harmoni->setTheme(new SimpleLinesTheme);


// :: Set up language directories ::
	Services::startService('Lang', MYDIR.'/main/languages', 'concerto');
	$langLoc =& Services::getService ('Lang');
//	$langLoc->setLanguage("es_ES");
//	$langLoc->setLanguage("en_US");
	$languages =& $langLoc->getLanguages();
//  printpre($languages);