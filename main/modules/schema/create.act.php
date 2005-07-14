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
	
		// Instantiate the wizard, then add our steps.
		$wizard =& new Wizard(_("Create a Schema"));
		
		// :: Step One ::
		$stepOne =& $wizard->createStep(_("Name & Description"));
		
		
		// Create the properties.
		$displayNameProp =& $stepOne->createProperty(
			RequestContext::name("schema_display_name"), 
			new RegexValidatorRule("^[^ ]{1}.*$"));
		$displayNameProp->setDefaultValue("");
		$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
		
		$descriptionProp =& $stepOne->createProperty(
			RequestContext::name("schema_description"), 
			new RegexValidatorRule(".*"));
		$descriptionProp->setDefaultValue("");
		
		$formatProp =& $stepOne->createProperty(
			RequestContext::name("format"),
			new RegexValidatorRule(".*"));
		$formatProp->setDefaultValue("Plain Text - UTF-8 encoding");
		
		
		// Create the step text
		ob_start();
		
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this Schema: ");
		$fieldName = RequestContext::name('schema_display_name');
		print "\n<br /><input type='text' name='$fieldName' value=\"[[$fieldName]]\" />[[$fieldName|Error]]";
		
		
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this Schema: ");
		$fieldName = RequestContext::name('schema_description');
		print "\n<br /><textarea name='$fieldName'>[[$fieldName]]</textarea>[[$fieldName|Error]]";
		
		
		print "\n<h2>"._("Format")."</h2>";
		print "\n"._("The format of data that is entered into the fields: ");
		print "\n<br /><em>"._("'Plain Text - ASCII encoding', 'XML', etc.")."</em>";
		$fieldName = RequestContext::name('format');
		print "\n<br /><input type='text' name='$fieldName' value=\"[[$fieldName]]\" size='25' />[[$fieldName|Error]]";
		
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$stepOne->setText(ob_get_contents());
		ob_end_clean();
			
		
		// :: Add Elements ::
		$elementStep =& $wizard->addStep(new MultiValuedWizardStep(_("Add Elements"), "elements"));
		
		$property =& $elementStep->createProperty(
			RequestContext::name("display_name"), 
			new RegexValidatorRule("^[^ ]{1}.*$"));
		$property->setDefaultValue("");
		$property->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
		
		$property =& $elementStep->createProperty(
			RequestContext::name("description"), 
			new RegexValidatorRule(".*"));
		$property->setDefaultValue("");
		
		$property =& $elementStep->createProperty(
			RequestContext::name("type"), 
			new RegexValidatorRule(".*"));
		$defaultType =& new Type ("Repository", "Harmoni", "string");
		$property->setDefaultValue(HarmoniType::typeToString($defaultType));
		
		$property =& $elementStep->createProperty(
			RequestContext::name("mandatory"), 
			new RegexValidatorRule(".*"));
		$property->setDefaultValue("FALSE");
		
		$property =& $elementStep->createProperty(
			RequestContext::name("repeatable"), 
			new RegexValidatorRule(".*"));
		$property->setDefaultValue("FALSE");
		
		$property =& $elementStep->createProperty(
			RequestContext::name("populatedbydr"), 
			new RegexValidatorRule(".*"));
		$property->setDefaultValue("FALSE");
		
		// We don't have any PartStructures yet, so we can't get them.
		
		ob_start();
		print "<h2>"._("Add New Elements")."</h2>";
		print "\n<p>"._("If none of the schemata listed below fit your needs, please click the button below to save your changes and create a new schema.")."</p>";
		
		print "\n<table border=\"0\">";
			
			print "\n<tr><td>";
				print _("DisplayName").": ";
			print "\n</td><td>";
				$fieldName = RequestContext::name('display_name');
				print "<input type='text' name='$fieldName' value=\"[[$fieldName]]\" />[[$fieldName|Error]]";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("Description").": ";
			print "\n</td><td>";
				$fieldName = RequestContext::name('description');
				print "<textarea name=\"$fieldName\" rows=\"3\" cols=\"25\">[[$fieldName]]</textarea>[[$fieldName|Error]]";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("Select a Type")."... ";
			print "\n</td><td>";
				$fieldName = RequestContext::name('type');
				print "\n<select name=\"$fieldName\">";
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
							print "\n<option value=\"".HarmoniType::typeToString($type)."\" [['$fieldName'=='".$typeString."'| selected='selected'|]]>";
							print HarmoniType::typeToString($type, " :: ");
							print "</option>";
						}
						break;
					}
				}
				print "\n</select>[[$fieldName|Error]]";
			print "\n</td></tr>";
	
			print "\n<tr><td>";
				print _("isMandatory? ");
			print "\n</td><td>";
				$fieldName = RequestContext::name('mandatory');
				print "<input type=\"radio\" name='$fieldName' value='TRUE' [['$fieldName'=='TRUE'| checked='checked'|]] />TRUE / ";
				print "<input type=\"radio\" name='$fieldName' value='FALSE' [['$fieldName'=='FALSE'| checked='checked'|]] /> FALSE";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("isRepeatable? ");
			print "\n</td><td>";
				$fieldName = RequestContext::name('repeatable');
				print "<input type=\"radio\" name='$fieldName' value='TRUE' [['$fieldName'=='TRUE'| checked='checked'|]] />TRUE / ";
				print "<input type=\"radio\" name='$fieldName' value='FALSE' [['$fieldName'=='FALSE'| checked='checked'|]] /> FALSE";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("isPopulatedByRepository? ");
			print "\n</td><td>";
				$fieldName = RequestContext::name('populatedbydr');
				print "<input type=\"radio\" name='$fieldName' value='TRUE' [['$fieldName'=='TRUE'| checked='checked'|]] />TRUE / ";
				print "<input type=\"radio\" name='$fieldName' value='FALSE' [['$fieldName'=='FALSE'| checked='checked'|]] /> FALSE";
			print "\n</td></tr>";
			
			print "</table>";
		
		print "\n<br />[Buttons]";
		print "\n<hr />";
		print _("Elements Added:");
		print "\n<table>";
		print "[List]\n<tr>";
		print "\n<td valign='top'>[ListButtons]<br />[ListMoveButtons]</td>";
		print "\n<td style='padding-bottom: 20px'>";
		print "\n\t<strong>"._("DisplayName").":</strong> [[".RequestContext::name('display_name')."]]";
		print "\n\t<br /><strong>"._("Description").":</strong> [[".RequestContext::name('description')."]]";
		print "\n\t<br /><strong>"._("Type").":</strong> [[".RequestContext::name('type')."]]";
		print "\n\t<br /><strong>"._("isMandatory").":</strong> [[".RequestContext::name('mandatory')."]]";
		print "\n\t<br /><strong>"._("isRepeatable").":</strong> [[".RequestContext::name('repeatable')."]]";
		print "\n\t<br /><strong>"._("isPopulatedByRepository").":</strong> [[".RequestContext::name('populatedbydr')."]]";
		print "</td>\n</tr>[/List]\n</table>";
	
		$elementStep->setText(ob_get_contents());
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
			
			$repository =& $this->getRepository();
			
			// Create the info Structure
			$recordStructure =& $repository->createRecordStructure($properties[RequestContext::name('schema_display_name')]->getValue(), 
									$properties[RequestContext::name('schema_description')]->getValue(), 
									$properties[RequestContext::name('format')]->getValue(),
									$properties[RequestContext::name('schema_display_name')]->getValue());
			Debug::printAll();
			$recordStructureId =& $recordStructure->getId();
			
			// Create a set for the RecordStructure
			$setManager =& Services::getService("Sets");
			$set =& $setManager->getSet($recordStructureId);
			// Store up the positions for later setting after all of the ids have
			// been added to the set and we can do checking to make sure that 
			// the specified positions are valid.
			$positions = array();
									
			// Create the PartStructures
			$partStructureProperties =& $properties['elements'];
			foreach (array_keys($partStructureProperties) as $index) {
				$type =& HarmoniType::stringToType(urldecode(
					$partStructureProperties[$index][RequestContext::name('type')]->getValue()));
				$partStructure =& $recordStructure->createPartStructure(
								$partStructureProperties[$index][RequestContext::name('display_name')]->getValue(),
								$partStructureProperties[$index][RequestContext::name('description')]->getValue(),
								$type,
								(($partStructureProperties[$index][RequestContext::name('mandatory')]->getValue())?TRUE:FALSE),
								(($partStructureProperties[$index][RequestContext::name('repeatable')]->getValue())?TRUE:FALSE),
								(($partStructureProperties[$index][RequestContext::name('populatedbydr')]->getValue())?TRUE:FALSE)
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
			array("__skip_to_step" => 2));
	}
}
