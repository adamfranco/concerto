<?php
/**
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class modify_slideshowAction 
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
		$harmoni =& Harmoni::Instance();
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");

		$harmoni->request->startNamespace("modify_slideshow");

		$return = $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			$idManager->getId(RequestContext::value('slideshow_id')));
		
		$harmoni->request->endNamespace();
		
		return $return;
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to modify this <em>Slideshow</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$harmoni =& Harmoni::Instance();
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");

		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));

		$harmoni->request->startNamespace("modify_slideshow");
	
		$asset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('slideshow_id')));

		$harmoni->request->endNamespace();

		return _("Modifying the ")." <em>".$asset->getDisplayName().
			"</em> "._("Slideshow");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::Instance();
		$harmoni->request->startNamespace("modify_slideshow");
		$harmoni->request->passthrough("slideshow_id");

		$actionRows =& $this->getActionRows();
		
// 		$idManager =& Services::getService("Id");
// 		$slideshowAssetId =& $idManager->getId(
// 			RequestContext::value('slideshow_id'));
		
		$cacheName = 'modify_slideshow_wizard_'.
			RequestContext::value('slideshow_id');

		$this->runWizard ( $cacheName, $actionRows );
		$harmoni->request->endNamespace();
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
		$idManager =& Services::getService("Id");		
		$setManager =& Services::getService("Sets");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$slideshowAsset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('slideshow_id')));
		
		// Instantiate the wizard, then add our steps.
		// fetch current slideshow slides HERE!!!
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Title &amp; Description"));

		// Create the properties.
		$displayNameProp =& $step->addComponent("display_name",
			new WTextField());
		$displayNameProp->setValue($slideshowAsset->getDisplayName());
		$displayNameProp->setErrorText("<nobr>".
			_("A value for this field is required.")."</nobr>");
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
	// 	$displayNameProp->setDefaultValue(_("Default Asset Name"));
//		$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
		
		$descriptionProp =& $step->addComponent("description",
			WTextArea::withRowsAndColumns(5,30));
		$descriptionProp->setValue($slideshowAsset->getDescription());
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
		
		$multField =& new SlideOrderedRepeatableComponentCollection();
		$slideStep->addComponent("slides", $multField);
		$multField->setStartingNumber(0);

		$property =& $multField->addComponent(
			"slideId", 
			new AssetComponent());
		$property->setParent($multField);
		
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
		
		$property->addOption("right", _("right"));
		$property->addOption("left", _("left"));
		$property->addOption("bottom", _("bottom"));
		$property->addOption("top", _("top"));
		$property->addOption("center", _("center/no-media"));
		$property->addOption("none", _("none/media-only"));
		
		
		$property =& $multField->addComponent(
			"show_target_metadata", 
			new WCheckBox());
		$property->setChecked(false);
 		$property->setLabel(_("Display Media Info?"));
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
		
		// Add the current assets to the list.
		$textPositionId =& $idManager->getId(
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.text_position");
		$showMetadataId =& $idManager->getId(
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.display_metadata");
		$targetId =& $idManager->getId(
			"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id");
		
		$slideIterator =& $slideshowAsset->getAssets();
		$slideOrder =& $setManager->getPersistentSet($slideshowAsset->getId());
		$orderedSlides = array();
		$orderlessSlides = array();

		while ($slideIterator->hasNext()) {
			$slideAsset =& $slideIterator->next();
			$slideId =& $slideAsset->getId();
/*
			// DEBUG
			$records =& $slideAsset->getRecordsByRecordStructure($idManager->getId("Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure"));
			$myCrapRecord =& $records->next();
			require_once(POLYPHONY."/main/library/DataManagerGUI/SimpleRecordPrinter.class.php");
			SimpleRecordPrinter::printRecord($myCrapRecord->_record);
			// END
*/			
			$collection = array();
			$collection['slideId'] =& $slideId;//->getIdString();
			$collection['title'] = $slideAsset->getDisplayName();
			$collection['caption'] = $slideAsset->getDescription();
			$textPositionIterator =& $slideAsset->getPartValuesByPartStructure(
				$textPositionId);
			if ($textPositionIterator->hasNext()) {
				$textPosition =& $textPositionIterator->next();
				$collection['text_position'] = $textPosition->asString();
			} else
				$collection['text_position'] = "right";
			$showMetadataIterator =& $slideAsset->getPartValuesByPartStructure(
				$showMetadataId);			
 			if ($showMetadataIterator->hasNext()) {
 				$showMetadata =& $showMetadataIterator->next();
 				$collection['show_target_metadata'] =
 					$showMetadata->value();
 			} else
				$collection['show_target_metadata'] = FALSE;
			$assetIdIterator =& $slideAsset->getPartValuesByPartStructure(
				$targetId);
			if ($assetIdIterator->hasNext()) {
				$id =& $assetIdIterator->next();
				$rule =& NonzeroLengthStringValidatorRule::getRule();
				if ($rule->check($id->asString()))
					$collection['_assetId'] =& new HarmoniId($id->asString());
			}
			if ($slideOrder->isInSet($slideId))
				$orderedSlides[$slideOrder->getPosition($slideId)] =
					$collection;
			else 
				$orderlessSlides[] = $collection;
		}
		// add them in order
		ksort($orderedSlides);
		foreach ($orderedSlides as $slide)
			$multField->addValueCollection($slide);
			
		foreach($orderlessSlides as $slide)
			$multField->addValueCollection($slide);		
			
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
		print "\n".
			_("The date that this <em>Slide-Show</em> becomes effective: ");
		print "\n<br />[[effective_date]]";
		
		print "\n<h2>"._("Expiration Date")."</h2>";
		print "\n"._("The date that this <em>Slide-Show</em> expires: ");
		print "\n<br />[[expiration_date]]";
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
		
		// Make sure we have a valid Repository
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
					"edu.middlebury.concerto.exhibition_repository"));
		$slideshowAsset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('slideshow_id')));
		
		$properties =& $wizard->getAllValues();
		
		// First, verify that we chose a parent that we can add children to.
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"), 
				$slideshowAsset->getId()))
		{
			$slideshowAssetId =& $slideshowAsset->getId();
			$this->_slideshowAssetId =& $slideshowAssetId;
			
			// Update the effective/expiration dates
			if ($properties['datestep']['effective_date'])
				$slideshowAsset->updateEffectiveDate(
					DateAndTime::fromString(
					$properties['datestep']['effective_date']));
			if ($properties['datestep']['expiration_date'])
				$slideshowAsset->updateExpirationDate(
					DateAndTime::fromString(
					$properties['datestep']['expiration_date']));			

			// --- Slides ---

			$slideAssetType = new HarmoniType("exhibitions", 
				"edu.middlebury.concerto", 
				"slide", 
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
			$pSlideOrder =& $setManager->getPersistentSet($slideshowAssetId);
			$slideIterator =& $slideshowAsset->getAssets();

			// ---- Add/Update Slides in new order (hopefully)		
			$existingSlides = array();
			while ($slideIterator->hasNext()) {
				$currentSlide =& $slideIterator->next();
				$id =& $currentSlide->getId();
				$existingSlides[] =& $id->getIdString();
			}

			$pSlideOrder->removeAllItems();

			foreach ($properties['slidestep']['slides'] as $slideProperties) {
// 				print get_class($slideProperties['slideId']).": ".$slideProperties['title'];

				if (!isset($slideProperties['slideId'])) {
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
						$textPosition = String::withValue(
							$slideProperties['text_position']);
					else
						$textPosition = String::withValue('');
					
					if (isset($slideProperties['show_target_metadata']))
						$displayMetadata = Boolean::withValue(
							$slideProperties['show_target_metadata']);
					else
						$displayMetadata = Boolean::false();
					
					if (isset($slideProperties['_assetId']))
						$targetId = String::withValue(
							$slideProperties['_assetId']->getIdString());
					else
						$targetId = String::withValue('');	
					
					// ---- Create the asset ----
					$slideAsset =& $repository->createAsset(
											$title, 
											$caption, 
											$slideAssetType);
					$slideAssetId =& $slideAsset->getId();
					$slideshowAsset->addAsset($slideAssetId);
					
					// Add it to the order field
					$pSlideOrder->addItem($slideAssetId);
					
					// ---- Set the additional info ----
					$slideRecord =& $slideAsset->createRecord($slideRecordStructId);
					$slideRecord->createPart($textPositionPartStructId,
						$textPosition);
					$slideRecord->createPart($displayMetadataPartStructId,
						$displayMetadata);
					$slideRecord->createPart($targetIdPartStructId, $targetId);
				} 
				else if (in_array($slideProperties['slideId']->getIdString(), $existingSlides)) {
					$slideAsset =& $repository->getAsset(
						$slideProperties['slideId']);
					$slideAsset->updateDisplayName($slideProperties['title']);
					$slideAsset->updateDescription(
						$slideProperties['caption']);
					$textPositionIterator =& 
						$slideAsset->getPartsByPartStructure(
						$textPositionPartStructId);
					if ($textPositionIterator->hasNext()) {
						$part =& $textPositionIterator->next();
						$part->updateValue(new String(
							$slideProperties['text_position']));
					}
					$showMetadataIterator =& 
						$slideAsset->getPartsByPartStructure(
						$displayMetadataPartStructId);			
					if ($showMetadataIterator->hasNext()) {
						$part =& $showMetadataIterator->next();
						$part->updateValue(new Boolean(
							$slideProperties['show_target_metadata']));
					}
					$pSlideOrder->addItem($slideProperties['slideId']);
					
					$records =& $slideAsset->getRecordsByRecordStructure(
						$slideRecordStructId);
					$slideRecord =& $records->next();
				}
			}
			// ==== Remove slide assets no longer in slideshow ----
			foreach($existingSlides as $older) {
				$old =& $idManager->getId($older);
				if (!$pSlideOrder->isInSet($old)) {
					$slideshowAsset->removeAsset($old, false);
					$repository->deleteAsset($old);
				}
			}
			
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
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId(
				"edu.middlebury.concerto.exhibition_repository"));

		$asset =& $repository->getAsset(
			$idManager->getId(RequestContext::value('slideshow_id')));
		$harmoni->request->forget('slideshow_id');
		$harmoni->request->endNamespace();
		$parents =& $asset->getParentsByType(new HarmoniType("Asset Types",
			"edu.middlebury.concerto", "Exhibition"));
		$parent =& $parents->next();
		$parentId =& $parent->getId();
		$url =& $harmoni->request->mkURL("exhibitions",
			"browse_exhibition", array(
			'exhibition_id' => $parentId->getIdString()));

		$harmoni->request->startNamespace("modify_slideshow");
		
		return $url->write();
	}
}

?>