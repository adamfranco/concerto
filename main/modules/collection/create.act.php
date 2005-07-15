<?php
/**
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

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
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.add_children"), 
					$idManager->getId("edu.middlebury.concerto.collections_root"));
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
		$centerPane =& $this->getActionRows();
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
		$displayNameProp =& $stepOne->createProperty(
				RequestContext::name('display_name'), 
				new RegexValidatorRule("^[^ ]{1}.*$"));
		$displayNameProp->setDefaultValue(_("Default Collection Name"));
		$displayNameProp->setErrorString(" <span style='color: #f00'>* "
				._("The name must not start with a space.")."</span>");
		
		$descriptionProp =& $stepOne->createProperty(
				RequestContext::name('description'), new RegexValidatorRule(".*"));
		$descriptionProp->setDefaultValue(_("Default Collection description."));
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Collection</em>: ");
		$fieldName = RequestContext::name('display_name');
		print "\n<br /><input type='text' name='$fieldName' value=\"[[$fieldName]]\" />[[$fieldName|Error]]";
		
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Collection</em>: ");
		$fieldName = RequestContext::name('description');
		print "\n<br /><textarea name='$fieldName'>[[$fieldName]]</textarea>[[$fieldName|Error]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$stepOne->setText(ob_get_contents());
		ob_end_clean();
		
		// :: Step Two ::
		$stepTwo =& $wizard->createStep(_("Type"));
		// Create the properties.
		$property =& $stepTwo->createProperty(RequestContext::name("type_domain"), new RegexValidatorRule(".*"));
		$property->setDefaultValue(_("Collections"));
		
		$property =& $stepTwo->createProperty(RequestContext::name("type_authority"), new RegexValidatorRule(".*"));
		$property->setDefaultValue(_("Concerto"));
		
		$property =& $stepTwo->createProperty(RequestContext::name("type_keyword"), new RegexValidatorRule(".*"));
		$property->setDefaultValue(_("Generic Collection"));
		
		$property =& $stepTwo->createProperty(RequestContext::name("type_description"), new RegexValidatorRule(".*"));
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
		$fieldName = RequestContext::name('type_domain');
		print "\n<input type='text' name='$fieldName' value=\"[[$fieldName]]\" />";
		print "\n\t\t</td>\n\t</tr>";
		
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Authority").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		$fieldName = RequestContext::name('type_authority');
		print "\n<input type='text' name='$fieldName' value=\"[[$fieldName]]\" />";
		print "\n\t\t</td>\n\t</tr>";
		
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Keyword").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		$fieldName = RequestContext::name('type_keyword');
		print "\n<input type='text' name='$fieldName' value=\"[[$fieldName]]\" />";
		print "\n\t\t</td>\n\t</tr>";
		
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Description").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		$fieldName = RequestContext::name('type_description');
		print "\n<textarea name='$fieldName'>[[$fieldName]]</textarea>";
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
			$type =& new HarmoniType($properties[RequestContext::name('type_domain')]->getValue(),
									$properties[RequestContext::name('type_authority')]->getValue(),
									$properties[RequestContext::name('type_keyword')]->getValue(),
									$properties[RequestContext::name('type_description')]->getValue());
			$repository =& $repositoryManager->createRepository(
								$properties[RequestContext::name('display_name')]->getValue(),
								$properties[RequestContext::name('description')]->getValue(), $type);
			
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
		$harmoni =& Harmoni::instance();
		
		if ($this->repositoryId)
			return $harmoni->request->quickURL("collection", "edit", array(
						"collection_id" => $this->repositoryId->getIdString(), 
						"__skip_to_step" => 2));
		else
			return $harmoni->request->quickURL("collections", "main");
	}
}
