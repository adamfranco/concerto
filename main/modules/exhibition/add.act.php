<?php
/**
 * @package concerto.modules.exhibition
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");

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
		// Check that the user can create an asset here
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
		return _("You are not authorized to create an<em>Exhibition</em> here.");
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
		$t =& $repository->getType();
		$s = $t->getKeyword();
		
		// Instantiate the wizard, then add our steps.
		if($s != "Exhibitions"){
		
			print ("error... you have some repository that's not an exhibition... you shouldn't be here my friend");
		
		}else{
			$wizard =& new Wizard(_("Add new Exhibition to the Exhibitions repository"));
			
			// :: Name and Description ::
			$step =& $wizard->addStep("namedesc", new WizardStep());
			$step->setDisplayName(_("Name &amp; Description"));
			
			// Create the properties.
			$displayNameProp =& $step->addComponent("display_name", new WTextField());
			$displayNameProp->setErrorString(_("A value is required for this field."));
			$displayNameProp->setErrorRule(new WECRegex("[\\w]+"));
			
			$descriptionProp =& $step->addComponent("description", WTextArea::withRowsAndColumns(5,30));
			
			// Create the step text
			ob_start();
			print "\n<h2>"._("Name")."</h2>";
			print "\n"._("The Name for this <em>Exhibition</em>: ");
			print "\n<br />[[display_name]]";
			print "\n<h2>"._("Description")."</h2>";
			print "\n"._("The Description for this <em>Exhibition</em>: ");
			print "\n<br />[[description]]";
			print "\n<div style='width: 400px'> &nbsp; </div>";
			$step->setContent(ob_get_contents());
			ob_end_clean();
			
			// :: Effective/Expiration Dates ::
			$step =& $wizard->addStep("dates", new WizardStep());
			$step->setDisplayName(_("Effective Dates")." ("._("optional").")");
			
			// Create the properties.
			$property =& $step->addComponent("effective_date", new WTextField());
//			$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
			
			$property =& $step->addComponent("expiration_date", new WTextField());
//			$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
			// Create the step text
			ob_start();
			print "\n<h2>"._("Effective Date")."</h2>";
			print "\n"._("The date that this <em>Exhibition</em> becomes effective: ");
			print "\n<br />[[effective_date]]";
			
			print "\n<h2>"._("Expiration Date")."</h2>";
			print "\n"._("The date that this <em>Exhibition</em> expires: ");
			print "\n<br />[[expiration_date]]";
			$step->setContent(ob_get_contents());
			ob_end_clean();
		}  
		return $wizard;
		
	}
	
	/**
	* Save our results. Tearing down and unsetting the Wizard is handled by
	* in {@link runWizard()} and does not need to be implemented here.
	*
	* @param string $cacheName
	* @return boolean TRUE if save was successful and tear-down/cleanup of the
	*              Wizard should ensue.
	* @access public
	* @since 4/28/05
	*/
	function saveWizard ( $cacheName ) {
	   $wizard =& $this->getWizard($cacheName);
	
	   // Make sure we have a valid Repository
	   $idManager =& Services::getService("Id");
	   $repositoryManager =& $this->getRepository();
	   $authZ =& Services::getService("AuthZ");
	   $properties =& $wizard->getProperties();
	
	   // Get the type
	   $assetType =& new HarmoniType('System Exhibitions', 'Concerto', 'Exhibition', 'A new exhibition');
	   $asset =& $repositoryManager->createAsset($properties['namedesc']['display_name'],$properties['namedesc']['description'],$assetType);
	   $assetId =& $asset->getId();
	   $this->_assetId =& $assetId;
	   //	   $content =& new Blob($properties['content']->getValue());
	   // $asset->updateContent($content);
	
		// Update the effective/expiration dates
		if ($properties['dates']['effective_date'])
			$asset->updateEffectiveDate(
				DateAndTime::fromString($properties['dates']['effective_date']));
		if ($properties['dates']['expiration_date'])
			$asset->updateExpirationDate(
				DateAndTime::fromString($properties['dates']['expiration_date']));
		return FALSE; 
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
	 if ($this->_assetId){
	   
	   $returnURL = $harmoni->request->quickURL("asset", "editview", array("collection_id", $repositoryId->getIdString(), "asset_id" => $this->_assetId->getIdString()));
	
	 } else {
	   
	   return $harmoni->request->quickURL("exhibition", "browse", array("collection_id" => $repositoryId->getIdString()));
	 }
	}
}
?>
