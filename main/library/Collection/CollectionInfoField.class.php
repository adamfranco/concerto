<?

	/**
	 * Each Asset has one of the AssetType supported by the Collection.  There are also zero or more InfoStructures required by the Collection for each AssetType. InfoStructures provide structural information.  The values for a given Asset's InfoStructure are stored in an InfoRecord.  InfoStructures can contain sub-elements which are referred to as InfoParts.  The structure defined in the InfoStructure and its InfoParts is used in for any InfoRecords for the Asset.  InfoRecords have InfoFields which parallel InfoParts.  <p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition License}.
	<p>SID Version: 1.0 rc6<p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition License}.
	 * @package Concerto.collection
	 */
class CollectionInfoField
{

	var $_drInfoField;
	var $_infoPart;
	var $_infoRecord;
	
	function ConcertoInfoField( & $drInfoField, & $infoPart, $infoRecord ) {
		$this->_drInfoField =& $drInfoField;
		$this->_infoPart =& $infoPart;
		$this->_infoRecord =& $infoRecord;
	}
	
	/**
	 * Get the Unique Id for this InfoStructure.
	 * @return object osid.shared.Id Unique Id this is usually set by a create method's implementation
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getId() {
		return $this->_drInfoField->getId();
	}

	/**
	 * Create an InfoField.  InfoRecords are composed of InfoFields. InfoFields can also contain other InfoFields.  Each InfoRecord is associated with a specific InfoStructure and each InfoField is associated with a specific InfoPart.
	 *  infoPartId
	 *  value
	 * @return object InfoField
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function & createInfoField(& $infoPartId, & $value) {
		$drInfoField =& $this->_drInfoField->createInfoField($infoPartId, $value);
		
		$id =& $drInfoField->getId();
		return $this->_infoRecord->getInfoField($id->getIdString());
	}

	/**
	 * Delete an InfoField and all its InfoFields.
	 *  infoFieldId
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function deleteInfoField(& $infoFieldId) {
		$this->_drInfoField->deleteInfoField($infoFieldId);
	}

	/**
	 * Get all the InfoFields in the InfoField.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object InfoFieldIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getInfoFields() {
		$drInfoFields =& $this->_drInfoField->getInfoFields();
		$infoFields = array();
		while ($drInfoFields->hasNext()) {
			$drInfoField =& $drInfoFields->next();
			$id =& $drInfoField->getId();
			$infoFields[] =& $this->_infoRecord->getInfoField($id);
		}
		
		// create an Iterator and return it
		$iterator =& new HarmoniIterator($infoFields);
		
		return $iterator;
	}

	/**
	 * Get the for this InfoField.
	 * @return java.io.Serializable
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getValue() {
		$this->_drInfoField->getValue();
	}

	/**
	 * Update the for this InfoField.
	 * null
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}
	 * @package Concerto.collection
	 */
	function updateValue(& $value) {
		$this->_drInfoField->updateValue($value);
	}

	/**
	 * Get the InfoPart associated with this InfoField.
	 * @return object InfoPart
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getInfoPart() {
		return $this->_infoPart;
	}
}
