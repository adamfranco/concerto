<?

require_once("CollectionInfoPart.class.php");
require_once("CollectionInfoRecord.class.php");
require_once(HARMONI."/oki/shared/HarmoniIterator.class.php");

/**
 * Each Asset has one of the AssetType supported by the Collection.  There are also zero or more InfoStructures required by the Collection for each AssetType. InfoStructures provide structural information.  The values for a given Asset's InfoStructure are stored in an InfoRecord.  InfoStructures can contain sub-elements which are referred to as InfoParts.  The structure defined in the InfoStructure and its InfoParts is used in for any InfoRecords for the Asset.  InfoRecords have InfoFields which parallel InfoParts.  <p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition License}.
<p>SID Version: 1.0 rc6<p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition License}.
 * @package Concerto.collection
 */
class CollectionInfoStructure
//	extends java.io.Serializable
{
	
	var $_drInfoStructure;
	
	var $_createdInfoParts;

	
	function ConcertoInfoStructure( & $drInfoStructure ) {
		$this->_drInfoStructure =& $drInfoStructure;
		$this->_createdInfoParts = array();
	}
	
	/**
	 * Get the name for this InfoStructure.
	 * @return String the name
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function getDisplayName() {
		return $this->_drInfoStructure->getDisplayName();
	}

	/**
	 * Get the description for this InfoStructure.
	 * @return String the name
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function getDescription() {
		return $this->_drInfoStructure->getDescription();
	}

	/**
	 * Get the Unique Id for this InfoStructure.
	 * @return object osid.shared.Id Unique Id this is usually set by a create method's implementation
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getId() {
		return $this->_drInfoStructure->getId();
	}

	/**
	 * Get all the InfoParts in the InfoStructure.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object InfoPartIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getInfoParts() {
		$drInfoParts =& $this->_drInfoStructure->getInfoParts();
		while ($drInfoParts->hasNext()) {
			$drInfoPart =& $drInfoParts->next();
			$id =& $drInfoPart->getId();
			if (!$this->_createdInfoParts[$id->getIdString()]) {
				$this->_createdInfoParts[$id->getIdString()] =& new CollectionInfoPart($drInfoPart);
			}
		}
		
		$iterator =& new HarmoniIterator($this->_createdInfoParts);
		return $iterator;
	}

	/**
	 * Get the schema for this InfoStructure.  The schema is defined by the implementation, e.g. Dublin Core.
	 * @return String
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function getSchema() {
		return $this->_drInfoStructure->getSchema();
	}

	/**
	 * Get the format for this InfoStructure.  The format is defined by the  
	 * implementation, e.g. XML.
	 * @return String
	 * @throws collection.CollectionException An exception with one of the  
	 * following messages defined in collection.CollectionException may be thrown:  
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED},  
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED},  
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR},  
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function getFormat() {
		return $this->_drInfoStructure->getFormat();
	}

	/**
	 * Validate an InfoRecord against its InfoStructure.  Return true if valid; 
	 * false otherwise.  The status of the Asset holding this InfoRecord is not 
	 * changed through this method.  The implementation may throw an Exception 
	 * for any validation failures and use the Exception's message to identify 
	 * specific causes.
	 * @param object infoRecord
	 * @return boolean
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}
	 * @package Concerto.collection
	 */
	function validateInfoRecord(& $infoRecord) {
		return $this->_drInfoStructure->getFormat();
	}

	/**
	 * Create an InfoPart in this InfoStructure. This is not part of the DR OSID at 
	 * the time of this writing, but is needed for dynamically created 
	 * InfoStructures/InfoParts.
	 *
	 * @param string $displayName 	The DisplayName of the new InfoStructure.
	 * @param string $description 	The Description of the new InfoStructure.
	 * @param object Type $type	 	One of the InfoTypes supported by this implementation.
	 *								E.g. string, shortstring, blob, datetime, integer, float,
	 *								
	 * @param boolean $isMandatory 	True if the InfoPart is Mandatory.
	 * @param boolean $isRepeatable True if the InfoPart is Repeatable.
	 * @param boolean $isPopulatedByDR 	True if the InfoPart is PopulatedBy the DR.
	 *
	 * @return object InfoPart The newly created InfoPart.
	 */
	function createInfoPart($displayName, $description, & $infoPartType, $isMandatory, $isRepeatable, $isPopulatedByDR) {
		$drInfoPart =& $this->_drInfoStructure->createInfoPart($displayName, $description, $infoPartType, $isMandatory, $isRepeatable, $isPopulatedByDR);
		
		$id =& $drInfoPart->getId();
		$this->_createdInfoParts[$id->getIdString()] =& new CollectionInfoPart($drInfoPart, $this);
		return $this->_createdInfoParts[$id->getIdString()];
	}

	/**
	 * Get the possible types for InfoParts.
	 *
	 * @return object TypeIterator The Types supported in this implementation.
	 */
	function getInfoPartTypes() {
		return $this->_drInfoStructure->getInfoPartTypes();
	}
	
}
