<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/modules/asset/AssetEditingAction.class.php");

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
class editAction 
	extends AssetEditingAction
{
	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 10/26/05
	 */
	function editAction () {
		$this->_init();
		
		$this->_recStructsToIgnore[] = 'FILE';
		
		$assetList = RequestContext::value("assets");
		$this->_cacheName = 'edit_asset_wizard_'.preg_replace("/[^a-zA-Z0-9]/", "_", $assetList);
		$this->_loadAssets(explode(",", RequestContext::value("assets")));
	}
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify"), 
					$this->_assets[0]->getId());
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to modify this <em>Asset</em>.");
	}
	
	/**
	 * Answer the asset properties Wizard step
	 * 
	 * @param object Wizard $wizard
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function &getAssetPropertiesStep () {
		/*********************************************************
	 *  :: Asset Properties ::
	 *********************************************************/
		$step =& new WizardStep();
		$step->setDisplayName(_("Basic Properties"));
		
	// Display Name
		$property =& $step->addComponent("display_name", new WTextField);
		$property->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$property->setSize(40);

		$property->setValue($this->_assets[0]->getDisplayName());
		
	// Description	
		$property =& $step->addComponent("description", new WTextArea);
		$property->setRows(3);
		$property->setColumns(40);
		
		$property->setValue($this->_assets[0]->getDescription());
				
	// Effective Date
		$property =& $step->addComponent("effective_date", new WTextField);
		$property->setSize(40);
		$date =& $this->_assets[0]->getEffectiveDate();
		if (is_object($date)) {
			$date =& $date->asDate();
			$property->setValue($date->yyyymmddString());
		}
	
	// Expiration Date
		$property =& $step->addComponent("expiration_date", new WTextField);
		$property->setSize(40);
		$date =& $this->_assets[0]->getExpirationDate();
		if (is_object($date)) {
			$date =& $date->asDate();
			$property->setValue($date->yyyymmddString());
		}
		
	 	
		$step->setContent($this->getAssetPropertiesContent());
		return $step;
	}
	
	/**
	 * Update the asset properties based on values from the wizard.
	 * 
	 * @param array $results
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function updateAssetProperties ( &$results, &$asset ) {
		// DisplayName
		$asset->updateDisplayName($results['display_name']);
		
		// Description
		$asset->updateDescription($results['description']);
		
		// Effective Date
		$newEffDate =& DateAndTime::fromString($results['effective_date']);
		$asset->updateEffectiveDate($newEffDate);
		
		// Expiration Date
		$newExpDate =& DateAndTime::fromString($results['expiration_date']);
		$asset->updateExpirationDate($newExpDate);
	}
	
	/**
	 * Answer a WizardStep for the AssetContent
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 10/26/05
	 */
	function getAssetContentStep () {
		$step =& new WizardStep();
		$step->setDisplayName(_("Content")." ("._("optional").")");
		
		$property =& $step->addComponent("content", new WTextArea);
		$property->setRows(20);
		$property->setColumns(70);
		
		$content =& $this->_assets[0]->getContent();
		$property->setValue($content->asString());
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Content")."</h2>";
		print "\n"._("This is an optional place to put content for this <em>Asset</em>. <br />If you would like more structure, you can create new schemas to hold the <em>Asset's</em> data.");
		print "\n<br />[[content]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		return $step;
	}
	
	/**
	 * Update the asset content based on the values from the wizard
	 * 
	 * @param array $results
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function updateAssetContent ( &$results, &$asset ) {
		// Content 
		$content =& $asset->getContent();
		$newContent =& Blob::withValue($results);
		if (is_object($content) && !$content->isEqualTo($newContent))
			$asset->updateContent($newContent);
	}
	
	
	/**
	 * Answer a step for all of the the files of the asset
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 10/31/05
	 */
	function &getFileRecordsStep () {
		$idManager =& Services::getService("Id");
		$repository =& $this->getRepository();
		$recStructId =& $idManager->getId("FILE");
		$recStruct =& $repository->getRecordStructure($recStructId);
		
		$step =& new WizardStep();
		$step->setDisplayName($recStruct->getDisplayName());
		
		$step->setContent("[[files]]");
		
		$repeatableComponent =& $step->addComponent("files", 
			new WRepeatableComponentCollection);
		$repeatableComponent->setStartingNumber(0);
		
		
		ob_start();
		
		$component =& $repeatableComponent->addComponent("record_id", new WHiddenField());
		
		$component =& $repeatableComponent->addComponent("file_upload", new WFileUploadField());
		
		print "\n<em>"._("Upload a new file or change file properties.")."</em>\n<hr />";
// 		print "\n<br /><strong>";
// 		if ($parts['FILE_NAME']->getValue()) {
// 			print _("New file (optional)");
// 		} else {
// 			print _("File");
// 		}
// 		print ":</strong>";
		
		print "\n[[file_upload]]";
		
		
		$component =& $repeatableComponent->addComponent("file_name", new WTextField());
		
		$component =& $repeatableComponent->addComponent("use_custom_filename", new WCheckBox());
		$component->setValue(false);
		
		
		$component =& $repeatableComponent->addComponent("file_size", new WTextField());
		$component->setReadOnly(TRUE);
		
		
		$component =& $repeatableComponent->addComponent("mime_type", new WTextField());
		
		$component =& $repeatableComponent->addComponent("use_custom_type", new WCheckBox());
		$component->setValue(false);
		
		
		// Dimensions 
		$dimensionComponent =& new WTextField();
		$dimensionComponent->setSize(8);
		$dimensionComponent->setStyle("text-align: right");
		$dimensionComponent->setErrorRule(new WECOptionalRegex("^([0-9]+px)?$"));
		$dimensionComponent->setErrorText(_("Must be a positive integer followed by 'px'."));
		$dimensionComponent->setOnChange("validateWizard(this.form);");

		$component =& $repeatableComponent->addComponent("height", $dimensionComponent->shallowCopy());
		

		$component =& $repeatableComponent->addComponent("use_custom_height", new WCheckBox());
		$component->setValue(false);
		
		$component =& $repeatableComponent->addComponent("width", $dimensionComponent->shallowCopy());
		
		
		$component =& $repeatableComponent->addComponent("use_custom_width", new WCheckBox());
		$component->setValue(false);
		
		
		// Thumnail Upload
		$component =& $repeatableComponent->addComponent("thumbnail_upload", new WFileUploadField());
		
		
		$component =& $repeatableComponent->addComponent("thumbnail_mime_type", new WTextField());
		
		$component =& $repeatableComponent->addComponent("use_custom_thumbnail_type", new WCheckBox());
		$component->setValue(false);
		
		
		// Thumbnail dimensions
		$component =& $repeatableComponent->addComponent("thumbnail_height", $dimensionComponent->shallowCopy());
		$component =& $repeatableComponent->addComponent("use_custom_thumbnail_height", new WCheckBox());
		$component->setValue(false);
		
		$component =& $repeatableComponent->addComponent("thumbnail_width", $dimensionComponent->shallowCopy());
		$component =& $repeatableComponent->addComponent("use_custom_thumbnail_width", new WCheckBox());
		$component->setValue(false);
		
		print "\n<p>"._("Change properties of the uploaded file to custom values:");
		
		print "\n<table border='1'>";
		
		print "\n<tr>";
		print "\n\t<th>";
		print "\n\t\t"._("Property")."";
		print "\n\t</th>";
		print "\n\t<th>";
		print "\n\t\t"._("Use Custom Value")."";
		print "\n\t</th>";
		print "\n\t<th>";
		print "\n\t\t"._("Custom Value")."";
		print "\n\t</th>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("File Name")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_filename]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[file_name]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("File Size")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
// 		print "\n\t\t[[size_from_file]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[file_size]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Mime Type")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_type]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[mime_type]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Width")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_width]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[width]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Height")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_height]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[height]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t &nbsp; ";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n[[thumbnail_upload]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail Mime Type")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_thumbnail_type]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[thumbnail_mime_type]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail Width")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_thumbnail_width]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[thumbnail_width]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail Height")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_thumbnail_height]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[thumbnail_height]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n</table>";
		
		print "\n</p>";
		
		$repeatableComponent->setContent(ob_get_contents());
		ob_end_clean();
		
		
		$records =& $this->_assets[0]->getRecordsByRecordStructure($recStructId);
		while ($records->hasNext()) {
			$record =& $records->next();
			
			$partIterator =& $record->getParts();
			$parts = array();
			while($partIterator->hasNext()) {
				$part =& $partIterator->next();
				$partStructure =& $part->getPartStructure();
				$partStructureId =& $partStructure->getId();
				$parts[$partStructureId->getIdString()] =& $part;
			}
			
			$collection = array();
			
			$recordId =& $record->getId();
			$collection['record_id'] = $recordId->getIdString();
			
			$collection['file_name'] = $parts['FILE_NAME']->getValue();
	
			$size =& ByteSize::withValue($parts['FILE_SIZE']->getValue());
			$collection['file_size'] = $size->asString();
	
			$collection['mime_type'] = $parts['MIME_TYPE']->getValue();
	
			$dim = $parts['DIMENSIONS']->getValue();
			if ($dim[1])
				$collection['height'] = $dim[1].'px';
			if ($dim[0])
				$collection['width'] = $dim[0].'px';
	
			$collection['thumbnail_mime_type'] = $parts['THUMBNAIL_MIME_TYPE']->getValue();
	
			$thumDim = $parts['THUMBNAIL_DIMENSIONS']->getValue();
			if ($thumDim[1])
				$collection['thumbnail_height'] = $thumDim[1].'px';
			if ($thumDim[0])
				$collection['thumbnail_width'] = $thumDim[0].'px';
			
			$repeatableComponent->addValueCollection($collection);
		}
				
		return $step;
	}
	
	/**
	 * Update the file records of the asset based on the values from the wizard
	 * 
	 * @param array $results
	 * @param array $initialState
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/26/05
	 */
	function updateFileRecords ( &$results, &$initialState, &$asset ) {
		printpre("<hr>updateFileRecords:".$asset->getDisplayName());
		
		$idManager =& Services::getService("Id");
		$recStructId =& $idManager->getId("FILE");
		
		foreach (array_keys($results) as $i) {
			$recordResults =& $results[$i];
			
			if ($recordResults['record_id']) {
				$record =& $asset->getRecord($idManager->getId($recordResults['record_id']));
			} else {
				$record =& $asset->createRecord($recStructId);
			}
			
			$this->updateFileRecord($recordResults, 
				$initialState[$i], $record);
		}
	}
	
	/**
	 * update a single file record based on the values from the wizard
	 * 
	 * @param array $results
	 * @param array $initialState
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 10/31/05
	 */
	function updateFileRecord ( &$results, &$initialState, &$record ) {
		$recordId =& $record->getId();
		printpre("<hr>updateFileRecord: ".$recordId->getIdString());
		
		// Get all the parts
		$partIterator =& $record->getParts();
		$parts = array();
		while($partIterator->hasNext()) {
			$part =& $partIterator->next();
			$partStructure =& $part->getPartStructure();
			$partStructureId =& $partStructure->getId();
			$parts[$partStructureId->getIdString()] =& $part;
		}
		
		// if a new File was uploaded, store it.
		if ($results['file_upload']['tmp_name'] 
			&& $results['file_upload']['name']) 
		{
			$name = $results['file_upload']['name'];
			$tmpName = $results['file_upload']['tmp_name'];			
			$mimeType = $results['file_upload']['type'];
			// If we weren't passed a mime type or were passed the generic
			// application/octet-stream type, see if we can figure out the
			// type.
			if (!$mimeType || $mimeType == 'application/octet-stream') {
				$mime =& Services::getService("MIME");
				$mimeType = $mime->getMimeTypeForFileName($name);
			}
			
			$parts['FILE_DATA']->updateValue(file_get_contents($tmpName));
			$parts['FILE_NAME']->updateValue($name);
			$parts['MIME_TYPE']->updateValue($mimeType);
		}
		
		// If we've uploaded a thumbnail, safe it.
		if ($results['thumbnail_upload']['tmp_name'] 
			&& $results['thumbnail_upload']['name']) 
		{
			$name = $results['thumbnail_upload']['name'];
			$tmpName = $results['thumbnail_upload']['tmp_name'];			
			$mimeType = $results['thumbnail_upload']['type'];
						
			// If we weren't passed a mime type or were passed the generic
			// application/octet-stream type, see if we can figure out the
			// type.
			if (!$mimeType || $mimeType == 'application/octet-stream') {
				$mime =& Services::getService("MIME");
				$mimeType = $mime->getMimeTypeForFileName($name);
			}
			
			$parts['THUMBNAIL_DATA']->updateValue(file_get_contents($tmpName));
			$parts['THUMBNAIL_MIME_TYPE']->updateValue($mimeType);
		}
		// otherwise, if we've uploaded a new file only, get rid of the
		// old one and try to create a new one
		else if ($results['file_upload']['tmp_name'] 
			&& $results['file_upload']['name']) 
		{
			$imageProcessor =& Services::getService("ImageProcessor");
			
			// If our image format is supported by the image processor,
			// generate a thumbnail.
			if ($imageProcessor->isFormatSupported($mimeType)) {				
				$parts['THUMBNAIL_DATA']->updateValue(
					$imageProcessor->generateThumbnailData($mimeType, 
											file_get_contents($tmpName)));
				$parts['THUMBNAIL_MIME_TYPE']->updateValue($imageProcessor->getThumbnailFormat());
			} 
			// just make our thumbnail results empty. Default icons will display
			// instead.
			else {
				$parts['THUMBNAIL_DATA']->updateValue("");
				$parts['THUMBNAIL_MIME_TYPE']->updateValue("NULL");
			}
		}
		
		// if the "use custom" box was checked store the name.
		if ($results['use_custom_filename']) {
			$parts['FILE_NAME']->updateValue($results['file_name']);
		}
		
		// if the "use custom" box was checked store the mime type.
		if ($results['use_custom_type']) {
			$parts['MIME_TYPE']->updateValue($results['mime_type']);
		}
		
		// if the "use custom" box was checked store the height.
		if ($results['use_custom_height']
			&& ereg("^([0-9]+)px$", $results['height'], $matches)) 
		{
			$dimArray = $parts['DIMENSIONS']->getValue();
			$dimArray[1] = $matches[1];
			print "Setting DIMENSIONS to:"; printpre($dimArray);
			$parts['DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
		
		// if the "use custom" box was checked store the width.
		if ($results['use_custom_width']
			&& ereg("^([0-9]+)px$", $results['width'], $matches)) 
		{
			$dimArray = $parts['DIMENSIONS']->getValue();
			$dimArray[0] = $matches[1];
			print "Setting DIMENSIONS to:"; printpre($dimArray);
			$parts['DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
		
		// if the "use custom" box was checked store the height.
		if ($results['use_custom_thumbnail_height']
			&& ereg("^([0-9]+)px$", $results['thumbnail_height'], $matches)) 
		{
			$dimArray = $parts['THUMBNAIL_DIMENSIONS']->getValue();
			$dimArray[1] = $matches[1];
			print "Setting THUMBNAIL_DIMENSIONS to:"; printpre($dimArray);
			$parts['THUMBNAIL_DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
		
		// if the "use custom" box was checked store the width.
		if ($results['use_custom_thumbnail_width']
			&& ereg("^([0-9]+)px$", $results['thumbnail_width'], $matches)) 
		{
			$dimArray = $parts['THUMBNAIL_DIMENSIONS']->getValue();
			$dimArray[0] = $matches[1];
			print "Setting THUMBNAIL_DIMENSIONS to:"; printpre($dimArray);
			$parts['THUMBNAIL_DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
	}
	
	/**
	 * Answer a Wizard step for the Record structure passed.
	 * 
	 * @param object RecordStructure $recStruct
	 * @return object WizardStep
	 * @access public
	 * @since 10/26/05
	 */
	function &getRecordStructureStep ( &$recStruct ) {
		$step =& new WizardStep();
		$step->setDisplayName($recStruct->getDisplayName());
		
		$allRecordsComponent =& $step->addComponent("records", new WRepeatableComponentCollection());
		$allRecordsComponent->setStartingNumber(0);
		$this->addPartStructureComponents($allRecordsComponent, $recStruct);
		
		$records =& $this->getRecordsForRecordStructure($recStruct);
		$values =& $this->getValuesForRecords($records);
		
		for ($i = 0; $i < count($values); $i++) {
			$allRecordsComponent->addValueCollection($values[$i]);
		}
				
		$step->setContent("[[records]]");
		
		return $step;
	}
	
	/**
	 * Add a table of PartStructure components to the given component.
	 * 
	 * @param object WizardComponent
	 * @return void
	 * @access public
	 * @since 10/27/05
	 */
	function addPartStructureComponents ( &$parentComponent, &$recStruct ) {
	
		$parentComponent->addComponent('record_id', new WHiddenField);
		
		ob_start();
		print "\n<table class='edit_table' cellspacing='0'>";
		
		$partStructs =& $recStruct->getPartStructures();
		while ($partStructs->hasNext()) {
			$partStruct =& $partStructs->next();
			$partStructId =& $partStruct->getId();
		
		// PartStructure
			if (!$partStruct->isRepeatable()) {
				$component =& $this->getComponentForPartStruct($partStruct);
			} else {
				$component =& $this->getRepeatablePartStructComponent($partStruct);				
			}
			
			$parentComponent->addComponent(
				str_replace(".", "_", $partStructId->getIdString()),
				$component);
			
			
			print "\n\t<tr>";
			print "\n\t\t<th>";
			print "\n\t\t\t".$partStruct->getDisplayName();
	// 		print "\n"._("The Name for this <em>Asset</em>: ");
			print "\n\t\t</th>";
			print "\n\t\t<td>";
			print "\n\t\t\t[[".str_replace(".", "_", $partStructId->getIdString())."]]";
			print "\n\t\t</td>";
			print "\n\t</tr>";
		}
	
		print "\n</table>";
		print "\n[[record_id]]";
	
		$parentComponent->setContent(ob_get_contents());
		ob_end_clean();
	}
	
	/**
	 * If the part is repeatable, then we will build a list of all parts,
	 * keeping track of which ones are had by all, and which ones are had
	 * just by some.
	 * 
	 * @param object PartStructure $partStruct
	 * @return object WizardComponent
	 * @access public
	 * @since 10/26/05
	 */
	function &getRepeatablePartStructComponent ( &$partStruct ) {
		
	// Make a component for each of values
		$repeatableProperty =& new WRepeatableComponentCollection();
		$repeatableProperty->setStartingNumber(0);
				
		$property =& $repeatableProperty->addComponent('partvalue',
				$this->getComponentForPartStruct($partStruct));
		
		ob_start();
		print "\n\t\t\t<div>";
		print "[[partvalue]]";
		print "\n\t\t\t</div>";
		$repeatableProperty->setElementLayout(ob_get_contents());
		ob_end_clean();
		
		return $repeatableProperty;
	}
	
	/**
	 * Answer an array of values for the records passed in an array
	 * 
	 * @param array $records
	 * @return array
	 * @access public
	 * @since 10/27/05
	 */
	function &getValuesForRecords ( &$records ) {
		$collections = array();
		for ($i = 0; $i < count($records); $i++) {
			$collections[$i] = array();
			$record =& $records[$i];
			$recordId =& $record->getId();
			$collections[$i]['record_id'] = $recordId->getIdString();
			
			$recordStruct =& $record->getRecordStructure();
			$partStructs =& $recordStruct->getPartStructures();
			while ($partStructs->hasNext()) {
				$partStruct =& $partStructs->next();
				$partStructId =& $partStruct->getId();
				$partStructIdString = str_replace(".", "_", $partStructId->getIdString());
				
				$partIterator =& $record->getPartsByPartStructure($partStructId);
				if ($partIterator->hasNext())
					$collections[$i][$partStructIdString] =& $this->getValuesForParts($partStruct, $partIterator);				
			}
		}
		return $collections;
	}
	
	/**
	 * Answer an array of values for the parts passed
	 * 
	 * @param array $parts
	 * @return array
	 * @access public
	 * @since 10/27/05
	 */
	function &getValuesForParts ( &$partStruct, &$parts ) {		
		if ($partStruct->isRepeatable()) {
			$collections = array();
			$i =0;
			while ($parts->hasNext()) {
				$part =& $parts->next();
				$collections[$i] = array();
				$collections[$i]['partvalue'] =& $part->getValue();
				$i++;
			}
			return $collections;
		} else {
			$part =& $parts->next();
			return $part->getValue();
		}
	}
	
	/**
	 * Update the records for the recStructId for the asset based on the results
	 * from the wizard.
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Id $recStructId
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateAssetRecords (&$results, &$initialState, &$recStructId, &$asset) {
		printpre("<hr>updateAssetRecords:".$asset->getDisplayName());
		
		$recStructIdString = str_replace(".", "_", $recStructId->getIdString());
		$idManager =& Services::getService("Id");
		
		foreach (array_keys($results[$recStructIdString]['records']) as $i) {
			$recordResults =& $results[$recStructIdString]['records'][$i];
			if ($recordResults['record_id']) {
				$record =& $asset->getRecord($idManager->getId($recordResults['record_id']));
			} else {
				$record =& $asset->createRecord($recStructId);
			}
			
			$this->updateRecord($recordResults, 
				$initialState[$recStructIdString]['records'][$i], $record);
		}
	}
	
	/**
	 * Update the given single valued part to reflect the value changed in the wizard
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateSingleValuedPart (&$partResults, &$partInitialState, 
		&$partStruct, &$record) 
	{
		$partStructId = $partStruct->getId();
		
		$partStructType =& $partStruct->getType();
		$valueObjClass =& $partStructureType->getKeyword();
		
		if ($partResults['partvalue'] != $partInitialState['partvalue'])
		{
			$parts =& $record->getPartsByPartStructure($partStructId);
			$part =& $parts->next();
			$part->updateValue(String::withvalue($partResults['partvalue']));
		}
	}
	
	/**
	 * Update the given repeatable part to reflect the value changed in the wizard.
	 *
	 * For "Value from Wizard" = wizVal and  "value originally in Part" = partVal
	 *	- If a partVal exists and is equal to a wizVal, leave it alone
	 *	- If a partVal exists, but is not equal to any wizVals, remove it.
	 *	- If a wizVal exists, but no partVals equal to it exist, add a new Part
	 * 
	 * @param array $results, the wizard results
	 * @param array $initialState, the initial wizard results
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function updateRepeatablePart (&$partResults, &$partInitialState, 
		&$partStruct, &$record) 
	{
		$partStructId = $partStruct->getId();
		$partValsHandled = array();
		
		$parts =& $record->getPartsByPartStructure($partStructId);
		while ($parts->hasNext()) {
			$part =& $parts->next();
			$partVal =& $part->getValue();
			$partStrVal = $partVal->asString();
			
			// Check for existance in the results.
			// if the value is not in the results, remove the part and continue.
			if (!$this->inWizArray($partVal, 'partvalue', $partResults)) {
				$record->deletePart($part->getId());
				$partValsHandled[] = $partStrVal;
				
				$partId =& $part->getId();
				printpre("\tDeleting Part: Id: ".$partId->getIdString()." Value: ".$partStrVal);
				
				continue;
			}
			
			// If the value is in the wizard results, do nothing
			$partValsHandled[] = $partStrVal;
			
			$partId =& $part->getId();
			printpre("\tIgnoring Part: Id: ".$partId->getIdString()." Value: ".$partStrVal);
			
			continue;
		}
		
		// Go through all of the Wizard result values. If any of them haven't
		// been handled and need to be, add them.
		foreach ($partResults as $key => $valueArray) {
			$valueStr = $valueArray['partvalue']->asString();
			
			if (!in_array($valueStr, $partValsHandled)) {
				$part =& $record->createPart($partStructId, $valueArray['partvalue']);
				
				$partId =& $part->getId();
				printpre("\tAdding Part: Id: ".$partId->getIdString()." Value: ".$valueStr);
			}
		}
	}
}