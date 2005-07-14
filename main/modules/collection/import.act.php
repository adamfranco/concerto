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
				$filename = $_FILES['userfile']['name'];
				if($filename == "") {
					throwError(new Error("Specify a file to upload", "concerto.collection", true));
					//die ("Specify a file to upload");
				}
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
						foreach ($import->documentElement->childNodes as $asset) {
							$assetInfo = array();
							$assetInfo[0] = $asset->childNodes[0]->getText();
								if ($name == "")
									$assetInfo[0] = "asset".$i;
							$assetInfo[1] = $iAsset->childNodes[1]->getText();																// description for asset
							$assetInfo[2] = $iAsset->childNodes[2]->getText();																	// type for asset, check for empty
								if ($assetInfo[2] == "")
									$assetInfo[2] = new HarmoniType("Asset Types", "Concerto", "Generic Asset");
								else
									$assetInfo[2] = new HarmoniType("Asset Types", "Concerto", $assetInfo[2]);
							$i++;																											// increment asset counter
							$recordList = array();
							foreach ($asset->childNodes as $record) {
								$partArray = array();
								if ($iRecord->nodeName == "record") {
									$structureId = matchSchema($record->getAttribute("schema"), $dr);
									foreach ($record->childNodes as $field)
										$partArray[] = $field->getAttribute("name");
									$partStructureIds = matchPartStructures($dr->getRecordStructure($structureId), $partArray);
									
						break;
					case "TXT":
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
							
							
						break;
				}
//======================================================== ABOVE IS STAYING IN ==============================================================//
					$iAssetList =& $import->documentElement->childNodes;															// according to DTD this should be an array of the assets
					$i=0;																											// for checking assetnames

					$fileStructureId =& $idManager->getId("FILE");																	// retain id for FILE records
					
					foreach ($iAssetList as $iAsset) {
						$name = $iAsset->childNodes[0]->getText();																	// name for asset ("" => "asset#")
						if ($name == "")
							$name = "asset".$i;
						$desc = $iAsset->childNodes[1]->upgetText();																	// description for asset
						$type = $iAsset->childNodes[2]->getText();																	// type for asset, check for empty
						if ($type == "")
							$type = new HarmoniType("Asset Types", "Concerto", "Generic Asset");
						else
							$type = new HarmoniType("Asset Types", "Concerto", $type);
						$i++;																										// increment asset counter
//============================================== ABOVE IS ASSET INFO ====================================================================================//		
						$iRecordList =& $iAsset->childNodes;																		// retain children of assets 

						$recordList = array();																						// array for retaining records until creation of asset

						foreach ($iRecordList as $iRecord) {
							if ($iRecord->nodeName == "record") {
								$record = array();																						// record array 0 -> RecordStructureId ; 1 -> PartStructureIds ; 2 -> Parts
								
								$schema = $iRecord->getAttribute("schema");																// retain schema
								if ($schema != "File")	{																				// if not FILE find appropriate RecordStructure
									$structures =& $dr->getRecordStructures();															
									$stop = true;
									while($structures->hasNext()) {
										$testStructure = $structures->next();
										if($testStructure->getDisplayName() == $schema) {
											$stop = FALSE;
											$record[] = $testStructure->getId();														// retain structureId
											$partStructures =& $testStructure->getPartStructures();										// retain array of partStructures (ALL FOR RECORDSTRUCTURE)
											break;
										}
									}
									if ($stop)
										throwError(new Error("Schema <emph>".$schema."</emph> does not exist in the collection", "concerto.collection", true));
									
									$iFields =& $iRecord->childNodes;																	// retain fields of record 
									$partStructureIds = array();																		// structure for corresponding structureid's
									$parts = array();																					// structure for corresponding data
									
									// find all populated field names and fill them?
									foreach ($iFields as $iField) {
										$go = FALSE;
										$fieldName = $iField->getAttribute("name");														// retain field name (partStructure)
										while ($partStructures->hasNext()) {															
											$partStructure = $partStructures->next();			
											if ($fieldName == $partStructure->getDisplayName()) {										// find the corresponding partStructure
												$go = TRUE;																				// found it (no error)
												$partStructureIds[] = $partStructure->getId();
												$parts[] = $iField->getText();
												break;
											}
										}
										if (!$go)
											throwError(new Error("Schema part <emph>".$fieldName."</emph> does not exist", "concerto.collection", true));
									}
									
//========================================= Skip Asset or die on broken part? to skip need new if/else statement outside foreaches ===================================================//
									
									$record[] = $partStructureIds;																		// retain partstructureids
									$record[] = $parts;																					// retain parts

									$recordList[] = $record;																			// retain record
								}
								else {
									$record[] = $fileStructureId;
									if ("filename" == $iRecord->childNodes[0]->getAttribute("name"))
										$filename = trim($iRecord->childNodes[0]->getText());
									else
										throwError(new Error("File record part <emph>".$filename."</emph> does not exist", "concerto.collection", true));
									
									$record[] = $filename;
									$recordList[] = $record;
								}
							}
						}
						$asset =& $dr->createAsset($name, $desc, $type);															// CREATE ASSSET AT END OF DATA POPULATION
						
						foreach ($recordList as $entry) {
							if ($entry[0] == $fileStructureId) {
								$fileRecord =& $asset->createRecord($fileStructureId);
								$fileRecord->createPart($idManager->getId("FILE_DATA"), file_get_contents($newPath."/data/".$entry[1]));
								$fileRecord->createPart($idManager->getId("FILE_NAME"), $entry[1]);
								$fileRecord->createPart($idManager->getId("THUMBNAIL_DATA"), file_get_contents($newPath."/data/".$entry[1]));
							}
							else {
								$assetRecord =& $asset->createRecord($entry[0]);													// create record with stored id
								$j = 0;																								// counter for parallel arrays
								foreach ($entry[1] as $id) {
									$assetRecord->createPart($id, new String($entry[2][$j]));										// access parallel arrays to create parts
									$j++;																							// increment
								}
							}
						}
					}
				}
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
