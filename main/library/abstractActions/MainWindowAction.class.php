<?php
/**
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/WizardAction.class.php");
require_once(HARMONI."GUIManager/Container.class.php");
require_once(HARMONI."GUIManager/Layouts/XLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."GUIManager/Components/Block.class.php");
require_once(HARMONI."GUIManager/Components/Menu.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemHeading.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemLink.class.php");
require_once(HARMONI."GUIManager/Components/Heading.class.php");
require_once(HARMONI."GUIManager/Components/Footer.class.php");

/**
 * The MainWindowAction is an abstract class that provides a standard way of setting
 * up and executing an action in the main window of the application. It provides
 * a structure for accessing various parts of this main window, as well as delegating
 * the implementation of some methods to decendent classes.
 * 
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class MainWindowAction 
	extends WizardAction {

	/**
	 * Build the content for this action.
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
		
	/**
	 * Execute this action. This is a template method that handles setting up
	 * components of the screen as well as authorization, delegating the various
	 * parts to descendent classes.
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function execute ( &$harmoni ) {
		$this->setHarmoni($harmoni);
		
		$pageTitle = 'Concerto';
		
		// Our Rows for action content
		$actionRows =& $this->getActionRows();
		
		// Check authorization
		if (!$this->isAuthorizedToExecute()) {
			$actionRows->add(new Block($this->getUnauthorizedMessage(), 3),
				"100%", null, CENTER, CENTER);
			return $actionRows;
		}
		
		// Add a heading if specified
		if ($headingText = $this->getHeadingText()) {
			$actionRows->add(
				new Heading($headingText, 2),
				"100%",
				null, 
				LEFT, 
				CENTER);
			
			$pageTitle .= ": ".$headingText;
		}
		
		// Set the page title
		$outputHandler =& $harmoni->getOutputHandler();
		$outputHandler->setHead(
			// Remove any existing title tags from the head text
			preg_replace("/<title>[^<]*<\/title>/", "", $outputHandler->getHead())
			//Add our new title
			."\n\t\t<title>"
			.strip_tags(preg_replace("/<(\/)?(em|i|b|strong)>/", "*", $pageTitle))
			."</title>");
		
		// Pass content generation off to our child classes
		$this->buildContent();
		
		return $actionRows;
	}
	
	/**
	 * Return the actionRows container
	 * 
	 * @return object Container
	 * @access public
	 * @since 4/26/05
	 */
	function &getActionRows () {
		if (!is_object($this->_actionRows))
			$this->_actionRows =& new Container(new YLayout(), OTHER, 1);
		
		return $this->_actionRows;
	}
}

?>