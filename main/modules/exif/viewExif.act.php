<?php
/**
 * @since 11/27/06
 * @package concerto.test
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
 
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/ExifRepositoryImporter.class.php");
require_once(EXIF);

/**
 * This Action allows the user to upload a file and view the exif/iptc fields and
 * data that it contains
 * 
 * @since 11/27/06
 * @package concerto.test
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class viewExifAction
	extends MainWindowAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/08/05
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$centerPane =& $this->getActionRows();
		$centerPane->add(new Heading(_("Upload a file to view its EXIF/IPTC data"), 2), null, null, LEFT, TOP);
		
		ob_start();
		print "\n<form action='".$harmoni->request->quickURL()."' method='post' enctype='multipart/form-data'>";
		print "\n\t<input type='file' name='".RequestContext::name('image_file')."'/>";
		print "\n\t<input type='submit'/>";
		print "\n</form>";
		
		$centerPane->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, LEFT, TOP);	
		
		if (isset($_FILES[RequestContext::name('image_file')])
			&& !$_FILES[RequestContext::name('image_file')]['error']) 
		{
			$fileArray = $_FILES[RequestContext::name('image_file')];
			ob_start();
			print "<h2>".$fileArray['name']."</h2>";
			
			$exifImporter =& new ExifRepositoryImporter($fileArray['tmp_name'], null, false);
			$exifImporter->getSingleAssetInfo($fileArray['tmp_name']);
			
// 			printpre($exifImporter->_photoshopIPTC);
			
			print "\n<h3>"._("IPTC Fields")."</h3>";
			$this->printFields($exifImporter->extractPhotoshopMetaData());
			print "\n<h3>"._("EXIF Fields")."</h3>";
			$this->printFields($exifImporter->extractExifMetadata($fileArray['tmp_name']));
			
			$centerPane->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, LEFT, TOP);	
		}		
	}
	
	/**
	 * Print out a field list
	 * 
	 * @param array $fields
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	function printFields ($fields) {
		print "\n<table border='1'>";
		print "\n\t<tr>";
		print "\n\t\t<th>"._("Field Name")."</th>";
		print "\n\t\t<th>"._("Value")."</th>";
		print "\n\t</tr>";
		foreach ($fields as $name => $value) {
			print "\n\t<tr>";
			print "\n\t<td>".$name."</td>";
			print "\n\t<td>".$value."</td>";
			print "\n\t</tr>";
		}		
		print "\n</table>";
	}
}

?>