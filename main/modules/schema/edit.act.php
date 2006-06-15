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
require_once(HARMONI."/utilities/StatusStars.class.php");


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
		$recStructFunctions =& $authZ->getFunctions(
								new Type (	"Authorization", 
											"edu.middlebury.harmoni", 
											"RecordStructures"));
		while ($recStructFunctions->hasNext()) {
			$function =& $recStructFunctions->next();
			if ($authZ->isUserAuthorized(
					$function->getId(), 
					$this->getRepositoryId()))
			{
				return true;
			}
		}
					
		return false;
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
		$repositoryId =& $repository->getId();
		$recordStructure =& $this->getRecordStructure();
		$recordStructureId =& $recordStructure->getId();
		
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		$canModify = false;
		$canModifyAuthorityList = false;
		
		if (preg_match("/^Repository::.+$/i", $recordStructureId->getIdString())) {
			if ($authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify_rec_struct"), 
					$repositoryId))
			{
				$canModify = true;
			}
		} else {
			if ($authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify_rec_struct"), 
					$idManager->getId("edu.middlebury.authorization.root")))
			{
				$canModify = true;
			}
		}
		if ($authZManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify_authority_list"), 
				$repositoryId))
		{
			$canModifyAuthorityList = true;
		}
		
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Step One ::
		$stepOne =& $wizard->addStep("namedesc", new WizardStep());
		$stepOne->setDisplayName(_("Name & Description"));
		
		// Create the properties.
		$displayNameProp =& $stepOne->addComponent("display_name",
			new WTextField());
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$displayNameProp->setErrorText(_("A value for this property is required."));
		$displayNameProp->setValue($recordStructure->getDisplayName());
		// Disable if unauthorized
		if (!$canModify)
			$displayNameProp->setEnabled(false, true);
		
		$descriptionProp =& $stepOne->addComponent("description",
			WTextArea::withRowsAndColumns(5, 30));
		$descriptionProp->setValue($recordStructure->getDescription());
		// Disable if unauthorized
		if (!$canModify)
			$descriptionProp->setEnabled(false, true);
		
		$formatProp =& $stepOne->addComponent("format",
			new WTextField());
		$formatProp->setValue("Plain Text - UTF-8 encoding");
		$formatProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$formatProp->setErrorText(_("A value for this property is required."));
		$formatProp->setSize(25);
		$formatProp->setValue($recordStructure->getFormat());
		// Disable if unauthorized
		if (!$canModify)
			$formatProp->setEnabled(false, true);
		
		
		
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
		$elementStep->setDisplayName(_("Fields"));
		ob_start();
		print "<h2>"._("Fields")."</h2>";
		print "\n<p>"._("Here you can modify the properties of fields and add new fields to the schema.")."</p>";
		print "\n<p>"._("<strong>Important:</strong> Properties marked with an asterisk (<span style='color: red'>*</span>) can not be changed after the field is created.")."</p>";
		print "[[elements]]";
		$elementStep->setContent(ob_get_contents());
		ob_end_clean();
		
	// Existing Elements
		$multField =& $elementStep->addComponent("elements", new WOrderedRepeatableComponentCollection());
		$multField->setAddLabel(_("Add New Field"));
		$multField->setRemoveLabel(_("Remove Field"));
		// Disable if unauthorized
		if (!$canModify)
			$multField->setEnabled(false, true);
		
		$property =& $multField->addComponent(
			"id", 
			new WHiddenField());
		
		$property =& $multField->addComponent(
			"display_name", 
			new WTextField());
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$property->setErrorText(_("A value for this property is required."));
		$property->setSize(40);
		if (!$canModify)
			$property->setEnabled(false, true);
		
		$property =& $multField->addComponent(
			"description", 
			WTextArea::withRowsAndColumns(2, 40));
		// Disable if unauthorized
		if (!$canModify)
			$property->setEnabled(false, true);
		
		
		
		$property =& $multField->addComponent(
			"type", 
			new WSelectList());
		$defaultType =& new Type ("Repository", "edu.middlebury.harmoni", "shortstring");
		$property->setValue(urlencode(HarmoniType::typeToString($defaultType, " :: ")));
		
		// We are going to assume that all RecordStructures have the same PartStructureTypes
		// in this Repository. This will allow us to list PartStructureTypes before
		// the RecordStructure is actually created.
		$recordStructures =& $repository->getRecordStructures();
		if (!$recordStructures->hasNext())
			throwError(new Error("No RecordStructures availible.", "Concerto"));
		
		$dmpType =& new Type("RecordStructures", "edu.middlebury.harmoni", "DataManagerPrimatives", "RecordStructures stored in the Harmoni DataManager.");
		$orderedTypes = array(
			"Repository :: edu.middlebury.harmoni :: shortstring"	
				=> _("Short String ----- text with max-length of 256 characters"),
			"Repository :: edu.middlebury.harmoni :: string" 
				=> _("String  ---------- text with unlimited length"),
			"Repository :: edu.middlebury.harmoni :: datetime" 
				=> _("Date [and Time] -- a date or more precise point in time"),
			"Repository :: edu.middlebury.harmoni :: integer" 
				=> _("Integer --------- a whole number: 1, 2, 3, etc"),
			"Repository :: edu.middlebury.harmoni :: float" 
				=> _("Float ----------- a decimal/scientific-notation number"),
			"Repository :: edu.middlebury.harmoni :: boolean" 
				=> _("Boolean --------- true or false (yes/no)"),
			"Repository :: edu.middlebury.harmoni :: blob" 
				=> _("BLOB ----------- Binary Large OBject, for binary data"),
			"Repository :: edu.middlebury.harmoni :: okitype" 
				=> _("O.K.I. Type ------ 'domain :: authority :: keyword' triplet"),
		);
		$unorderedTypes = array();
		while ($recordStructures->hasNext()) {
			// we want just the datamanager structure types, so just 
			// get the first structure that has Format "DataManagerPrimatives"
			$tmpRecordStructure =& $recordStructures->next();
			if ($dmpType->isEqual($tmpRecordStructure->getType())) {
				$types =& $tmpRecordStructure->getPartStructureTypes();
				while ($types->hasNext()) {
					$type =& $types->next();
					$typeString = Type::typeToString($type, " :: ");
					if (!array_key_exists($typeString, $orderedTypes))
						$unorderedTypes[$typeString] = $typeString;
				}
				break;
			}
		}
		foreach ($orderedTypes as $typeString => $desc) {
			$property->addOption(urlencode($typeString), $desc);
		}
		foreach ($unorderedTypes as $typeString => $desc) {
			$property->addOption(urlencode($typeString), $desc);
		}
		
		$property =& $multField->addComponent("orig_type", new WHiddenField());
		
		$property =& $multField->addComponent(
			"mandatory", 
			new WCheckBox());
		$property->setChecked(false);
		$property->setLabel(_("yes"));
		// Disable if unauthorized
		if (!$canModify)
			$property->setEnabled(false, true);
		
		$property =& $multField->addComponent(
			"repeatable", 
			new WCheckBox());
