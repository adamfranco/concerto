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
 * This class is the most simple abstraction of an action. It provides a structure
 * for common methods
 * 
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 * @since 4/28/05
 */
class Action {
		
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
	 * Return the harmoni object
	 * 
	 * @return object Harmoni
	 * @access public
	 * @since 4/26/05
	 */
	function &getHarmoni () {
		return $this->_harmoni;
	}
	
	/**
	 * Set the harmoni object
	 * 
	 * @param object Harmoni $harmoni
	 * @return void
	 * @access public
	 * @since 4/28/05
	 */
	function setHarmoni ( &$harmoni ) {
		$this->_harmoni =& $harmoni;
	}
	
	/**
	 * Answer the requested module, maybe other than this action's module if this
	 * action was chained onto another's request.
	 * 
	 * @return string
	 * @access public
	 * @since 6/3/05
	 */
	function requestedModule () {
		$harmoni =& Harmoni::instance();
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		return $module;
	}
	
	/**
	 * Answer the requested action, maybe other than this action's action if this
	 * action was chained onto another's request.
	 * 
	 * @return string
	 * @access public
	 * @since 6/3/05
	 */
	function requestedAction () {
		$harmoni =& Harmoni::instance();
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		return $action;
	}
}

?>