<?php
/**
 * @package concerto.modules.exhibition
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General \
Public License (GPL)
*
* @version $Id$
*/

require_once(MYDIR."/main/library/abstractActions/AssetAction\
.class.php");

/**
 *
 *
 * @package concerto.modules.exhibition
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General \
Public License (GPL)
*
* @version $Id: edit.act.php,v 1.12 2005/04/28 22:13:40 adam\
franco Exp $
*/
class editAction
        extends AssetAction
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
   throwError(new Error("You must define an id for AZ_EDIT", "concerto.exhibition", true));
 if (!defined("AZ_VIEW"))
   throwError(new Error("You must define an id for AZ_VIEW", "concerto.exhibition", true));

 // Check that the user can access this collection
 $authZ =& Services::getService("AuthZ");
 $idManager =& Services::getService("Id");
 return $authZ->isUserAuthorized(
				 $idManager->getId(AZ_EDIT),
				 $this->getAssetId());
  }

  /**
   * Return the "unauthorized" string to pring
   *
   * @return string
   * @access public
   * @since 4/26/05
   */
  function getUnauthorizedMessage () {
    return _("You are not authorized to edit this <em>Exhibition</em>.");
  }

  /**
   * Build the content for this action
   *
   * @return boolean
   * @access public
   * @since 4/26/05
   */
  function buildContent () {
    $centerPane =& $this->getActionRows();
    $assetId =& $this->getAssetId();
                $cacheName = 'edit_asset_wizard_'.$assetId->getIdString();

                $this->runWizard ( $cacheName, $centerPane );
  }

  /**
   * Save our results. Tearing down and unsetting the Wizard is handled by
   * in {@link runWizard()} and does not need to be imp
   * @param string $cacheName
   * @return boolean TRUE if save was successful and tear-down/cleanup of the
   *              Wizard should ensue.
   * @access public
   * @since 4/28/05
   */
  function saveWizard ( $cacheName ) {
    $asset =& $this->getAsset();
    $wizard =& $this->getWizard($cacheName);

    $properties =& $wizard->getProperties();

    // Update the name and description
    $asset->updateDisplayName($properties['display_name']->getValue());
    $asset->updateDescription($properties['description']->getValue());
    $content =& new Blob($properties['content']->getValue());
    $asset->updateContent($content);


    // Update the effective/expiration dates
    if ($properties['effective_date']->getValue())
      $asset->updateEffectiveDate(
				  new Time($properties['effective_date']->getValue()));
    if ($properties['expiration_date']->getValue())
      $asset->updateExpirationDate(
				   new Time($properties['expiration_date']->getValue()));

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
    $assetId =& $this->getAssetId();
    $repositoryId =& $this->getRepositoryId();
                return MYURL."/exhibition/editview/".$repositoryId->getIdString()
		  ."/".$assetId->getIdString()."/";
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
    $harmoni =& $this->getHarmoni();
    $assetId =& $this->getAssetId();
    $asset =& $this->getAsset();
    
    // Instantiate the wizard, then add our steps
    $wizard =& new Wizard(_("Edit Asset"));      
    
    
    // :: Name and Description ::
    $step =& $wizard->createStep(_("Name & Description"));

    // Create the properties.
    $displayNameProp =& $step->createProperty("display_name", new RegexValidatorRule("^[^ ]{1}.*$"));
    $displayNameProp->setDefaultValue($asset->getDisplayName());
    $displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
    $descriptionProp =& $step->createProperty("dscription", new RegexValidatorRule(".*"));
    $descriptionProp->setDefaultValue($asset->getDescription());

    // Create the step text
    ob_start();
    print "\n<h2>"._("Name")."</h2>";
    print "\n"._("The Name for this <em>Exhibition</em>: ");
                print "\n<br /><input type='text' name='display_name' value=\"[[display_name]]\" />[[display_name|Error]]"\
		  ;
                print "\n<h2>"._("Description")."</h2>";
                print "\n"._("The Description for this <em>Exhibition</em>: ");
                print "\n<br /><textarea name='description'>[[description]]</textarea>[[description|Error]]";
                print "\n<div style='width: 400px'> &nbsp; </div>";
                $step->setText(ob_get_contents());
                ob_end_clean();

                // :: Content ::
                $step =& $wizard->createStep(_("Content")." ("._("optional").")");

                $property =& $step->createProperty("content",new RegexValidatorRule(".*"));
                $content =& $asset->getContent();

                if ($content->toString())
		  $property->setDefaultValue($content->toString());

                // Create the step text
                ob_start();
                print "\n<h2>"._("Content")."</h2>";
                print "\n"._("This is an optional place to put content for this <em>Exhibition</em>. <br />If you would like more structure, you can create new schemas to hold the <em>Exhibition's</em> data.");
                print "\n<br /><textarea name='content' cols='50' rows='20'>[[content]]</textarea>[[content|Error]]";
  print "\n<div style='width: 400px'> &nbsp; </div>";
  $step->setText(ob_get_contents());
  ob_end_clean();



  // :: Effective/Expiration Dates ::
  $step =& $wizard->createStep(_("Effective Dates")." ("._("optional").")");

  // Create the properties.
  $property =& $step->createProperty("effective_date", new RegexValidatorRule("^(([0-9]{4,8}))?$"));
  $date =& $asset->getEffectiveDate();
  $property->setDefaultValue($date->getYear()
			     .(($date->getMonth()<10)?"0".intval($date->getMonth()):$date->getMonth())
			     .(($date->getDay()<10)?"0".intval($date->getDay()):$date->getDay()));
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
  
  return $wizard;
  }
	
}

