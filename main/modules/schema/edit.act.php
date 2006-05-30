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
require_once(HARMONI."debugHandler/PlainTextDebugHandlerPrinter.class.php");

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
		print "\n<p>"._("Here you can modify the properties of elements and add new elements to the schema.")."</p>";
		print "\n<p>"._("<strong>Important:</strong> Elements marked with an asterisk (<span style='color: red'>*</span>) can not be changed after the element is created.")."</p>";
		print "[[elements]]";
		$elementStep->setContent(ob_get_contents());
		ob_end_clean();
		
	// Existing Elements
		$multField =& new WOrderedRepeatableComponentCollection();
		$elementStep->addComponent("elements", $multField);
		
		$property =& $multField->addComponent(
			"id", 
			new WHiddenField());
		
		$property =& $multField->addComponent(
			"display_name", 
			new WTextField());
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$property->setErrorText(_("A value for this field is required."));
		$property->setSize(40);
		
		$property =& $multField->addComponent(
			"description", 
			WTextArea::withRowsAndColumns(2, 40));
		
		
		
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
		
		$dmpType =& new Type("RecordStructures", "edu.middlebury.harmoni", "DataManagerPrimatives", "RecordStructures stored in the Harmoni DataManager.");
		while ($recordStructures->hasNext()) {
			// we want just the datamanager structure types, so just 
			// get the first structure that has Format "DataManagerPrimatives"
			$tmpRecordStructure =& $recordStructures->next();
			if ($dmpType->isEqual($tmpRecordStructure->getType())) {
				$types =& $tmpRecordStructure->getPartStructureTypes();
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
// 		$property->setChecked(false);
		$property->setLabel(_("yes"));
		
// 		$property =& $multField->addComponent(
// 			"populatedbydr", 
// 			new WCheckBox());
// 		$property->setChecked(false);
// 		$property->setLabel(_("yes"));
		
		
		$property =& $multField->addComponent(
			"authoritative_values", 
			WTextArea::withRowsAndColumns(10, 40));
			
		$property =& $multField->addComponent(
			"allow_addition", 
			new WCheckBox());
		$property->setChecked(false);
		$property->setLabel(_("yes"));
		
		
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
			
			print "\n<tr><td style='color: red;'>";
				print _("Type")."... ";
			print "\n *</td><td>";
				print "[[type]]";
			print "\n</td></tr>";
	
			print "\n<tr><td>";
				print _("isMandatory? ");
			print "\n</td><td>";
				print "[[mandatory]]";
			print "\n</td></tr>";
			
			print "\n<tr><td style='color: red;'>";
				print _("isRepeatable? ");
			print "\n *</td><td>";
				print "[[repeatable]]";
			print "\n</td></tr>";
			
// 			print "\n<tr><td>";
// 				print _("isPopulatedByRepository? ");
// 			print "\n</td><td>";
// 				print "[[populatedbydr]]";
// 			print "\n</td></tr>";

			print "\n<tr><td>";
				print _("Authoritative Values: ");
			print "\n</td><td>";
				print "[[authoritative_values]]";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("Allow User Addition of Authoritative Values: ");
				print "\n\t<br/><span style='font-style: italic; font-size: small'>";
				print _("If checked, the Asset editing interface will be allow new authoritative values to be added to this element. If not checked, new values can only be added here.");
				print "</span>";
			print "\n</td><td>";
				print "[[allow_addition]]";
			print "\n</td></tr>";
			
			print "</table>";
		
		
	
		$multField->setElementLayout(ob_get_contents());
		ob_end_clean();
		
		// Add the existing Elements/PartStructures
		// First load the ordered PartStructures, then the rest
		$i = 0;
		$setManager =& Services::getService("Sets");
		$set =& $setManager->getPersistentSet($recordStructure->getId());
		$set->reset();
		while ($set->hasNext()) {
			$partStructure =& $recordStructure->getPartStructure($set->next());
			$this->addPartStructureCollection($multField, $partStructure);
			$i++;
		}
			
		$partStructures =& $recordStructure->getPartStructures();
		while ($partStructures->hasNext()) {
			$partStructure =& $partStructures->next();
			if (!$set->isInSet($partStructure->getId())) {
				$this->addPartStructureCollection($multField, $partStructure);
				$i++;
			}
		}
		$multField->setStartingNumber(0);
		
		return $wizard;
	}
	
	/**
	 * Add a partStructure Collection to a multField
	 * 
	 * @param <##>
	 * @return void
	 * @access public
	 * @since 4/24/06
	 */
	function addPartStructureCollection ( &$multField, &$partStructure ) {
		$collection = array();
		$partStructureId =& $partStructure->getId();
		$collection['id'] = $partStructureId->getIdString();
		$collection['display_name'] = $partStructure->getDisplayName();
		$collection['description'] = $partStructure->getDescription();
		$type =& $partStructure->getType();
		$collection['type'] = urlencode(HarmoniType::typeToString($type, " :: "));
		$collection['mandatory'] = $partStructure->isMandatory();
		$collection['repeatable'] = $partStructure->isRepeatable();
// 		$collection['populatedbydr'] = $partStructure->isPopulatedByRepository();
		
		$authoritativeValues =& $partStructure->getAuthoritativeValues();
		$collection['authoritative_values'] = '';
		while ($authoritativeValues->hasNext()) {
			$value =& $authoritativeValues->next();
			$collection['authoritative_values'] .= preg_replace('/[\n\r]/', '', 
				$value->asString());
			$collection['authoritative_values'] .= "\n";
		}
		$collection['allow_addition'] = $partStructure->isUserAdditionAllowed();

		
		
		$newCollection =& $multField->addValueCollection($collection, false);
		
		$newCollection['type']->setEnabled(false, true);
// 		$newCollection['repeatable']->setEnabled(false, true);
// 		$newCollection['populatedbydr']->setEnabled(false, true);
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
// 		Debug::level(100);
		$wizard =& $this->getWizard($cacheName);
		// If all properties validate then go through the steps nessisary to
		// save the data.
		if ($wizard->validate()) {
			$properties = $wizard->getAllValues();
// 			printpre($properties);
			
			$repository =& $this->getRepository();
			$recordStructure =& $this->getRecordStructure();
			
			if ($properties['namedesc']['display_name'] != $recordStructure->getDisplayName())
				$recordStructure->updateDisplayName($properties['namedesc']['display_name']);
			if ($properties['namedesc']['description'] != $recordStructure->getDescription())
				$recordStructure->updateDescription($properties['namedesc']['description']);
			if ($properties['namedesc']['format'] != $recordStructure->getFormat())
				$recordStructure->updateFormat($properties['namedesc']['format']);

			$recordStructureId =& $recordStructure->getId();
			$idManager =& Services::getService("Id");
			
			// Create a set for the RecordStructure
			$setManager =& Services::getService("Sets");
			$set =& $setManager->getPersistentSet($recordStructureId);

			// Update the existing part structures
			$i = 0;
			foreach (array_keys($properties['elementstep']['elements']) as $index) {
				$partStructProps =& $properties['elementstep']['elements'][$index];
				
				print "\n<hr/>";
				printpre($partStructProps['id']);
				
				if ($partStructProps['id']) {
					$partStructId =& $idManager->getId($partStructProps['id']);				
					$partStruct =& $recordStructure->getPartStructure($partStructId);	
					
					if ($partStructProps['display_name'] != $partStruct->getDisplayName())
						$partStruct->updateDisplayName($partStructProps['display_name']);
					if ($partStructProps['description'] != $partStruct->getDescription())
						$partStruct->updateDescription($partStructProps['description']);
					if ($partStructProps['mandatory'] != $partStruct->isMandatory())
						$partStruct->updateIsMandatory($partStructProps['mandatory']);
					if ($partStructProps['repeatable'] != $partStruct->isRepeatable())
						$partStruct->updateIsRepeatable(($partStructProps['repeatable']?TRUE:FALSE));
	// 				if ($partStructProps['populatedbydr'] != $partStruct->isPopulatedByRepository())
	// 					$partStruct->updateIsPopulatedByRepository($partStructProps['populatedbydr']);
				} else {
					$type =& HarmoniType::fromString(urldecode(
						$partStructProps['type']), " :: ");
					$partStruct =& $recordStructure->createPartStructure(
									$partStructProps['display_name'],
									$partStructProps['description'],
									$type,
									(($partStructProps['mandatory'])?TRUE:FALSE),
									(($partStructProps['repeatable'])?TRUE:FALSE),
									FALSE
									);
					
					$partStructId =& $partStruct->getId();
				}
				
				// Authoritative values
				$valuesString = trim($partStructProps['authoritative_values']);
				if ($valuesString) {
					$authoritativeStrings = explode("\n", $valuesString);
					array_walk($authoritativeStrings, "removeExcessWhitespace");
					
					// Remove and missing values
					$authoritativeValues =& $partStruct->getAuthoritativeValues();
					while ($authoritativeValues->hasNext()) {
						$value =& $authoritativeValues->next();
						if (!in_array($value->asString(), $authoritativeStrings))
							$partStruct->removeAuthoritativeValue($value);
					}
					
					// Add new values
					foreach ($authoritativeStrings as $valueString) {
						if ($valueString)
							$partStruct->addAuthoritativeValueAsString($valueString);
					}
				}
				$partStruct->setUserAdditionAllowed(
					(($partStructProps['allow_addition'])?TRUE:FALSE));
				
				// Order of part structures
				if (!$set->isInSet($partStructId))
					$set->addItem($partStructId);
				$set->moveToPosition($partStructId, $i);
				
				$i++;
			}
			
			// Log the success or failure
			if (Services::serviceRunning("Logging")) {
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
			
			
// 			Debug::printAll(new PlainTextDebugHandlerPrinter);
// 			exit;
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

/**
 * 
 * 
 * @param <##>
 * @return <##>
 * @access public
 * @since 4/26/06
 */
function removeExcessWhitespace (&$string) {
	$string = trim($string);
}
