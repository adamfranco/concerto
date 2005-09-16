<?php

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once("Archive/Tar.php");
require_once(POLYPHONY."/main/library/RepositoryImporter/XMLRepositoryImporter.class.php");
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
			new MySQLDatabase("localhost", "niraj_concerto", "test", "test"));
		$dbHandler->connect($n_C_Index);

		$this->_exhibitionRepositoryId =& $idManager->getId(
			"edu.middlebury.concerto.exhibition_repository");
		$this->_importer =& new XMLRepositoryImporter("/tmp/mdbexhib.tar.gz",
			$this->_exhibitionRepositoryId);
		
		$exhibitionsQuery =& new SelectQuery;
		$exhibitionsQuery->addTable("pressets");
		$exhibitionsQuery->addColumn("id", "exhibitionId");
		$exhibitionsQuery->addColumn("title", "exhibitionName");
		$exhibitionsQuery->addColumn("presentations", "slideshowIds");
		$exhibitionsQuery->addWhere("presentations IS NOT NULL");
		$exhibitionsQuery->addOrderBy("id");
		
		$exhibitionsResults =& $dbHandler->query($exhibitionsQuery, $mdbIndex);

		while ($exhibitionsResults->hasMoreRows()) {
			$exhibition =& $exhibitionsResults->next();
			
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
				$slideshow =& $slideshowsResults->next();
				
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
						$slide =& $slideResult->getCurrentRow();
					
						$idQuery =& new SelectQuery;
						$idQuery->addTable("temp_id_matrix");
						$idQuery->addColumn("media_id");
						$idQuery->addColumn("asset_id");
						$idQuery->addWhere("media_id = ".$slide['media_id']);
						
						$idResult =& $dbHandler->query($idQuery, $n_C_Index);
						if ($idResult->getNumberOfRows() == 1) {
							$idRow =& $idResult->getCurrentRow();
							$this->addSlide($slide, $idRow['asset_id']);
						}
						else {
							$empty = "";
							$this->addSlide($slide, $empty);		
						}
						$idResult->free();
						unset($idQuery);
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

		if ($this->_importer->hasErrors()) {
			print("The bad news is that some errors occured during import, they are: <br />");
			$this->_importer->printErrorMessages();
		}
		if ($this->_importer->hasAssets()) {
			print("The good news is that ".count($this->_importer->getGoodAssetIds())." assets were created during import, they are: <br />");
			$this->_importer->printGoodAssetIds();
		}
		
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
		$this->_xmlFile = fopen(
			"/home/cshubert/public_html/importer/importtest/metadata.xml",
			"wt");
		fwrite($this->_xmlFile, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
		fwrite($this->_xmlFile, "<import>\n");
		fwrite($this->_xmlFile, "\t<asset>\n".
			"\t\t<name>".$exhibition['exhibitionName']."</name>\n".
			"\t\t<description></description>\n".
			"\t\t<type>Exhibition</type>\n");
	}
	
	/**
	 * Opens a new slideshow
	 *
	 * @param array $slide
	 * @since 8/9/05
	 */
	function openSlideshow (&$slideshow) {
		fwrite($this->_xmlFile, "\t\t<asset buildOrderedSet='TRUE'>\n".
			"\t\t\t<name>".$slideshow['slideshowName']."</name>\n".
			"\t\t\t<description><![CDATA[".$slideshow['slideshowDescription'].
				"]]></description>\n".
			"\t\t\t<type>Slideshow</type>\n");
	}
	
	/**
	 * Opens a new slideshow
	 *
	 * @since 8/9/05
	 */
	function closeSlideshow () {
		fwrite($this->_xmlFile, "\t\t</asset>\n");
	}	
	
	/**
	 * Creates an entire slide entry in the xmlFile
	 *
	 * @param array $slide
	 * @since 8/9/05
	 */
	function addSlide (&$slide, &$asset_id) {
	fwrite($this->_xmlFile, "\t\t\t<asset>\n".
			"\t\t\t\t<name></name>\n".
			"\t\t\t\t<description><![CDATA[".
				$slide['slideCaption']."]]></description>\n".
			"\t\t\t\t<type>Slide</type>\n".
			"\t\t\t\t<record schema=\"Slide Schema\">\n".
			"\t\t\t\t\t<field name=\"target id\">".
				$asset_id."</field>\n".
			"\t\t\t\t\t<field name=\"text position\">right</field>\n".
			"\t\t\t\t\t<field name=\"display metadata\">false</field>\n".
			"\t\t\t\t</record>\n".
			"\t\t\t</asset>\n");
	}

	/**
	 * Closes and Imports a single exhibition
	 *
	 * @since 8/9/05
	 */
	function closeAndImportExhibition () {
		fwrite($this->_xmlFile,	"\t</asset>\n".
			"</import>");
		fclose($this->_xmlFile);
		$tar = new Archive_Tar("/tmp/mdbexhib.tar.gz", "gz");
		$fileArray = array(
			"/home/cshubert/public_html/importer/importtest/metadata.xml");
		$tar->createModify($fileArray, "", 
			"/home/cshubert/public_html/importer/importtest");
		
		$this->_importer->import();
		unset($tar);
	}
}
?>