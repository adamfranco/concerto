<?

/**
 * The Wizard class provides a system for posting, retrieving, and
 * validating user input over a series of steps, as well as maintianing
 * the submitted values over a series of steps, until the wizard is saved.
 * The wizard is designed to be called from within a single action. The values
 * of its state allow its steps to work as "sub-actions". 
 */

class Wizard {
	
	/**
	 * The title of this Wizard
	 * @attribute private string _displayName
	 */
	 var $_displayName;
	
	/**
	 * The (1-based) number of the current step.
	 * @attribute private integer _currentStep
	 */
	var $_currentStep;
	
	/**
	 * The steps within the Wizard.
	 * @attribute private array _steps
	 */
	var $_steps;
	
	/**
	 * If true, steps can be accessed non-linearly.
	 * @attribute private boolean _allowStepLinks
	 */
	 var $_allowStepLinks;	
	
	/**
	 * Constructor
	 * @param string $displayName The title of this wizard.
	 * @param boolean $allowStepLinks If true, steps can be accessed non-linearly.
	 * @return void
	 */
	function Wizard ( $displayName, $allowStepLinks = TRUE ) {
		ArgumentValidator::validate($displayName, new StringValidatorRule, true);
		ArgumentValidator::validate($allowStepLinks, new BooleanValidatorRule, true);
		
		$this->_displayName = $displayName;
		$this->_allowStepLinks = $allowStepLinks;
		$this->_currentStep = 1;
		$this->_steps = array();
	}
	
	/**
	 * Adds a new Step in the Wizard
	 * @parm string $displayName The displayName of this step.
	 * @return object The new step.
	 */
	function & createStep ( $displayName ) {
		$stepNumber = count($this->_steps) + 1;
		$this->_steps[$stepNumber] =& new WizardStep ( $displayName );
		return $this->_steps[$stepNumber];
	}
	
	/**
	 * If the values of the current step are good, make the requested step
	 * the current one.
	 */
	function goToStep ( $stepNumber ) {
		ArgumentValidator::validate($stepNumber, new IntegerValidatorRule, true);
		if (!$this->_steps[$stepNumber])
			throwError(new Error("Step, ".$stepNumber.", does not exist in Wizard.", "Wizard", 1));

		if ($this->_steps[$this->_currentStep]->updateProperties())
			$this->_currentStep = $stepNumber;
	}
	
	/**
	 * If the values of the current step are good, move to the next step.
	 * @return void
	 */
	function next () {
		if ($this->_currentStep == count($this->_steps))
			throwError(new Error("No more steps in Wizard.", "Wizard", 1));

		if ($this->_steps[$this->_currentStep]->updateProperties())
			$this->_currentStep = $this->_currentStep + 1;
	}
	
	/**
	 * If the values of the current step are good, move to the previous step.
	 * @return void
	 */
	function previous () {
		if ($this->_currentStep == 1)
			throwError(new Error("No more steps in Wizard.", "Wizard", 1));

		if ($this->_steps[$this->_currentStep]->updateProperties())
			$this->_currentStep = $this->_currentStep - 1;
	}
	
	/**
	 * True if there is a next step
	 * @return boolean
	 */
	function hasNext () {
		if ($this->_currentStep == count($this->_steps))
			return false;
		else
			return true;
	}
	
	/**
	 * True if there is a previous step
	 * @return boolean
	 */
	function hasPrevious () {
		if ($this->_currentStep == 1)
			return false;
		else
			return true;
	}
	