// 		$property->setChecked(false);
		$property->setLabel(_("yes"));
		// Disable if unauthorized
		if (!$canModify)
			$property->setEnabled(false, true);
		
		
		$property =& $multField->addComponent(
			"authoritative_values", 
			WTextArea::withRowsAndColumns(10, 40));
		// Disable if unauthorized
		if (!$canModifyAuthorityList)
			$property->setEnabled(false, true);
		
		
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
				print "[[orig_type]]";
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
			
			print "</table>";
		
		
	
		$multField->setElementLayout(ob_get_contents());
		ob_end_clean();
		$multField->setStartingNumber(0);
		
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
		$collection['orig_type'] = urlencode(HarmoniType::typeToString($type, " :: "));
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
		
		
			
		// Allow conversion of the type if the user is authorized to convert_rec_structs
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		if (preg_match("/^Repository::.+$/i", $partStructureId->getIdString()) 
					&& $authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.convert_rec_struct"), 
						$partStructure->getRepositoryId())
					&& $authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.modify"), 
						$partStructure->getRepositoryId())
				|| ($authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.convert_rec_struct"), 
						$idManager->getId("edu.middlebury.authorization.root"))
					&& $authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.modify"), 
						$idManager->getId("edu.middlebury.authorization.root"))))
		{
			$newCollection =& $multField->addValueCollection($collection, true);
			
			// Allow conversion of type
			$newCollection['type']->addConfirm(_("Are you sure that you want to change the type of this field?\\n\\nConverting ShortStrings to Strings is usually safe, but other conversion may cause data truncation or data loss if there are records for this collection that contain values that cannot be mapped directly to the new data type. Please consult the following guide.\\n\\n-- Safe Conversions --\\nShortString => String \\nShortString => Blob\\nString => Blob  \\nDateTime => ShortString \\nDateTime => String \\nInteger => ShortString \\nInteger => String  \\nFloat => ShortString \\nFloat => String  \\nOKI Type => ShortString \\nOKI Type => String \\n\\n\\n-- Conversions that may be truncated --\\nString => ShortString \\n\\n\\n-- Conversions that may or may not work --\\nShortString => DateTime \\nShortString => Integer \\nShortString => Float \\nShortString => OKI Type \\nString => DateTime \\nString => Integer \\nString => Float \\nString => OKI Type \\nBlob => ShortString \\nBlob => String"));
			if (!preg_match("/^Repository::.+$/i", $partStructureId->getIdString()))
				$newCollection['type']->addConfirm(_("This is a global Schema, changing the type will modify all Collections. Continue?"));
			
			// If a part structure is not repeatable, it can be made repeatable, but
			// not unmade repeatable without proper authorization.
			if ($partStructure->isRepeatable())
				$newCollection['repeatable']->addConfirm(_('Removing the the \\\'isRepeatable\\\' flag for this field may cause any existing repeatable values to become inaccessable or corrupted. Only remove this flag if you are absolutely sure that there are NO Assets that have multiple values for this field.\n\nAre you sure that you want to continue?'));
		} else {
			$newCollection =& $multField->addValueCollection($collection, false);
			
			$newCollection['type']->setEnabled(false, true);
			
			// If a part structure is not repeatable, it can be made repeatable, but
			// not unmade repeatable.
			if ($partStructure->isRepeatable())
				$newCollection['repeatable']->setEnabled(false, true);
		}
		
		
		
		$newCollection['_remove']->addConfirm(_("Removing this Field will delete all Record values (in all Assets) that use this Field.\\n\\nAre you sure that you want to continue?"));
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
			$properties = $wizard->getAllValues();
