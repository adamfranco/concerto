<?

/**
 * The Wizard class provides a system for posting, retrieving, and
 * validating user input over a series of steps, as well as maintianing
 * the submitted values over a series of steps, until the wizard is saved.
 * The wizard is designed to be called from within a single action. The values
 * of its state allow its steps to work as "sub-actions". 
 */

class WizardStep {
	
	/**
	 * The displayName of this WizardStep
	 * @attribute private string _displayName
	 */
	var $_displayName;
	
	/**
	 * The properties handled by the Wizard.
	 * @attribute private array _properties
	 */
	var $_properties;
	
	/**
	 * Constructor
	 * @param string $displayName The displayName of this step.
	 */
	function WizardStep ( $displayName ) {
		ArgumentValidator::validate($displayName, new StringValidatorRule, true);
		$this->_displayName = $displayName;
	}
	
	/**
	 * Returns the displayName of this WizardStep
	 * @return string
	 */
	function getDisplayName () {
		return $this->_displayName;
	}
	
		/**
	 * creates a new Property for this step
	 * @parm object WizardProperty $property The property to add.
	 * @return object WizardProperty
	 */
	function & createProperty ( & $propertyName ) {
		ArgumentValidator::validate($property, new ExtendsValidatorRule("WizardProperty"), true);
		if ($this->_properties[$propertyName])
			throwError(new Error("Property, ".$propertyName.", already exists in Wizard.", "Wizard", 1));

		$this->_properties[$propertyName] =& new WizardProperty( $name );
		return $this->_properties[$propertyName];
	}
	
	/**
	 * Gets a Property
	 * @parm string $name The name of the requested property.
	 * @return object WizardProperty
	 */
	function & getProperty ( $propertyName ) {
		ArgumentValidator::validate($propertyName, new StringValidatorRule, true);
		if (!$this->_properties[$propertyName])
			throwError(new Error("Property, ".$propertyName.", does not exist in Wizard.", "Wizard", 1));

		return $this->_properties[$propertyName];
	}
	
	/**
	 * Gets an array all Properties indexed by property name.
	 * @return array 
	 */
	function & getProperties () {
		return $this->_properties;
	}
	
	
	/**
	 * Returns a layout of content for this WizardStep
	 * @param object Harmoni The harmoni object which contains the current context.
	 * @return object Layout
	 */
	function & getLayout (& $harmoni) {
		$stepLayout =& new SingleContentLayout;
		
		$text = $this->_parseFormText();
		
		$stepLayout->addComponent(new Content($text));
		
		return $stepLayout;
	}
}

?>