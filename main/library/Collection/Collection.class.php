<?

require_once("CollectionAsset.class.php");
require_once("CollectionInfoStructure.class.php");
require_once(HARMONI."/oki/shared/HarmoniIterator.class.php");

/**
 * Collection manages Assets of various Types and information about the Assets.  Assets are created, persisted, and validated by the Collection.  When initially created, an Asset has an immutable Type and Unique Id and its validation status is false.  In this state, all methods can be called, but integrity checks are not enforced.  When the Asset and its InfoRecords are ready to be validated, the validateAsset method checks the Asset and sets the validation status.  When working with a valid Asset, all methods include integrity checks and an exception is thrown if the activity would result in an inappropriate state.  Optionally, the invalidateAsset method can be called to release the requirement for integrity checks, but the Asset will not become valid again, until validateAsset is called and the entire Asset is checked.    <p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition License}.
<p>SID Version: 1.0 rc6<p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition License}.
 * @package Concerto.collection
 */
class Collection
{
	
	var $_dr;
	
	/**
	 * Constructor
	 */
	function Collection (& $digitalRepository) {
		ArgumentValidator::validate($digitalRepository, new ExtendsValidatorRule("DigitalRepository"));
		
		$this->_dr =& $digitalRepository;
	}
	 
	/**
	 * Returns if this Asset is valid or not.
	 * @param object assetId
	 * @return bool
	 */
	function isAssetValid( & $assetId ) {
		return $this->_dr->isAssetValid($assetId);
	}

	/**
	 * Get the name for this Collection.
	 * @return String the name
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function getDisplayName() { 
		return $this->_dr->getDisplayName();
	}

	/**
	 * Update the name for this Collection.
	 * @param string displayName
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}
	 * @package Concerto.collection
	 */
	function updateDisplayName($displayName) { 
		$this->_dr->updateDisplayName($displayName);
	}

	/**
	 * Get the description for this Collection.
	 * @return String the name
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function getDescription() {
		return $this->_dr->getDescription();
	}

	/**
	 * Update the description for this Collection.
	 * @param string description
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}
	 * @package Concerto.collection
	 */
	function updateDescription($description) { 
		$this->_dr->updateDescription($description);
	}

	/**
	 * Get the Unique Id for this Collection.
	 * @return object osid.shared.Id Unique Id this is usually set by a create method's implementation
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getId() {
		return $this->_dr->getId();
	}

	/**
	 * Get the the CollectionType of this Collection.
	 * @return object osid.shared.Type
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getType() {
		return $this->_dr->getType();
	}

	/**
	 * Create a new Asset of this AssetType to this Collection.  The implementation of this method sets the Id for the new object.
	 * @return object Asset
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_TYPE UNKNOWN_TYPE}
	 * @package Concerto.collection
	 */
	function & createAsset($displayName, $description, & $assetType) {
		$drAsset =& $this->_dr->createAsset($displayName, $description, & $assetType);
		$id =& $drAsset->getId();
		$this->_createdAssets[$id->getIdString()] =& new CollectionAsset($drAsset);
		return $this->_createdAssets[$id->getIdString()];
	}

	/**
	 * Delete an Asset from this Collection.
	 * null
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function deleteAsset(& $assetId) {
		$this->_dr->deleteAsset($assetId);
		unset($this->_createdAssets[$assetId->getIdString()]);
	}

	/**
	 * Get all the Assets in this Collection.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object AssetIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getAssets() {
		$drAssets =& $this->_dr->getAssets();
		while ($drAssets->hasNext()) {
			$drAsset =& $drAssets->next();
			$id =& $drAsset->getId();
			if (!$this->_createdAssets[$id->getIdString()]) {
				$this->_createdAssets[$id->getIdString()] =& new CollectionAsset($drAsset);
			}
		}
		
		$iterator =& new HarmoniIterator($this->_createdAssets);
		return $iterator;
	}

	/**
	 * Get all the Assets of the specified AssetType in this Asset.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object AssetIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_TYPE UNKNOWN_TYPE}
	 * @package Concerto.collection
	 */
	function & getAssetsByType(& $assetType) {
		$drAssets =& $this->_dr->getAssetsByType($assetType);
		
		$assetsOfType =& array();
		while ($drAssets->hasNext()) {
			$drAsset =& $drAssets->next();
			$id =& $drAsset->getId();
			if (!$this->_createdAssets[$id->getIdString()]) {
				$this->_createdAssets[$id->getIdString()] =& new CollectionAsset($drAsset);
			}
			
			$assetsOfType[] =& $this->_createdAssets[$id->getIdString()];
		}
		
		$iterator =& new HarmoniIterator($assetsOfType);
		return $iterator;
	}

	/**
	 * Get all the AssetTypes in this Collection.  AssetTypes are used to categorize Assets.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object osid.shared.TypeIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getAssetTypes() {
		return $this->_dr->getAssetTypes();
	}

	/**
	 * Get all the InfoStructures in this Collection.  InfoStructures are used to categorize information about Assets.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object InfoStructureIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getInfoStructures() {
		return $this->_dr->getInfoStructures();
	}

	/**
	 * Get the InfoStructures that this AssetType must support.  InfoStructures are used to categorize information about Assets.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object InfoStructureIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_TYPE UNKNOWN_TYPE}
	 * @package Concerto.collection
	 */
	function & getMandatoryInfoStructures(& $assetType) {
		return $this->_dr->getInfoStructures();
	}

