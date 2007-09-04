<?php
/**
 * @since 3/6/07
 * @package concerto.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * <##>
 * 
 * @since 3/6/07
 * @package concerto.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update001_DublinCoreAction
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/5/07
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2007, 3, 6);
		return $date;
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/5/07
	 */
	function getTitle () {
		return _('Convert Dublin Core ids.');
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/5/07
	 */
	function getDescription () {
		return _("This update will replace all occurances of the first existing Dublin Core schema id with 'dc' in the repository implemenation as well as in Sets.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/5/07
	 */
	function isInPlace () {
		$repositoryManager = Services::getService('Repository');
		$dcId =$this->getDestId();
		$repositories =$repositoryManager->getRepositories();
		if ($repositories->hasNext()) {
			$repository =$repositories->next();
			$recStructs =$repository->getRecordStructures();
			while ($recStructs->hasNext()) {
				$recStruct =$recStructs->next();
				if ($dcId->isEqual($recStruct->getId()))
					return true;
			}
			
			return false;
		} else {
			return true;
		}		
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/7/07
	 */
	function runUpdate () {
		$repositoryManager = Services::getService('Repository');
		$dcId =$this->getDestId();
		$repositories =$repositoryManager->getRepositories();
		if ($repositories->hasNext()) {
			$repository =$repositories->next();
			$recStructs =$repository->getRecordStructures();
			while ($recStructs->hasNext()) {
				$recStruct =$recStructs->next();
				if ($recStruct->getDisplayName() == $this->getSourceName()) {
					$sourceId =$recStruct->getId();
					return $this->convertRecordStructureIds($sourceId, $dcId);
				}
			}
			
			print $this->getSourceName()." not found.";
		} else {
			print "No Repositories";
		}
		
		return false;
	}
	
	/**
	 * Convert all occurrances of the sourceId in the data manager and sets
	 * into the destination id.
	 * 
	 * @param object Id $sourceId
	 * @param object Id $destId
	 * @return boolean
	 * @access public
	 * @since 3/7/07
	 */
	function convertRecordStructureIds ( $sourceId, $destId ) {
		$sourceIdString = $sourceId->getIdString();
		$destIdString = $destId->getIdString();
		
		printpre("Converting from id '".$sourceIdString."' to '".$destIdString."'.");
		
		$fieldMapping = $this->getFieldMapping();
		$dbc = Services::getService('DatabaseManager');
		
		$dbc->beginTransaction();
		
		$query = new SelectQuery;
		$query->addColumn('id');
		$query->addColumn('name');
		$query->addTable('dm_schema_field');
		$query->addWhere("fk_schema='".addslashes($sourceIdString)."'");
		$results =$dbc->query($query, 0);
		
		while ($results->hasNext()) {
			$row = $results->next();
			$fieldMapping[$row['name']]['source_id'] = $row['id'];
		}
		$results->free();
		
		
		// Update the dm_schema table
		$query = new UpdateQuery;
		$query->setTable('dm_schema');
		$query->setColumns(array('id'));
		$query->setValues(array("'".addslashes($destIdString)."'"));
		$query->addWhere("id='".addslashes($sourceIdString)."'");
		$results =$dbc->query($query, 0);
		print "\n<br/>".$results->getNumberOfRows()." "._("rows in dm_schema updated");
		
		// Check to ensure that all mappings are valid
		foreach ($fieldMapping as $mapping) {
			if (!$mapping['dest_id'] || !$mapping['source_id']) {
				print "\n<br/>Error: the following mapping is invalid: ";
				printpre($mapping);
				return false;
			}
		}
		
		// Update the dm_schema_field table
		foreach ($fieldMapping as $mapping) {
			$query = new UpdateQuery;
			$query->setTable('dm_schema_field');
			$query->setColumns(array(
				'id', 
				'fk_schema'
			));
			$query->setValues(array(
				"'".addslashes($mapping['dest_id'])."'",
				"'".addslashes($destIdString)."'"
			));
			$query->addWhere("id='".addslashes($mapping['source_id'])."'");
			$results =$dbc->query($query, 0);
			print "\n<br/>".$results->getNumberOfRows()." "._("rows in dm_schema_field updated");
		}
		
		// Update the dm_record table
		$query = new UpdateQuery;
		$query->setTable('dm_record');
		$query->setColumns(array('fk_schema'));
		$query->setValues(array("'".addslashes($destIdString)."'"));
		$query->addWhere("fk_schema='".addslashes($sourceIdString)."'");
		$results =$dbc->query($query, 0);
		print "\n<br/>".$results->getNumberOfRows()." "._("rows in dm_record updated");
		
		// Update the dm_record_field table
		foreach ($fieldMapping as $mapping) {
			$query = new UpdateQuery;
			$query->setTable('dm_record_field');
			$query->setColumns(array('fk_schema_field'));
			$query->setValues(array("'".addslashes($mapping['dest_id'])."'"));
			$query->addWhere("fk_schema_field='".addslashes($mapping['source_id'])."'");
			$results =$dbc->query($query, 0);
			print "\n<br/>".$results->getNumberOfRows()." "._("rows in dm_record_field updated");
		}
		
		// Update the sets table
		$query = new UpdateQuery;
		$query->setTable('sets');
		$query->setColumns(array('id'));
		$query->setValues(array("'".addslashes($destIdString)."'"));
		$query->addWhere("id='".addslashes($sourceIdString)."'");
		$results =$dbc->query($query, 0);
		print "\n<br/>".$results->getNumberOfRows()." "._("rows in sets updated");
		
		$query = new UpdateQuery;
		$query->setTable('sets');
		$query->setColumns(array('item_id'));
		$query->setValues(array("'".addslashes($destIdString)."'"));
		$query->addWhere("item_id='".addslashes($sourceIdString)."'");
		$results =$dbc->query($query, 0);
		print "\n<br/>".$results->getNumberOfRows()." "._("rows in sets updated");
		
		// Update the dm_record_field table
		foreach ($fieldMapping as $mapping) {
			$query = new UpdateQuery;
			$query->setTable('sets');
			$query->setColumns(array('id'));
			$query->setValues(array("'".addslashes($mapping['dest_id'])."'"));
			$query->addWhere("id='".addslashes($mapping['source_id'])."'");
			$results =$dbc->query($query, 0);
			print "\n<br/>".$results->getNumberOfRows()." "._("rows in sets updated");
			
			$query = new UpdateQuery;
			$query->setTable('sets');
			$query->setColumns(array('item_id'));
			$query->setValues(array("'".addslashes($mapping['dest_id'])."'"));
			$query->addWhere("item_id='".addslashes($mapping['source_id'])."'");
			$results =$dbc->query($query, 0);
			print "\n<br/>".$results->getNumberOfRows()." "._("rows in sets updated");
		}
		
		$dbc->commitTransaction();
		
		return true;
	}
	
	/**
	 * Answer the id that we will be converting to
	 * 
	 * @return object Id
	 * @access public
	 * @since 3/7/07
	 */
	function getDestId () {
		$idManager = Services::getService('Id');
		$dcId =$idManager->getId('dc');
		return $dcId;
	}
	
	/**
	 * Answer the name of the source Record Structure that will be
	 * converted to the destination Id
	 * 
	 * @return string
	 * @access public
	 * @since 3/7/07
	 */
	function getSourceName () {
		return 'Dublin Core';
	}
	
	/**
	 * Answer a mapping of Name to destination id
	 * 
	 * @return array
	 * @access public
	 * @since 3/7/07
	 */
	function getFieldMapping () {
		return array (
			'Title' 		=> array ('dest_id' => 'dc.title'),
			'Creator'	 	=> array ('dest_id' => 'dc.creator'),
			'Subject' 		=> array ('dest_id' => 'dc.subject'),
			'Description' 	=> array ('dest_id' => 'dc.description'),
			'Publisher' 	=> array ('dest_id' => 'dc.publisher'),
			'Contributor' 	=> array ('dest_id' => 'dc.contributor'),
			'Date' 			=> array ('dest_id' => 'dc.date'),
			'Type' 			=> array ('dest_id' => 'dc.type'),
			'Format' 		=> array ('dest_id' => 'dc.format'),
			'Identifier' 	=> array ('dest_id' => 'dc.identifier'),
			'Source' 		=> array ('dest_id' => 'dc.source'),
			'Language' 		=> array ('dest_id' => 'dc.language'),
			'Relation' 		=> array ('dest_id' => 'dc.relation'),
			'Coverage' 		=> array ('dest_id' => 'dc.coverage'),
			'Rights' 		=> array ('dest_id' => 'dc.rights')
		);
	}
}

?>