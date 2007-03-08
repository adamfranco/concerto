<?php
/**
 * @package concerto.modules.admin
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * 
 * 
 * @package concerto.modules.admin
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class providerAction 
	extends Action
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function execute () {
		$harmoni =& Harmoni::instance();
		if (!isset($_SESSION['oai_table_setup_complete'])) {
			$dbc =& Services::getService("DatabaseManager");
			$tables = $dbc->getTableList(OAI_DBID);
			
			if (!in_array('oai_records', $tables))
				SQLUtils::runSQLfile(dirname(__FILE__)."/phpoai2/doc/oai_records_mysql.sql", OAI_DBID);
			
			$_SESSION['oai_table_setup_complete'] = true;
		}
		
		
		require(dirname(__FILE__)."/phpoai2/oai2.php");
		
		while (ob_get_level())
			ob_end_flush();
		exit;
	}
}

?>
