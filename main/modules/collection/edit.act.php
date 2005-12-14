<?php
/**
 * @package concerto.modules.collection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

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
class editAction 
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
		$cacheName = 'edit_collection_wizard_'.$repositoryId->getIdString();
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
		return _("Edit a Collection");
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
		$repositoryId =& $this->getRepositoryId();
		$harmoni =& Harmoni::instance();
		
		$harmoni->history->markReturnURL(
			$harmoni->request->quickURL("collection", "edit", array(
					"collection_id" => $repositoryId->getIdString())));
	
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Step One ::
		$stepOne =& $wizard->addStep("namedesc", new WizardStep());
		$stepOne->setDisplayName(_("Name &amp; Description"));
		
		// Create the step text
		ob_start();
		
		$displayNameProp =& $stepOne->addComponent("display_name", new WTextField());
		$displayNameProp->setValue($repository->getDisplayName());
		$displayNameProp->setErrorText(_("A value for this field is required."));
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));

		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Collection</em>: ");
		print "\n<br />[[display_name]]";
		
		
		$fieldname = RequestContext::name('description');
		$descriptionProp =& $stepOne->addComponent("description", WTextArea::withRowsAndColumns(3,50));
		$descriptionProp->setValue($repository->getDescription());
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Collection</em>: ");
		print "\n<br />[[description]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$stepOne->setContent(ob_get_contents());
		ob_end_clean();
		
		
		// :: Schema Selection ::
		$selectStep =& $wizard->addStep("schema", new WizardStep());
		$selectStep->setDisplayName(_("Schema Selection"));
		
		// get an iterator of all RecordStructures
		$recordStructures =& $repository->getRecordStructures();
		$setManager =& Services::getService("Sets");
		$set =& $setManager->getPersistentSet($repositoryId);
		
		ob_start();
		$fieldname = RequestContext::name('create_schema');
		$selectStep->addComponent("_create_schema", WSaveButton::withLabel(_("Save Changes and Create a New Schema")));
		
		print "<h2>"._("Select Cataloging Schemata")."</h2>";
		print "\n<p>"._("Select which cataloging schemata you wish to appear during <em>Asset</em> creation and editing. <em>Assets</em> can hold data in any of the schemata, but only the ones selected here will be availible when adding new data.")."</p>";
		print "\n<p>"._("If none of the schemata listed below fit your needs, please click the button below to save your changes and create a new schema.")."</p>";
		print "\n[[_create_schema]]";
	
		
		
		print "\n<br /><table border='1'>";
		print "\n\t<tr>";
		print "\n\t<th>"._("Display Name")."</th>";
		print "\n\t<th>"._("Description")."</th>";
		print "\n\t<th>"._("Order/Position")."</th>";
		print "\n\t</tr>";
		
		// Get the number of info structures
		$numRecordStructures = 0;
		while ($recordStructures->hasNext()) {
			$recordStructure =& $recordStructures->next();
			$numRecordStructures++;
		}
		
		$idManager =& Services::getService("Id");
		$assetContentStructureId =& $idManager->getId("edu.middlebury.harmoni.repository.asset_content");
		$recordStructures =& $repository->getRecordStructures();
		while ($recordStructures->hasNext()) {
			$recordStructure =& $recordStructures->next();
			$recordStructureId =& $recordStructure->getId();
			
			// Don't list the asset Content structure.
			if ($recordStructureId->isEqual($assetContentStructureId))
				continue;
			
			// Create the properties.
			// 'in set' property
			$fieldname = "schema_".str_replace(".","__",$recordStructureId->getIdString());
			$property =& $selectStep->addComponent($fieldname, new WCheckBox());
			if ($set->isInSet($recordStructureId))
				$property->setChecked(true);
			else
				$property->setChecked(false);
				
			$property->setLabel($recordStructure->getDisplayName());
			$property->setStyle("font-weight: 900;");
			
			// Order property
			$orderFieldName = $fieldname."_position";
			$property =& $selectStep->addComponent($orderFieldName, new WSelectList());
			if ($set->isInSet($recordStructureId))
				$property->setValue($set->getPosition($recordStructureId)+1);
			else
				$property->setValue(0);
			
			print "\n<tr><td valign='top'>";
			print "\n\t[[$fieldname]]";
//			print "\n\t<strong>".a."</strong>";
			$description =& HtmlString::withValue($recordStructure->getDescription());
			$description->trim(100);	// trim to 100 words
			print "\n</td><td valign='top'>\n\t<div style='font-style: italics'>".$description->asString()."</div>";
			$harmoni->history->markReturnURL(
				"concerto/collection/edit/".$repositoryId->getIdString());
			print " <a href='";
			print $harmoni->request->quickURL("schema", "view", array(
						"collection_id" => $repositoryId->getIdString(),
						"recordstructure_id" => $recordStructureId->getIdString(),
						"__skip_to_step" => 2));
			print "'>more...</a>";
			print "\n</td><td valign='top'>";
			
			print "\n\t[[$orderFieldName]]";
			for ($i=0; $i <= $numRecordStructures; $i++) {
//				print "\n\t\t<option value='$i' [['$orderFieldname' == '$i'|selected='selected'|]]>".(($i)?$i:"")."</option>";
				$property->addOption($i, $i?$i:"");
			}
			
			print "\n</td></tr>";
		}
		print "\n</table>";
		
		$selectStep->setContent(ob_get_contents());
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
	// 		print "Now Saving: ";
	//		printpre($properties);
			
			// Save the Repository
			$id =& $this->getRepositoryId();
			$repository =& $this->getRepository();
			
			$repository->updateDisplayName($properties['namedesc']['display_name']);
			$repository->updateDescription($properties['namedesc']['description']);
			
			
		// Save the Schema settings.
			
			// Get the set for this Repository
			$setManager =& Services::getService("Sets");
			$set =& $setManager->getPersistentSet($id);
			
			// get an iterator of all RecordStructures
			$recordStructures =& $repository->getRecordStructures();
			
			// Store up the positions for later setting after all of the ids have
			// been added to the set and we can do checking to make sure that 
			// the specified positions are valid.
			$positions = array();
			$existingStructures = array();
			$numStructures = 0;
			
			// Go through each RecordStructure
			while ($recordStructures->hasNext()) {
				$recordStructure =& $recordStructures->next();
				$recordStructureId =& $recordStructure->getId();
				
				// If the box is checked, make sure that the ID is in the set
				$fieldName = "schema_".str_replace(".","__",$recordStructureId->getIdString());
				if ($properties['schema'][$fieldName]) {
					if (!$set->isInSet($recordStructureId))
						$set->addItem($recordStructureId);
					if ($position = $properties['schema'][$fieldName."_position"])
						$positions[$position-1] = $recordStructureId;
					
					// Store some info so that we can check that all structures are valid.
					$existingStructures[] = $recordStructureId->getIdString();
					$numStructures++;
				}
				// Otherwise, remove the ID from the set.
				else {
					if ($set->isInSet($recordStructureId))
						$set->removeItem($recordStructureId);
				}
			}
			
			// Go through the positions and set them all.
			ksort ($positions);
			$countPositions = $set->count();
			foreach (array_keys($positions) as $position) {
				if ($position < 0 || $position >= $countPositions) {
					// move to the last position
					$set->moveToPosition($positions[$position], $countPositions-1);
				} else {
					$set->moveToPosition($positions[$position], $position);
				}
			}
			
			// Remove any RecordStructures from the set that may have been removed/
			// made-not-availible by some other application.
			if ($numStructures != $set->count()) {
				$set->reset();
				while($set->hasNext()) {
					$id =& $set->next();
					if (!in_array($id->getIdString(), $existingStructures))
						$set->removeItem($id);
				}
			}
			
			// Move to Schema creation if that button is pressed.
			if ($properties['schema']['_create_schema']) {
				$this->closeWizard($cacheName);
				$harmoni =& Harmoni::instance();
				RequestContext::locationHeader($harmoni->request->quickURL("schema", "create", array(
						"collection_id" => $id->getIdString())));
				exit(0);
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
		$repositoryId =& $this->getRepositoryId();
		$harmoni =& Harmoni::instance();
		return $harmoni->request->quickURL("collection", "browse", array("collection_id" => $repositoryId->getIdString()));
	}
}