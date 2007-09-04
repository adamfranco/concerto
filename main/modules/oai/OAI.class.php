<?php
/**
 * @since 3/9/07
 * @package concerto.oai
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * Utilities for the OAI system
 * 
 * @since 3/9/07
 * @package concerto.oai
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class OAI {
		
	/**
	 * Answer the current record-table name
	 * 
	 * @return string
	 * @access public
	 * @static
	 * @since 3/9/07
	 */
	function getCurrentRecordTable () {
		$currentHarvesterConfig = OAI::getCurrentHarvesterConfig();
		return 'oai_'.$currentHarvesterConfig['name'];
	}
	
	/**
	 * Answer the current harvester config array
	 * 
	 * @return string
	 * @access public
	 * @static
	 * @since 3/9/07
	 */
	function getCurrentHarvesterConfig () {
		global $errors;
		
		$harmoni = Harmoni::instance();
		$config =$harmoni->getAttachedData('OAI_CONFIG');
		if ($config->getProperty('ENABLE_OAI')) {
			$harvesterConfig = $config->getProperty('OAI_HARVESTER_CONFIG');
			foreach ($harvesterConfig as $configArray) {
				if (!count($configArray["ips_allowed"])) {
					return $configArray;
				} else {
					foreach ($configArray["ips_allowed"] as $ipRange) {
						$ipRange = str_replace(".", "\\.", $ipRange);
						if (preg_match('/^'.$ipRange.'/', $_SERVER['REMOTE_ADDR'])) {
							return $configArray;
						}
					}
				}
			}
			require_once(dirname(__FILE__)."/phpoai2/oai2/oaidp-util.php");
			require_once(dirname(__FILE__)."/phpoai2/oai2/oaidp-config.php");
			$errors .= oai_error('unauthorizedHarvesterIP', "\$_SERVER['REMOTE_ADDR']", $_SERVER['REMOTE_ADDR']);
		} else {
			require_once(dirname(__FILE__)."/phpoai2/oai2/oaidp-util.php");
			require_once(dirname(__FILE__)."/phpoai2/oai2/oaidp-config.php");
// 			throwError(new Error('harvesting disabled'));
			$errors .= oai_error('harvestingDisabled');
		}
		
		oai_exit();		
	}
	
}

?>