<?php
/**
 * @since 7/9/07
 * @package concerto.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
 
require_once(HARMONI.'/DBHandler/GenericSQLQuery.class.php');
require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add a column to the repository's asset info table for a creator id
 * 
 * @since 7/9/07
 * @package concerto.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update004_AssetCreatorAction 
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 7/9/07
	 */
	function &getDateIntroduced () {
		$date =& Date::withYearMonthDay(2007, 7, 9);
		return $date;
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 7/9/07
	 */
	function getTitle () {
		return _("Add a 'creator' column to the dr_asset_info table.");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 7/9/07
	 */
	function getDescription () {
		return _("This update will add a column to store the id of the agent who created an asset.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/9/07
	 */
	function isInPlace () {
		$dbc =& Services::getService('DatabaseManager');
		$query = new GenericSQLQuery();
		$query->addSQLQuery("DESCRIBE `dr_asset_info`");
		$result =& $dbc->query($query);
		$result =& $result->returnAsSelectQueryResult();
		
		$exists = false;
		while($result->hasMoreRows()) {
			if ($result->field(0) == "creator") {
				$exists = true;
				break;
			}
			$result->advanceRow();
		}
		$result->free();
		
		return $exists;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/9/07
	 */
	function runUpdate () {
		$dbc =& Services::getService('DatabaseManager');
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("ALTER TABLE `dr_asset_info` ADD `creator` VARCHAR( 75 ) AFTER `create_timestamp`");
		
		$dbc->query($query);
		
		return true;
	}
}

?>