<?php
//define("FILEID", "dev_id-");
require_once(HARMONI."utilities/Dearchiver.class.php");
require_once(MYDIR."/main/library/abstractActions/RepositoryAction.class.php");
class importAction extends RepositoryAction {
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/08/05
	 */
	function isAuthorizedToExecute () {
		// Check for our authorization function definitions
		if (!defined("AZ_EDIT"))
			throwError(new Error("You must define an id for AZ_EDIT", "concerto.collection", true));
		
		// Check that the user can access this collection
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $authZ->isUserAuthorized(
					$idManager->getId(AZ_EDIT), 
					$this->getRepositoryId());
		}
	
	/**
	 * Return the "unauthorized" string to pring
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
			$centerPane =& $this->getActionRows();
			$dr =& $this->getRepository();
			$idManager =& Services::getService("Id");
			ob_start();
			if(isset($_REQUEST['submit'])) {
				$path = $_FILES['userfile']['tmp_name'];
				// append 0 for unique foldername
				$newPath = $path."0";
				$filename = $_FILES['userfile']['name'];
				if($filename == "") {
					throwError(new Error("Specify a file to upload", "concerto.collection", true));
					//die ("Specify a file to upload");
				}
				// create unique folder
				mkdir($newPath);
				
				// move uploaded file or lose uploaded file
				move_uploaded_file($_FILES['userfile']['tmp_name'], $newPath.DIRECTORY_SEPARATOR.$filename);
				$dearchiver =& new Dearchiver();
				$dearchiver->uncompressFile($newPath.DIRECTORY_SEPARATOR.$filename, $newPath);
				
				// tab delim txt file with metadata
				$meta = fopen($newPath."/metadata.txt", "r");
				
				//get the schema from the first line of the file
				$schema = fgets($meta);
				$schema = ereg_replace("[\n\r]*$","",$schema);
				/*
				$id = $idManager->getId("dev_id-38");
				$dr =& $drManager->getRepository($id);
				$schemas =& $dr->getRecordStructures();
				while($schemas->hasNext()) {
					$structure =& $schemas->next();
					if($structure->getDisplayName() == $schema)
					break;
				}
				*/
				
				//iterate through sructures and find the correct one
				$structures =& $dr->getRecordStructures();
				while($structures->hasNext()){
					$testStructure = $structures->next();
					if($testStructure->getDisplayName() == $schema)
						$structure = $testStructure;
					if($testStructure->getDisplayName() == "File")
						$fileStr = $testStructure;
				}
				
				//get the Filestructure
				$fileStrId =& $fileStr->getId();
				
				// make sure it found the right schema
				if (!$structure)
					throwError(new Error("Schema <emph>".$schema. "</emph>does not exist in the collection", "concerto.collection", true));
					//die ("Schema does not exist");

				$structureId = $structure->getId();
				
				//the second row of the file is the partstructrues, get their ids
				$titleline = ereg_replace("[\n\r]*$", "", fgets($meta));
				$titles = explode ("\t", $titleline);
				$partArray = array();
				for($j=4; $j<count($titles); $j++) {
					$partStructures =& $structure->getPartStructures();							
					//iterate through partstructures and find corresponding to the title
					while ($partStructures->hasNext()) {										
						$partStructure = $partStructures->next();
						if ($titles[$j] == $partStructure->getDisplayName())
						break;
					}
					
					//verify the correct partstructure
					if ($partStructure->getDisplayName() != $titles[$j]) 
					throwError(new Error("Schema part <emph>".$titles[$j]."</emph>does not exist",
										"concerto.collection", true));
					//die ("Schema part does not exist");

					// put part ids in array
					$partArray[] = $partStructure->getId();
				}
				//create the assets
				$i = 0;
				while ($line = ereg_replace("[\n\r]*$","",fgets($meta))) {
					// create assets in here
					$metadata = explode("\t", $line);
					if($metadata[0]==""){
						//	$reqPath = explode("/", $metadata[3]);
						//	$name = $reqPath[count($reqPath)-1];
						$name = "asset".$i;
					}
					else
					$name=$metadata[0];
					$description = $metadata[1];
					if($metadata[2] == "")
					$type = new HarmoniType("Asset Types", "Concerto", "Generic Asset");
					else
					$type = new HarmoniType("Asset Types", "Concerto", $metadata[2]);
					//printpre($metadata);
					//exit;
					// create asset
					$asset =& $dr->createAsset($name, $description, $type);
					
					$assetRecord =& $asset->createRecord($structureId);
					
					// fill available data
					for($k=0; $k<count($partArray);$k++)
						$assetRecord->createPart($partArray[$k], new String($metadata[$k+4]));
					$i++;
					if($metadata[3] != "") {
						$fileRecord =& $asset->createRecord($fileStrId);
						$fileDataPart = $idManager->getId("FILE_DATA");
						//$file = fopen($newPath."data/".$metadata[3], "rb");
						$fileRecord->createPart($fileDataPart, file_get_contents($newPath."/data/".$metadata[3]));
//						$fileNameId = $idManager->getId("FILE_NAME");
$abcdefg = "filename";
						$fileRecord->createPart($idManager->getId("FILE_NAME"), $abcdefg);
						$fileRecord->createPart($idManager->getId("MIME_TYPE"), "file");
						$fileRecord->createPart($idManager->getId("THUMBNAIL_DATA"), file_get_contents($newPath."/data/".$metadata[3]));
						$fileRecord->createPart($idManager->getId("THUMBNAIL_MIME_TYPE"), "file");
						
					}
					
						
					// create record
				}

				// store actual DATA specified by filename in asset
				// delete files and folders in tmp (created by this process) ($newPath)

			}

			else {
				print <<<end
	\t<table border='0' cellpadding='5' align = 'center'>
	<tr><td colspan ='2'>Type in the address for or browse to file to import 
	and click on the upload file button.</td></tr>
	\t\t<form enctype ='multipart/form-data' action='' method='POST'>
	\t\t<input type ='hidden', name='MAX_FILE_SIZE' value = '20000' />
	\t\t<tr><td>Select the input archive type: </td><td><select name ='archivetype'>
	\t\t<option selected>Tab-delimited</option>
	\t\t<option>XML</option>
	\t\t<option>Exif</option>
	\t\t<option>File</option>
	\t\t</select></td></tr>
	\t\t<tr><td><input name='userfile' type='file' /></td>
	\t\t<td><input type='submit' name ='submit' value ='upload file' /></td></tr>
	\t\t</form>
	\t</table>
end;
		$printText = new Block(ob_get_contents(), 3);
		ob_end_clean();
		$centerPane->add($printText, "100%", null, LEFT, CENTER);
		
		}
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
