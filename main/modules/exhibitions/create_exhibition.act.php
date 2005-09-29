<?php
/**
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");

/**
 * 
 * 
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class create_exhibitionAction 
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
		// Check that the user can create an asset here.
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		 
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$idManager->getId("edu.middlebury.concerto.exhibition_repository"));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create an <em>Exhibition</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Create an <em>Exhibition</em>");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		
		$centerPane =& $this->getActionRows();
		$cacheName = 'create_exhibition_wizard';
		
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
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		
		$exhibitionRepositoryId =& $idManager->getId(
				"edu.middlebury.concerto.exhibition_repository");
		$repository =& $repositoryManager->getRepository($exhibitionRepositoryId);
		
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Name &amp; Description"));
		
		// Create the properties.
		$displayNameProp =& $step->addComponent("display_name", new WTextField());
		$displayNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$displayNameProp->setErrorRule(new WECRegex("[\\w]+"));
		
		$descriptionProp =& $step->addComponent("description", WTextArea::withRowsAndColumns(5,30));
				
		// Create the step text
		ob_start();
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Exhibition</em>: ");
		print "\n<br />[[display_name]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Exhibition</em>: ");
		print "\n<br />[[description]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		// :: Effective/Expiration Dates ::
		$step =& $wizard->addStep("datestep", new WizardStep());
		$step->setDisplayName(_("Effective Dates")." ("._("optional").")");
		
		// Create the properties.
		$property =& $step->addComponent("effective_date", new WTextField());
		$property =& $step->addComponent("expiration_date", new WTextField());
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Effective Date")."</h2>";
		print "\n"._("The date that this <em>Exhibition</em> becomes effective: ");
		print "\n<br />[[effective_date]]";
		
		print "\n<h2>"._("Expiration Date")."</h2>";
		print "\n"._("The date that this <em>Exhibition</em> expires: ");
		print "\n<br />[[expiration_date]]";
		$step->setContent(ob_get_contents());
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
		
		// Make sure we have a valid Repository
		$idManager =& Services::getService("Id");
		$authZ =& Services::getService("AuthZ");
		$repositoryManager =& Services::getService("Repository");
		
		$exhibitionRepositoryId =& $idManager->getId(
				"edu.middlebury.concerto.exhibition_repository");
		$repository =& $repositoryManager->getRepository($exhibitionRepositoryId);
		
		$properties =& $wizard->getAllValues();
		
		$assetType = new Type("Assets Types",
							"edu.middlebury.concerto",
							"Exhibition",
							"Exhibition Assets are containers for Slideshows.");
		
		
		$asset =& $repository->createAsset($properties['namedescstep']['display_name'], 
									$properties['namedescstep']['description'], 
									$assetType);
									
		$assetId =& $asset->getId();
		$this->_assetId =& $assetId;
		
		// Update the effective/expiration dates
		if ($properties['datestep']['effective_date'])
			$asset->updateEffectiveDate(
				DateAndTime::fromString($properties['datestep']['effective_date']));
		if ($properties['datestep']['expiration_date'])
			$asset->updateExpirationDate(
				DateAndTime::fromString($properties['datestep']['expiration_date']));
		
		return TRUE;
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
// 		$repositoryId =& $this->getRepositoryId();
// 		if ($this->_assetId) 
// 			return $harmoni->request->quickURL("asset", "editview", array(
// 				"collection_id" => $repositoryId->getIdString(),
// 				"asset_id" => $this->_assetId->getIdString()));
// 		else
			return $harmoni->request->quickURL("exhibitions", "browse");
	}
}

?>