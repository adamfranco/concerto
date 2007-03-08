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

require_once(dirname(__FILE__)."/Update001_DublinCore.act.php");

/**
 * Update numeric ids for the VRA Core Schema to use know string Ids
 * 
 * @since 3/8/07
 * @package concerto.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update002_VRACoreAction 
	extends Update001_DublinCoreAction
{
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/5/07
	 */
	function getTitle () {
		return _('Convert VRA Core ids.');
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/5/07
	 */
	function getDescription () {
		return _("This update will replace all occurances of the first existing VRA Core schema id with 'vra_core' in the repository implemenation as well as in Sets.");
	}
		
	/**
	 * Answer the id that we will be converting to
	 * 
	 * @return object Id
	 * @access public
	 * @since 3/7/07
	 */
	function &getDestId () {
		$idManager =& Services::getService('Id');
		$dcId =& $idManager->getId('vra_core');
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
		return 'VRA Core';
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
			'Title' 		=> array ('dest_id' => 'vra_core.title'),
			'Creator'	 	=> array ('dest_id' => 'vra_core.creator'),
			'Subject' 		=> array ('dest_id' => 'vra_core.subject'),
			'Description' 	=> array ('dest_id' => 'vra_core.description'),
// 			'Publisher' 	=> array ('dest_id' => 'vra_core.publisher'),
// 			'Contributor' 	=> array ('dest_id' => 'vra_core.contributor'),
			'Date' 			=> array ('dest_id' => 'vra_core.date'),
			'Type' 			=> array ('dest_id' => 'vra_core.type'),
			'Material' 		=> array ('dest_id' => 'vra_core.material'),
			'Measurements'	=> array ('dest_id' => 'vra_core.measurements'),
			'Record Type' 	=> array ('dest_id' => 'vra_core.record_type'),
			'Style/Period'	=> array ('dest_id' => 'vra_core.style_period'),
			'Technique'		=> array ('dest_id' => 'vra_core.technique'),
			'ID Number' 	=> array ('dest_id' => 'vra_core.id_number'),
			'Source' 		=> array ('dest_id' => 'vra_core.source'),
			'Location' 		=> array ('dest_id' => 'vra_core.location'),
			'Relation' 		=> array ('dest_id' => 'vra_core.relation'),
 			'Culture' 		=> array ('dest_id' => 'vra_core.culture'),
			'Rights' 		=> array ('dest_id' => 'vra_core.rights')
		);
	}
	
}

?>