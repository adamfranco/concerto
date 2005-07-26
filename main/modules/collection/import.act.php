<?php
require_once("/home/cshubert/public_html/importer/domit/xml_domit_include.php");

//require_once(MYDIR."/domit/xml_domit_include.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/XMLRepositoryImporter.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/TabRepositoryImporter.class.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/ExifRepositoryImporter.class.php");

require_once(HARMONI."utilities/Dearchiver.class.php");
require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");
require_once(HARMONI."utilities/MIMETypes.class.php");

class importAction extends RepositoryAction {
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/08/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
		$idManager->getId("edu.middlebury.authorization.modify"),
		$this->getRepositoryId());
	}

	/**
 	 * Return the "unauthorized" string to print
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to edit this <em>Collection</em>.");
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getHeadingText () {
		$repository =& $this->getRepository();
		return _("Import Assets to the")
		." <em>".$repository->getDisplayName()."</em> "
		._(" Collection");
	}

	

	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 6/08/05
	 */
	function buildContent () {
		$dr =& $this->getRepository();
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("concerto/import");
		$centerPane =& $this->getActionRows();
		$idManager =& Services::getService("Id");
		ob_start();
		if(RequestContext::value("submit")){
			$userfile = RequestContext::value("userfile");
			$path = $userfile['tmp_name'];
			$filename = $userfile['name'];
			$newPath = $path."0";

			if($filename == "")
			throwError(new Error("Specify a file to upload", "concerto.collection", true));

			$ext = RequestContext::value("archivetype");
			importAction::uploadFile($path, $filename);
			if ($ext == "Tab-Delimited") 
				$importer =& new TabRepositoryImporter($path."0/".$filename, $dr->getId());
			else if ($ext == "XML") 
				$importer =& new XMLRepositoryImporter($path."0/".$filename, $dr->getId());
			else if ($ext == "Exif") 
				$importer =& new ExifRepositoryImporter($path."0/".$filename, $dr->getId());
			
			if ($importer->isDataValid())
				$importer->import();
			else {
				print <<<END
				
				<h1>Holy jeepers, Wilson! The data wasn't the right format!</h1>
END;
			}
		}

		else {
			$archivetype = RequestContext::name("archivetype");
			$userfile = RequestContext::name("userfile");
			$submit = RequestContext::name("submit");

			print <<<end
			<table border='0' cellpadding='5' align = 'center'>
			<tr><td colspan ='2'>Type in the address for or browse to file to import 
				and click on the upload file button.</td></tr>
				<form enctype ='multipart/form-data' action='' method='POST'>
					<tr><td>Select the input archive type: </td><td><select name ='$archivetype'>
					<option selected>Tab-Delimited</option>
					<option>XML</option>
					<option>Exif</option>
					<option>File</option>
					</select></td></tr>
					<tr><td><input name='$userfile' type='file' /></td>
					<td><input type='submit' name ='$submit' value ='upload file' /></td></tr>
					</form>
					</table>
end;
			$printText = new Block(ob_get_contents(), 3);
			ob_end_clean();
			$centerPane->add($printText, "100%", null, LEFT, CENTER);

		}

		$harmoni->request->endNamespace();
	}
	
	/**
 	 * uncompress the archive in a unique folder for use by the importer
 	 * 
 	 * @access public
 	 * @since 7/18/05
	 */

	function uploadFile ($path, $filename) {
		$newPath = $path."0";
		// create unique folder
		mkdir($newPath);

		// move uploaded file or lose uploaded file

		$userfile = move_uploaded_file($path, $newPath.DIRECTORY_SEPARATOR.$filename);
		
	}
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getReturnUrl () {
		$repositoryId =& $this->getRepositoryId();
		$harmoni =& Harmoni::instance();
		return $harmoni->request->quickURL("collection", "browse", array("collection_id" => $repositoryId->getIdString()));
	}
}
?>
