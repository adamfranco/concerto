<?php
/**
 * @since 3/8/07
 * @package concerto.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Update the old-style edu.middlebury.concerto.collections_root id to
 * edu.middlebury.repositories_root
 * 
 * @since 3/8/07
 * @package concerto.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update003_CollectionRootAction 
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/8/07
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
	 * @since 3/8/07
	 */
	function getTitle () {
		return _("Convert From 'collections_root' to 'repositories_root'.");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/8/07
	 */
	function getDescription () {
		return _("This update will replace all occurances of the old-style 'edu.middlebury.concerto.collections_root' id with 'edu.middlebury.repositories_root'. After running the update you will need to update your repository configuration to use the new root id.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/8/07
	 */
	function isInPlace () {
		if (REPOSITORY_ROOT_ID == "edu.middlebury.concerto.collections_root")
			return false;
		else
			return true;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/8/07
	 */
	function runUpdate () {
		$dbc = Services::getService('DatabaseManager');
		
		$dbc->beginTransaction();
		
		$toUpdate = array(
			array(	'table'		=> "j_node_node",
					'column'	=> "fk_parent"),
					
			array(	'table'		=> "j_node_node",
					'column'	=> "fk_child"),
					
			array(	'table'		=> "node",
					'column'	=> "node_id"),
					
			array(	'table'		=> "node_ancestry",
					'column'	=> "fk_node"),
					
			array(	'table'		=> "node_ancestry",
					'column'	=> "fk_ancestor"),
					
			array(	'table'		=> "node_ancestry",
					'column'	=> "fk_ancestors_child")
			
		);
		
		foreach ($toUpdate as $unit) {
			$query = new UpdateQuery;
			$query->setTable($unit['table']);
			$query->setColumns(array($unit['column']));
			$query->setValues(array("'edu.middlebury.repositories_root'"));
			$query->addWhere($unit['column']."='edu.middlebury.concerto.collections_root'");
			$results =$dbc->query($query, 0);
			print "\n<br/>".$results->getNumberOfRows()." rows in ".$unit['table']." (column ".$unit['column'].") updated";
		}
		
		$dbc->commitTransaction();
		
		print "<div style='font-weight: bold'>"._("Please update your repository config to use the new repository root id, 'edu.middlebury.repositories_root'.")."</div>";
		
		return true;
	}
	
}

?>