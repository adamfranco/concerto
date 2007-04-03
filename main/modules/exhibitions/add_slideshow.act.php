<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."/utilities/StatusStars.class.php");

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
class add_slideshowAction 
	extends MainWindowAction
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
			$idManager->getId(RequestContext::value('exhibition_id')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create a SlideShow in this <em>Exhibition</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$asset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
		return _("Add a SlideShow to the")." <em>".$asset->getDisplayName()."</em> "._("Exhibition");
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
		$harmoni->request->passthrough("exhibition_id");
		
		$actionRows =& $this->getActionRows();
		
		$idManager =& Services::getService("Id");
		$exhibitionAssetId =& $idManager->getId(RequestContext::value('exhibition_id'));
		
		$cacheName = 'add_slideshow_wizard_'.$exhibitionAssetId->getIdString();
		
		$this->runWizard ( $cacheName, $actionRows );
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
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$exhibitionAsset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Title &amp; Description"));
		
		// Create the properties.
		$displayNameProp =& $step->addComponent("display_name", new WTextField());
		$displayNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
	// 	$displayNameProp->setDefaultValue(_("Default Asset Name"));
//		$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
		
		$descriptionProp =& $step->addComponent("description", WTextArea::withRowsAndColumns(5,30));
	// 	$descriptionProp->setDefaultValue(_("Default Asset description."));
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Title")."</h2>";
		print "\n"._("The title of this <em>SlideShow</em>: ");
		print "\n<br />[[display_name]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("A description of this <em>SlideShow</em>: ");
		print "\n<br />[[description]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		
		
		// :: Slides ::
		$slideStep =& $wizard->addStep("slidestep",new WizardStep());
		$slideStep->setDisplayName(_("Slides"));
		
		$multField =& $slideStep->addComponent("slides", 
						new SlideOrderedRepeatableComponentCollection());
		$multField->setStartingNumber(0);
		$multField->setRemoveLabel(_("Remove Slide"));
		
		$property =& $multField->addComponent(
			"title", 
			new WTextField());
		$property->setSize(20);
		
		$property =& $multField->addComponent(
			"caption", 
			WTextArea::withRowsAndColumns(5, 30));
		
		
		
		$property =& $multField->addComponent(
			"text_position", 
			new WSelectList());
		$property->setValue("right");
		
		$property->addOption("right", _("text on right"));
		$property->addOption("left", _("text on left"));
		$property->addOption("bottom", _("text on bottom"));
		$property->addOption("top", _("text on top"));
		$property->addOption("none", _("no text (media-only)"));
		$property->addOption("center", _("text centered (no-media)"));
		
		
		$property =& $multField->addComponent(
			"show_target_metadata", 
			new WCheckBox());
		$property->setChecked(false);
 		$property->setLabel(_("Display metadata from media?"));
		
		ob_start();
		print "\n<table border=\"0\">";
			
			print "\n<tr><td>";
				print _("Title").": ";
			print "\n</td><td>";
				print "[[title]]";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("Caption").": ";
			print "\n</td><td>";
				print "[[caption]]";
			print "\n</td></tr>";
			
			print "\n<tr><td>";
				print _("Text Position").": ";
			print "\n</td><td>";
				print "[[text_position]]";
			print "\n</td></tr>";
	
			print "\n<tr><td>";
				print "[[show_target_metadata]]";
			print "\n</td><td>";
			print "\n</td></tr>";
			
			print "</table>";
		
		
	
		$multField->setElementLayout(ob_get_contents());
		ob_end_clean();
		
		ob_start();
		print "<h2>"._("Slides")."</h2>";
		print "[[slides]]";
		$slideStep->setContent(ob_get_contents());
		ob_end_clean();		
		
		
		
		// :: Effective/Expiration Dates ::
// 		$step =& $wizard->addStep("datestep", new WizardStep());
// 		$step->setDisplayName(_("Effective Dates")." ("._("optional").")");
// 		
// 		// Create the properties.
// 		$property =& $step->addComponent("effective_date", new WTextField());
// 	//	$property->setDefaultValue();
// //		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
// 	
// 		$property =& $step->addComponent("expiration_date", new WTextField());
// 	//	$property->setDefaultValue();
// //		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
// 		
// 		// Create the step text
// 		ob_start();
// 		print "\n<h2>"._("Effective Date")."</h2>";
// 		print "\n"._("The date that this <em>Slide-Show</em> becomes effective: ");
// 		print "\n<br />[[effective_date]]";
// 		
// 		print "\n<h2>"._("Expiration Date")."</h2>";
// 		print "\n"._("The date that this <em>Slide-Show</em> expires: ");
// 		print "\n<br />[[expiration_date]]";
// 		$step->setContent(ob_get_contents());
// 		ob_end_clean();
		
		
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
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$exhibitionAsset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('exhibition_id')));
		
		$properties = $wizard->getAllValues();
		
		$status =& new StatusStars(_("Saving Slideshow"));
		$status->initializeStatistics(count($properties['slidestep']['slides']) + 2);
		
		// First, verify that we chose a parent that we can add children to.
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"), 
				$exhibitionAsset->getId()))
		{
			
			$slideshowAssetType = new HarmoniType("Asset Types", 
										"edu.middlebury.concerto", 
										"Slideshow", 
										"Slide-Shows are ordered collections of slides that contain captions and may reference media Assets.");
			
			
			$slideshowAsset =& $repository->createAsset(
										$properties['namedescstep']['display_name'], 
										$properties['namedescstep']['description'], 
										$slideshowAssetType);
										
			$slideshowAssetId =& $slideshowAsset->getId();
			$this->_slideshowAssetId =& $slideshowAssetId;
			
			// Update the effective/expiration dates
// 			if ($properties['datestep']['effective_date'])
// 				$slideshowAsset->updateEffectiveDate(
// 					DateAndTime::fromString($properties['datestep']['effective_date']));
// 			if ($properties['datestep']['expiration_date'])
// 				$slideshowAsset->updateExpirationDate(
// 					DateAndTime::fromString($properties['datestep']['expiration_date']));
			
			$exhibitionAsset->addAsset($slideshowAssetId);
			
			
			// --- Slides ---
			$slideAssetType = new HarmoniType("Asset Types", 
										"edu.middlebury.concerto", 
										"Slide", 
										"Slides are components of Slide-Shows that contain captions and may reference media Assets.");
			$slideRecordStructId =& $idManager->getId(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure");
			$targetIdPartStructId =& $idManager->getId(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id");
			$textPositionPartStructId =& $idManager->getId(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.text_position");
			$displayMetadataPartStructId =& $idManager->getId(
				"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.display_metadata");
				
			$setManager =& Services::getService("Sets");
			$slideOrder =& $setManager->getPersistentSet($slideshowAssetId);
			$status->updateStatistics();
			
			foreach ($properties['slidestep']['slides'] as $slideProperties) {
				
				// ---- Clean the inputs ----
				if (isset($slideProperties['title']))
					$title = $slideProperties['title'];
				else
					$title = '';
					
				if (isset($slideProperties['caption']))
					$caption = $slideProperties['caption'];
				else
					$caption = '';
				
				if (isset($slideProperties['text_position']))
					$textPosition = String::withValue($slideProperties['text_position']);
				else
					$textPosition = String::withValue('');
				
				if (isset($slideProperties['show_target_metadata']))
					$displayMetadata = Boolean::withValue($slideProperties['show_target_metadata']);
				else
					$displayMetadata = Boolean::false();
				
				if (isset($slideProperties['_assetId']))
					$targetId = String::withValue($slideProperties['_assetId']->getIdString());
				else
					$targetId = String::withValue('');

				
				// ---- Create the asset ----
				$slideAsset =& $repository->createAsset(
										$title, 
										$caption, 
										$slideAssetType);
				$slideAssetId =& $slideAsset->getId();
				$slideshowAsset->addAsset($slideAssetId);
				$slideOrder->addItem($slideAssetId);
				
				// ---- Set the additional info ----
				$slideRecord =& $slideAsset->createRecord($slideRecordStructId);
				$slideRecord->createPart($textPositionPartStructId, $textPosition);
				$slideRecord->createPart($displayMetadataPartStructId, $displayMetadata);
				$slideRecord->createPart($targetIdPartStructId, $targetId);
				
				$status->updateStatistics();
			}
			
			// Log the success or failure
			if (Services::serviceRunning("Logging")) {
				$loggingManager =& Services::getService("Logging");
				$log =& $loggingManager->getLogForWriting("Concerto");
				$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType =& new Type("logging", "edu.middlebury", "Event_Notice",
								"Normal events.");
				
				$item =& new AgentNodeEntryItem("Create Node", "Slideshow added");
				$item->addNodeId($slideshowAssetId);
				$item->addNodeId($exhibitionAsset->getId());
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			
			$status->updateStatistics();
						
			return TRUE;
		} 
		// If we don't have authorization to add to the picked parent, send us back to
		// that step.
		else {
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
		$url =& $harmoni->request->mkURL("exhibitions", "browse_exhibition");
		return $url->write();
	}
}

?>