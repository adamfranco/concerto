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
require_once(dirname(__FILE__)."/OAI.class.php");

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
		if (!isset($_SESSION['oai_table_setup_complete'])) {
			$dbc = Services::getService("DatabaseManager");
			$harmoni = Harmoni::instance();
			$config =$harmoni->getAttachedData('OAI_CONFIG');
			$tables = $dbc->getTableList($config->getProperty('OAI_DBID'));
			$harvesterConfig = $config->getProperty('OAI_HARVESTER_CONFIG');
			
			foreach ($harvesterConfig as $configArray) {
				$table = 'oai_'.$configArray['name'];
				if (!in_array($table, $tables)) {
					$queryString = file_get_contents(
						dirname(__FILE__)."/phpoai2/doc/oai_records_mysql.sql");
					$queryString = str_replace('oai_records', $table, $queryString);
					
					$query = new GenericSQLQuery;
					$query->addSQLQuery(SQLUtils::parseSQLString($queryString));
					
					$dbc->query($query,	$config->getProperty('OAI_DBID'));
				}
			}
			
			$_SESSION['oai_table_setup_complete'] = true;
		}
		
		
		require(dirname(__FILE__)."/phpoai2/oai2.php");
		
		
		exit;
	}
}

?>