	/**
	 * Returns a layout of content for the current Wizard-state
	 * @param object Harmoni The harmoni object which contains the current context.
	 * @return object Layout
	 */
	function & getLayout (& $harmoni) {
		// Make sure we have a valid Wizard
		if (!count($this->_steps))
			throwError(new Error("No steps in Wizard.", "Wizard", 1));
			
		$wizardLayout =& new RowLayout;
		
		// :: Form tags for around the layout :: 
		$wizardLayout->setPreSurroundingText("<form action='".MYURL."/".implode("/", $harmoni->pathInfoParts)."/' method='post' id='wizardform'>");
		$wizardLayout->setPostSurroundingText("</form>");
		
		// :: Heading ::
		$heading =& new SingleContentLayout(HEADING_WIDGET, 2);
		$heading->addContent(new Content($this->_displayName.": ".
					$this->_currentStep." - ".
					$this->_steps[$this->_currentStep]->getDisplayName()));
		$wizardLayout->addComponent($heading);
		
		$lower =& new ColumnLayout;
		$wizardLayout->addComponent($lower);
			
		// :: Steps Menu ::
		$menu =& new VerticalMenuLayout(MENU_WIDGET, 2);
		$lower->addComponent($menu);
		foreach (array_keys($this->_steps) as $number) {
			if ($number != $this->_currentStep
				&& $this->_allowStepLinks) {
				$menu->addComponent(
					new LinkMenuItem($number." - ".
						$this->_steps[$number]->getDisplayName(),
						MYURL."/".implode("/",$harmoni->pathInfoParts)."/".$number."/",
						FALSE)
				);			
			} else {
				$menu->addComponent(
					new StandardMenuItem($number." - ".
						$this->_steps[$number]->getDisplayName(),
						($number == $this->_currentStep)?TRUE:FALSE)
				);
			}
		}
		if ($this->_allowStepLinks || !$this->hasNext()) {
			$menu->addComponent(
				new LinkMenuItem(_("Save"),
					MYURL."/".implode("/",$harmoni->pathInfoParts)."/save/",
					FALSE)
			);
		}
		$menu->addComponent(
			new LinkMenuItem(_("Cancel"),
				MYURL."/".implode("/",$harmoni->pathInfoParts)."/cancel/",
				FALSE)
		);
		
		$center = new RowLayout;
		$lower->addComponent($center);
		
		// :: Buttons ::
		$buttons =& new SingleContentLayout;
		$buttonText = "\n<table>";
		if (count($this->_steps) > 1) {
			$buttonText .= "\n\t<tr>";
			$buttonText .= "\n\t\t<td align='left'>";
			if ($this->hasPrevious())
				$buttonText .= "\n\t\t\t<input type='submit' name='previous' value='"._("Previous")."'>";
			else
				$buttonText .= "\n\t\t\t &nbsp; ";
			$buttonText .= "\n\t\t</td>";
			$buttonText .= "\n\t\t<td align='right'>";
			if ($this->hasNext())
				$buttonText .= "\n\t\t\t<input type='submit' name='next' value='"._("Next")."'>";
			else
				$buttonText .= "\n\t\t\t &nbsp; ";
			$buttonText .= "\n\t\t</td>";
			$buttonText .= "\n\t</tr>";
		}
		$buttonText .= "\n\t<tr>";
		$buttonText .= "\n\t\t<td align='left'>";
		$buttonText .= "\n\t\t\t<input type='submit' name='cancel' value='"._("Cancel")."'>";
		$buttonText .= "\n\t\t</td>";
		$buttonText .= "\n\t\t<td align='right'>";
		$buttonText .= "\n\t\t\t<input type='submit' name='save' value='"._("Save")."'>";
		$buttonText .= "\n\t\t</td>";
		$buttonText .= "\n\t</tr>";
		
		$buttonText .= "\n</table>";
		$center->addComponent($buttons);
		
		// :: The Current Step ::
		$stepLayout =& $this->_steps[$this->_currentStep]->getLayout($harmoni);
		$center->addComponent($stepLayout);
		
		// :: Buttons Redeuex ::
		$center->addComponent($buttons);
		
		return $wizardLayout;
	}
	
	/**
	 * Returns a array of all properties in all steps.
	 * If steps have properties of the same name, there could be conflicts.
	 * @return array An array of Property objects.
	 */
	function & getProperties() {
		$allProperties = array();
		foreach (array_keys($this->_steps) as $number) {
			$stepProperties =& $this->_steps->getProperties();
			foreach (array_keys($stepProperties) as $name) {
				$allProperties[$name] =& $stepProperties[$name];
			}
		}
		return $allProperties;
	}
}

?>