// 			printpre($properties);
// 			exit;
			
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
			$authZManager =& Services::getService("AuthZ");
			
			// Create a set for the RecordStructure
			$setManager =& Services::getService("Sets");
			$set =& $setManager->getPersistentSet($recordStructureId);

			// Update the existing part structures
			$i = 0;
			$existingPartStructureIds = array();
			foreach (array_keys($properties['elementstep']['elements']) as $index) {
				$partStructProps =& $properties['elementstep']['elements'][$index];
				
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
					
					
					// Data Type conversion
					$type =& HarmoniType::fromString(urldecode(
						$partStructProps['type']), " :: ");
					if (!$type->isEqual($partStruct->getType())
						&& (preg_match("/^Repository::.+$/i", $partStructId->getIdString()) 
							&& $authZManager->isUserAuthorized(
								$idManager->getId("edu.middlebury.authorization.convert_rec_struct"), 
								$partStruct->getRepositoryId())
							&& $authZManager->isUserAuthorized(
								$idManager->getId("edu.middlebury.authorization.modify"), 
								$partStruct->getRepositoryId())
						|| ($authZManager->isUserAuthorized(
								$idManager->getId("edu.middlebury.authorization.convert_rec_struct"), 
								$idManager->getId("edu.middlebury.authorization.root"))
							&& $authZManager->isUserAuthorized(
								$idManager->getId("edu.middlebury.authorization.modify"), 
								$idManager->getId("edu.middlebury.authorization.root")))))
					{
						$partStruct =& $recordStructure->convertPartStructureToType(
							$partStructId, $type, 
							new StatusStars("Converting data type for PartStructure: ".$partStruct->getDisplayName()));
						// Remove the old value from the set
						if ($set->isInSet($partStructId))
							$set->removeItem($partStructId);
						
						// Update the id to reflect the id of the new part
						$partStructId =& $partStruct->getId();
					}
					
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
				//if ($valuesString) {
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
				//}
				
				// Order of part structures
				if (!$set->isInSet($partStructId))
					$set->addItem($partStructId);
				$set->moveToPosition($partStructId, $i);
				
				$existingPartStructureIds[$partStructId->getIdString()] = $partStructId;
				
				$i++;
			}
			
			// Delete any removed partStructures
			if ((preg_match("/^Repository::.+$/i", $recordStructureId->getIdString()) 
					&& $authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.convert_rec_struct"), 
						$partStruct->getRepositoryId())
					&& $authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.modify"), 
						$partStruct->getRepositoryId())
				|| ($authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.convert_rec_struct"), 
						$idManager->getId("edu.middlebury.authorization.root"))
					&& $authZManager->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.modify"), 
						$idManager->getId("edu.middlebury.authorization.root")))))
			{
				$partStructs =& $recordStructure->getPartStructures();
				while ($partStructs->hasNext()) {
					$partStruct =& $partStructs->next();
					$partStructId =& $partStruct->getId();
					if (!array_key_exists($partStructId->getIdString(), $existingPartStructureIds)) {
						printpre("Deleting PartStructure: ".$partStruct->getDisplayName()." Id: ".$partStructId->getIdString());
						$recordStructure->deletePartStructure($partStructId);
						// Remove the old value from the set
						if ($set->isInSet($partStructId))
							$set->removeItem($partStructId);
					}
				}
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
		$recordStructureId =& $this->getRecordStructureId();
		
		return $harmoni->history->getReturnURL(
				"concerto/schema/edit-return/".$recordStructureId->getIdString());
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
