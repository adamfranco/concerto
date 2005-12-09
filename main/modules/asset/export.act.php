<?php
/**
 * @since 9/27/05
 * @package concerto.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * This is the export action for an asset
 * 
 * @since 9/27/05
 * @package concerto.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class exportAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/27/05
	 */
	function isAuthorizedToExecute () {
		$harmoni =& Harmoni::instance();
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");

		$harmoni->request->startNamespace('export');

		$return = $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.access"),
			$idManager->getId(RequestContext::value('asset_id')));

		$harmoni->request->endNamespace();
		
		// Check that the user can create an asset here.
		return $return;
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 9/27/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to export this <em>Asset</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 9/28/05
	 */
	function getHeadingText () {
		$harmoni =& Harmoni::Instance();
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");

		$harmoni->request->startNamespace('export');
		
		$repository =& $repositoryManager->getRepository($idManager->getId(
			RequestContext::value('collection_id')));

		$asset =& $repository->getAsset(
			$idManager->getId(RequestContext::value('asset_id')));
		
		$harmoni->request->endNamespace();

		return _("Export the <em>".$asset->getDisplayName()."</em> Asset out of <em>Concerto</em>");
	}

	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 9/27/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::Instance();
		$harmoni->request->startNamespace('export');
		$harmoni->request->passthrough('asset_id');
		$harmoni->request->passthrough('collection_id');

		$centerPane =& $this->getActionRows();

		$cacheName = 'export_asset_wizard_'.
			RequestContext::value('asset_id');
		
		$this->runWizard ( $cacheName, $centerPane );
		$harmoni->request->endNamespace();
	}
		
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 9/28/05
	 */
	function &createWizard () {
		$harmoni =& Harmoni::instance();
		
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		
		$repositoryId =& 
			$idManager->getId(RequestContext::value('collection_id'));
		
		$repository =& $repositoryManager->getRepository($repositoryId);
		$asset =& $repository->getAsset(
			$idManager->getId(RequestContext::value('asset_id')));

		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleWizard::withText(
			"\n<h2>"._("Export Type")."</h2>".
			"\n"._("The depth of the export of this <em>Asset</em>:").
			"\n<br />[[export_type]]".
			"\n<h2>"._("Effective Date")."</h2>".
			"\n"._("The date that this exported <em>Asset</em> becomes effective: ").
			"\n<br />[[effective_date]]".
			"\n<h2>"._("Expiration Date")."</h2>".
			"\n"._("The date that this exported <em>Asset</em> expires: ").
			"\n<br />[[expiration_date]]".
			"<table width='100%' border='0' style='margin-top:20px' >\n" .
			"<tr>\n" .
			"<td align='left' width='50%'>\n" .
			"[[_cancel]]".
			"</td>\n" .
			"<td align='right' width='50%'>\n" .
			"[[_save]]".
			"</td></tr></table>");

		
		// Create the properties.
// 		$fileNameProp =& $wizard->addComponent("filepath", new WTextField());
// 		$fileNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
// 		$fileNameProp->setErrorRule(new WECRegex("[\\w]+"));
// 		$fileNameProp->setValue("/");

		$datefield =& $wizard->addComponent("effective_date", new WTextField());
		$date =& DateAndTime::Now();
		$datefield->setValue($date->asString());		

		$date2field =& $wizard->addComponent("expiration_date", new WTextField());
		$date2 =& $asset->getExpirationDate();
		
		if (is_object($date2))
			$date2field->setValue($date->asString());
		
		$type =& $wizard->addComponent("export_type", new WSelectList());
		$type->setValue("down");
		$type->addOption("this", _("only this node"));
		$type->addOption("down", _("this node and all children"));

		$save =& $wizard->addComponent("_save", 
			WSaveButton::withLabel("Export"));
		$cancel =& $wizard->addComponent("_cancel", new WCancelButton());

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
		
		$properties =& $wizard->getAllValues();
		
		$assetId =& RequestContext::value('asset_id');
		
		$exporter =& new XMLAssetExporter($assetId, $properties['export_type'],
			$properties['effective_date'], $properties['expiration_date']);

		$exporter->export();

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
		$harmoni =& Harmoni::instance();
		
		$harmoni->request->forget('asset_id');
		$harmoni->request->forget('collection_id');
		return $harmoni->request->quickURL("collections", "namebrowse");
	}
}

?>