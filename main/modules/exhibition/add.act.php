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
    if (!defined("AZ_ADD_CHILDREN"))
      throwError(new Error("You must define an id for AZ_ADD_CHILDREN", "concerto.exhibition", true));

    // Check that the user can create an asset here
    $authZ =& Services::getService("AuthZ");
    $idManager =& Services::getService("Id");

    return $authZ->isUserAuthorized(
		    $idManager->getId(AZ_ADD_CHILDREN),
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
    $s =& $t->getKeyword();
    
    // Instantiate the wizard, then add our steps.
  if($s != "Exhibitions"){

    print ("error... you have some repository that's not an exhibition... you shouldn't be here my friend");
  
  }else{
    $wizard =& new Wizard(_("Add new Exhibition to the Exhibitions repository"));
       
    // :: Name and Description ::
    $step =& $wizard->createStep(_("Name &amp; Description"));

    // Create the properties.
    $displayNameProp =& $step->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
    $displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");

    $descriptionProp =& $step->createProperty("description", new RegexValidatorRule(".*"));

    // Create the step text
    ob_start();
    print "\n<h2>"._("Name")."</h2>";
    print "\n"._("The Name for this <em>Exhibition</em>: ");
    print "\n<br /><input type='text' name='display_name' value=\"[[display_name]]\" />[[display_name|Error]]";
    print "\n<h2>"._("Description")."</h2>";
    print "\n"._("The Description for this <em>Exhibition</em>: ");
    print "\n<br /><textarea rows='5' cols='30' name='description'>[[description]]</textarea>[[description|Error]]";
    print "\n<div style='width: 400px'> &nbsp; </div>";
    $step->setText(ob_get_contents());
    ob_end_clean();

    // :: Effective/Expiration Dates ::
    $step =& $wizard->createStep(_("Effective Dates")." ("._("optional").")");

    // Create the properties.
    $property =& $step->createProperty("effective_date", new RegexValidatorRule("^(([0-9]{4,8}))?$"));
    $property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");

    $property =& $step->createProperty("expiration_date", new RegexValidatorRule("^(([0-9]{4,8}))?$"));
    $property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
    // Create the step text
    ob_start();
    print "\n<h2>"._("Effective Date")."</h2>";
    print "\n"._("The date that this <em>Exhibition</em> becomes effective: ");
    print "\n<br /><input type='text' name='effective_date' value=\"[[effective_date]]\" />[[effective_date|Error]]";

    print "\n<h2>"._("Expiration Date")."</h2>";
    print "\n"._("The date that this <em>Exhibition</em> expires: ");
    print "\n<br /><input type='text' name='expiration_date' value=\"[[expiration_date]]\" />[[expiration_date|Error]]";
    $step->setText(ob_get_contents());
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
	   $asset =& $repositoryManager->createAsset($properties['display_name']->getValue(),$properties['description']->getValue(),$assetType);
	   $assetId =& $asset->getId();
	   $this->_assetId =& $assetId;
	   //	   $content =& new Blob($properties['content']->getValue());
	   // $asset->updateContent($content);

		// Update the effective/expiration dates
		if ($properties['effective_date']->getValue())
			$asset->updateEffectiveDate(
				DateAndTime::fromString($properties['effective_date']->getValue()));
		if ($properties['expiration_date']->getValue())
			$asset->updateExpirationDate(
				DateAndTime::fromString($properties['expiration_date']->getValue()));
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
       
       $returnURL = MYURL."/asset/editview/".$repositoryId->getIdString()."/".$this->_assetId->getIdString()."/";

     } else {
       
       return MYURL."/exhibition/browse/".$repositoryId->getIdString()."/";
     }
   }
}
?>
