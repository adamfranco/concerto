<?

/**
 * This is an abstract class that provides much of the functionality of the 
 * various WizardProperties. 
 * 
 * @package concerto.wizard
 * @author Adam Franco
 * @copyright 2004 Middlebury College
 * @access public
 * @version $Id$
 */
 
class WizardProperty {
	
	/**
	 * @attribute string _name The name the property
	 */
	var $_name;
	
	/**
	 * @attribute mixed _value The current value of the property
	 */
	var $_value;
	
	/**
	 * @attribute mixed _defaultValue The default value of the property
	 */
	var $_defaultValue;
	
	/**
	 * Constructor: throw error as this is an abstract class.
	 */
	function WizardProperty ( $name ) {
		throwError(new Error("Instantiate a child class instead.", "Wizard", 1));
	}
	
	/**
	 * Returns the value of this Property
	 * @access public
	 * @return mixed The Value
	 */
	function getValue () {
		if (isset($this->_value))
			return $this->_value;
		else
			return $this->_defaultValue;
	}
	
	/**
	 * Set the default value. This will be returned by getValue if no
	 * value has been set yet.
	 * @param mixed $defaultValue The new default value for this Property.
	 * @access public
	 * @return void
	 */
	function setDefaultValue ( $defaultValue ) {
		$this->_defaultValue = $defaultValue;
	}
	
	function update () {
		// Set the value from the request array.
		if (isset($_REQUEST[$this->_name]))
			$this->_value = $_REQUEST[$this->_name];
		else
			throwError(new Error("Requested property, ".$this->_name.", does not exist in the _REQUEST array.", "Wizard", 1));

		return $this->_validate($this->_value);			
	}
	
	/**
	 * Validate the given input against our internal checks. Return TRUE if the
	 * supplied input is valid.
	 * @param mixed $value The value to check.
	 * @access protected
	 * @return boolean
	 */
	function _validate ( $value ) {
		(throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "Interface", TRUE)) || die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."));
	}
}