<?php
/**
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * The MainWindowAction is an abstract class that provides a standard way of setting
 * up and executing an action in the main window of the application.
 * 
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class MainWindowAction {

	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return '';
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
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
		$this->_harmoni =& $harmoni;
		
		$pageTitle = 'Concerto';
		
		// Generarate our window template
		$this->generateWindow();
		
		// Check authorization
		if (!$this->isAuthorizedToExecute()) {
			$centerPane =& $this->getCenterPane();
			$centerPane->add(new Block($this->getUnauthorizedMessage(), 3),
				"100%", null, CENTER, CENTER);
			return $this->_mainScreen;
		}
		
		// Add a heading if specified
		if ($headingText = $this->getHeadingText()) {
			$this->_actionRows->add(
				new Heading($headingText, 2),
				"100%",
				null, 
				LEFT, 
				CENTER);
			
			$pageTitle .= ": ".$headingText;
		}
		
		// Set the page title
		$outputHandler =& $this->_harmoni->getOutputHandler();
		$outputHandler->setHead(
			// Remove any existing title tags from the head text
			preg_replace("/<title>[^<]*<\/title>/", "", $outputHandler->getHead())
			//Add our new title
			."\n\t\t<title>"
			.strip_tags(preg_replace("/<(\/)?(em|i|b|strong)>/", "*", $pageTitle))
			."</title>");
		
		// Pass content generation off to our child classes
		$this->buildContent();
		
		return $this->_mainScreen;
	}
	
	/**
	 * Generate the main window containing menus and such
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function generateWindow () {
		// Get the Layout compontents. See core/modules/moduleStructure.txt
		// for more info. 
		$this->_harmoni->ActionHandler->execute("window", "screen");
		$this->_mainScreen =& $this->_harmoni->getAttachedData('mainScreen');
		$this->_statusBar =& $this->_harmoni->getAttachedData('statusBar');
		$this->_centerPane =& $this->_harmoni->getAttachedData('centerPane');
		 
		
		// Our Rows for action content
		$yLayout =& new YLayout();
		$this->_actionRows =& new Container($yLayout,OTHER,1);
		$this->_centerPane->add($this->_actionRows, null, null, CENTER, TOP);
	}
	
	/**
	 * Return the center container
	 * 
	 * @return object Container
	 * @access public
	 * @since 4/26/05
	 */
	function &getCenterPane () {
		return $this->_centerPane;
	}
	
	/**
	 * Return the actionRows container
	 * 
	 * @return object Container
	 * @access public
	 * @since 4/26/05
	 */
	function &getActionRows () {
		return $this->_actionRows;
	}
	
	/**
	 * Return the getStatusBar container
	 * 
	 * @return object Container
	 * @access public
	 * @since 4/26/05
	 */
	function &getStatusBar () {
		return $this->_statusBar;
	}
	
	/**
	 * Return the mainScreen container
	 * 
	 * @return object Container
	 * @access public
	 * @since 4/26/05
	 */
	function &getMainScreen () {
		return $this->_mainScreen;
	}
	
	/**
	 * Return the harmoni object
	 * 
	 * @return object Harmoni
	 * @access public
	 * @since 4/26/05
	 */
	function &getHarmoni () {
		return $this->_harmoni;
	}
}

?>