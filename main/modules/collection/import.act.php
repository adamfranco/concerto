<?php
//define("FILEID", "dev_id-");
require_once("/home/cshubert/public_html/importer/domit/xml_domit_include.php");
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
		move_uploaded_file($_FILES['userfile']['tmp_name'], $newPath.DIRECTORY_SEPARATOR.$filename);
		$dearchiver =& new Dearchiver();
		$dearchiver->uncompressFile($newPath.DIRECTORY_SEPARATOR.$filename, $newPath);
	}



	/**
	 * tries to match given string to a schema with the same name.
	 * 
	 * @return false if no schema is matched, and the schemaId if matched
	 * @access public
	 * @since 7/18/05
	*/

	function matchSchema ($schema, $repository) {
		$structures =& $repository->getRecordStructures();
		$stop = true;
		while($structures->hasNext()) {
			$testStructure = $structures->next();
			if($testStructure->getDisplayName() == $schema) {
				$structureId = $testStructure->getId();														// retain structureId
				return $structureId;
			}
			return false;
		}
	}

	/**
 * tries to match the given array with partstructure in the given structure
 * 
 * @return false if not matched and an array of partstructure ids
 * @access public
 * @since 7/18/05
 */

	function matchPartStructures ($schema, $partArray) {
		$partStructureIds = array();
		foreach ($partArray as $part) {
			$stop = true;
			$partStructures =& $schema->getPartStructures();
			while ($partStructures->hasNext()) {
				$partStructure = $partStructures->next();
				if ($part == $partStructure->getDisplayName()) {										// find the corresponding partStructure
				$partStructureIds[] = $partStructure->getId();
				$stop = false;
				break;
				}
			}
			if ($stop)
			return false;
		}
		return $partStructureIds;
	}

	/**
	 * builds asset in repository from assetinfo and records from recordlist
	 *
	 * @access public
	 * @since 7/18/05
	 *
	*/

	function buildAsset($repository, $assetInfo, $recordList) {
		$idManager = Services::getService("Id");
		$asset =& $repository->createAsset($assetInfo[0], $assetInfo[1], $assetInfo[2]);
		foreach($recordList as $entry) {
			$assetRecord =& $asset->createRecord($entry[0]);													// create record with stored id
			$j = 0;																								// counter for parallel arrays
			foreach ($entry[1] as $id) {
				$assetRecord->createPart($id, $entry[2][$j]);										// access parallel arrays to create parts
				$j++;																							// increment
			}
			if ($entry[0] == $fileStructureId) {
				$fileRecord =& $asset->createRecord($fileStructureId);
				$fileRecord->createPart($idManager->getId("FILE_DATA"), file_get_contents($newPath."/data/".$entry[1]));
				$fileRecord->createPart($idManager->getId("THUMBNAIL_DATA"), file_get_contents($newPath."/data/".$entry[1]));
			}
		}
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
			$filename = $_FILES['userfile']['name'];

			if($filename == "")
			throwError(new Error("Specify a file to upload", "concerto.collection", true));

			$ext = $_REQUEST['archivetype'];
			uploadFile($path, $filename);
			$fileStructureId =& $idManager->getId("FILE");																				// stored for all archive types!!
			$i = 0;																														// counter for assets
			// WHICH FILETYPE?
			switch ($ext) {
				case "XML":
				$import =& new DOMIT_Document();																					// instantiate new DOMIT_Document

				if ($import->loadXML($newPath."/metadata.xml")) {																	// parse the file
				if (!($import->documentElement->hasChildNodes()))																// check for assets
				throwError(new Error("There are no assets to import", "concerto.collection", true));
				}

				else
				throwError(new Error("XML parse failed", "concerto.collection", true));

				// ASSET LOOP
				$iAssetList =& $import->documentElement->childnodes;
				foreach ($iAssetList as $asset) {
					$assetInfo = array();
					$assetInfo[0] = $asset->childNodes[0]->getText();
					if ($name == "")
					$assetInfo[0] = "asset".$i;
					$assetInfo[1] = $asset->childNodes[1]->getText();																// description for asset
					$assetInfo[2] = $asset->childNodes[2]->getText();																	// type for asset, check for empty
					if ($assetInfo[2] == "")
					$assetInfo[2] = new HarmoniType("Asset Types", "Concerto", "Generic Asset");
					else
					$assetInfo[2] = new HarmoniType("Asset Types", "Concerto", $assetInfo[2]);
					$i++;

					$iRecordList =& $asset->childNodes;																											// increment asset counter
					$recordList = array();
					foreach ($iRecordList as $record) {
						$recordListElement = array();
						if ($record->nodeName == "record") {
							$structureId = matchSchema($record->getAttribute("schema"), $dr);
							$recordListElement[] = $structureId;
							$partArray = array();
							$parts = array();
							foreach ($record->childNodes as $field) {
								$partArray[] = $field->getAttribute("name");
								$parts[] = $field->getText();
							}
							$partStructureIds = matchPartStructures($dr->getRecordStructure($structureId), $partArray);

							if(!$partStructureIds)
							throwError(new Error("One or more of the Parts specified in the xml file is not valid.  The first".$i."assets were imported", "concerto.collection", true));

							$recordListElement[] = $partStructureIds;
							$recordListElement[] = $parts;
						}
						$recordList[]=$recordListElement;
					}
					buildAsset($assetInfo, $recordList);
				}
				break;
				case "Tab-Delimited":
				$meta = fopen($newPath."/metadata.txt", "r");
				$schema = fgets($meta);
				$schema = ereg_replace("[\n\r]*$","",$schema);
				$structureId = matchSchema($schema, $dr);

				if (!$structureId)
				throwError(new Error("Schema <emph>".$schema. "</emph>does not exist in the collection", "concerto.collection", true));

				$titleline = ereg_replace("[\n\r]*$", "", fgets($meta));
				$titles = explode ("\t", $titleline);
				$partStructureIds = matchPartStructures($dr->getRecordStructure($structureId), $titles);

				if (!$partStructureIds)
				throwError(new Error("Schema part <emph>".$titles[$j]."</emph>does not exist", "concerto.collection", true));

				// ASSET loop
				while ($line = ereg_replace("[\n\r]*$","",fgets($meta))) {
					$assetInfo = array();
					$metadata = explode("\t", $line);
					if($metadata[0]==""){
						//	$reqPath = explode("/", $metadata[3]);
						//	$name = $reqPath[count($reqPath)-1];
						$assetInfo[0] = "asset".$i;
					}
					else
					$assetInfo[0] = $metadata[0];

					$assetInfo[1] = $metadata[1];

					if($metadata[2] == "")
					$assetInfo[2] = new HarmoniType("Asset Types", "Concerto", "Generic Asset");
					else
					$assetInfo[2] = new HarmoniType("Asset Types", "Concerto", $metadata[2]);
					$i++;

					$asset =& $dr->createAsset($assetInfo[0], $assetInfo[1], $assetInfo[2]);
					$assetRecord = $asset->createRecord($structureId);

					for($k=0;$k<count($partStructureIds); $k++) {
						$type = $partStructureIds->getType();
						$type->getKeyword();
						$assetRecord->createPart($partStructureIds[$k], $metadata[$k+4]);
					}

					if($metadata[3] != "") {

						if(!file_exists($newPath."/data/".$metadata[3]))
						throwError("The file ".$metadata[3]." does not exist", "concerto.collection", true);

						$fileRecord =& $asset->createRecord($fileStructureId);
						$fileDataPart = $idManager->getId("FILE_DATA");
						$fileRecord->createPart($fileDataPart, file_get_contents($newPath."/data/".$metadata[3]));
						$fileRecord->createPart($idManager->getId("FILE_NAME"), $metadata[3]);
						$fileRecord->createPart($idManager->getId("THUMBNAIL_DATA"), file_get_contents($newPath."/data/".$metadata[3]));

					}
				}
				break;
			}
		}

		else {
			print <<<end
			<table border='0' cellpadding='5' align = 'center'>
			<tr><td colspan ='2'>Type in the address for or browse to file to import 
				and click on the upload file button.</td></tr>
				<form enctype ='multipart/form-data' action='' method='POST'>
					<input type ='hidden', name='MAX_FILE_SIZE' value = '20000' />
					<tr><td>Select the input archive type: </td><td><select name ='archivetype'>
					<option selected>Tab-Delimited</option>
					<option>XML</option>
					<option>Exif</option>
					<option>File</option>
					</select></td></tr>
					<tr><td><input name='userfile' type='file' /></td>
					<td><input type='submit' name ='submit' value ='upload file' /></td></tr>
					</form>
					</table>
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
