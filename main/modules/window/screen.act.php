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
	$text .= "<img src='".MYPATH."/main/modules/window/logo.gif' border='0' />";
	$text .= "</a>";
	$logo->addComponent(new Content($text));
	
	// Language Bar
	$languageBar =& new SingleContentLayout();
	$headRow->addComponent($languageBar, TOP, LEFT);
		$languageText = "\n<form action='".MYURL."/language/change/".
						implode("/", $harmoni->pathInfoParts)."' method='post'>";
		$languageText .= "\n\t<select name='language'>";
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
		$languageText .= "\n\t<input type='submit'>";
		$languageText .= "\n</form>";
	$languageBar->addComponent(new Content($languageText));
	
	// Header space
	$header =& new SingleContentLayout();
	$headRow->addComponent($header, TOP, CENTER);
	$header->addComponent(new Content(" &nbsp; &nbsp; &nbsp; "));
	
	// Status Bar
	$statusBar =& new SingleContentLayout();
	$headRow->addComponent($statusBar, TOP, RIGHT);
		$statusText = "";
		if ($harmoni->LoginState->isValid()) {
			$statusText .= "<a href='".MYURL."/auth/logout/".
						implode("/", $harmoni->pathInfoParts)."'>";
			$statusText .= _("You are logged in.");
		} else {
			$statusText .= "<a href='".MYURL."/auth/login/".
						implode("/", $harmoni->pathInfoParts)."'>";
			$statusText .= _("You are not logged in.");
		}
		$statusText .= "</a>";
	$statusBar->addComponent(new Content($statusText));

// :: Center Pane ::
	$centerPane =& new ColumnLayout();
	$mainScreen->addComponent($centerPane, TOP, LEFT);
	
	// Main Menu
	$mainMenu =& ConcertoMenuGenerator::generateMainMenu($harmoni->getCurrentAction());
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