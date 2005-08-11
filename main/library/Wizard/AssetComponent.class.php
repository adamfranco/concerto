<?php
/**
 * @since Jul 21, 2005
 * @package concerto.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/Wizard/WizardComponent.abstract.php");

/**
 * This adds an input type='text' field to a {@link Wizard}.
 * 
 * @since Jul 21, 2005
 * @package concerto.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class AssetComponent 
	extends WizardComponent 
{
	var $_style = null;
	var $_id = null;	

	/**
	 * Sets the Id for this component to store and display
	 * @param object Id $id
	 * @access public
	 * @return void
	 */
	function setId ( &$id ) {
		$this->_id = $id;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	function update ($fieldName) {
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return $this->_id;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		ob_start();
		
		if (is_object($this->_id)) {
			$repositoryManager =& Services::getService('Repository');
			$asset =& $repositoryManager->getAsset($this->_id);
			
			print "\n<table border='0'>";
			print "\n\t<tr>\n\t\t<td>";
			
			$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($this->_id);
			if (!is_null($thumbnailURL)) {
				
//				print "\n\t<br /><a href='".$assetViewUrl."'>";
				print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' />";
//				print "\n\t</a>";
			}
			
			print "\n\t\t</td>\n\t\t</tr>";
			print "\n\t<tr>\n\t\t<td>";
			
			print _("Id: ").$this->_id->getIdString();
			
			print "\n\t\t</td>\n\t\t</tr>";
			print "\n\t<tr>\n\t\t<td>";
			
			print _("Name: ").$asset->getDisplayName();
			
			print "\n\t\t</td>\n\t\t</tr>";
			print "\n</table>";
		}
		
		$m = ob_get_contents();
		ob_end_clean();
		return $m;
	}
}

?>