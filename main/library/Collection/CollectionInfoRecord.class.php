<?

require_once(Concerto."/oki/collection/ConcertoInfoField.class.php");
require_once(Concerto."/oki/collection/ConcertoInfoFieldIterator.class.php");

	/**
	 * Each Asset has one of the AssetType supported by the Collection.  There are also zero or more InfoStructures required by the Collection for each AssetType. InfoStructures provide structural information.  The values for a given Asset's InfoStructure are stored in an InfoRecord.  InfoStructures can contain sub-elements which are referred to as InfoParts.  The structure defined in the InfoStructure and its InfoParts is used in for any InfoRecords for the Asset.  InfoRecords have InfoFields which parallel InfoParts.  <p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition License}.
	<p>SID Version: 1.0 rc6<p>Licensed under the {@link SidLicense MIT O.K.I&#46; SID Definition License}.
	 * @package Concerto.collection
	 */
class CollectionInfoRecord extends InfoRecord
//	extends java.io.Serializable
{
	
	var $_drInfoRecord;
	var $_infoStructure;
	var $_createdInfoFields;
	
	function CollectionInfoRecord( & $drInfoRecord, & $infoStructure) {
		$this->_drInfoRecord =& $drInfoRecord;
		$this->_infoStructure =& $infoStructure;		
		$this->_createdInfoFields = array();
	}

	/**
	 * Get the Unique Id for this InfoRecord.
	 * @return object osid.shared.Id Unique Id this is usually set by a create method's implementation
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getId() {
		return $this->_drInfoRecord->getId();
	}

	/**
	 * Create an InfoField.  InfoRecords are composed of InfoFields. InfoFields can also contain other InfoFields.  Each InfoRecord is associated with a specific InfoStructure and each InfoField is associated with a specific InfoPart.
	 * @param object infoPartId
	 * @param mixed value
	 * @return object InfoField
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function & createInfoField(& $infoPartId, & $value) {
		$drInfoField =& $this->_drInfoRecord->createInfoField($infoPartId, $value);
		$infoPart =& $this->_infoStructure->getInfoPart($infoPartId);
		
		$id =& $drInfoField->getId();
		$this->_createdInfoFields[$id->getIdString()] =& new CollectionInfoField($drInfoField, $infoPart, $this);
		return $this->_createdInfoFields[$id->getIdString()];
	}

	/**
	 * Delete an InfoField and all its InfoFields.
	 * @param object infoFieldId
	 * @throws collection.CollectionException An exception with one of the following 
	 * messages defined in collection.CollectionException may be thrown: 
	 * {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, 
	 * {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, 
	 * {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, 
	 * {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}, 
	 * {@link CollectionException#NULL_ARGUMENT NULL_ARGUMENT}, 
	 * {@link CollectionException#UNKNOWN_ID UNKNOWN_ID}
	 * @package Concerto.collection
	 */
	function deleteInfoField(& $infoFieldId) {
		$this->_drInfoRecord->deleteInfoField($infoFieldId);
		unset($this->_createdInfoFields[$infoFieldId->getIdString()]);
	}

	/**
	 * Get all the InfoFields in the InfoRecord.  Iterators return a group of items, one item at a time.  The Iterator's hasNext method returns <code>true</code> if there are additional objects available; <code>false</code> otherwise.  The Iterator's next method returns the next object.
	 * @return object InfoFieldIterator  The order of the objects returned by the Iterator is not guaranteed.
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getInfoFields() {
		$drInfoFields =& $this->_drInfoRecord->getInfoFields();
		while ($drInfoFields->hasNext()) {
			$drInfoField =& $drInfoFields->next();
			$id =& $drInfoField->getId();
			if (!$this->_createdInfoFields[$id->getIdString()]) {
				$drInfoPart =& $drInfoField->getInfoPart();
				$infoPartId =& $drInfoPart->getId();
				$infoPart =& $this->_infoStructure->getInfoPart($infoPartId);
				$this->_createdInfoFields[$id->getIdString()] =& new CollectionInfoField($drInfoField, $infoPart, $this);
			}
		}
		
		$iterator =& new HarmoniIterator($this->_createdInfoFields);
		return $iterator;
	}

	/**
	 * Return true if this InfoRecord is multi-valued; false otherwise.  This is determined by the implementation.
	 * @return boolean
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function isMultivalued() {
		return $this->_drInfoRecord->isMultivalued();
	}

	/**
	 * Get the InfoStructure associated with this InfoRecord.
	 * @return object InfoStructure
	 * @throws collection.CollectionException An exception with one of the following messages defined in collection.CollectionException may be thrown: {@link CollectionException#OPERATION_FAILED OPERATION_FAILED}, {@link CollectionException#PERMISSION_DENIED PERMISSION_DENIED}, {@link CollectionException#CONFIGURATION_ERROR CONFIGURATION_ERROR}, {@link CollectionException#UNIMPLEMENTED UNIMPLEMENTED}
	 * @package Concerto.collection
	 */
	function & getInfoStructure() {
		return $this->_infoStructure;
	}
}