	/**
	 * Get all the SearchTypes supported by this Collection.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object osid.shared.TypeIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getSearchTypes() {
		return $this->_dr->getSearchTypes();
	}

	/**
	 * Get all the StatusTypes supported by this Collection.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object osid.shared.TypeIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getStatusTypes() {
		return $this->_dr->getSearchTypes();
	}

	/**
	 * Get the the StatusType of this Asset.
	 * @return object osid.shared.Type
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function & getStatus(& $assetId) {
		return $this->_dr->getStatus($assetId);
	}

	/**
	 * Validate all the InfoRecords for an Asset and set its status Type accordingly.  If the Asset is valid, return true; otherwise return false.  The implementation may throw an Exception for any validation failures and use the Exception's message to identify specific causes.
	 * null
	 * @return boolean
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function validateAsset(& $assetId) {
		return $this->_dr->validateAsset($assetId);
	}

	/**
	 * Set the Asset's status Type accordingly and relax validation checking when creating InfoRecords and InfoFields or updating InfoField's values.
	 * null
	 * @return boolean
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function invalidateAsset(& $assetId) {
		return $this->_dr->invalidateAsset($assetId);
	}

	/**
	 * Get the Asset with the specified Unique Id.
	 *  assetId
	 * @return object Asset
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function & getAsset(& $assetId) {
		if (!$this->_createdAssets[$assetId->getIdString()]) {
			$drAsset =& $this->_dr->getAsset($assetId);
			$this->_createdAssets[$assetId->getIdString()] =& new CollectionAsset($drAsset);
		}
		
		// Dish out the asset.
		return $this->_createdAssets[$assetId->getIdString()];
	}

	/**
	 * Get the Asset with the specified Unique Id and appropriate for the date specified.  The date permits
	 * @param object assetId
	 * @param object DateTime $date The date to get.
	 * @return object Asset
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#NO_OBJECT_WITH_THIS_DATE NO_OBJECT_WITH_THIS_DATE}
	 * @package Concerto.collection
	 */
	function & getAssetByDate(& $assetId, & $date) {
		$drAsset =& $this->_dr->getAssetByDate($assetId, $date);
		return new CollectionAsset($drAsset);
	}

	/**
	 * Get all the dates for the Asset with the specified Unique Id.  These dates could be for a form of versioning.
	 * @return object osid.shared.CalendarIterator
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}
	 * @package Concerto.collection
	 */
	function & getAssetDates(& $assetId) {
		return $this->_dr->getAssetDates($assetId);
	}

	/**
	 * Perform a search of the specified Type and get all the Assets that satisfy the SearchCriteria.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @param mixed searchCriteria
	 * @param object searchType
	 * @return object AssetIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_TYPE UNKNOWN_TYPE}
	 * @package Concerto.collection
	 */
	function & getAssetsBySearch(& $searchCriteria, & $searchType) {
		$drAssets =& $this->_dr->getAssetsBySearch(searchCriteria, $searchType);
		
		$assetsBySearch =& array();
		while ($drAssets->hasNext()) {
			$drAsset =& $drAssets->next();
			$id =& $drAsset->getId();
			if (!$this->_createdAssets[$id->getIdString()]) {
				$this->_createdAssets[$id->getIdString()] =& new CollectionAsset($drAsset);
			}
			
			$assetsBySearch[] =& $this->_createdAssets[$id->getIdString()];
		}
		
		$iterator =& new HarmoniIterator($assetsBySearch);
		return $iterator;
	}

	/**
	 * Create in a copy of an Asset.  The Id, AssetType, and Collection for the new Asset is set by the implementation.  All InfoRecords are similarly copied.
	 * @param object asset
	 * @return object osid.shared.Id
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function & copyAsset(& $asset) {
		return $this->_dr->copyAsset($asset);
	}
	
	/**
	 * Create an InfoStructure in this DR. This is not part of the DR OSID at 
	 * the time of this writing, but is needed for dynamically created 
	 * InfoStructures.
	 *
	 * @param string $displayName 	The DisplayName of the new InfoStructure.
	 * @param string $description 	The Description of the new InfoStructure.
	 * @param string $format 		The Format of the new InfoStructure.
	 * @param string $schema 		The schema of the new InfoStructure.
	 *
	 * @return object InfoStructure The newly created InfoStructure.
	 */
	function createInfoStructure($displayName, $description, $format, $schema) {
		$drInfoStructure =& $this->_dr->createInfoStructure($displayName, $description, $format, $schema);
		$id =& $drInfoStructure->getId();
		$this->_createdInfoStructures[$id->getIdString()] =& new CollectionInfoStructure(
																$drInfoStructure);
		return $this->_createdInfoStructures[$id->getIdString()];
	}

}
?>