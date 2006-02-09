<?php

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");


//apd_set_pprof_trace(); 

class exportexhibmdbAction 
	extends MainWindowAction {
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
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Exhibition Export/Import");
	}

	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {	
		$dbHandler =& Services::getService("DBHandler");
		$repositoryManager =& Services::getService("Repository");
		$idManager =& Services::getService("Id");
		
		$mdbIndex = $dbHandler->addDatabase(
			new MySQLDatabase("localhost", "whitey_mediadb", "test", "test"));
		$dbHandler->connect($mdbIndex);

		$n_C_Index = $dbHandler->addDatabase(
			new MySQLDatabase("localhost", "mdb_concerto", "test", "test"));


		$dbHandler->connect($n_C_Index);
		
		$exhibitionsQuery =& new SelectQuery;
		$exhibitionsQuery->addTable("pressets");
		$exhibitionsQuery->addColumn("id", "exhibitionId");
		$exhibitionsQuery->addColumn("title", "exhibitionName");
		$exhibitionsQuery->addColumn("presentations", "slideshowIds");
		$exhibitionsQuery->addWhere("presentations IS NOT NULL");
		$exhibitionsQuery->addOrderBy("id");
		
		$exhibitionsResults =& $dbHandler->query($exhibitionsQuery, $mdbIndex);

		while ($exhibitionsResults->hasMoreRows()) {
			$exhibition = $exhibitionsResults->next();
			
			$this->openExhibition($exhibition);
			
			$slideshowsQuery =& new SelectQuery;
			$slideshowsQuery->addTable("preslists");
			$slideshowsQuery->addColumn("id", "slideshowId");
			$slideshowsQuery->addColumn("title", "slideshowName");
			$slideshowsQuery->addColumn("description", "slideshowDescription");
			$slideshowsQuery->addColumn("preslist", "slideOrder");
			$slideshowsQuery->addWhere("preslist IS NOT NULL");
			$slideshowsQuery->addWhere("presset = ".
				$exhibition['exhibitionId']);
			$slideshowsQuery->addOrderBy("id");
			
			$slideshowsResults =& $dbHandler->query($slideshowsQuery,
				$mdbIndex);

			while ($slideshowsResults->hasNext()) {
				$slideshow = $slideshowsResults->next();
				
				$this->openSlideshow($slideshow);
				
				$order = explode(",", $slideshow['slideOrder']);
				foreach ($order as $presmediaId) {
					$slideQuery =& new SelectQuery;
					$slideQuery->addTable("presmedia");
					$slideQuery->addColumn("comment", "slideCaption");
					$slideQuery->addColumn("media_id");
					$slideQuery->addWhere("pres_id = "
						.$slideshow['slideshowId']);
					$slideQuery->addWhere("id = ".$presmediaId);
					
					$slideResult =& $dbHandler->query($slideQuery, $mdbIndex);

					if ($slideResult->getNumberOfRows() == 1) {
						$slide = $slideResult->getCurrentRow();
					
						$mediaQuery =& new SelectQuery;
						$mediaQuery->addTable("media");
						$mediaQuery->addColumn("id");
						$mediaQuery->addColumn("fname");
						$mediaQuery->addWhere("id = ".$slide['media_id']);
						$mediaResult =& $dbHandler->query($mediaQuery, 
							$mdbIndex);
						if ($mediaResult->getNumberOfRows() == 1) {
							$media = $mediaResult->getCurrentRow();
							
							$idQuery =& new SelectQuery;
							$idQuery->addTable("dr_file");
							$idQuery->addTable("dr_asset_record", INNER_JOIN,
								"dr_file.id = dr_asset_record.FK_record");
							$idQuery->addColumn("dr_asset_record.FK_asset",
								"asset_id");
							$idQuery->addColumn("dr_file.filename");
							$idQuery->addWhere("dr_file.filename = '".
								rawurlencode($media['fname'])."'");
							
							$idResult =& $dbHandler->query($idQuery, 
								$n_C_Index);
							if ($idResult->getNumberOfRows() == 1) {
								$idRow = $idResult->getCurrentRow();
								$this->addSlide($slide, $idRow['asset_id']);
							}
							$idResult->free();
							unset($idQuery);
						} else {
							$empty = "";
							$this->addSlide($slide, $empty);		
						}
						$mediaResult->free();
						unset($mediaQuery);
					}
//					else
//						print "Bad presmedia: ".$presmediaId."<br />";
					$slideResult->free();
					unset($slideQuery);
				}
				unset($order);
				$this->closeSlideshow();
			}
			$slideshowsResults->free();
			unset($slideshowQuery);
			
			$this->closeAndImportExhibition();
		}
		$exhibitionsResults->free();
		unset($exhibitionsQuery);		

		$centerPane =& $this->getActionRows();
		ob_start();

// 		if ($this->_importer->hasErrors()) {
// 			print("The bad news is that some errors occured during import, they are: <br />");
// 			$this->_importer->printErrorMessages();
// 		}
// 		if ($this->_importer->hasAssets()) {
// 			print("The good news is that ".count($this->_importer->getGoodAssetIds())." assets were created during import, they are: <br />");
// 			$this->_importer->printGoodAssetIds();
// 		}
		
		$centerPane->add(new Block(ob_get_contents(), 1));
		ob_end_clean();

		$dbHandler->disconnect($mdbIndex);

		return true;
	}

	/**
	 * Begins a new file for a new Exhibition
	 *
	 * @param array $slide
	 * @since 8/9/05
	 */
	function openExhibition (&$exhibition) {
		if (!is_dir("/tmp/mdbExhibExport3"))
			mkdir("/tmp/mdbExhibExport3");

		if (!is_dir("/tmp/mdbExhibExport3/".$exhibition['exhibitionId']))
			mkdir("/tmp/mdbExhibExport3/".$exhibition['exhibitionId']);
		$this->_xmlFile =& fopen("/tmp/mdbExhibExport3/".
			$exhibition['exhibitionId']."/metadata.xml", "w");
		fwrite($this->_xmlFile,
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");

		fwrite($this->_xmlFile, "<import>\n");
		fwrite($this->_xmlFile, "\t<repository 
			id=\"edu.middlebury.concerto.exhibition_repository\">\n");
// recordstructure
		fwrite($this->_xmlFile, "\t\t<recordstructure id=\"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure\" xml:id=\"slidestructure\">\n".
			"\t\t\t<name>Slide Schema</name>\n".
			"\t\t\t<partstructure id=\"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id\" xml:id=\"target id\" isMandatory=\"FALSE\" isRepeatable=\"FALSE\" isPopulated=\"FALSE\">
				<name>target id</name>
				<type>
					<domain>Repository</domain>
					<authority>edu.middlebury.harmoni</authority>
					<keyword>string</keyword>
				</type>
</partstructure>\n".
			"\t\t\t<partstructure id=\"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.text_position\" xml:id=\"text position\" isMandatory=\"FALSE\" isRepeatable=\"FALSE\" isPopulated=\"FALSE\">
				<name>text position</name>
				<type>
					<domain>Repository</domain>
					<authority>edu.middlebury.harmoni</authority>
					<keyword>string</keyword>
				</type>
			</partstructure>\n".
			"\t\t\t<partstructure id=\"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.display_metadata\" xml:id=\"display metadata\" isMandatory=\"FALSE\" isRepeatable=\"FALSE\" isPopulated=\"FALSE\">
				<name>display metadata</name>
				<type>
					<domain>Repository</domain>
					<authority>edu.middlebury.harmoni</authority>
					<keyword>boolean</keyword>
				</type>
			</partstructure>\n".
		"\t\t</recordstructure>\n");
// recordstructure
		fwrite($this->_xmlFile, "\t\t<asset>\n".
			"\t\t\t<name>".$exhibition['exhibitionName']."</name>\n".
			"\t\t\t<description></description>\n".
			"\t\t\t<type>\n".
			"\t\t\t\t<domain>Asset Types</domain>\n".
			"\t\t\t\t<authority>edu.middlebury.concerto</authority>\n".
			"\t\t\t\t<keyword>Exhibition</keyword>\n".
			"\t\t\t</type>\n");
	}
	
	/**
	 * Opens a new slideshow
	 *
	 * @param array $slide
	 * @since 8/9/05
	 */
	function openSlideshow (&$slideshow) {
		fwrite($this->_xmlFile, "\t\t\t<asset maintainOrder=\"TRUE\">\n".
			"\t\t\t\t<name>".$slideshow['slideshowName']."</name>\n".
			"\t\t\t\t<description><![CDATA[".$slideshow['slideshowDescription'].
				"]]></description>\n".
			"\t\t\t\t<type>\n".
			"\t\t\t\t\t<domain>Asset Types</domain>\n".
			"\t\t\t\t\t<authority>edu.middlebury.concerto</authority>\n".
			"\t\t\t\t\t<keyword>Slideshow</keyword>\n".
			"\t\t\t\t</type>\n");
	}
	
	/**
	 * Opens a new slideshow
	 *
	 * @since 8/9/05
	 */
	function closeSlideshow () {
		fwrite($this->_xmlFile, "\t\t\t</asset>\n");
	}	
	
	/**
	 * Creates an entire slide entry in the xmlFile
	 *
	 * @param array $slide
	 * @since 8/9/05
	 */
	function addSlide (&$slide, &$asset_id) {
	fwrite($this->_xmlFile, "\t\t\t\t<asset>\n".
			"\t\t\t\t\t<name></name>\n".
			"\t\t\t\t\t<description><![CDATA[".
				$slide['slideCaption']."]]></description>\n".
			"\t\t\t\t\t<type>\n".
			"\t\t\t\t\t\t<domain>Asset Types</domain>\n".
			"\t\t\t\t\t\t<authority>edu.middlebury.concerto</authority>\n".
			"\t\t\t\t\t\t<keyword>Slide</keyword>\n".
			"\t\t\t\t\t</type>\n".
			"\t\t\t\t\t<record xml:id=\"slidestructure\">\n".
			"\t\t\t\t\t\t<part xml:id=\"target id\">".
				$asset_id."</part>\n".
			"\t\t\t\t\t\t<part xml:id=\"text position\">right</part>\n".
			"\t\t\t\t\t\t<part xml:id=\"display metadata\">false</part>\n".
			"\t\t\t\t\t</record>\n".
			"\t\t\t\t</asset>\n");
	}

	/**
	 * Closes and Imports a single exhibition
	 *
	 * @since 8/9/05
	 */
	function closeAndImportExhibition () {
		fwrite($this->_xmlFile,	"\t\t</asset>\n".
			"\t</repository>\n".
			"</import>");
		fclose($this->_xmlFile);
	
		$array = array("edu.middlebury.concerto.exhibition_repository",
"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure",
"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.target_id",
"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.text_position",
"Repository::edu.middlebury.concerto.exhibition_repository::edu.middlebury.concerto.slide_record_structure.edu.middlebury.concerto.slide_record_structure.display_metadata");

// 		if (isset($this->_importer))
// 			unset($this->_importer);
// 		
// 		$this->_importer =& XMLImporter::withFile($array, 
// 			"/home/cshubert/public_html/importer/importtest/metadata.xml",
// 			"insert");
// 
// 		$this->_importer->parseAndImportBelow();
	}
}
?>