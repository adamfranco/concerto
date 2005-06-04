<?php
/**
 * @package concerto.modules.window
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * build the frame of the window
 * 
 * @package concerto.modules.window
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class displayAction 
	extends Action
{
		
	/**
	 * Execute the Action
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function execute ( &$harmoni ) {
		/**
		 * @package concerto.display
		 * 
		 * @copyright Copyright &copy; 2005, Middlebury College
		 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
		 *
		 * @version $Id$
		 */
		 
		require_once(HARMONI."GUIManager/Components/Block.class.php");
		require_once(HARMONI."GUIManager/Components/Menu.class.php");
		require_once(HARMONI."GUIManager/Components/MenuItemHeading.class.php");
		require_once(HARMONI."GUIManager/Components/MenuItemLink.class.php");
		require_once(HARMONI."GUIManager/Components/Heading.class.php");
		require_once(HARMONI."GUIManager/Components/Footer.class.php");
		require_once(HARMONI."GUIManager/Container.class.php");
		
		require_once(HARMONI."GUIManager/Layouts/XLayout.class.php");
		require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
		
		require_once(HARMONI."GUIManager/StyleProperties/FloatSP.class.php");
		
		// Set a default title
		$outputHandler =& $harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead()."\n\t\t<title>Conc
		erto</title>");		
		
		$xLayout =& new XLayout();
		$yLayout =& new YLayout();
		
		
		$mainScreen =& new Container($yLayout, BLOCK, 1);
		
	// :: Top Row ::
		// The top row for the logo and status bar.
		$headRow =& new Container($xLayout, OTHER, 1);
		
		// The logo
		$logo =& new Block("\n<a href='".MYPATH."/'> <img src='".MYPATH."/main/modules/window/logo.gif' 
							style='border: 0px;' alt='"._("Concerto Logo'"). "/> </a>",2);
		$headRow->add($logo, null, null, LEFT, TOP);
		
		
		
		// Language Bar
		$harmoni->history->markReturnURL("polyphony/language/change");
		$languageText = "\n<form action='".$harmoni->request->quickURL("language", "change")."' method='post'>";
	$harmoni->request->startNamespace("polyphony");
	$languageText .= "\n\t<div>\n\t<select name='".$harmoni->request->getName("language")."'>";
	$harmoni->request->endNamespace();
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
		
		$languageBar =& new Block($languageText,2);
		$headRow->add($languageBar, null, null, LEFT,TOP);
		
		
		$statusRow = new Container($yLayout, OTHER, 1);
		$headRow->add($statusRow,null,null,RIGHT,TOP);
		
		
		// Status Bar
				ob_start();
				$authNManager =& Services::getService("AuthN");
				$agentManager =& Services::getService("Agent");
				$authTypes =& $authNManager->getAuthenticationTypes();
				print "\n<table border='1'>";
				print "\n\t<tr><th colspan='3'><center>";
				print _("Current Authentications: ");
				print "</center>\n\t</th></tr>";
				
				while($authTypes->hasNextType()) {
					$authType =& $authTypes->nextType();
					$typeString = HarmoniType::typeToString($authType);
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
					
					$harmoni->request->startNamespace("polyphony");
					
					if ($this->requestedModule() != 'auth')
						$harmoni->history->markReturnURL("polyphony/login");
						
					if ($authNManager->isUserAuthenticated($authType)) {
						$url = $harmoni->request->quickURL(
							"auth",
							"logout_type",
							array("type"=>urlencode($typeString))
						);
						print "<a href='".$url."'>Log Out</a>";
					} else {
						$url = $harmoni->request->quickURL(
							"auth",
							"login_type",
							array("type"=>urlencode($typeString))
						);
						print "<a href='".$url."'>Log In</a>";
					}
					$harmoni->request->endNamespace();
					
					print "\n\t\t</td>";
					print "\n\t</tr>";
				}
			print "\n</table>";
		
		$statusBar =& new Block(ob_get_contents(),2);
		$statusRow->add($statusBar,null,null,RIGHT,TOP);
		ob_end_clean();
		//Add the headerRow to the mainScreen
		$mainScreen->add($headRow, "100%", null, LEFT, TOP);
		
	// :: Center Pane ::
		$centerPane =& new Container($xLayout, OTHER, 1);
		$mainScreen->add($centerPane,"100%",null, LEFT, TOP);		
		
		// Main menu
		$mainMenu =& ConcertoMenuGenerator::generateMainMenu($harmoni);
		$centerPane->add($mainMenu,"140px",null, LEFT, TOP);
		
		// use the result from previous actions
		if ($harmoni->printedResult) {
			$contentDestination =& new Container($yLayout, OTHER, 1);
			$centerPane->add($contentDestination, null, null, LEFT, TOP);
			$contentDestination->add(new Block($harmoni->printedResult, 1), null, null, TOP, CENTER);
			$harmoni->printedResult = '';
		} else {
			$contentDestination =& $centerPane;
		}
		
		// use the result from previous actions
		$contentDestination->add($harmoni->result, null, null, TOP, CENTER); 
		
		
		
	// :: Footer ::
		$footerRow =& new Container($yLayout, OTHER, 1);
		$footerText = "Concerto v.0.1 &copy;2004 Middlebury College: <a href=''>";
		$footerText .= _("credits");
		$footerText .= "</a>";
		$footer =& new Block($footerText,2);
		$footerRow->add($footer,null,null,RIGHT,BOTTOM);
		
		$mainScreen->add($footerRow, "100%", null, RIGHT, BOTTOM);

		return $mainScreen;
	}	
}

?>