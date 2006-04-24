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
		if(is_object($this->_assets[0]->getEffectiveDate())) {
			$date =& $this->_assets[0]->getEffectiveDate();
			$date =& $date->asDate();
			$property->setValue($date->yyyymmddString());
		}	
	
	// Expiration Date
		$property =& $step->addComponent("expiration_date", new WTextField);
		$property->setSize(40);
		if (is_object($this->_assets[0]->getExpirationDate())) {
			$date =& $this->_assets[0]->getExpirationDate();
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
		
		ob_start();
		print "\n<h2>"._("File")."</h2>";
		print "\n[[files]]";
		$step->setContent(ob_get_clean());
		
		$repeatableComponent =& $step->addComponent("files", 
			new WRepeatableComponentCollection);
		$repeatableComponent->setStartingNumber(0);
		
		
		ob_start();
		
		$component =& $repeatableComponent->addComponent("record_id", new WHiddenField());
		
		$component =& $repeatableComponent->addComponent("file_upload", new WFileUploadField());		
		
		$vComponent =& $repeatableComponent->addComponent("file_name", new WVerifiedChangeInput());
		$component =& $vComponent->setInputComponent(new WTextField);
		
		$component =& $repeatableComponent->addComponent("file_size", new WTextField());
		$component->setEnabled(FALSE, TRUE);
		
		
		$vComponent =& $repeatableComponent->addComponent("mime_type", new WVerifiedChangeInput());
		$component =& $vComponent->setInputComponent(new WTextField);
		
		
		// Dimensions 
		$dimensionComponent =& new WTextField();
		$dimensionComponent->setSize(8);
		$dimensionComponent->setStyle("text-align: right");
		$dimensionComponent->setErrorRule(new WECOptionalRegex("^([0-9]+px)?$"));
		$dimensionComponent->setErrorText(_("Must be a positive integer followed by 'px'."));
		$dimensionComponent->setOnChange("validateWizard(this.form);");
		
		$vComponent =& $repeatableComponent->addComponent("height", new WVerifiedChangeInput());
		$component =& $vComponent->setInputComponent($dimensionComponent->shallowCopy());
		
		$vComponent =& $repeatableComponent->addComponent("width", new WVerifiedChangeInput());
		$component =& $vComponent->setInputComponent($dimensionComponent->shallowCopy());
		
		
		// Thumnail Upload
		$component =& $repeatableComponent->addComponent("thumbnail_upload", new WFileUploadField());
		
		$vComponent =& $repeatableComponent->addComponent("thumbnail_mime_type", new WVerifiedChangeInput());
		$component =& $vComponent->setInputComponent(new WTextField);
		
		// Thumbnail dimensions
		$vComponent =& $repeatableComponent->addComponent("thumbnail_height", new WVerifiedChangeInput());
		$component =& $vComponent->setInputComponent($dimensionComponent->shallowCopy());
		
		$vComponent =& $repeatableComponent->addComponent("thumbnail_width", new WVerifiedChangeInput());
		$component =& $vComponent->setInputComponent($dimensionComponent->shallowCopy());
		
		print "\n<p>"._("Upload a new file and/or change the properties below.")."</p>";
		print "\n[[file_upload]]";
		
		print "\n<p>";
		print _("By default, the values below will be automatically populated from your uploaded file.");
		print " "._("If needed, change the properties below to custom values: ");
		
		print "\n<table border='1'>";
		
		print "\n<tr>";
		print "\n\t<th>";
		print "\n\t\t"._("Property")."";
		print "\n\t</th>";
		print "\n\t<th>";
		print "\n\t\t"._("Custom Value")."";
		print "\n\t</th>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("File Name")."";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[file_name]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("File Size")."";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[file_size]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Mime Type")."";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[mime_type]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Width")."";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[width]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Height")."";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[height]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail")."";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n[[thumbnail_upload]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail Mime Type")."";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[thumbnail_mime_type]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail Width")."";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[thumbnail_width]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail Height")."";
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
			
			$collection['file_upload'] = array(
					"starting_name" => $parts['FILE_NAME']->getValue(),
					"starting_size" => $parts['FILE_SIZE']->getValue());
			
			$collection['file_name'] = $parts['FILE_NAME']->getValue();
	
			$size =& ByteSize::withValue($parts['FILE_SIZE']->getValue());
			$collection['file_size'] = $size->asString();
	
			$collection['mime_type'] = $parts['MIME_TYPE']->getValue();
	
			$dim = $parts['DIMENSIONS']->getValue();
			if ($dim[1])
				$collection['height'] = $dim[1].'px';
			if ($dim[0])
				$collection['width'] = $dim[0].'px';
			
			$collection['thumbnail_upload'] = array(
					"starting_name" => "thumb.jpg",
					"starting_size" => strlen($parts['THUMBNAIL_DATA']->getValue()));
	
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
		$exisistingRecords = array();
		
		foreach (array_keys($results) as $i) {
			$recordResults =& $results[$i];
			
			if (isset($recordResults['record_id'])) {
				$recordId =& $idManager->getId($recordResults['record_id']);
				$record =& $asset->getRecord($recordId);
			} else {
				$record =& $asset->createRecord($recStructId);
				$recordId =& $record->getId();
			}
			
			$exisistingRecords[] =& $recordId->getIdString();
			
			$this->updateFileRecord($recordResults, 
				$initialState[$i], $record);
		}
		
		// Delete any records that were removed.
		$records =& $asset->getRecordsByRecordStructure($recStructId);
		while ($records->hasNext()) {
			$record =& $records->next();
			$recordId =& $record->getId();
			if (!in_array($recordId->getIdString(), $exisistingRecords))
				$asset->deleteRecord($recordId);
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
		if ($results['file_name']['checked'] == '1') {
			$parts['FILE_NAME']->updateValue($results['file_name']['value']);
		}
		
		// if the "use custom" box was checked store the mime type.
		if ($results['mime_type']['checked'] == '1') {
			$parts['MIME_TYPE']->updateValue($results['mime_type']['value']);
		}
		
		// if the "use custom" box was checked store the height.
		if ($results['height']['checked'] == '1'
			&& ereg("^([0-9]+)px$", $results['height']['value'], $matches)) 
		{
			$dimArray = $parts['DIMENSIONS']->getValue();
			$dimArray[1] = $matches[1];
			print "Setting DIMENSIONS to:"; printpre($dimArray);
			$parts['DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
		
		// if the "use custom" box was checked store the width.
		if ($results['width']['checked'] == '1'
			&& ereg("^([0-9]+)px$", $results['width']['value'], $matches)) 
		{
			$dimArray = $parts['DIMENSIONS']->getValue();
			$dimArray[0] = $matches[1];
			print "Setting DIMENSIONS to:"; printpre($dimArray);
			$parts['DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
		
		// if the "use custom" box was checked store the height.
		if ($results['thumbnail_height']['checked'] == '1'
			&& ereg("^([0-9]+)px$", $results['thumbnail_height']['value'], $matches)) 
		{
			$dimArray = $parts['THUMBNAIL_DIMENSIONS']->getValue();
			$dimArray[1] = $matches[1];
			print "Setting THUMBNAIL_DIMENSIONS to:"; printpre($dimArray);
			$parts['THUMBNAIL_DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
		
		// if the "use custom" box was checked store the width.
		if ($results['thumbnail_width']['checked'] == '1'
			&& ereg("^([0-9]+)px$", $results['thumbnail_width']['value'], $matches)) 
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
				
		ob_start();
		print "\n<h2>".$recStruct->getDisplayName()."</h2>";
		print "\n[[records]]";
		$step->setContent(ob_get_clean());
		
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
				preg_replace("/[^a-zA-Z0-9:_\-]/", "_", $partStructId->getIdString()),
				$component);
			
			
			print "\n\t<tr>";
			print "\n\t\t<th>";
			print "\n\t\t\t".$partStruct->getDisplayName();
	// 		print "\n"._("The Name for this <em>Asset</em>: ");
			print "\n\t\t</th>";
			print "\n\t\t<td>";
			print "\n\t\t\t[[".preg_replace("/[^a-zA-Z0-9:_\-]/", "_",  $partStructId->getIdString())."]]";
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
				$partStructIdString = preg_replace("/[^a-zA-Z0-9:_\-]/", "_", $partStructId->getIdString());
				
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
		
		$recStructIdString = preg_replace("/[^a-zA-Z0-9:_\-]/", "_", $recStructId->getIdString());
		$idManager =& Services::getService("Id");
		$exisistingRecords = array();
		
		foreach (array_keys($results[$recStructIdString]['records']) as $i) {
			$recordResults =& $results[$recStructIdString]['records'][$i];
			if ($recordResults['record_id']) {
				$recordId =& $idManager->getId($recordResults['record_id']);
				$record =& $asset->getRecord($recordId);
			} else {
				$record =& $asset->createRecord($recStructId);
				$recordId =& $record->getId();
			}
			
			$exisistingRecords[] = $recordId->getIdString();
			
			$this->updateRecord($recordResults, 
				$initialState[$recStructIdString]['records'][$i], $record);
		}
		
		// Delete any records that were removed.
		$records =& $asset->getRecordsByRecordStructure($recStructId);
		while ($records->hasNext()) {
			$record =& $records->next();
			$recordId =& $record->getId();
			if (!in_array($recordId->getIdString(), $exisistingRecords))
				$asset->deleteRecord($recordId);
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
		print "PartResults: ";
		printpre($partResults);
		print "InitialState: ";
		printpre($partInitialState);
		
		$partStructId = $partStruct->getId();
		
		$partStructType =& $partStruct->getType();
		$valueObjClass = $partStructType->getKeyword();
		
		if ($partResults->isNotEqualTo($partInitialState))
		{
			$parts =& $record->getPartsByPartStructure($partStructId);
			if ($parts->hasNext()) {
				$part =& $parts->next();
				$part->updateValue($partResults);
			} else {
				$part =& $record->createPart($partStructId, $partResults);
			}
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
	
	/**
	 * Answer the step for setting the parent of the asset
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 12/15/05
	 */
	function &getParentStep () {
		// :: Parent ::
		$step =& new WizardStep();
		$step->setDisplayName(_("Parent")." ("._("optional").")");
		
		// Create the properties.
		$property =& $step->addComponent("parent", new WSelectList());
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Parent <em>Asset</em>")."</h2>";
		print "\n"._("Optionally select one of the <em>Assets</em> below if you wish to make this asset a child of another asset: ");
		print "\n<br />[[parent]]";
		
		$step->setContent(ob_get_clean());
		
		
		$harmoni =& Harmoni::instance();
		$authZManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		// Check for authorization to remove the existing parent.
		$parents =& $this->_assets[0]->getParents();
		if ($parents->hasNext()) {
			$parent =& $parents->next();
			$parentId =& $parent->getId();
			
			// If we aren't authorized to change the parent, just use it as the only option.
			if ($authZManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.remove_children"),
				$parentId))
			{
				$property->addOption("NONE", _("None"));
				$property->addOption($parentId->getIdString(), $parentId->getIdString()." - ".$parent->getDisplayName());
				$property->setValue($parentId->getIdString());
			} else {
				$property->addOption($parentId->getIdString(), $parentId->getIdString()." - ".$parent->getDisplayName());
				$property->setValue($parentId->getIdString());

				return $step;
			}
		} else {
			$property->addOption("NONE", _("None"));
			$property->setValue("NONE");
		}
	
		
		// print options for the rest of the assets
		$repository =& $this->_assets[0]->getRepository();
		$assets =& $repository->getAssets();
		while ($assets->hasNext()) {
			$asset =& $assets->next();
			$assetId =& $asset->getId();
			if ($authZManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$assetId)
				&& (!isset($parentId) || !$assetId->isEqual($parentId))
				&& !$assetId->isEqual($this->_assets[0]->getId()))
			{
				$property->addOption($assetId->getIdString(), $assetId->getIdString()." - ".$asset->getDisplayName());
			}
		}
		
		return $step;
	}
}