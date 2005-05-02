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
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$centerPane =& $this->getCenterPane();
		$cacheName = 'create_collection_wizard';
		
		$this->runWizard ( $cacheName, $centerPane );
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function &createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard =& new Wizard(_("Create a Collection"));
		
		// :: Step One ::
		$stepOne =& $wizard->createStep(_("Name & Description"));
		
		// Create the properties.
		$displayNameProp =& $stepOne->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
		$displayNameProp->setDefaultValue(_("Default Collection Name"));
		$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
		
		$descriptionProp =& $stepOne->createProperty("description", new RegexValidatorRule(".*"));
		$descriptionProp->setDefaultValue(_("Default Collection description."));
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Collection</em>: ");
		print "\n<br /><input type='text' name='display_name' value=\"[[display_name]]\" />[[display_name|Error]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Collection</em>: ");
		print "\n<br /><textarea name='description'>[[description]]</textarea>[[description|Error]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$stepOne->setText(ob_get_contents());
		ob_end_clean();
		
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
		ob_start();
		print "<h2>"._("Type")."</h2>";
		print "\n"._("All <em>Collections</em> have an immutable type. This type can be used to catagorize <em>Collections</em>, but is not necessary.");
		print "\n<table>";
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Domain").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n<input type='text' name='type_domain' value=\"[[type_domain]]\" />";
		print "\n\t\t</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Authority").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n<input type='text' name='type_authority' value=\"[[type_authority]]\" />";
		print "\n\t\t</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Keyword").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n<input type='text' name='type_keyword' value=\"[[type_keyword]]\" />";
		print "\n\t\t</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Description").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n<textarea name='type_description'>[[type_description]]</textarea>";
		print "\n\t\t</td>\n\t</tr>";
		print "\n</table>";
		$stepTwo->setText(ob_get_contents());
		ob_end_clean();
	
		return $wizard;
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		$wizard =& $this->getWizard($cacheName);
	
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
			
			$this->repositoryId =& $repository->getId();
			
			return TRUE;
		} else
			return FALSE;
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		if ($this->repositoryId)
			return MYURL."/collection/edit/"
				.$this->repositoryId->getIdString()."/?__skip_to_step=2";
		else
			return MYURL."/collections/main/";
	}
}
