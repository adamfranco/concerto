<?php
/**
 * @package concerto.modules.asset
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
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class addAction 
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
		// Check that the user can create an asset here.
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		 
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
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
		return _("You are not authorized to create an <em>Asset</em> here.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough("collection_id");
		
		$centerPane =& $this->getActionRows();
		$repositoryId =& $this->getRepositoryId();
		$cacheName = 'add_asset_wizard_'.$repositoryId->getIdString();
		
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
		$repository =& $this->getRepository();
	
		return _("Add Asset to the ")."<em>".$repository->getDisplayName()."</em> "._("Collection");
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
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Name &amp; Description"));
		
		// Create the properties.
		$displayNameProp =& $step->addComponent("display_name", new WTextField());
// 		$displayNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
// 		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
	// 	$displayNameProp->setDefaultValue(_("Default Asset Name"));
//		$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
		
		$descriptionProp =& $step->addComponent("description", WTextArea::withRowsAndColumns(5,30));
	// 	$descriptionProp->setDefaultValue(_("Default Asset description."));
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Asset</em>: ");
		print "\n<br />[[display_name]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Asset</em>: ");
		print "\n<br />[[description]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		
	
		// :: Type Step ::
		$step =& $wizard->addStep("typestep", new WizardStep());
		$step->setDisplayName(_("Type"));
		// Create the properties.
		$selectProperty =& $step->addComponent("type", new WSelectOrNew());
		
		// Default Types
		$defaultTypes = array();
		$defaultTypes[] =& new Type("Asset Types", "edu.middlebury", "Generic Asset", 
						"This is an <em>Asset</em> of unspecified type.");
		$defaultTypes[] =& new Type("Asset Types", "edu.middlebury", "Image", 
						"Assets of this type represent an image.");
		$defaultTypes[] =& new Type("Asset Types", "edu.middlebury", "Document", 
						"Assets of this type represent an non-image document.");
		$defaultTypes[] =& new Type("Asset Types", "edu.middlebury", "Audio", 
						"Assets of this type represent an audio recording.");
		$defaultTypes[] =& new Type("Asset Types", "edu.middlebury", "Video", 
						"Assets of this type represent an video recording.");
		$defaultTypes[] =& new Type("Asset Types", "edu.middlebury", "Container", 
						"Assets of this type primarily serve as containers to hold other Assets, possibly for organizational purposes. Similar to a 'folder' or 'directory' on a filesystem.");
						
		foreach ($defaultTypes as $type) {
			$typeString = Type::typeToString($type);
			$typeKey = urlencode($typeString);
			$selectProperty->addOption($typeKey, $typeString);
		}
		
		// Types in the repository
		$assetTypes =& $repository->getAssetTypes();
		while ($assetTypes->hasNext()) {
			$assetType =& $assetTypes->next();
			$typeKey = urlencode(Type::typeToString($assetType));
			$selectProperty->addOption($typeKey, Type::typeToString($assetType));
		}
		
		$selectProperty->setValue(urlencode(Type::typeToString($defaultTypes[0])));
		
		$newTypeProperty =& $selectProperty->setNewComponent(new WComponentCollection);
		$property =& $newTypeProperty->addComponent("type_domain", new WTextField());
		$property->setStartingDisplayText(_("e.g. Asset Types"));
		
		$property =& $newTypeProperty->addComponent("type_authority", new WTextField());
		$property->setStartingDisplayText(_("e.g. Concerto"));
		
		$property =& $newTypeProperty->addComponent("type_keyword", new WTextField());
		$property->setStartingDisplayText(_("e.g. Generic Asset"));
		
		$property =& $newTypeProperty->addComponent("type_description", WTextArea::withRowsAndColumns(5,30));
		$property->setStartingDisplayText(_("e.g. This is an <em>Asset</em> of unspecified type."));
		
		// create the text for the new type property.
		ob_start();
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
		$newTypeProperty->setContent(ob_get_contents());
		ob_end_clean();
		
		// create the text for the step
		ob_start();
		print "<h2>"._("Type")."</h2>";
		print "\n"._("All <em>Assets</em> have an immutable type. This type can be used to catagorize <em>Assets</em>, but is not necessary.");
		print "\n<br />"._("Select a type here or select <em>* New Value *</em> create a new one:");
		print "\n[[type]]";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
// 		
// 		// :: Content ::
// 		$step =& $wizard->addStep("contentstep", new WizardStep());
// 		$step->setDisplayName(_("Content")." ("._("optional").")");
// 		
// 		$property =& $step->addComponent("content", WTextArea::withRowsAndColumns(20,50));
// 		
// 		// Create the step text
// 		ob_start();
// 		print "\n<h2>"._("Content")."</h2>";
// 		print "\n"._("This is an optional place to put content for this <em>Asset</em>. <br />If you would like more structure, you can create new schemas to hold the <em>Asset's</em> data.");
// 		print "\n<br />[[content]]";
// 		print "\n<div style='width: 400px'> &nbsp; </div>";
// 		$step->setContent(ob_get_contents());
// 		ob_end_clean();
		
		// :: Effective/Expiration Dates ::
		$step =& $wizard->addStep("datestep", new WizardStep());
		$step->setDisplayName(_("Effective Dates")." ("._("optional").")");
		
		// Create the properties.
		$property =& $step->addComponent("effective_date", new WTextField());
	//	$property->setDefaultValue();
//		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
	
		$property =& $step->addComponent("expiration_date", new WTextField());
	//	$property->setDefaultValue();
//		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Effective Date")."</h2>";
		print "\n"._("The date that this <em>Asset</em> becomes effective: ");
		print "\n<br />[[effective_date]]";
		
		print "\n<h2>"._("Expiration Date")."</h2>";
		print "\n"._("The date that this <em>Asset</em> expires: ");
		print "\n<br />[[expiration_date]]";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		
		
		// :: Parent ::
		$step =& $wizard->addStep("parentstep", new WizardStep());
		$step->setDisplayName(_("Parent")." ("._("optional").")");
		
		// Create the properties.
		$property =& $step->addComponent("parent", new WSelectList());
		$harmoni =& Harmoni::instance();
		
		$property->addOption("NONE", _("None"));
		
		$assets =& $repository->getAssets();
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		while ($assets->hasNext()) {
			$asset =& $assets->next();
			$assetId =& $asset->getId();
			if ($authZManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$assetId))
			{
				$property->addOption($assetId->getIdString(), $assetId->getIdString()." - ".$asset->getDisplayName());
			}
		}
		
		if (RequestContext::value('parent'))
			$property->setValue(RequestContext::value('parent'));
		else
			$property->setValue("NONE");
				
		// Create the step text
		ob_start();
		print "\n<h2>"._("Parent <em>Asset</em>")."</h2>";
		print "\n"._("Select one of the <em>Assets</em> below if you wish to make this new asset a child of another asset: ");
		print "\n<br />[[parent]]";
		
		$step->setContent(ob_get_contents());
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
		
		if (!$wizard->validate()) return false;
		
		// Make sure we have a valid Repository
		$idManager =& Services::getService("Id");
		$authZ =& Services::getService("AuthZ");
	
		$repository =& $this->getRepository();
		
		$properties = $wizard->getAllValues();
		
		// First, verify that we chose a parent that we can add children to.
		if (!$properties['parentstep']['parent'] 
			|| $properties['parentstep']['parent'] == 'NONE'
			|| ($parentId =& $idManager->getId($properties['parentstep']['parent'])
				&& $authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.add_children"), $parentId)))
		{
			
			// Get the type from the select if one is specified
			if (is_string($properties['typestep']['type'])) {
				$typeString = urldecode($properties['typestep']['type']);
				$assetType = Type::fromString($typeString);
			} 
			// Otherwise, Generate the type from the specified fields
			else if (is_array($properties['typestep']['type'])
				&& $properties['typestep']['type']['type_domain']
				&& $properties['typestep']['type']['type_authority']
				&& $properties['typestep']['type']['type_keyword'])
			{
				$domain = $properties['typestep']['type']['type_domain'];
				$authority = $properties['typestep']['type']['type_authority'];
				$keyword = $properties['typestep']['type']['type_keyword'];
				$description = $properties['typestep']['type']['type_description'];
				if (!($domain && $authority && $keyword)) {
					$wizard->setStep("typestep");
					return false;
				}
				$assetType = new Type($domain, 
											$authority, 
											$keyword, 
											$description);
			}
			// Lastly, if we don't have a valid type, use the generic one
			else {
				$assetType = new Type("Asset Types", "edu.middlebury", "Generic Asset", 
						"This is an <em>Asset</em> of unspecified type.");
			}
			
			$asset =& $repository->createAsset($properties['namedescstep']['display_name'], 
										$properties['namedescstep']['description'], 
										$assetType);
										
			$assetId =& $asset->getId();
			$this->_assetId =& $assetId;
			
			$content =& Blob::withValue($properties['contentstep']['content']);
			$asset->updateContent($content);
			
			// Update the effective/expiration dates
			if ($properties['datestep']['effective_date'])
				$asset->updateEffectiveDate(
					DateAndTime::fromString($properties['datestep']['effective_date']));
			if ($properties['datestep']['expiration_date'])
				$asset->updateExpirationDate(
					DateAndTime::fromString($properties['datestep']['expiration_date']));
			
			// Add our parent if we have specified one.
			if ($properties['parentstep']['parent'] 
				&& $properties['parentstep']['parent'] != 'NONE') 
			{
				$parentId =& $idManager->getId($properties['parentstep']['parent']);
				$parentAsset =& $repository->getAsset($parentId);
				$parentAsset->addAsset($assetId);
			}
			
			// Log the success or failure
			if (Services::serviceRunning("Logging")) {
				$loggingManager =& Services::getService("Logging");
				$log =& $loggingManager->getLogForWriting("Concerto");
				$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType =& new Type("logging", "edu.middlebury", "Event_Notice",
								"Normal events.");
				
				$item =& new AgentNodeEntryItem("Create Node", "Asset added");
				$item->addNodeId($assetId);
				$item->addNodeId($repository->getId());
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			
			return TRUE;
		} 
		// If we don't have authorization to add to the picked parent, send us back to
		// that step.
		else {
			$wizard->setStep("parentstep");
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
		if ($this->_assetId) 
			return $harmoni->request->quickURL("asset", "edit", array(
				"collection_id" => $repositoryId->getIdString(),
				"assets" => $this->_assetId->getIdString()));
		else
			return $harmoni->request->quickURL("collection", "browse", array(
				"collection_id" => $repositoryId->getIdString()));
	}
}

?>