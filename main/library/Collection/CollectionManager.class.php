<?

require_once("Collection.class.php");
require_once("CollectionAsset.class.php");
require_once(HARMONI."/oki/shared/HarmoniIterator.class.php");

/**
 * The CollectionManager supports creating and deleting Collections
 * and Assets as well as getting the various Types used.  
 * <p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition 
 * License}.
 *
 * <p>SID Version: 1.0 rc6<p>Licensed under the {@link SidLicense MIT O.K.I&#46; 
 * SID Definition License}.
 * @package Concerto.collection
 */

class CollectionManager
{
	
	var $_configuration;
	var $_collectionValidFlags;
	var $_hierarchy;
	var $_createdDRs;
	
	/**
	 * Constructor
	 * @param array $configuration	An array of the configuration options 
	 * nessisary to load this manager. To use the a specific manager store, a 
	 * store data source must be configured as noted in the class of said 
	 * manager store.
	 * manager.
	 * @access public
	 */
	function CollectionManager ($configuration = NULL) {
		
		// Store the configuration
		$this->_configuration =& $configuration;
		
		$this->_drManager =& Services::getService("DR");
	}

	/**
	 * Create a new Collection of the specified Type.  The implementation
	 * of this method sets the Id for the new object.
	 * @param string displayName
	 * @param string description
	 * @param object Type collectionType
	 * @return object collection
	 * @throws collection.CollectionException An exception with one of the 
	 * following messages defined in collection.CollectionException may be 
	 * thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, 
	 * {@link CollectionException#UNKNOWN_TYPE UNKNOWN_TYPE}
	 * @package Concerto.collection
	 */
	function & createCollection ($displayName, $description, & $collectionType) {
		$dr =& $this->_drManager->createDigitalRepository ($displayName, $description, $collectionType);
	
		$id =& $dr->getId();
		$this->_collections[$id->getIdString()] =& new Collection($dr);
		return $this->_collections[$id->getIdString()];
	}

	/**
	 * Delete a Collection.
	 * null
	 * @param object Id $collectionId
	 * @throws collection.CollectionException An exception with one of the 
	 * following messages defined in collection.CollectionException may be 
	 * thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, 
	 * {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function deleteCollection(& $collectionId) {
		$this->_drManager->deleteDigitalRepository($collectionId);
		unset $this->_collections[$id->getIdString()];
	}

	/**
	 * Get all the Collections.  Iterators return a group of items, one 
	 * item at a time.  The Iterator's hasNext method returns <code>true</code> 
	 * if there are additional objects available; <code>false</code> otherwise.  
	 * The Iterator's next method returns the next object.
	 * @return object collectionIterator  The order of the objects 
	 * returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the 
	 * following messages defined in collection.CollectionException may be 
	 * thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getCollections() {
		$drs =& $this->_drManager->getDigitalRepositories();
		while ($drs->hasNext()) {
			$dr =& $drs->next();
			$id =& $dr->getId();
			if (!$this->_collections[$id->getIdString()]) {
				$this->_collections[$id->getIdString()] =& new Collection($dr);
			}
		}
		
		$iterator =& new HarmoniIterator($this->_collections);
		return $iterator;
	}

	/**
	 * Get all the Collections of the specified Type.  Iterators 
	 * return a group of items, one item at a time.  The Iterator's hasNext 
	 * method returns <code>true</code> if there are additional objects 
	 * available; 
	 * <code>false</code> otherwise.  The Iterator's next method returns the 
	 * next object.
	 * @param object Type collectionType
	 * @return object collectionIterator  The order of the objects returned
	 *  by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one 
	 * of the following messages defined in collection.CollectionException 
	 * may be thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, 
	 * {@link CollectionException#UNKNOWN_TYPE UNKNOWN_TYPE}
	 * @package Concerto.collection
	 */
	function & getCollectionsByType(& $collectionType) {
		$drs =& $this->_drManager->getDigitalRepositoriesByType($collectionType);
		
		$collectionsOfType = array();
		while ($drs->hasNext()) {
			$dr =& $drs->next();
			$id =& $dr->getId();
			if (!$this->_collections[$id->getIdString()]) {
				$this->_collections[$id->getIdString()] =& new Collection($dr);
			}
			$collectionsOfType[] =& $this->_collections[$id->getIdString()];
		}
		
		$iterator =& new HarmoniIterator($collectionsOfType);
		return $iterator;
	}

