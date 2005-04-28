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
		// Check for our authorization function definitions
		if (!defined("AZ_EDIT"))
			throwError(new Error("You must define an id for AZ_EDIT", "concerto.collection", true));
		if (!defined("AZ_VIEW"))
			throwError(new Error("You must define an id for AZ_VIEW", "concerto.collection", true));
		
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId(AZ_EDIT), 
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
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return '';
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$centerPane =& $this->getCenterPane();
		$harmoni =& $this->getHarmoni();
		$repositoryId =& $this->getRepositoryId();

		// Create the wizard.
		 if ($_SESSION['create_schema_wizard_'.$repositoryId->getIdString()]) {
			$wizard =& $_SESSION['create_schema_wizard_'.$repositoryId->getIdString()];
		 } else {
		
			$repository =& $this->getRepository();
		
			// Instantiate the wizard, then add our steps.
			$wizard =& new Wizard(_("Create a Schema"));
			$_SESSION['create_schema_wizard_'.$repositoryId->getIdString()] =& $wizard;
			
			// :: Step One ::
			$stepOne =& $wizard->createStep(_("Name & Description"));
			
			
			// Create the properties.
			$displayNameProp =& $stepOne->createProperty("schema_display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
			$displayNameProp->setDefaultValue("");
			$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
			
			$descriptionProp =& $stepOne->createProperty("schema_description", new RegexValidatorRule(".*"));
			$descriptionProp->setDefaultValue("");
			
			$formatProp =& $stepOne->createProperty("format", new RegexValidatorRule(".*"));
			$formatProp->setDefaultValue("Plain Text - UTF-8 encoding");
			
			
			// Create the step text
			ob_start();
			print "\n<h2>"._("Name")."</h2>";
			print "\n"._("The Name for this Schema: ");
			print "\n<br /><input type='text' name='schema_display_name' value=\"[[schema_display_name]]\" />[[schema_display_name|Error]]";
			print "\n<h2>"._("Description")."</h2>";
			print "\n"._("The Description for this Schema: ");
			print "\n<br /><textarea name='schema_description'>[[schema_description]]</textarea>[[schema_description|Error]]";
			print "\n<h2>"._("Format")."</h2>";
			print "\n"._("The format of data that is entered into the fields: ");
			print "\n<br /><em>"._("'Plain Text - ASCII encoding', 'XML', etc.")."</em>";
			print "\n<br /><input type='text' name='format' value=\"[[format]]\" size='25' />[[format|Error]]";
			print "\n<div style='width: 400px'> &nbsp; </div>";
			$stepOne->setText(ob_get_contents());
			ob_end_clean();
				
			
			// :: Add Elements ::
			$elementStep =& $wizard->addStep(new MultiValuedWizardStep(_("Add Elements"), "elements"));
			$_SESSION['create_schema_wizard_'.$repositoryId->getIdString()."element_step"] =& $elementStep;
			
			$property =& $elementStep->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
			$property->setDefaultValue("");
			$property->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
			
			$property =& $elementStep->createProperty("description", new RegexValidatorRule(".*"));
			$property->setDefaultValue("");
			
			$property =& $elementStep->createProperty("type", new RegexValidatorRule(".*"));
			$property->setDefaultValue("Repository/Harmoni/string");
			
			$property =& $elementStep->createProperty("mandatory", new RegexValidatorRule(".*"));
			$property->setDefaultValue("FALSE");
			
			$property =& $elementStep->createProperty("repeatable", new RegexValidatorRule(".*"));
			$property->setDefaultValue("FALSE");
			
			$property =& $elementStep->createProperty("populatedbydr", new RegexValidatorRule(".*"));
			$property->setDefaultValue("FALSE");
			
			// We don't have any PartStructures yet, so we can't get them.
			
			ob_start();
			print "<h2>"._("Add New Elements")."</h2>";
			print "\n<p>"._("If none of the schemata listed below fit your needs, please click the button below to save your changes and create a new schema.")."</p>";
			
			print "\n<table border=\"0\">";
				print "\n<tr><td>";
					print _("DisplayName").": ";
				print "\n</td><td>";
					print "<input type='text' name='display_name' value=\"[[display_name]]\" />[[display_name|Error]]";
				print "\n</td></tr>";
				print "\n<tr><td>";
					print _("Description").": ";
				print "\n</td><td>";
					print "<textarea name=\"description\" rows=\"3\" cols=\"25\">[[description]]</textarea>[[description|Error]]";
				print "\n</td></tr>";
				print "\n<tr><td>";
					print _("Select a Type")."... ";
				print "\n</td><td>";
					print "\n<select name=\"type\">";
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
								$typeString = urlencode($type->getDomain())."/".urlencode($type->getAuthority())."/".urlencode($type->getKeyword());
								print "\n<option value=\"".$typeString."\" [['type'=='".$typeString."'| selected='selected'|]]>";
								print $type->getDomain()." :: ".$type->getAuthority()." :: ".$type->getKeyword();
								print "</option>";
							}
							break;
						}
					}
					print "\n</select>[[type|Error]]";
				print "\n</td></tr>";
		
				print "\n<tr><td>";
					print _("isMandatory? ");
				print "\n</td><td>";
					print "<input type=\"radio\" name='mandatory' value='TRUE' [['mandatory'=='TRUE'| checked='checked'|]] />TRUE / ";
					print "<input type=\"radio\" name='mandatory' value='FALSE' [['mandatory'=='FALSE'| checked='checked'|]] /> FALSE";
				print "\n</td></tr>";
				
				print "\n<tr><td>";
					print _("isRepeatable? ");
				print "\n</td><td>";
					print "<input type=\"radio\" name='repeatable' value='TRUE' [['repeatable'=='TRUE'| checked='checked'|]] />TRUE / ";
					print "<input type=\"radio\" name='repeatable' value='FALSE' [['repeatable'=='FALSE'| checked='checked'|]] /> FALSE";
				print "\n</td></tr>";
				
				print "\n<tr><td>";
					print _("isPopulatedByRepository? ");
				print "\n</td><td>";
					print "<input type=\"radio\" name='populatedbydr' value='TRUE' [['populatedbydr'=='TRUE'| checked='checked'|]] />TRUE / ";
					print "<input type=\"radio\" name='populatedbydr' value='FALSE' [['populatedbydr'=='FALSE'| checked='checked'|]] /> FALSE";
				print "\n</td></tr>";
				
				print "</table>";
			
			print "\n<br />[Buttons]";
			print "\n<hr />";
			print _("Elements Added:");
			print "\n<table>";
			print "[List]\n<tr>";
			print "\n<td valign='top'>[ListButtons]<br />[ListMoveButtons]</td>";
			print "\n<td style='padding-bottom: 20px'>";
			print "\n\t<strong>"._("DisplayName").":</strong> [[display_name]]";
			print "\n\t<br /><strong>"._("Description").":</strong> [[description]]";
			print "\n\t<br /><strong>"._("Type").":</strong> [[type]]";
			print "\n\t<br /><strong>"._("isMandatory").":</strong> [[mandatory]]";
			print "\n\t<br /><strong>"._("isRepeatable").":</strong> [[repeatable]]";
			print "\n\t<br /><strong>"._("isPopulatedByRepository").":</strong> [[populatedbydr]]";
			print "</td>\n</tr>[/List]\n</table>";
		
			$elementStep->setText(ob_get_contents());
			ob_end_clean();
		}
		
		// Prepare the return URL so that we can get back to where we were.
		$currentPathInfo = array();
		for ($i = 3; $i < count($harmoni->pathInfoParts); $i++) {
			$currentPathInfo[] = $harmoni->pathInfoParts[$i];
		}
		$returnURL = MYURL."/".implode("/",$currentPathInfo);
		
		if ($wizard->isSaveRequested()) {
			// If all properties validate then go through the steps nessisary to
			// save the data.
			if ($wizard->updateLastStep()) {
				$properties =& $wizard->getProperties();
				
				$repository =& $this->getRepository();
				
				// Create the info Structure
				$recordStructure =& $repository->createRecordStructure($properties['schema_display_name']->getValue(), 
										$properties['schema_description']->getValue(), 
										$properties['format']->getValue(),
										$properties['schema_display_name']->getValue());
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
					$typeString = urldecode($partStructureProperties[$index]['type']->getValue());
					$typeParts = explode("/", $typeString);
					$type =& new HarmoniType($typeParts[0], $typeParts[1], $typeParts[2], $typeParts[3]);
					$partStructure =& $recordStructure->createPartStructure(
									$partStructureProperties[$index]['display_name']->getValue(),
									$partStructureProperties[$index]['description']->getValue(),
									$type,
									(($partStructureProperties[$index]['mandatory']->getValue())?TRUE:FALSE),
									(($partStructureProperties[$index]['repeatable']->getValue())?TRUE:FALSE),
									(($partStructureProperties[$index]['populatedbydr']->getValue())?TRUE:FALSE)
									);
					
					$partStructureId =& $partStructure->getId();
					// Add the PartStructureId to the set
					if (!$set->isInSet($partStructureId))
						$set->addItem($partStructureId);
				}
				
				// Unset the wizard
				$wizard = NULL;
				unset ($_SESSION['create_schema_wizard_'.$repositoryId->getIdString()]);
				unset ($wizard);
				
				// Head off to editing our new collection.
				header("Location: ".$returnURL."?__skip_to_step=2");
			}
			
		} else if ($wizard->isCancelRequested()) {
			$wizard = NULL;
			unset ($_SESSION['create_schema_wizard_'.$repositoryId->getIdString()]);
			unset ($wizard);
			header("Location: ".$returnURL."?__skip_to_step=2");
			
		}
		
		$wizardLayout =& $wizard->getLayout($harmoni);
		$centerPane->add($wizardLayout, null, null, CENTER, CENTER);
	}
}