<?

require_once("CollectionInfoStructure.class.php");
require_once("CollectionInfoRecord.class.php");
require_once("CollectionInfoField.class.php");
require_once(HARMONI."/oki/shared/HarmoniIterator.class.php");

/**
 * Asset manages the Asset itself.  Assets have content as well as InfoRecords
 * appropriate to the AssetType and InfoStructures for the Asset.  Assets may
 * also contain other Assets.
 *
 * @package Concerto.collection
 * @author Adam Franco
 * @copyright 2004 Middlebury College
 * @access public
 */

class CollectionAsset
{ // begin Asset
	
	var $_drAsset;
	
	var $_createdInfoRecords;
	
	/**
	 * Constructor
	 * 
	 *
	 *
	 */
	function CollectionAsset (& $drAsset) {
		ArgumentValidator::validate($digitalRepository, new ExtendsValidatorRule("Asset"));
		
	 	$this->_drAsset =& $drAsset;
	 	$this->_createdInfoRecords = array();
	 }

	/**
	 * Get the display name for this Asset.
	 *
	 * @return String the display name
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function getDisplayName() {
		return $this->_drAsset->getDisplayName();
	}

	/**
	 * Update the display name for this Asset.
	 *
	 * @param String displayName
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function updateDisplayName($displayName) {
		$this->_drAsset->updateDisplayName($displayName);
	}

	/**
	 * Get the description for this Asset.
	 *
	 * @return String the description
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function getDescription() {
		return $this->_drAsset->getDescription();
	}

	/**
	 * Update the description for this Asset.
	 *
	 * @param String description
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function updateDescription($description) {
		$this->_drAsset->updateDescription($description);
	}

	/**
	 * Get the unique Id for this Asset.
	 *
	 * @return object osid.shared.Id A unique Id that is usually set by a create
	 *		 method's implementation
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getId() {
		return $this->_drAsset->getId();
	}

	/**
	 * Get the Collection in which this Asset resides.  This is set by
	 * the Collection's createAsset method.
	 *
	 * @return object osid.shared.Id A unique Id that is usually set by a create
	 *		 method's implementation collectionId
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getCollection() {
		$dr =& $this->_drAsset->getDigitalRepository();
		$drId =& $dr->getId();
		
		$collectionManager =& Services::getService("CollectionManager");
		return $collectionManager->getCollection($drId);
	}

	/**
	 * Get an Asset's content.  This method can be a convenience if one is not
	 * interested in all the structure of the InfoRecords.
	 *
	 * @return java.io.Serializable
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getContent() {
		return $this->_drAsset->getContent();
	}

	/**
	 * Update an Asset's content.
	 *
	 * @param mixed java.io.Serializable
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function updateContent(& $content) {
		$this->_drAsset->updateContent($content);
	}

	/**
	 * Get an Asset's EffectiveDate
	 *
	 * @return java.util.Calendar
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getEffectiveDate() {
		return $this->_drAsset->getEffectiveDate();
	}

	/**
	 * Update an Asset's EffectiveDate.
	 *
	 * @param object java.util.Calendar
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function updateEffectiveDate(& $effectiveDate) {
		return $this->_drAsset->updateEffectiveDate($effectiveDate);
	}

	/**
	 * Get an Asset's EffectiveDate
	 *
	 * @return java.util.Calendar
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getExpirationDate() {
		return $this->_drAsset->getExpirationDate();
	}

	/**
	 * Update an Asset's ExpirationDate.
	 *
	 * @param object java.util.Calendar
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function updateExpirationDate(& $expirationDate) {
		return $this->_drAsset->updateExpirationDate($expirationDate);
	}

	/**
	 * Add an Asset to this Asset.
	 *
	 * @param object osid.shared.Id assetId
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT, UNKNOWN_ID, ALREADY_ADDED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function addAsset(& $assetId) {
		$this->_drAsset->addAsset($assetId);
	}

	/**
	 * Remove an Asset to this Asset.  This method does not delete the Asset
	 * from the Collection.
	 *
	 * @param object osid.shared.Id assetId
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT, UNKNOWN_ID
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function removeAsset(& $assetId, $includeChildren) {
		$this->_drAsset->removeAsset($assetId, $includeChildren);
	}

	/**
	 * Get all the Assets in this Asset.  Iterators return a set, one at a
	 * time.  The Iterator's hasNext method returns true if there are
	 * additional objects available; false otherwise.  The Iterator's next
	 * method returns the next object.
	 *
	 * @return object collection.AssetIterator  The order of the objects returned by the
	 *		 Iterator is not guaranteed.
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	/**
	 * Get all the Assets of the specified AssetType in this Collection.
	 * Iterators return a set, one at a time.  The Iterator's hasNext method
	 * returns true if there are additional objects available; false
	 * otherwise.  The Iterator's next method returns the next object.
	 *
	 * @return object collection.AssetIterator  The order of the objects returned by the
	 *		 Iterator is not guaranteed.
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT, UNKNOWN_TYPE
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getAssets() {
		$drAssets =& $this->_drAsset->getAssets();
		$collection =& $this->getCollection();
		$assets = array();
		
		while ($drAssets->hasNext()) {
			$drAsset =& $drAsset->next();
			$id =& $drAsset->getId();
			$assets[] =& $collection->getAsset($id);
		}
		
		// create an AssetIterator and return it
		$assetIterator =& new HarmoniIterator($assets);
		
		return $assetIterator;
	}

	/**
	 * Create a new Asset InfoRecord of the specified InfoStructure.   The
	 * implementation of this method sets the Id for the new object.
	 *
	 * @param object osid.shared.Id infoStructureId
	 *
	 * @return object collection.InfoRecord
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT, UNKNOWN_ID
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & createInfoRecord(& $infoStructureId) {
		$drInfoRecord =& $this->_drAsset->createInfoRecord($infoStructureId);
		$id =& $drInfoRecord->getId();
		
		return $this->getInfoRecord($id);
	}

	/**
	 * Add the specified InfoStructure and all the related InfoRecords from the
	 * specified asset.  The current and future content of the specified
	 * InfoRecord is synchronized automatically.
	 *
	 * @param object osid.shared.Id assetId
	 * @param object osid.shared.Id infoStructureId
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT, ALREADY_INHERITING_STRUCTURE
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function inheritInfoStructure(& $infoStructureId, & $assetId) {	
		$this->_drAsset->inheritInfoStructure($infoStructureId, $assetId);
	}

	/**
	 * Add the specified InfoStructure and all the related InfoRecords from the
	 * specified asset.
	 *
	 * @param object osid.shared.Id assetId
	 * @param object osid.shared.Id infoStructureId
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function copyInfoStructure(& $infoStructureId, & $assetId) {
		$this->_drAsset->copyInfoStructure($infoStructureId, $assetId);
	}

	/**
	 * Delete an InfoRecord.  If the specified InfoRecord has content that is
	 * inherited by other InfoRecords, those
	 *
	 * @param object osid.shared.Id infoRecordId
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT, UNKNOWN_ID
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function deleteInfoRecord(& $infoRecordId) {
		$this->_drAsset->deleteInfoRecord($infoRecordId);
	}

	/**
	 * Get the InfoRecord of the specified ID for this Asset.
	 *
	 * @param object osid.shared.Id infoRecordId
	 *
	 * @return object collection.InfoRecord 
	 *
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getInfoRecord(& $infoRecordId ) {
		if (!$this->_createdInfoRecords[$infoRecordId->getIdString()]) {
			$drInfoRecord =& $this->_drAsset->getInfoRecord($infoRecordId);
			$drInfoStructure =& $drInfoRecord->getInfoStructure();
			$infoStructureId =& $drInfoStructure->getId();
			$collection =& $this->getCollection();
			$infoStructure =& $collection->getInfoStructure($infoStructureId);
			
			$this->_createdInfoRecords[$infoRecordId->getIdString()] =& new CollectionInfoRecord($drInfoRecord, $infoStructure, $this);
		}
				
		return $this->_createdInfoRecords[$infoRecordId->getIdString()];
	}

	/**
	 * Get all the InfoRecords for this Asset.  Iterators return a set, one at
	 * a time.  The Iterator's hasNext method returns true if there are
	 * additional objects available; false otherwise.  The Iterator's next
	 * method returns the next object.
	 *
	 * @return object collection.InfoRecordIterator  The order of the objects returned by
	 *		 the Iterator is not guaranteed.
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */	 
	/**
	 * Get all the InfoRecords of the specified InfoStructure for this Asset.
	 * Iterators return a set, one at a time.  The Iterator's hasNext method
	 * returns true if there are additional objects available; false
	 * otherwise.  The Iterator's next method returns the next object.
	 *
	 * @param object osid.shared.Id infoStructureId
	 *
	 * @return object collection.InfoRecordIterator  The order of the objects returned by
	 *		 the Iterator is not guaranteed.
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED, NULL_ARGUMENT, CANNOT_COPY_OR_INHERIT_SELF
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getInfoRecords( $infoStructureId = null ) {
		$drInfoRecords =& $this->_drAsset->getInfoRecords($infoStructureId);
		
		$infoRecords = array();
		
		while ($drInfoRecords->hasNext()) {
			$drInfoRecord =& $drInfoRecords->next();
			$infoRecordId =& $drInfoRecord->getId();
			
			if (!$this->_createdInfoRecords[$infoRecordId->getIdString()]) {
				$drInfoStructure =& $drInfoRecord->getInfoStructure();
				$infoStructureId =& $drInfoStructure->getId();
				$collection =& $this->getCollection();
				$infoStructure =& $collection->getInfoStructure($infoStructureId);

				$this->_createdInfoRecords[$infoRecordId->getIdString()] =& new CollectionInfoRecord($drInfoRecord, $infoStructure, $this);
			}
					
			$infoRecords[] =& $this->_createdInfoRecords[$infoRecordId->getIdString()];
		
		$iterator =& new HarmoniIterator($infoRecords);
		return $iterator;
	}

	/**
	 * Description_getAssetTypes=Get the AssetType of this Asset.  AssetTypes
	 * are used to categorize Assets.
	 *
	 * @return object osid.shared.Type
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getAssetType() {
		return $this->_drAsset->getType();
	}

	/**
	 * Get all the InfoStructures for this Asset.  InfoStructures are used to
	 * categorize information about Assets.  Iterators return a set, one at a
	 * time.  The Iterator's hasNext method returns true if there are
	 * additional objects available; false otherwise.  The Iterator's next
	 * method returns the next object.
	 *
	 * @return object osid.shared.TypeIterator The order of the objects returned by
	 *		 the Iterator is not guaranteed.
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getInfoStructures() {
		$drInfoStructures =& $this->_drAsset->getInfoStructures();
		$collection =& $this->getCollection();
		
		$infoStructures = array();
		
		while ($drInfoStructures->hasNext()) {
			$drInfoStructure =& $drInfoStructures->next();
			$infoStructureId =& $drInfoStructure->getId();
			
			if (!$this->_createdInfoStructures[$infoStructureId->getIdString()]) {
				$this->_createdInfoStructures[$infoStructureId->getIdString()] =& $collection->getInfoStructure($infoStructureId);
			}
					
			$infoStructures[] =& $this->_createdInfoStructures[$infoStructureId->getIdString()];
		
		$iterator =& new HarmoniIterator($infoStructures);
		return $iterator;
	}

	/**
	 * Get the InfoStructure associated with this Asset's content.
	 *
	 * @return object collection.InfoStructure
	 *
	 * @throws An exception with one of the following messages defined in
	 *		 collection.CollectionException may be thrown:
	 *		 OPERATION_FAILED
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getContentInfoStructure() {
		$drInfoStructure =& $this->_drAsset->getContentInfoStructure();
		$collection =& $this->getCollection();
		
		$infoStructureId =& $drInfoStructure->getId();
			
		if (!$this->_createdInfoStructures[$infoStructureId->getIdString()]) {
			$this->_createdInfoStructures[$infoStructureId->getIdString()] =& $collection->getInfoStructure($infoStructureId);
			}
					
		return $this->_createdInfoStructures[$infoStructureId->getIdString()];
	}
	
	/**
	 * Get the InfoField for an InfoRecord for this Asset that matches this 
	 * InfoField Unique Id.
	 *
	 * @param object osid.shared.Id infoFieldId
	 *
	 * @return object collection.InfoField
	 *
	 * @throws An exception with one of the following messages defined in 
	 * 		collection.CollectionException may be thrown: 
	 * 		OPERATION_FAILED, PERMISSION_DENIED, CONFIGURATION_ERROR, 
	 *		UNIMPLEMENTED, NULL_ARGUMENT, UNKNOWN_ID
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getInfoField(& $infoFieldId) {
		$drInfoField =& $this->_drAsset->getInfoField($infoFieldId);
		
		return new CollectionInfoField($drInfoField);
	}
	
	/**
	 * Get the Value of the InfoField of the InfoRecord for this Asset that 
	 * matches this InfoField Unique Id.
	 *
	 * @param object osid.shared.Id infoFieldId
	 *
	 * @return java.io.Serializable
	 *
	 * @throws An exception with one of the following messages defined in 
	 * 		collection.CollectionException may be thrown: 
	 * 		OPERATION_FAILED, PERMISSION_DENIED, CONFIGURATION_ERROR, 
	 *		UNIMPLEMENTED, NULL_ARGUMENT, UNKNOWN_ID
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getInfoFieldValue(& $infoFieldId) {
		$infoField =& $this->getInfoField($infoFieldId);
		return $infoField->getValue();
	}
	
	/**
	 * Get the InfoFields of the InfoRecords for this Asset that are based 
	 * on this InfoStructure InfoPart Unique Id.
	 *
	 * @param object osid.shared.Id infoPartId
	 *
	 * @return object collection.InfoFieldIterator
	 *
	 * @throws An exception with one of the following messages defined in 
	 * 		collection.CollectionException may be thrown: 
	 * 		OPERATION_FAILED, PERMISSION_DENIED, CONFIGURATION_ERROR, 
	 *		UNIMPLEMENTED, NULL_ARGUMENT, UNKNOWN_ID
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getInfoFieldByPart(& $infoPartId) {
		$drInfoFields =& $this->getInfoFieldsByPart($infoPartId);
		
		$infoFields = array();
		
		while ($drInfoFields->hasNext()) {
			$drInfoField =& $drInfoFields->next();
			$infoFieldId =& $drInfoField->getId();
			$drInfoRecord =& $drInfoField->getInfoRecord();
			$infoRecordId =& $drInfoRecord->getId();
			$infoRecord =& $this->getInfoRecord($infoRecordId);
			
			$infoFields[] =& $infoRecord->getInfoField($infoFieldId);
		}
		
		$iterator =& new HarmoniIterator($infoFields);
		return $iterator;
	}
	
	/**
	 * Get the Values of the InfoFields of the InfoRecords for this Asset
	 * that are based on this InfoStructure InfoPart Unique Id.
	 *
	 * @param object osid.shared.Id infoPartId
	 *
	 * @return object osid.shared.SerializableObjectIterator
	 *
	 * @throws An exception with one of the following messages defined in 
	 * 		collection.CollectionException may be thrown: 
	 * 		OPERATION_FAILED, PERMISSION_DENIED, CONFIGURATION_ERROR, 
	 *		UNIMPLEMENTED, NULL_ARGUMENT, UNKNOWN_ID
 	 *
	 * @todo Replace JavaDoc with PHPDoc
	 */
	function & getInfoFieldValueByPart(& $infoPartId) {
		$infoFields =& $this->getInfoFieldsByPart($infoPartId);
		
		$infoFieldValues = array();
		
		while ($infoFields->hasNext()) {
			$infoField =& $infoFields->next();
			$infoFieldValues[] =& $infoField->getValue();
		
		$iterator =& new HarmoniIterator($infoFieldValues);
		return $iterator;
	}

} // end Asset

?>