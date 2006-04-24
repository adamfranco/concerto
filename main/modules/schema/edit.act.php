<?php
/**
 * @package concerto.modules.schema
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RecordStructureAction.class.php");

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
class editAction 
	extends RecordStructureAction
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
		return _("You are not authorized to edit this <em>Schema</em>.");
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
		$recordStructureId =& $this->getRecordStructureId();
		$cacheName = 'edit_schema_wizard_'.$recordStructureId->getIdString();
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough("collection_id");
		$harmoni->request->passthrough("recordstructure_id");
		
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
		$recordStructure =& $this->getRecordStructure();
		return _("Edit the <em>").$recordStructure->getDisplayName()._("</em> Schema");
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
		$recordStructure =& $this->getRecordStructure();
	
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Step One ::
		$stepOne =& $wizard->addStep("namedesc", new WizardStep());
		$stepOne->setDisplayName(_("Name & Description"));
		
		// Create the properties.
		$displayNameProp =& $stepOne->addComponent("display_name",
			new WTextField());
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$displayNameProp->setErrorText(_("A value for this field is required."));
		$displayNameProp->setValue($recordStructure->getDisplayName());
		
		$descriptionProp =& $stepOne->addComponent("description",
			WTextArea::withRowsAndColumns(5, 30));
		$descriptionProp->setValue($recordStructure->getDescription());
		
		$formatProp =& $stepOne->addComponent("format",
			new WTextField());
		$formatProp->setValue("Plain Text - UTF-8 encoding");
		$formatProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$formatProp->setErrorText(_("A value for this field is required."));
		$formatProp->setSize(25);
		$formatProp->setValue($recordStructure->getFormat());
		
		
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
			
		
	// :: Elements ::
		$elementStep =& $wizard->addStep("elementstep",new WizardStep());
		$elementStep->setDisplayName(_("Elements"));
		ob_start();
		print "<h2>"._("Elements")."</h2>";
		print "\n<p>"._("Here you can re-arrange and add new elements to the schema.")."</p>";
		print "[[elements]]";
		print "[[new_elements]]";
		$elementStep->setContent(ob_get_contents());
		ob_end_clean();
		
	// Existing Elements
		$multField =& new WRepeatableComponentCollection();
		$elementStep->addComponent("elements", $multField);
		
		
		$property =& $multField->addComponent(
			"display_name", 
			new WTextField());
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
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
		$property->setReadOnly(TRUE);		
		
		$property =& $multField->addComponent(
			"mandatory", 
			new WCheckBox());
		$property->setChecked(false);
		$property->setLabel(_("yes"));
		
		$property =& $multField->addComponent(
			"repeatable", 
			new WCheckBox());
// 		$property->setChecked(false);
		$property->setLabel(_("yes"));
		$property->setReadOnly(TRUE);
		
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
				print _("Type")."... ";
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
		
		// Add the existing Elements/PartStructures
		$partStructures =& $recordStructure->getPartStructures();
		$i = 0;
		while ($partStructures->hasNext()) {
			$partStructure =& $partStructures->next();
			$collection = array();
			$partStructureId =& $partStructure->getId();
			$collection['part_structure_id'] = preg_replace(
					"/[^a-zA-Z0-9:_\-]/", "_", $partStructureId->getIdString());
			$collection['display_name'] = $partStructure->getDisplayName();
			$collection['description'] = $partStructure->getDescription();
			$type =& $partStructure->getType();
			$collection['type'] = urlencode(HarmoniType::typeToString($type, " :: "));
			$collection['mandatory'] = $partStructure->isMandatory();
			$collection['repeatable'] = $partStructure->isRepeatable();
			$collection['populatedbydr'] = $partStructure->isPopulatedByRepository();
			
			$multField->addValueCollection($collection);
			$i++;
		}
		$multField->setMiminum($i);
		$multField->setMaximum($i);
		$multField->setStartingNumber(0);
		$multField->setAreElementsRemovable(FALSE);
		
		
	// New Elements	
		$multField =& new WRepeatableComponentCollection();
		$elementStep->addComponent("new_elements", $multField);
		$multField->setStartingNumber(0);
		
		$property =& $multField->addComponent(
			"display_name", 
			new WTextField());
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
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
				$type =& HarmoniType::fromString(urldecode(
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
			
			// Log the success or failure
			if (Services::serviceAvailable("Logging")) {
				$loggingManager =& Services::getService("Logging");
				$log =& $loggingManager->getLogForWriting("Concerto");
				$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType =& new Type("logging", "edu.middlebury", "Event_Notice",
								"Normal events.");
				
				$item =& new AgentNodeEntryItem("Modify Node", "RecordStructure created:\n<br/>&nbsp; &nbsp; &nbsp; ".$recordStructure->getDisplayName());
				$item->addNodeId($repository->getId());
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
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
		$repositoryId =& $this->getRepositoryId();
		$recordStructureId =& $this->getRecordStructureId();
		
		return $harmoni->request->quickURL("schema", "view",
			array("collection_id" => $repositoryId->getIdString(),
				"recordstructure_id" => $recordStructureId->getIdString()));
	}
}
