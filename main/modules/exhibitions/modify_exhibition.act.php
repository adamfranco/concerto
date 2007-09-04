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
class modify_exhibitionAction 
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
		$harmoni = Harmoni::instance();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		$harmoni->request->startNamespace('modify_exhibition');
		$harmoni->request->passthrough('exhibition_id');
		$exhibitionId =$idManager->getId(RequestContext::value('exhibition_id'));
		$harmoni->request->endNamespace();
		
		// Check that the user can create an asset here.
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$exhibitionId);
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to modify this <em>Exhibition</em>.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$centerPane =$this->getActionRows();
		$cacheName = 'modify_exhibition_wizard';
		
		$this->runWizard ( $cacheName, $centerPane );
	}
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$idManager = Services::getService("Id");
		$repositoryManager = Services::getService("Repository");
		$repository =$repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));

		$harmoni = Harmoni::Instance();
		$harmoni->request->startNamespace("modify_exhibition");
	
		$asset =$repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));

		$harmoni->request->endNamespace();

		return _("Modifying the ")." <em>".$asset->getDisplayName().
			"</em> "._("Exhibition");
	}
		
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function createWizard () {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('modify_exhibition');
		$harmoni->request->passthrough('exhibition_id');
		
		$idManager = Services::getService("Id");
		$repositoryManager = Services::getService("Repository");
		
		$exhibitionRepositoryId =$idManager->getId(
				"edu.middlebury.concerto.exhibition_repository");
		$repository =$repositoryManager->getRepository($exhibitionRepositoryId);
		$asset =$repository->getAsset(
					$idManager->getId(RequestContext::value('exhibition_id')));

		
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =$wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Name &amp; Description"));
		
		// Create the properties.
		$displayNameProp =$step->addComponent("display_name", new WTextField());
		$displayNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$displayNameProp->setValue($asset->getDisplayName());
		
		$descriptionProp =$step->addComponent("description", WTextArea::withRowsAndColumns(5,30));
		$descriptionProp->setValue($asset->getDescription());
				
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
		$step =$wizard->addStep("datestep", new WizardStep());
		$step->setDisplayName(_("Effective Dates")." ("._("optional").")");
		
		// Create the properties.
		$property =$step->addComponent("effective_date", new WTextField());
		$date =$asset->getEffectiveDate();
		if (is_object($date))
			$property->setValue($date->asString());
		
		$property =$step->addComponent("expiration_date", new WTextField());
		$date =$asset->getExpirationDate();
		if (is_object($date))
			$property->setValue($date->asString());
		
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
		
		$harmoni->request->endNamespace();
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
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('modify_exhibition');
		$harmoni->request->passthrough('exhibition_id');
		
		$wizard =$this->getWizard($cacheName);
		
		// Make sure we have a valid Repository
		$idManager = Services::getService("Id");
		$authZ = Services::getService("AuthZ");
		$repositoryManager = Services::getService("Repository");
		
		$exhibitionRepositoryId =$idManager->getId(
				"edu.middlebury.concerto.exhibition_repository");
		$repository =$repositoryManager->getRepository($exhibitionRepositoryId);
		$asset = $repository->getAsset(
					$idManager->getId(RequestContext::value('exhibition_id')));
		
		$properties =$wizard->getAllValues();
		
		$assetType = new Type("Asset Types",
							"edu.middlebury.concerto",
							"Exhibition",
							"Exhibition Assets are containers for Slideshows.");
		
		
		$asset->updateDisplayName($properties['namedescstep']['display_name']);
		$asset->updateDescription($properties['namedescstep']['description']);
									
		$assetId =$asset->getId();
		$this->_assetId =$assetId;
		
		// Update the effective/expiration dates
		if ($properties['datestep']['effective_date'])
			$asset->updateEffectiveDate(
				DateAndTime::fromString($properties['datestep']['effective_date']));
		if ($properties['datestep']['expiration_date'])
			$asset->updateExpirationDate(
				DateAndTime::fromString($properties['datestep']['expiration_date']));
		
		// Log the success or failure
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log =$loggingManager->getLogForWriting("Concerto");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item = new AgentNodeEntryItem("Modify Node", "Exhibition Modified");
			$item->addNodeId($asset->getId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		$harmoni->request->endNamespace();
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
		$harmoni = Harmoni::instance();
		return $harmoni->request->quickURL("exhibitions", "browse");
	}
}

?>