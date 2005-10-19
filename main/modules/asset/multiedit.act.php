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
class multieditAction 
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
// 		$authZ =& Services::getService("AuthZ");
// 		$idManager =& Services::getService("Id");
// 		return $authZ->isUserAuthorized(
// 					$idManager->getId("edu.middlebury.authorization.modify"), 
// 					$this->getAssetId());
		return true;
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to edit this <em>Asset</em>.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough("collection_id", "asset_id", "assets");
		$centerPane =& $this->getActionRows();
		
		$assetList = RequestContext::value("assets");
		
		$cacheName = 'edit_asset_wizard_'.$assetList;
		
		$this->runWizard ( $cacheName, $centerPane );
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
// 		$asset =& $this->getAsset();
// 		$wizard =& $this->getWizard($cacheName);
// 				
// 		$properties =& $wizard->getAllValues();
// 			
// 		// Update the name and description
// 		$asset->updateDisplayName($properties['namedescstep']['display_name']);
// 		$asset->updateDescription($properties['namedescstep']['description']);
// 		$content =& Blob::withValue($properties['contentstep']['content']);
// 		$asset->updateContent($content);
// 		
// 		
// 		// Update the effective/expiration dates
// 		if ($properties['datestep']['effective_date'])
// 			$asset->updateEffectiveDate(
// 				DateAndTime::fromString($properties['datestep']['effective_date']));
// 		if ($properties['datestep']['expiration_date'])
// 			$asset->updateExpirationDate(
// 				DateAndTime::fromString($properties['datestep']['expiration_date']));
		
		return TRUE;
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
		
		if ($assetIdString = RequestContext::value("asset_id")) {
			return $harmoni->request->quickURL("asset", "browse", 
					array("collection_id" => $repositoryId->getIdString(), 
					"asset_id" => $assetIdString));
		} else {
			return $harmoni->request->quickURL("collection", "browse", 
					array("collection_id" => $repositoryId->getIdString()));
		}
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
		$harmoni =& Harmoni::instance();
	
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();		
		
		// :: Asset Properties ::
		$step =& $wizard->addStep("assetproperties", new WizardStep());
		$step->setDisplayName(_("Basic Properties"));
		
		// Create the properties.
		$displayNameProp =& $step->addComponent("display_name", new WTextField());
// 		$displayNameProp->setValue($asset->getDisplayName());
		$displayNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
		$property =& $step->addComponent("mod_display_name", new WCheckBox());

		
		$descriptionProp =& $step->addComponent("description", new WTextArea());
// 		$descriptionProp->setValue($asset->getDescription());
		$descriptionProp->setRows(3);
		$descriptionProp->setColumns(70);
		
		$property =& $step->addComponent("mod_description", new WCheckBox());
		
		// Create the properties.
		$property =& $step->addComponent("effective_date", new WTextField());
// 		$date =& $asset->getEffectiveDate();
// 		if ($date) {
// 			$date =& $date->asDate();
// 			$property->setValue($date->yyyymmddString());
// 		}

		$property =& $step->addComponent("mod_effective_date", new WCheckBox());
	
		$property =& $step->addComponent("expiration_date", new WTextField());
// 		$date =& $asset->getExpirationDate();
// 		if ($date) {
// 			$date =& $date->asDate();
// 			$property->setValue($date->yyyymmddString());
// 		}

		$property =& $step->addComponent("mod_expiration_date", new WCheckBox());
		
		// Create the step text
		ob_start();
		$style = "
		<style type='text/css'>			
			.edit_table td, th {
				border-top: 1px solid;
				padding: 5px;
				padding-bottom: 20px;
				vertical-align: top;
				text-align: left;
			}
			.edit_table .mod {
				vertical-align: middle;
				text-align: center;
			}
		</style>
		";
		$outputHandler =& $harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead().$style);
		
		print "\n<table class='edit_table' cellspacing='0'>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>";
		print "\n\t\t\t"._("Name");
// 		print "\n"._("The Name for this <em>Asset</em>: ");
		print "\n\t\t</th>";
		print "\n\t\t<td class='mod'>";
		print "\n\t\t\t[[mod_display_name]]";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n\t\t\t[[display_name]]";
		print "\n\t\t<td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>";
		print "\n\t\t\t"._("Description");
// 		print "\n"._("The Description for this <em>Asset</em>: ");
		print "\n\t\t</th>";
		print "\n\t\t<td class='mod'>";
		print "\n\t\t\t[[mod_description]]";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n\t\t\t[[description]]";
		print "\n\t\t<td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>";
		print "\n\t\t\t"._("Effective Date");
// 		print "\n"._("The date that this <em>Asset</em> becomes effective: ");
		print "\n\t\t</th>";
		print "\n\t\t<td class='mod'>";
		print "\n\t\t\t[[mod_effective_date]]";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n\t\t\t[[effective_date]]";
		print "\n\t\t<td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>";
		print "\n\t\t\t"._("Expiration Date");
// 		print "\n"._("The date that this <em>Asset</em> expires: ");
		print "\n\t\t</th>";
		print "\n\t\t<td class='mod'>";
		print "\n\t\t\t[[mod_expiration_date]]";
		print "\n\t\t</td>";
		print "\n\t\t<td>";
		print "\n\t\t\t[[expiration_date]]";
		print "\n\t\t<td>";
		print "\n\t</tr>";
		
		print "\n</table>";
		
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		
		// :: Content ::
		$step =& $wizard->addStep("contentstep", new WizardStep());
		$step->setDisplayName(_("Content")." ("._("optional").")");
		
		$property =& $step->addComponent("content", new WTextArea());
		$property->setRows(20);
		$property->setColumns(70);
// 		$content =& $asset->getContent();
// 		
// 		if ($content->asString())
// 			$property->setValue($content->asString());
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Content")."</h2>";
		print "\n"._("This is an optional place to put content for this <em>Asset</em>. <br />If you would like more structure, you can create new schemas to hold the <em>Asset's</em> data.");
		print "\n<br />[[content]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		
	
		return $wizard;
	}
}