<?

	/**
	 * Each Asset has one of the AssetType supported by the Collection.  There are also zero or more InfoStructures required by the Collection for each AssetType. InfoStructures provide structural information.  The values for a given Asset's InfoStructure are stored in an InfoRecord.  InfoStructures can contain sub-elements which are referred to as InfoParts.  The structure defined in the InfoStructure and its InfoParts is used in for any InfoRecords for the Asset.  InfoRecords have InfoFields which parallel InfoParts.  <p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition License}.
	<p>SID Version: 1.0 rc6<p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition License}.
	 * @package Concerto.collection
	 */
class ConcertoInfoPart extends InfoPart
//	extends java.io.Serializable
{

	var $_infoPart;
	var $_infoStructure;
	
	function ConcertoInfoPart( & $drInfoPart, & $infoStructure) {
		$this->_infoPart =& $drInfoPart;
		$this->_infoStructure =& $infoStructure;
	}
	
	/**
	 * Get the name for this InfoPart.
	 * @return String the name
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function getDisplayName() {
		return $this->_infoPart->getDisplayName();
	}

	/**
	 * Get the description for this InfoPart.
	 * @return String the name
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function getDescription() {
		return $this->_infoPart->getDescription();
	}

	/**
	 * Get the Unique Id for this InfoPart.
	 * @return object osid.shared.Id Unique Id this is usually set by a create method's implementation
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getId() {		
		return $this->_infoPart->getId();
	}

	/**
	 * Get all the InfoParts in the InfoPart.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object InfoPartIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getInfoParts() {
		$drInfoParts =& $this->_drInfoPart->getInfoParts();
		$collection =& $this->getCollection();
		$InfoParts = array();
		
		while ($drInfoParts->hasNext()) {
			$drInfoPart =& $drInfoPart->next();
			$id =& $drInfoPart->getId();
			$InfoParts[] =& $collection->getInfoPart($id);
		}
		
		// create an InfoPartIterator and return it
		$InfoPartIterator =& new HarmoniIterator($InfoParts);
		
		return $InfoPartIterator;
	}

	/**
	 * Return true if this InfoPart is automatically populated by the Collection; false otherwise.  Examples of the kind of InfoParts that might be populated are a time-stamp or the Agent setting the data.
	 * @return boolean
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function isPopulatedByDR() {
		return $this->_infoPart->isPopulatedByDR();
	}

	/**
	 * Return true if this InfoPart is mandatory; false otherwise.
	 * @return boolean
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function isManditory() {
		return $this->_infoPart->isManditory();
	}

	/**
	 * Return true if this InfoPart is repeatable; false otherwise.
	 * @return boolean
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function isRepeatable() {
		return $this->_infoPart->isRepeatable();
	}

	/**
	 * Get the InfoPart associated with this InfoStructure.
	 * @return object InfoStructure
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getInfoStructure() {
		return $this->_infoStructure;
	}

	/**
	 * Validate an InfoField against its InfoPart.  Return true if valid; false otherwise.  The status of the Asset holding this InfoRecord is not changed through this method.  The implementation may throw an Exception for any validation failures and use the Exception's message to identify specific causes.
	 * @param object infoField
	 * @return boolean
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}
	 * @package Concerto.collection
	 */
	function validateInfoField(& $infoField) {
		return $this->_infoPart->validateInfoField($infoField);
	}
}
