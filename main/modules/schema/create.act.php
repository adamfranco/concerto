<?php
/**
 * @package concerto.modules.schema
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
 * @package concerto.modules.schema
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class createAction 
	extends RepositoryAction
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
					$this->getRepositoryId());
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to edit this <em>Collection</em>.");
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
		$repositoryId =& $this->getRepositoryId();
		$cacheName = 'create_schema_wizard_'.$repositoryId->getIdString();
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough("collection_id");
		
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
		return _("Create a Schema");
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
		$repository =& $this->getRepository();
	
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Step One ::
		$stepOne =& $wizard->addStep("namedesc", new WizardStep());
		$stepOne->setDisplayName(_("Name & Description"));
		
		// Create the properties.
		$displayNameProp =& $stepOne->addComponent("display_name",
			new WTextField());
		$displayNameProp->setErrorRule(new WECRegex("[\\w]+"));
		$displayNameProp->setErrorText(_("A value for this field is required."));
		
		$descriptionProp =& $stepOne->addComponent("description",
			WTextArea::withRowsAndColumns(5, 30));
		
		$formatProp =& $stepOne->addComponent("format",
			new WTextField());
		$formatProp->setValue("Plain Text - UTF-8 encoding");
		$formatProp->setErrorRule(new WECRegex("[\\w]+"));
		$formatProp->setErrorText(_("A value for this field is required."));
		$formatProp->setSize(25);
		
		
		// Create the step text
		ob_start();
		
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this Schema: ");
		print "\n<br />[[display_name]]";
		
		
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this Schema: ");
		print "\n<br />[[description]]";
		
		
		print "\n<h2>"._("Format")."</h2>";
		print "\n"._("The format of data that is entered into the fields: ");
		print "\n<br /><em>"._("'Plain Text - ASCII encoding', 'XML', etc.")."</em>";
		print "\n<br />[[format]]";
		
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$stepOne->setContent(ob_get_contents());
		ob_end_clean();
			
		
		// :: Add Elements ::
		$elementStep =& $wizard->addStep("elementstep",new WizardStep());
		$elementStep->setDisplayName(_("Add Elements"));
		
		$multField =& new WRepeatableComponentCollection();
		$elementStep->addComponent("elements", $multField);
		
		$property =& $multField->addComponent(
			"display_name", 
			new WTextField());
		$property->setErrorRule(new WECRegex("[\\w]+"));
		$property->setErrorText(_("A value for this field is required."));
		$property->setSize(20);
		
		$property =& $multField->addComponent(
			"description", 
			WTextArea::withRowsAndColumns(2, 30));
		
		
		
		$property =& $multField->addComponent(
			"type", 
			new WSelectList());
		$defaultType =& new Type ("Repository", "edu.middlebury.harmoni", "string");
		$property->setValue(urlencode(HarmoniType::typeToString($defaultType, " :: ")));
		
		// We are going to assume that all RecordStructures have the same PartStructureTypes
		// in this Repository. This will allow us to list PartStructureTypes before
		// the RecordStructure is actually created.
		$recordStructures =& $repository->getRecordStructures();
		if (!$recordStructures->hasNext())
			throwError(new Error("No RecordStructures availible.", "Concerto"));
			
		while ($recordStructures->hasNext()) {
			// we want just the datamanager structure types, so just 
			// get the first structure that has Format "DataManagerPrimatives"
			$recordStructure =& $recordStructures->next();
			if ($recordStructure->getFormat() == "DataManagerPrimatives") {
				$types =& $recordStructure->getPartStructureTypes();
				while ($types->hasNext()) {
					$type =& $types->next();
					$property->addOption(urlencode(HarmoniType::typeToString($type, " :: ")),HarmoniType::typeToString($type, " :: "));
				}
				break;
			}
		}
		
		
		$property =& $multField->addComponent(
			"mandatory", 
			new WCheckBox());
		$property->setChecked(false);
		$property->setLabel(_("yes"));
		
		$property =& $multField->addComponent(
			"repeatable", 
			new WCheckBox());
		$property->setChecked(false);
		$property->setLabel(_("yes"));
		
		$property =& $multField->addComponent(
			"populatedbydr", 
			new WCheckBox());
		$property->setChecked(false);
		$property->setLabel(_("yes"));
		
		// We don't have any PartStructures yet, so we can't get them.
		
		ob_start();

		print "\n<table border=\"0\">";
			
			print "\n<tr><td>";
				print _("DisplayName").": ";
			print "\n</td><td>";
				print "[[display_name]]";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("Description").": ";
			print "\n</td><td>";
				print "[[description]]";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("Select a Type")."... ";
			print "\n</td><td>";
				print "[[type]]";
			print "\n</td></tr>";
	
			print "\n<tr><td>";
				print _("isMandatory? ");
			print "\n</td><td>";
				print "[[mandatory]]";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("isRepeatable? ");
			print "\n</td><td>";
				print "[[repeatable]]";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("isPopulatedByRepository? ");
			print "\n</td><td>";
				print "[[populatedbydr]]";
			print "\n</td></tr>";
			
			print "</table>";
		
		
	
		$multField->setElementLayout(ob_get_contents());
		ob_end_clean();
		
		ob_start();
		print "<h2>"._("Add New Elements")."</h2>";
		print "\n<p>"._("If none of the schemata listed below fit your needs, please click the button below to save your changes and create a new schema.")."</p>";
		print "[[elements]]";
		$elementStep->setContent(ob_get_contents());
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
		if ($wizard->validate()) {
			$properties =& $wizard->getAllValues();
			
			$repository =& $this->getRepository();
			
			// Create the info Structure
			$recordStructure =& $repository->createRecordStructure($properties['namedesc']['display_name'], 
									$properties['namedesc']['description'], 
									$properties['namedesc']['format'],
									$properties['namedesc']['display_name']);
			Debug::printAll();
			$recordStructureId =& $recordStructure->getId();
			
			// Create a set for the RecordStructure
			$setManager =& Services::getService("Sets");
			$set =& $setManager->getPersistentSet($recordStructureId);

			// Store up the positions for later setting after all of the ids have
			// been added to the set and we can do checking to make sure that 
			// the specified positions are valid.
			$positions = array();
			
			// Create the PartStructures
			$partStructureProperties =& $properties['elementstep']['elements'];
			foreach (array_keys($partStructureProperties) as $index) {
				$type =& HarmoniType::stringToType(urldecode(
					$partStructureProperties[$index]['type']), " :: ");
				$partStructure =& $recordStructure->createPartStructure(
								$partStructureProperties[$index]['display_name'],
								$partStructureProperties[$index]['description'],
								$type,
								(($partStructureProperties[$index]['mandatory'])?TRUE:FALSE),
								(($partStructureProperties[$index]['repeatable'])?TRUE:FALSE),
								(($partStructureProperties[$index]['populatedbydr'])?TRUE:FALSE)
								);
				
				$partStructureId =& $partStructure->getId();
				// Add the PartStructureId to the set
				if (!$set->isInSet($partStructureId))
					$set->addItem($partStructureId);
			}
			
			return TRUE;
			
		} else {
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
		return $harmoni->request->quickURL("collection", "edit",
			array("wizardSkipToStep" => "schema"));
	}
}
