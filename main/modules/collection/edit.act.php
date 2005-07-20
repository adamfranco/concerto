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
		
		// Move to Schema creation if that button is pressed.
		if (RequestContext::value('create_schema') && $this->saveWizard($cacheName)) {
			$this->closeWizard($cacheName);
			$harmoni =& Harmoni::instance();
			RequestContext::locationHeader($harmoni->request->quickURL("schema", "create", array(
					"collection_id" => $repositoryId->getIdString())));
			return TRUE;
		}
		
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
		$repository =& $this->getRepository();
		$repositoryId =& $this->getRepositoryId();
		$harmoni =& Harmoni::instance();
		
		$harmoni->history->markReturnURL(
			"concerto/collection/edit/".$repositoryId->getIdString());
	
		// Instantiate the wizard, then add our steps.
		$wizard =& new Wizard(_("Edit a Collection"));
		
		// :: Step One ::
		$stepOne =& $wizard->createStep(_("Name &amp; Description"));
		
		
		// Create the step text
		ob_start();
		
		$fieldname = RequestContext::name('display_name');
		$displayNameProp =& $stepOne->createProperty($fieldname, new RegexValidatorRule("^[^ ]{1}.*$"));
		$displayNameProp->setDefaultValue($repository->getDisplayName());
		$displayNameProp->setErrorString(" <span style='color: #f00'>* "
			._("The name must not start with a space.")."</span>");
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Collection</em>: ");
		print "\n<br /><input type='text' name='$fieldname' value=\"[[$fieldname]]\" />[[$fieldname|Error]]";
		
		
		$fieldname = RequestContext::name('description');
		$descriptionProp =& $stepOne->createProperty($fieldname, new RegexValidatorRule(".*"));
		$descriptionProp->setDefaultValue($repository->getDescription());
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Collection</em>: ");
		print "\n<br /><textarea name='$fieldname' rows='5' cols='30'>[[$fieldname]]</textarea>[[$fieldname|Error]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$stepOne->setText(ob_get_contents());
		ob_end_clean();
		
		
		// :: Schema Selection ::
		$selectStep =& $wizard->createStep(_("Schema Selection"));
		
		// get an iterator of all RecordStructures
		$recordStructures =& $repository->getRecordStructures();
		$setManager =& Services::getService("Sets");
		$set =& $setManager->getSet($repositoryId);
		
		ob_start();
		print "<h2>"._("Select Cataloging Schemata")."</h2>";
		print "\n<p>"._("Select which cataloging schemata you wish to appear during <em>Asset</em> creation and editing. <em>Assets</em> can hold data in any of the schemata, but only the ones selected here will be availible when adding new data.")."</p>";
		print "\n<p>"._("If none of the schemata listed below fit your needs, please click the button below to save your changes and create a new schema.")."</p>";
		print "\n<input type='submit' name='create_schema' value='"._("Save Changes and Create a new Schema")."' />";
	
		
		
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
		
		$recordStructures =& $repository->getRecordStructures();
		while ($recordStructures->hasNext()) {
			$recordStructure =& $recordStructures->next();
			$recordStructureId =& $recordStructure->getId();
			
			// Create the properties.
			// 'in set' property
			$fieldname = RequestContext::name("schema_".$recordStructureId->getIdString());
			$property =& $selectStep->createProperty($fieldname, new RegexValidatorRule(".*"), FALSE);
			if ($set->isInSet($recordStructureId))
				$property->setDefaultValue(1);
			else
				$property->setDefaultValue(0);
			
			// Order property
			$orderFieldname = RequestContext::name("schema_".$recordStructureId->getIdString()."_position");
			$property =& $selectStep->createProperty($orderFieldname, new RegexValidatorRule(".*"), FALSE);
			if ($set->isInSet($recordStructureId))
				$property->setDefaultValue($set->getPosition($recordStructureId)+1);
			else
				$property->setDefaultValue(0);
			
			print "\n<tr><td valign='top'>";
			print "\n\t<input type='checkbox' name='$fieldname' value='1' [['$fieldname' == TRUE|checked='checked'|]] />";
			print "\n\t<strong>".$recordStructure->getDisplayName()."</strong>";
			print "\n</td><td valign='top'>\n\t<em>".$recordStructure->getDescription()."</em>";
			print " <a href='";
			print $harmoni->request->quickURL("schema", "view", array(
						"collection_id" => $repositoryId->getIdString(),
						"recordstructure_id" => $recordStructureId->getIdString(),
						"__skip_to_step" => 2));
			print "'>more...</a>";
			print "\n</td><td valign='top'>";
			
			print "\n\t<select name='$orderFieldname'>";
			for ($i=0; $i <= $numRecordStructures; $i++) {
				print "\n\t\t<option value='$i' [['$orderFieldname' == '$i'|selected='selected'|]]>".(($i)?$i:"")."</option>";
			}
			print "\n\t</select>";
			
			print "\n</td></tr>";
		}
		print "\n</table>";
		
		$selectStep->setText(ob_get_contents());
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
		if ($wizard->updateLastStep()) {
			$properties =& $wizard->getProperties();
	// 		print "Now Saving: ";
	// 		printpre($properties);
			
			// Save the Repository
			$id =& $this->getRepositoryId();
			$repository =& $this->getRepository();
			
			$repository->updateDisplayName($properties['display_name']->getValue());
			$repository->updateDescription($properties['description']->getValue());
			
			
		// Save the Schema settings.
			
			// Get the set for this Repository
			$setManager =& Services::getService("Sets");
			$set =& $setManager->getSet($id);
			
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
				$fieldName = RequestContext::name("schema_".$recordStructureId->getIdString());
				if ($properties[$fieldName]->getValue()) {
					if (!$set->isInSet($recordStructureId))
						$set->addItem($recordStructureId);
					if ($position = $properties[$fieldName."_position"]->getValue())
						$positions[$position-1] =& $recordStructureId;
					
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