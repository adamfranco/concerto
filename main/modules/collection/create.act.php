<?php
/**
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class createAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check for our authorization function definitions
		if (!defined("AZ_ADD_CHILDREN"))
			throwError(new Error("You must define an id for AZ_ADD_CHILDREN", "concerto.collection", true));
		
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId(AZ_ADD_CHILDREN), 
					$idManager->getId(REPOSITORY_NODE_ID));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create a <em>Collection</em>.");
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
		$centerPane =& $this->getCenterPane();
		$harmoni =& $this->getHarmoni();
		
		// Create the wizard.
		
		 if ($_SESSION['create_collection_wizard']) {
			$wizard =& $_SESSION['create_collection_wizard'];
		 } else {
		
			// Instantiate the wizard, then add our steps.
			$wizard =& new Wizard(_("Create a Collection"));
			$_SESSION['create_collection_wizard'] =& $wizard;
			
			// :: Step One ::
			$stepOne =& $wizard->createStep(_("Name & Description"));
			
			// Create the properties.
			$displayNameProp =& $stepOne->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
			$displayNameProp->setDefaultValue(_("Default Collection Name"));
			$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
			
			$descriptionProp =& $stepOne->createProperty("description", new RegexValidatorRule(".*"));
			$descriptionProp->setDefaultValue(_("Default Collection description."));
			
			// Create the step text
			$stepOneText = "\n<h2>"._("Name")."</h2>";
			$stepOneText .= "\n"._("The Name for this <em>Collection</em>: ");
			$stepOneText .= "\n<br /><input type='text' name='display_name' value=\"[[display_name]]\" />[[display_name|Error]]";
			$stepOneText .= "\n<h2>"._("Description")."</h2>";
			$stepOneText .= "\n"._("The Description for this <em>Collection</em>: ");
			$stepOneText .= "\n<br /><textarea name='description'>[[description]]</textarea>[[description|Error]]";
			$stepOneText .= "\n<div style='width: 400px'> &nbsp; </div>";
			$stepOne->setText($stepOneText);
			
			// :: Step Two ::
			$stepTwo =& $wizard->createStep(_("Type"));
			// Create the properties.
			$property =& $stepTwo->createProperty("type_domain", new RegexValidatorRule(".*"));
			$property->setDefaultValue(_("Collections"));
			
			$property =& $stepTwo->createProperty("type_authority", new RegexValidatorRule(".*"));
			$property->setDefaultValue(_("Concerto"));
			
			$property =& $stepTwo->createProperty("type_keyword", new RegexValidatorRule(".*"));
			$property->setDefaultValue(_("Generic Collection"));
			
			$property =& $stepTwo->createProperty("type_description", new RegexValidatorRule(".*"));
			$property->setDefaultValue(_("This is a <em>Collection</em> of unspecified type."));
			
			// create the text
			$stepTwoText = "<h2>"._("Type")."</h2>";
			$stepTwoText .= "\n"._("All <em>Collections</em> have an immutable type. This type can be used to catagorize <em>Collections</em>, but is not necessary.");
			$stepTwoText .= "\n<table>";
			$stepTwoText .= "\n\t<tr>\n\t\t<td>";
			$stepTwoText .= "<strong>"._("Domain").": </strong>";
			$stepTwoText .= "\n\t\t</td>";
			$stepTwoText .= "\n\t\t<td>";
			$stepTwoText .= "\n<input type='text' name='type_domain' value=\"[[type_domain]]\" />";
			$stepTwoText .= "\n\t\t</td>\n\t</tr>";
			$stepTwoText .= "\n\t<tr>\n\t\t<td>";
			$stepTwoText .= "<strong>"._("Authority").": </strong>";
			$stepTwoText .= "\n\t\t</td>";
			$stepTwoText .= "\n\t\t<td>";
			$stepTwoText .= "\n<input type='text' name='type_authority' value=\"[[type_authority]]\" />";
			$stepTwoText .= "\n\t\t</td>\n\t</tr>";
			$stepTwoText .= "\n\t<tr>\n\t\t<td>";
			$stepTwoText .= "<strong>"._("Keyword").": </strong>";
			$stepTwoText .= "\n\t\t</td>";
			$stepTwoText .= "\n\t\t<td>";
			$stepTwoText .= "\n<input type='text' name='type_keyword' value=\"[[type_keyword]]\" />";
			$stepTwoText .= "\n\t\t</td>\n\t</tr>";
			$stepTwoText .= "\n\t<tr>\n\t\t<td>";
			$stepTwoText .= "<strong>"._("Description").": </strong>";
			$stepTwoText .= "\n\t\t</td>";
			$stepTwoText .= "\n\t\t<td>";
			$stepTwoText .= "\n<textarea name='type_description'>[[type_description]]</textarea>";
			$stepTwoText .= "\n\t\t</td>\n\t</tr>";
			$stepTwoText .= "\n</table>";
			$stepTwo->setText($stepTwoText);
		
		}
		
		// Handle saving if requested
		if ($wizard->isSaveRequested()) {
		
			// If all properties validate then go through the steps nessisary to
			// save the data.
			if ($wizard->updateLastStep()) {
				$properties =& $wizard->getProperties();
				
				// Create the repository and get its id.
				$repositoryManager =& Services::getService("Repository");
				$type =& new HarmoniType($properties['type_domain']->getValue(),
										$properties['type_authority']->getValue(),
										$properties['type_keyword']->getValue(),
										$properties['type_description']->getValue());
				$repository =& $repositoryManager->createRepository(
									$properties['display_name']->getValue(),
									$properties['description']->getValue(), $type);
				
				// Unset the wizard
				$wizard = NULL;
				unset ($_SESSION['create_collection_wizard']);
				unset ($wizard);
				
				// Head off to editing our new collection.
				$id =& $repository->getId();
				header(header("Location: ".MYURL."/collection/edit/".$id->getIdString()."/?__skip_to_step=2"));
			}
		
		// Handle canceling if requested
		} else if ($wizard->isCancelRequested()) {
			$wizard = NULL;
			unset ($_SESSION['create_collection_wizard']);
			unset ($wizard);
			header("Location: ".MYURL."/collections/main/");
		}
		
		$wizardLayout =& $wizard->getLayout($harmoni);
		$centerPane->add($wizardLayout, null, null, CENTER, TOP);
	}
}