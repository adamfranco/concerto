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
		$centerPane =& $this->getActionRows();
		$repositoryId =& $this->getRepositoryId();
		$cacheName = 'add_asset_wizard_'.$repositoryId->getIdString();
		
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
		$wizard =& new Wizard(_("Add Asset to the ")."<em>".$repository->getDisplayName()."</em> "._("Collection"));
		
		// :: Name and Description ::
		$step =& $wizard->createStep(_("Name &amp; Description"));
		
		// Create the properties.
		$displayNameProp =& $step->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
	// 	$displayNameProp->setDefaultValue(_("Default Asset Name"));
		$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
		
		$descriptionProp =& $step->createProperty("description", new RegexValidatorRule(".*"));
	// 	$descriptionProp->setDefaultValue(_("Default Asset description."));
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Asset</em>: ");
		print "\n<br /><input type='text' name='display_name' value=\"[[display_name]]\" />[[display_name|Error]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Asset</em>: ");
		print "\n<br /><textarea rows='5' cols='30' name='description'>[[description]]</textarea>[[description|Error]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setText(ob_get_contents());
		ob_end_clean();
		
		
	
		// :: Type Step ::
		$step =& $wizard->createStep(_("Type"));
		// Create the properties.
		$property =& $step->createProperty("option_type", new RegexValidatorRule(".*"));
		$property->setDefaultValue(_("NONE"));
		
		$property =& $step->createProperty("type_domain", new RegexValidatorRule(".*"));
		$property->setDefaultValue(_("Asset Types"));
		
		$property =& $step->createProperty("type_authority", new RegexValidatorRule(".*"));
		$property->setDefaultValue(_("Concerto"));
		
		$property =& $step->createProperty("type_keyword", new RegexValidatorRule(".*"));
		$property->setDefaultValue(_("Generic Asset"));
		
		$property =& $step->createProperty("type_description", new RegexValidatorRule(".*"));
		$property->setDefaultValue(_("This is an <em>Asset</em> of unspecified type."));
		
		// create the text
		ob_start();
		print "<h2>"._("Type")."</h2>";
		print "\n"._("All <em>Assets</em> have an immutable type. This type can be used to catagorize <em>Assets</em>, but is not necessary.");
		print "\n<br />Select a type here or use the fields below to create a new one:";
		print "\n<select name='option_type'>";
		print "\n\t<option value='NONE' [['option_type'=='NONE'|selected='selected'|]]>Use Fields Below...</option>";
		$assetTypes =& $repository->getAssetTypes();
		while ($assetTypes->hasNext()) {
			$assetType =& $assetTypes->next();
			$typeKey = urlencode($assetType->getDomain()."::".$assetType->getAuthority()."::".$assetType->getKeyword());
			print "\n\t<option value='".$typeKey."' [['option_type'=='".$typeKey."'|selected='selected'|]]>"
				.$assetType->getDomain()."::".$assetType->getAuthority()."::".$assetType->getKeyword()
				."</option>";
		}
		print "\n</select>";
		print "\n<br />";
		print "\n<br />";
		print "\n<table>";
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Domain").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n<input type='text' name='type_domain' value=\"[[type_domain]]\" />";
		print "\n\t\t</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Authority").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n<input type='text' name='type_authority' value=\"[[type_authority]]\" />";
		print "\n\t\t</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Keyword").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n<input type='text' name='type_keyword' value=\"[[type_keyword]]\" />";
		print "\n\t\t</td>\n\t</tr>";
		print "\n\t<tr>\n\t\t<td>";
		print "<strong>"._("Description").": </strong>";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n<textarea name='type_description' rows='5' cols='30'>[[type_description]]</textarea>";
		print "\n\t\t</td>\n\t</tr>";
		print "\n</table>";
		$step->setText(ob_get_contents());
		ob_end_clean();
		
		
		// :: Content ::
		$step =& $wizard->createStep(_("Content")." ("._("optional").")");
		
		$property =& $step->createProperty("content", new RegexValidatorRule(".*"));
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Content")."</h2>";
		print "\n"._("This is an optional place to put content for this <em>Asset</em>. <br />If you would like more structure, you can create new schemas to hold the <em>Asset's</em> data.");
		print "\n<br /><textarea name='content' cols='50' rows='20'>[[content]]</textarea>[[content|Error]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setText(ob_get_contents());
		ob_end_clean();
		
		// :: Effective/Expiration Dates ::
		$step =& $wizard->createStep(_("Effective Dates")." ("._("optional").")");
		
		// Create the properties.
		$property =& $step->createProperty("effective_date", new RegexValidatorRule("^(([0-9]{4,8}))?$"));
	//	$property->setDefaultValue();
		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
	
		$property =& $step->createProperty("expiration_date", new RegexValidatorRule("^(([0-9]{4,8}))?$"));
	//	$property->setDefaultValue();
		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Effective Date")."</h2>";
		print "\n"._("The date that this <em>Asset</em> becomes effective: ");
		print "\n<br /><input type='text' name='effective_date' value=\"[[effective_date]]\" />[[effective_date|Error]]";
		
		print "\n<h2>"._("Expiration Date")."</h2>";
		print "\n"._("The date that this <em>Asset</em> expires: ");
		print "\n<br /><input type='text' name='expiration_date' value=\"[[expiration_date]]\" />[[expiration_date|Error]]";
		$step->setText(ob_get_contents());
		ob_end_clean();
		
		
		
		// :: Parent ::
		$step =& $wizard->createStep(_("Parent")." ("._("optional").")");
		
		// Create the properties.
		$property =& $step->createProperty("parent", new AlwaysTrueValidatorRule);
		$harmoni =& Harmoni::instance();
		
		if (RequestContext::value('parent'))
			$property->setDefaultValue(RequestContext::value('parent'));
		else
			$property->setDefaultValue("NONE");
				
		// Create the step text
		ob_start();
		print "\n<h2>"._("Parent <em>Asset</em>")."</h2>";
		print "\n"._("Select one of the <em>Assets</em> below if you wish to make this new asset a child of another asset: ");
		print "\n<br /><select name='parent'>";
		print "\n\t<option value='NONE' [['parent'=='NONE'|selected='selected'|]]>None</option>";
		$assets =& $repository->getAssets();
		while ($assets->hasNext()) {
			$asset =& $assets->next();
			$assetId =& $asset->getId();
			print "\n\t<option value='".$assetId->getIdString()."' [['parent'=='".$assetId->getIdString()."'|selected='selected'|]]>Id: ".$assetId->getIdString()." - ".$asset->getDisplayName()."</option>";
		}
			
		print "\n</select>";
		
		$step->setText(ob_get_contents());
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
		
		// Make sure we have a valid Repository
		$idManager =& Services::getService("Id");
		$authZ =& Services::getService("AuthZ");
	
		$repository =& $this->getRepository();
		
		$properties =& $wizard->getProperties();
		
		// First, verify that we chose a parent that we can add children to.
		if (!$properties['parent']->getValue() 
			|| $properties['parent']->getValue() == 'NONE'
			|| ($parentId =& $idManager->getId($properties['parent']->getValue())
				&& $authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.add_children"), $parentId)))
		{
			
			// Get the type from the select if one is specified
			if ($properties['option_type']->getValue() != 'NONE') {
				$typeString = urldecode($properties['option_type']->getValue());
				$typeparts = explode("::", $typeString);
				$assetType = new HarmoniType($typeparts[0], $typeparts[1], $typeparts[2]);
			} 
			// Otherwise, Generate the type from the specified fields
			else {
				$assetType = new HarmoniType($properties['type_domain']->getValue(), 
											$properties['type_authority']->getValue(), 
											$properties['type_keyword']->getValue(), 
											$properties['type_description']->getValue());
			}
			
			$asset =& $repository->createAsset($properties['display_name']->getValue(), 
										$properties['description']->getValue(), 
										$assetType);
										
			$assetId =& $asset->getId();
			$this->_assetId =& $assetId;
			
			$content =& new Blob($properties['content']->getValue());
			$asset->updateContent($content);
			
			// Update the effective/expiration dates
			if ($properties['effective_date']->getValue())
				$asset->updateEffectiveDate(
					DateAndTime::fromString($properties['effective_date']->getValue()));
			if ($properties['expiration_date']->getValue())
				$asset->updateExpirationDate(
					DateAndTime::fromString($properties['expiration_date']->getValue()));
			
			// Add our parent if we have specified one.
			if ($properties['parent']->getValue() 
				&& $properties['parent']->getValue() != 'NONE') 
			{
				$parentId =& $idManager->getId($properties['parent']->getValue());
				$parentAsset =& $repository->getAsset($parentId);
				$parentAsset->addAsset($assetId);
			}
			
			return TRUE;
		} 
		// If we don't have authorization to add to the picked parent, send us back to
		// that step.
		else {
			$wizzard->skipToStep(5);
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
			return $harmoni->request->quickURL("asset", "editview", array(
				"collection_id" => $repositoryId->getIdString(),
				"asset_id" => $this->_assetId->getIdString()));
		else
			return $harmoni->request->quickURL("collection", "browse", array(
				"collection_id" => $repositoryId->getIdString()));
	}
}

?>