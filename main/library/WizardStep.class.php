<?

require_once(dirname(__FILE__)."/RegexWizardProperty.class.php");

/**
 * The Wizard class provides a system for registering Wizard properties and 
 * associating those properties with the appropriate form elements.
 *
 * @package concerto.wizard
 * @author Adam Franco
 * @copyright 2004 Middlebury College
 * @access public
 * @version $Id$
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
		$this->_properties = array();
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
	 * @parm string $propertyName The property name.
	 * @param string $type The desired type of property.
	 * @return object WizardProperty
	 */
	function & createProperty ( $propertyName, $type ) {
		ArgumentValidator::validate($propertyName, new StringValidatorRule, true);
		ArgumentValidator::validate($propertyName, new StringValidatorRule, true);
		$validTypes = array("Regex", "Integer", "Float", "Boolean", "Option");
		if (!in_array($type, $validTypes))
			throwError(new Error("Unkown Property type, ".$type.".", "Wizard", 1));
		
		if ($this->_properties[$propertyName])
			throwError(new Error("Property, ".$propertyName.", already exists in Wizard.", "Wizard", 1));

		$className = $type."WizardProperty";
		$this->_properties[$propertyName] =& new $className( $propertyName );
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
	 * Go through all properties and update their values from the submitted
	 * form. Return false if any of the submitted values are invalid.
	 *
	 * @access public
	 * @return boolean True on success. False on invalid Property values.
	 */
	function updateProperties () {
		$valid = TRUE;
		foreach (array_keys($this->_properties) as $name) {
			if (!$this->_properties[$name]->update())
				$valid = FALSE;
		}
		
		return $valid;
	}
	
	/**
	 * Returns a layout of content for this WizardStep
	 * @param object Harmoni The harmoni object which contains the current context.
	 * @return object Layout
	 */
	function & getLayout (& $harmoni) {
		$stepLayout =& new SingleContentLayout (TEXT_BLOCK_WIDGET, 2);
		
		$text = $this->_parseText();
		
		$stepLayout->addComponent(new Content($text));
		
		return $stepLayout;
	}
	
	/**
	 * Sets the text of this wizard step. The text is a string and can contain
	 * elements that will be parsed with values from the current state of the
	 * step properties.
	 *
	 * Parsed elements can have two forms:
	 * 		[[PropertyName]]
	 * or
	 * 		[[PropertyName Operator ComparisonValue|StringIfTrue|StringIfFalse]]
	 * 
	 * The property-name element should not be quoted unless quotes are
	 * nessisary for use in the comparison string. In that case single quotes, ',
	 * should be used. Examples:
	 *
	 * 		<input type='text' name='title' value='[[title]]'>
	 *
	 * 		<input type='text' name='age' value='[[age]]'> [[age < 18|*You are not old enough!*|You are old enough.]]
	 * 		
	 * 		<input type='radio' name='width' value='5' [[width == 5| checked='checked'|]]> Narrow Width
	 * 		<input type='radio' name='width' value='10' [[width == 10| checked='checked'|]]> Wide Width
	 * 		
	 * 		<input type='radio' name='size' value='S' [['size' == 'S'| checked='checked'|]]> Small
	 * 		<input type='radio' name='size' value='L' [['size' == 'L'| checked='checked'|]]> Large
	 * 
	 * @param string $text The HTML text for this step.
	 * @access public
	 * @return void
	 */
	function setText ( $text ) {
		ArgumentValidator::validate($text, new StringValidatorRule, true);
		
		$this->_text = $text;
	}
	
	/**
	 * Parses the step text to fit the current values of the step properties.
	 * Parsed elements can have two forms:
	 * 		[[PropertyName]]
	 * or
	 * 		[[PropertyName Operator ComparisonValue|StringIfTrue|StringIfFalse]]
	 * 
	 * The property-name element should not be quoted unless quotes are
	 * nessisary for use in the comparison string. In that case single quotes, ',
	 * should be used. Examples:
	 *
	 * 		<input type='text' name='title' value='[[title]]'>
	 *
	 * 		<input type='text' name='age' value='[[age]]'> [[age < 18|*You are not old enough!*|You are old enough.]]
	 * 		
	 * 		<input type='radio' name='width' value='5' [[width == 5| checked='checked'|]]> Narrow Width
	 * 		<input type='radio' name='width' value='10' [[width == 10| checked='checked'|]]> Wide Width
	 * 		
	 * 		<input type='radio' name='size' value='S' [['size' == 'S'| checked='checked'|]]> Small
	 * 		<input type='radio' name='size' value='L' [['size' == 'L'| checked='checked'|]]> Large
	 * 
	 * @access private
	 * @return string
	 */
	function _parseText () {
		// Make a copy of our form text for output
		$outputText = $this->_text;
		
		// Get a list of all [[xxxxx]] elements
		preg_match_all("/\[{2}[^\[]*\]{2}/", $outputText, $matches);
		if (count($matches[0])) {
			foreach ($matches[0] as $match) {
				
				// if this element is of the [[propertyname]] form,
				// replace the element with the value of the property.
				if (preg_match("/\[{2}([^|]*)\]{2}/", $match, $parts)) {
					$outputText = str_replace($match, $this->_properties[$parts[1]]->getValue(), $outputText);
				
				// if this element is of the 
				// [['PropertyName' == 'ComparisonVal'|StringIfEquivalent|StringIfNotEquivalent]]
				// form, then compare the value of the property to the ComparisonVal
				// and replace the whole element with the appropriate.
				//
				// RegEx Details - Look for
				// '[[' followed by something followed by
				// 	== OR < OR > OR <= OR >=
				// followed by "|", a string, "|", another string, then ']]'
				} else if (preg_match("/\[{2}(.*)(==|<|>|<=|>=)([^<>=]*)\|(.*)\|(.*)\]{2}/", $match, $parts)) {
					
					$name = trim($parts[1]);
					// If the property name is quoted, get the value and quote it.
					// RegEx Details: look for begining and ending quotes.
					if (preg_match("/^'(.*)'$/", $name, $nameParts)) 
						$value = "'".$this->_properties[$nameParts[1]]->getValue()."'";
					else
						$value = $this->_properties[$name]->getValue();
					
					$operator = trim($parts[2]);
					$compVal = trim($parts[3]);
					
					// Build our comparison operation string
					$comparison = "if (".$value." ".$operator." ".$compVal.") return TRUE; else return FALSE;";
					
					// Evaluate our comparison and replace with the appropriate
					// string.
					if (eval($comparison))
						$outputText = str_replace($match, $parts[4], $text);
					else
						$outputText = str_replace($match, $parts[5], $text);
				} else {
					throwError(new Error("Unknown String form, ".$match.".", "Wizard", 1));
				}
			}
			return $outputText;
		
		// If we don't have any [[xxxxx]] elements, just return the text.
		} else {
			return $outputText;
		}
	}
}

?>