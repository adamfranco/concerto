<?

require_once(MYDIR."/main/library/ConcertoMenuGenerator.class.php");

// Set a default title
$theme =& $harmoni->getTheme();
$theme->setPageTitle("Concerto");


$mainScreen =& new RowLayout(TEXT_BLOCK_WIDGET, 1);

// :: Top Row ::
	// The top row for the Concerto logo and status bar.
	$headRow =& new ColumnLayout();
	$mainScreen->addComponent($headRow, TOP, CENTER);
	
	// The Concerto logo
	$logo =& new SingleContentLayout();
	$headRow->addComponent($logo, TOP, LEFT);
	$text = "\n<a href='".MYPATH."/'>";
	$text .= "<img src='".MYPATH."/main/modules/window/logo.gif' style='border: 0px;' alt='"._("Concerto Logo'")." />";
	$text .= "</a>";
	$logo->addComponent(new Content($text));
	
	// Language Bar
	$languageBar =& new SingleContentLayout();
	$headRow->addComponent($languageBar, TOP, LEFT);
		$languageText = "\n<form action='".MYURL."/language/change/".
						implode("/", $harmoni->pathInfoParts)."' method='post'>";
		$languageText .= "\n\t<div>\n\t<select name='language'>";
		$langLoc =& Services::getService('Lang');
		$currentCode = $langLoc->getLanguage();
		$languages = $langLoc->getLanguages();
		ksort($languages);
		foreach($languages as $code => $language) {
			$languageText .= "\n\t\t<option value='".$code."'".
							(($code == $currentCode)?" selected='selected'":"").">";
			$languageText .= $language."</option>";
		}
		$languageText .= "\n\t</select>";
		$languageText .= "\n\t<input type='submit' />";
		$languageText .= "\n\t</div>\n</form>";
	$languageBar->addComponent(new Content($languageText));
	
	// Header space
	$header =& new SingleContentLayout();
	$headRow->addComponent($header, TOP, CENTER);
	$header->addComponent(new Content(" &nbsp; &nbsp; &nbsp; "));
	
	// Status Bar
	$statusBar =& new SingleContentLayout();
	$headRow->addComponent($statusBar, TOP, RIGHT);
		ob_start();
		$authNManager =& Services::getService("AuthN");
		$agentManager =& Services::getService("Agent");
		$authTypes =& $authNManager->getAuthenticationTypes();
		print "\n<table border='1'>";
		print "\n\t<tr><th colspan='3'>";
		print _("Current Authentications: ");
		print "\n\t</th></tr>";
		
		while($authTypes->hasNextType()) {
			$authType =& $authTypes->nextType();
			$typeString = $authType->getDomain()."::".$authType->getAuthority()
				."::".$authType->getKeyword();
			print "\n\t<tr>";
			print "\n\t\t<td>";
			print "<a href='#' title='$typeString' onClick='alert(\"$typeString\")'>";
			print $authType->getKeyword();
			print "</a>";
			print "\n\t\t</td>";
			print "\n\t\t<td>";
			$userId =& $authNManager->getUserId($authType);
			$userAgent =& $agentManager->getAgent($userId);
			print $userId->getIdString();
			print ": ";
			print $userAgent->getDisplayName();
			print "\n\t\t</td>";
			print "\n\t\t<td>";
			if ($authNManager->isUserAuthenticated($authType)) {
				print "<a href='".MYURL."/auth/logout_type/".urlencode($typeString)."/".
					implode("/", $harmoni->pathInfoParts)."'>Log Out</a>";
			} else {
				print "<a href='".MYURL."/auth/login_type/".urlencode($typeString)."/".
					implode("/", $harmoni->pathInfoParts)."'>Log In</a>";
			}
			print "\n\t\t</td>";
			print "\n\t</tr>";
		}
		print "\n</table>";
		
// 		if ($harmoni->LoginState->isValid()) {
// 			print $harmoni->LoginState->getAgentName();
// 			print " - <a href='".MYURL."/auth/logout/".
// 						implode("/", $harmoni->pathInfoParts)."'>";
// 			print _("Log Out");
// 		} else {
// 			print _("anonymous");
// 			print " - <a href='".MYURL."/auth/login/".
// 						implode("/", $harmoni->pathInfoParts)."'>";
// 			print _("Log In");
// 		}
// 		print "</a>";
	$statusBar->addComponent(new Content(ob_get_contents()));
	ob_end_clean();

// :: Center Pane ::
	$centerPane =& new ColumnLayout();
	$mainScreen->addComponent($centerPane, TOP, LEFT);
	
	// Main Menu
	$mainMenu =& ConcertoMenuGenerator::generateMainMenu($harmoni);
	$centerPane->addComponent($mainMenu, TOP, LEFT);

// :: Footer ::
	$footer =& new SingleContentLayout();
	$mainScreen->addComponent($footer, BOTTOM, RIGHT);
	$footerText = "Concerto v.0.1 &copy;2004 Middlebury College: <a href=''>";
	$footerText .= _("credits");
	$footerText .= "</a>";
	$footer->addComponent(new Content($footerText));

$harmoni->attachData('mainScreen', $mainScreen);
$harmoni->attachData('statusBar', $statusBar);
$harmoni->attachData('centerPane', $centerPane);

?>