	/**
	 * Get a specific Collection by Unique Id.
	 * @param object Id $collectionId
	 * @return object collection
	 * @throws collection.CollectionException An exception with one of the 
	 * following messages defined in collection.CollectionException may be 
	 * thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, 
	 * {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function & getCollection(& $collectionId) {
		if (!$this->_collections[$collectionId->getIdString()]) {
			$dr =& $this->_drManager->getDigitalRepository($collectionId);
			$this->_collections[$collectionId->getIdString()] =& new Collection($dr);
		}
		
		return $this->_collections[$collectionId->getIdString()];
	}

	/**
	 * Get the Asset with the specified Unique Id.
	 * @param object Id $assetId
	 * @return object Asset
	 * @throws collection.CollectionException An exception with one of the
	 * following messages defined in collection.CollectionException may be 
	 * thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, 
	 * {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function & getAsset(& $assetId) {
		$drAsset =& $this->_drManager->getAsset($assetId);
		$dr =& $drAsset->getDigitalRepository();
		$drId =& $dr->getId();
		$collection =& $this->getCollection($drId);
		$asset =& $collection->getAsset($assetId);
		return $asset;
	}

	/**
	 * Get the Asset with the specified Unique Id and appropriate for the date 
	 * specified.  The date permits
	 * @param object Id $assetId
	 * @param object date
	 * @return object Asset
	 * @throws collection.CollectionException An exception with one of the 
	 * following messages defined in collection.CollectionException may be 
	 * thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, 
	 * {@link CollectionException#NO_OBJECT_WITH_THIS_DATE NO_OBJECT_WITH_THIS_DATE}
	 * @package Concerto.collection
	 */
	function & getAssetByDate(& $assetId, & $date) {
		$drAsset =& $this->_drManager->getAsset($assetId);
		$dr =& $drAsset->getDigitalRepository();
		$drId =& $dr->getId();
		$collection =& $this->getCollection($drId);
		$asset =& $collection->getAssetByDate($assetId, $date);
		return $asset;
	}

	/**
	 * Get all the dates for the Asset with the specified Unique Id.  These 
	 * dates could be for a form of versioning.
	 * @param object Id $assetId
	 * @return object osid.shared.CalendarIterator
	 * @throws collection.CollectionException An exception with one of the 
	 * following messages defined in collection.CollectionException may be 
	 * thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}
	 * @package Concerto.collection
	 */
	function & getAssetDates(& $assetId) {
		$drAsset =& $this->_drManager->getAsset($assetId);
		$dr =& $drAsset->getDigitalRepository();
		$drId =& $dr->getId();
		$collection =& $this->getCollection($drId);
		$dates =& $collection->getAssetDates($assetId);
		return $dates;
	}

	/**
	 * Perform a search of the specified Type and get all the Assets that 
	 * satisfy the SearchCriteria.  The search is performed for all specified 
	 * Collections.  Iterators return a group of items, one item at a 
	 * time.  The Iterator's hasNext method returns <code>true</code> if there 
	 * are additional objects available; <code>false</code> otherwise.  The 
	 * Iterator's next method returns the next object.
	 * @param object digitalRepositories
	 * @param mixed searchCriteria
	 * @param object searchType
	 * @return object AssetIterator  The order of the objects returned by the 
	 * Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the 
	 * following messages defined in collection.CollectionException may be 
	 * thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, 
	 * {@link CollectionException#UNKNOWN_TYPE UNKNOWN_TYPE}, 
	 * {@link CollectionException#UNKNOWN_DR UNKNOWN_DR}
	 * @package Concerto.collection
	 */
	function & getAssets(& $collections, & $searchCriteria, & $searchType) {
		$combinedAssets = array();
		
		foreach (array_keys($collections) as $key) {
			// Get the assets that match from this DR.
			$assets =& $collections[$key]->getAssetsBySearch($searchCriteria, $searchType);
			
			// Add the assets from this dr into our combined array.
			while ($assets->hasNext()) {
				$combinedAssets[] =& $assets->next();
			}
		}
		
		// create an AssetIterator with all fo the Assets in the createdAssets array
		$assetIterator =& new HarmoniIterator($combinedAssets);
		
		return $assetIterator;
	}
	
	/**
	 * Create in a Collection a copy of an Asset.  The Id, AssetType, and 
	 * Collection for the new Asset is set by the implementation.  All 
	 * InfoRecords are similarly copied.
	 * @param object collection
	 * @param object assetId
	 * @return object osid.shared.Id
	 * @throws collection.CollectionException An exception with one of the 
	 * following messages defined in collection.CollectionException may be 
	 * thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, 
	 * {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function & copyAsset(& $collection, & $assetId) {
		$asset =& $collection->getAsset($assetId);
		return $collection->copyAsset( $asset );
	}
	

	/**
	 * Get all the CollectionTypes in this CollectionManager.  
	 * CollectionTypes are used to categorize Collections.  
	 * Iterators return a group of items, one item at a time.  The Iterator's 
	 * hasNext method returns <code>true</code> if there are additional objects 
	 * available; <code>false</code> otherwise.  The Iterator's next method 
	 * returns the next object.
	 * @return object osid.shared.TypeIterator  The order of the objects 
	 * returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the 
	 * following messages defined in collection.CollectionException may be 
	 * thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getCollectionTypes() {
		return $this->_drManager->getDigitalRepositoryTypes();
	}


	/**
	 * The start function is called when a service is created. Services may
	 * want to do pre-processing setup before any users are allowed access to
	 * them.
	 * @access public
	 * @return void
	 **/
	function start() {
	}
	
	/**
	 * The stop function is called when a Concerto service object is being 
	 * destroyed.
	 * Services may want to do post-processing such as content output or 
	 * committing changes to a database, etc.
	 * @access public
	 * @return void
	 **/
	function stop() {
	}
}

?>