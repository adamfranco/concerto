<?php
/**
 * @package concerto.modules.asset
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
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class add_slideshowAction 
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
			$idManager->getId(RequestContext::value('exhibition_id')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create a SlideShow in this <em>Exhibition</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$asset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
		return _("Creating a SlideShow in the")." <em>".$asset->getDisplayName()."</em> "._("Exhibition");
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
		$harmoni->request->passthrough("exhibition_id");
		
		$actionRows =& $this->getActionRows();
		
		$idManager =& Services::getService("Id");
		$exhibitionAssetId =& $idManager->getId(RequestContext::value('exhibition_id'));
		
		$cacheName = 'add_slideshow_wizard_'.$exhibitionAssetId->getIdString();
		
		$this->runWizard ( $cacheName, $actionRows );
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
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$exhibitionAsset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withTitleAndDefaultLayout(_("Add a SlideShow to the ")."<em>".$exhibitionAsset->getDisplayName()."</em> "._("Exhibition"));
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Title &amp; Description"));
		
		// Create the properties.
		$displayNameProp =& $step->addComponent("display_name", new WTextField());
		$displayNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$displayNameProp->setErrorRule(new WECRegex("[\\w]+"));
	// 	$displayNameProp->setDefaultValue(_("Default Asset Name"));
//		$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
		
		$descriptionProp =& $step->addComponent("description", WTextArea::withRowsAndColumns(5,30));
	// 	$descriptionProp->setDefaultValue(_("Default Asset description."));
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Title")."</h2>";
		print "\n"._("The title of this <em>SlideShow</em>: ");
		print "\n<br />[[display_name]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("A description of this <em>SlideShow</em>: ");
		print "\n<br />[[description]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		
		// :: Effective/Expiration Dates ::
		$step =& $wizard->addStep("datestep", new WizardStep());
		$step->setDisplayName(_("Effective Dates")." ("._("optional").")");
		
		// Create the properties.
		$property =& $step->addComponent("effective_date", new WTextField());
	//	$property->setDefaultValue();
//		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
	
		$property =& $step->addComponent("expiration_date", new WTextField());
	//	$property->setDefaultValue();
//		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Effective Date")."</h2>";
		print "\n"._("The date that this <em>Slide-Show</em> becomes effective: ");
		print "\n<br />[[effective_date]]";
		
		print "\n<h2>"._("Expiration Date")."</h2>";
		print "\n"._("The date that this <em>Slide-Show</em> expires: ");
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
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$exhibitionAsset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
		
		$properties =& $wizard->getAllValues();
		
		// First, verify that we chose a parent that we can add children to.
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"), 
				$exhibitionAsset->getId()))
		{
			
			$assetType = new HarmoniType("exhibitions", 
										"edu.middlebury.concerto", 
										"slideshow", 
										"Slide-Shows are ordered collections of slides that contain captions and may reference media Assets.");
			
			
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
			
			$exhibitionAsset->addAsset($assetId);
			
			return TRUE;
		} 
		// If we don't have authorization to add to the picked parent, send us back to
		// that step.
		else {
			return FALSE;
		}
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
		$repositoryId =& $this->getRepositoryId();
		if ($this->_assetId) 
			return $harmoni->request->quickURL("asset", "editview", array(
				"collection_id" => $repositoryId->getIdString(),
				"asset_id" => $this->_assetId->getIdString()));
		else
			return $harmoni->request->quickURL("collection", "browse", array(
				"collection_id" => $repositoryId->getIdString()));
	}
}

?>