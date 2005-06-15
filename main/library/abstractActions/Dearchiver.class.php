<?php
/**
 * @since 06/10/05
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 *
 * @link http://sourceforge.net/projects/concerto
 */ 

/**
 * Uncompresses and dearchives files from the input file.
 *
 * @package concerto.modules
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

class Dearchiver {
	
	/**
	 * Figures out the type of archive.
	 * 
	 * @param string filename
	 * @return string the extension of the file (zip, gz, bz2, tar.gz, tar.bz2)
	 * @access  private
	 * @since 06/10/05
	 *
	 */
	function _getFileType($filename) {
		$filenameParts = explode(".", $filename);
		$filenamePartsCount = count($filenameParts);
		if($filenameParts[$filenamePartsCount-2] == "tar") {
			if($filenameParts[$filenamePartsCount-1] == "gz")
				return "tar.gz";
			if($filenameParts[$filenamePartsCount-1] == "bz2")
				return "tar.bz2";			
		}
		else {
			return $filenameParts[$filenamePartsCount-1];
		}
	}

	/**
	 * Uncompresses the archive appropriate to its filetype to the given path.
	 * 
	 * @param string $filename
	 * @access public
	 * @since 06/10/05
	 *
	 */
	function uncompressFile($filename, $path) {
		$fileType = $this->_getFileType($filename);
		switch ($fileType) {
			//case "tar":
			case "tar.gz":
				$tar = Archive_Tar($filename, "gz");
				$tar->extract($path);//MYDIR."/../concerto_data/import");
				break;
			case "tar.bz2":
				$tar = Archive_Tar($filename, "bz2");
				$tar->extract($path);//MYDIR."/..concerto_data/import");
				break;
			case "zip":
				$zip = zip_open($filename);
				
				mkdir($path."/data");//"/www/cshubert/uncompressor/test/data");
				
				while ($zip_entry = zip_read($zip)) {
					
					$entry_name = zip_entry_name($zip_entry);
					$base_name = basename($entry_name);
					
					if ($base_name == "metadata.txt")
						$outFile = fopen($path."/metadata.txt", "wb");//"/www/cshubert/uncompressor/test/metadata.txt", "wb");
					else if($base_name == "data")
						$outFile = FALSE;
					else 
						$outFile = fopen($path."/data/".basename($entry_name), "wb");//"/www/cshubert/uncompressor/test/data/".basename($entry_name), "wb");
					
					if ($outFile) {
						zip_entry_open($zip, $zip_entry, "rb");
						while ($buffer = zip_entry_read($zip_entry, 1024))
							fwrite($outFile, $buffer, 1024);
						fclose($outFile);
						zip_entry_close($zip_entry);
					}
				}
				break;
			case "gz":
				$file = gzopen($filename, "rb");
				$outFile = fopen($path, "wb");//MYDIR."../concerto_data/import/", "wb");
				//echo "wrote outFile<br />";
				while (!gzeof($file)) {
					$buffer = gzread($file, 1024);
					fwrite ($outFile, $buffer, 1024);
				}
				//echo "done gunzipping<br />";
				gzclose($file);
				fclose($outFile);
				//echo "closed up shop<br />";
				break;
			case "bz2":
				$file = bzopen($filename, "rb");
				$outFile = fopen($path. "wb");//MYDIR."../concerto_data/import/", "wb");
				while ($buffer = bzread($file, 1024)) 
					fwrite($outFile, $buffer, 1024);
				fclose($outFile);
				bzclose($file);
				break;
		}
		//return MYDIR."/../concerto_data/import/";
	}
}
?>