<?

require_once(dirname(__FILE__)."/WizardProperty.class.php");

/**
 * This class that provides a WizardProperty that can validate its input against
 * an arbitrary regular expression string. 
 * 
 * @package concerto.wizard
 * @author Adam Franco
 * @copyright 2004 Middlebury College
 * @access public
 * @version $Id$
 */
 
class RegexWizardProperty extends WizardProperty {
	
	/**
	 * @attribute string _expression The regex expression used to validate this
	 * property
	 */
	var $_expression;
	
	/**
	 * Constructor: throw error as this is an abstract class.
	 */
	function RegexWizardProperty ( $name ) {
		ArgumentValidator::validate($name, new StringValidatorRule, true);

		$this->_name = $name;
	}
	
	function setExpression ( $reqexExpression ) {
		ArgumentValidator::validate($reqexExpression, new StringValidatorRule, true);
		
		$this->_expression = $reqexExpression;
	}
	
	/**
	 * Validate the given input against our internal checks. Return TRUE if the
	 * supplied input is valid.
	 * @param mixed $value The value to check.
	 * @access protected
	 * @return boolean
	 */
	function _validate ( $value ) {
		return ereg($this->_expression, $value);
	}
}