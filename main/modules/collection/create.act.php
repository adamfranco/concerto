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
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.add_children"), 
					$idManager->getId(REPOSITORY_ROOT_ID));
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
		$centerPane =$this->getActionRows();
		$cacheName = 'create_collection_wizard';
		
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
		return _("Create a Collection");
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
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		// :: Step One ::
		$stepOne =$wizard->addStep("namedesc", new WizardStep());
		$stepOne->setDisplayName(_("Name & Description"));
		
		// Create the properties.
		$displayNameProp =$stepOne->addComponent("display_name", new WTextField());
		$displayNameProp->setErrorText(_("A value for this field is required."));
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$descriptionProp =$stepOne->addComponent("description", WTextArea::withRowsAndColumns(10, 80));
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Collection</em>: ");
		print "\n<br />[[display_name]]";
		
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Collection</em>: ");
		print "\n<br />[[description]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$stepOne->setContent(ob_get_contents());
		ob_end_clean();
		
		// :: Step Two ::
		$stepTwo =$wizard->addStep("type", new WizardStep());
		$stepTwo->setDisplayName(_("Type"));
		// Create the properties.
		$property =$stepTwo->addComponent("type_domain", new WTextField());
		$property->setValue(_("Collections"));
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$property->setErrorText(_("A value for this field is required."));
		
		$property =$stepTwo->addComponent("type_authority", new WTextField());
		$property->setValue(_("Concerto"));
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$property->setErrorText(_("A value for this field is required."));
		
		$property =$stepTwo->addComponent("type_keyword", new WTextField());
		$property->setValue(_("Generic Collection"));
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$property->setErrorText(_("A value for this field is required."));
		
		$property =$stepTwo->addComponent("type_description", WTextArea::withRowsAndColumns(3, 50));
		$property->setValue(_("This is a <em>Collection</em> of unspecified type."));
		
		// create the text
		ob_start();
		print "<h2>"._("Type")."</h2>";
		print "\n"._("All <em>Collections</em> have an immutable type. This type can be used to catagorize <em>Collections</em>, but is not necessary.");
		print "\n<table>";

		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Domain").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n[[type_domain]]";
		print "\n\t\t</td>\n\t</tr>";
		
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Authority").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n[[type_authority]]";
		print "\n\t\t</td>\n\t</tr>";
		
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Keyword").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n[[type_keyword]]";
		print "\n\t\t</td>\n\t</tr>";
		
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Description").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n[[type_description]]";
		print "\n\t\t</td>\n\t</tr>";
		
		print "\n</table>";
		$stepTwo->setContent(ob_get_contents());
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
		$wizard =$this->getWizard($cacheName);
	
		// If all properties validate then go through the steps nessisary to
		// save the data.
		if ($wizard->validate()) {
			$properties = $wizard->getAllValues();
			
			// Create the repository and get its id.
			$repositoryManager = Services::getService("Repository");
			$type = new HarmoniType($properties['type']['type_domain'],
									$properties['type']['type_authority'],
									$properties['type']['type_keyword'],
									$properties['type']['type_description']);
			$repository =$repositoryManager->createRepository(
								$properties['namedesc']['display_name'],
								$properties['namedesc']['description'], $type);
			
			$this->repositoryId =$repository->getId();
			
			// Add the File Record Structure Id as a default Schema
			$setManager = Services::getService("Sets");
			$idManager = Services::getService("Id");
			$set =$setManager->getPersistentSet($this->repositoryId);
			$set->addItem($idManager->getId('FILE'));
			
			
			// Log the success or failure
			if (Services::serviceRunning("Logging")) {
				$loggingManager = Services::getService("Logging");
				$log =$loggingManager->getLogForWriting("Concerto");
				$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
								"Normal events.");
				
				$item = new AgentNodeEntryItem("Create Node", "Repository added");
				$item->addNodeId($repository->getId());
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			
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
		$harmoni = Harmoni::instance();
		
		if (isset($this->repositoryId))
			return $harmoni->request->quickURL("collection", "edit", array(
						"collection_id" => $this->repositoryId->getIdString(), 
						"wizardSkipToStep" => "schema"));
		else
			return $harmoni->request->quickURL("collections", "namebrowse");
	}
}
