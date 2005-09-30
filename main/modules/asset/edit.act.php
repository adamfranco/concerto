<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/AssetAction.class.php");

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
class editAction 
	extends AssetAction
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
					$idManager->getId("edu.middlebury.authorization.modify"), 
					$this->getAssetId());
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to edit this <em>Asset</em>.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough("collection_id", "asset_id");
		$centerPane =& $this->getActionRows();
		$assetId =& $this->getAssetId();
		$cacheName = 'edit_asset_wizard_'.$assetId->getIdString();
		
		$this->runWizard ( $cacheName, $centerPane );
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
		$asset =& $this->getAsset();
		$wizard =& $this->getWizard($cacheName);
				
		$properties =& $wizard->getAllValues();
			
		// Update the name and description
		$asset->updateDisplayName($properties['namedescstep']['display_name']);
		$asset->updateDescription($properties['namedescstep']['description']);
		$content =& Blob::withValue($properties['contentstep']['content']);
		$asset->updateContent($content);
		
		
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
		$assetId =& $this->getAssetId();
		$repositoryId =& $this->getRepositoryId();
		$harmoni =& Harmoni::instance();
		
		return $harmoni->request->quickURL("asset", "editview", 
					array("collection_id" => $repositoryId->getIdString(), 
					"asset_id" => $assetId->getIdString()));
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
		$harmoni =& Harmoni::instance();
		$assetId =& $this->getAssetId();
		$asset =& $this->getAsset();
	
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();		
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Name & Description"));
		
		// Create the properties.
		$displayNameProp =& $step->addComponent("display_name", new WTextField());
		$displayNameProp->setValue($asset->getDisplayName());
		$displayNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$displayNameProp->setErrorRule(new WECRegex("[\\w]+"));

		
		$descriptionProp =& $step->addComponent("description", new WTextArea());
		$descriptionProp->setValue($asset->getDescription());
		$descriptionProp->setRows(3);
		$descriptionProp->setColumns(70);
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Asset</em>: ");
		print "\n<br />[[display_name]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Asset</em>: ");
		print "\n<br />[[description]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		
		// :: Content ::
		$step =& $wizard->addStep("contentstep", new WizardStep());
		$step->setDisplayName(_("Content")." ("._("optional").")");
		
		$property =& $step->addComponent("content", new WTextArea());
		$property->setRows(20);
		$property->setColumns(70);
		$content =& $asset->getContent();
		
		if ($content->asString())
			$property->setValue($content->asString());
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Content")."</h2>";
		print "\n"._("This is an optional place to put content for this <em>Asset</em>. <br />If you would like more structure, you can create new schemas to hold the <em>Asset's</em> data.");
		print "\n<br />[[content]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		
		
		// :: Effective/Expiration Dates ::
		$step =& $wizard->addStep("datestep", new WizardStep());
		$step->setDisplayName(_("Effective Dates")." ("._("optional").")");
		
		// Create the properties.
		$property =& $step->addComponent("effective_date", new WTextField());
		$date =& $asset->getEffectiveDate();
		if ($date) {
			$date =& $date->asDate();
			$property->setValue($date->yyyymmddString());
		}
	
		$property =& $step->addComponent("expiration_date", new WTextField());
		$date =& $asset->getExpirationDate();
		if ($date) {
			$date =& $date->asDate();
			$property->setValue($date->yyyymmddString());
		}
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Effective Date")."</h2>";
		print "\n"._("The date that this <em>Asset</em> becomes effective: ");
		print "\n<br />[[effective_date]]";
		
		print "\n<h2>"._("Expiration Date")."</h2>";
		print "\n"._("The date that this <em>Asset</em> expires: ");
		print "\n<br />[[expiration_date]]";
		$step->setContent(ob_get_contents());
		ob_end_clean();
	
		return $wizard;
	}
}