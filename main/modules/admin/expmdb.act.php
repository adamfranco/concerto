<?php

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

//apd_set_pprof_trace(); 

class expmdbAction 
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
		return _("MediaDB Export");
	}

	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {	
		$dbHandler = Services::getService("DBHandler");
		$repositoryManager = Services::getService("Repository");
		$setManager = Services::getService("Sets");
		$idManager = Services::getService("Id");

print "lines 46 - 48 in expmdb.act.php need to be modified for database access";
	// ===== ET (GOOD) MEDIADB DATABASE CONNECTION ===== //
//		$mdbIndex = $dbHandler->addDatabase(
//			new MySQLDatabase("host", "db", "uname", "password"));
//		$dbHandler->connect($mdbIndex);
exit();

	// ===== CONCERTO DATABASE CONNECTION ===== //
		$dbHandler->disconnect(IMPORTER_CONNECTION);
		$dbHandler->connect(IMPORTER_CONNECTION);

	// ===== TMP TABLE FOR ID MATCHING ===== //
		$createTableQuery = new GenericSQLQuery;
		$createTableQuery->addSQLQuery(
			"Create table if not exists temp_id_matrix ( 
			media_id int not null ,
			asset_id varchar(50) not null)");

		$dbHandler->query($createTableQuery, $mdbIndex);
	// ===== COLLECTIONS QUERY ===== //
		$collectionsQuery = new SelectQuery();
		$collectionsQuery->addTable("mediasets");
		$collectionsQuery->addColumn("mediasets.id", "id");
		$collectionsQuery->addColumn("mediasets.title", "name");
		$collectionsQuery->addColumn("mediasets.fieldlist", "fieldlist");
		$collectionsQuery->addColumn("mediasets.keywords", "description");

		$collections =$dbHandler->query($collectionsQuery, $mdbIndex);
		$this->_folder = 'mdb1_conc2';

		if (!is_dir("/tmp/".$this->_folder))
			mkdir("/tmp/".$this->_folder);
	// ===== COLUMN TITLES FROM MEDIADB ===== //
		$this->_pSArray = explode(" ", "subject url creator category01 category02 date publisher contributor type format identifier source language relation coverage rights owner measurements material technique location id_number style_period culture");
		$this->_dcArray = explode(" ", "title creator subject description publisher contributor date type format identifier source language relation coverage rights");
		$this->_dc = count($this->_dcArray);
		$this->_vraArray = explode(" ", "title measurements material technique creator date location publisher contributor type format identifier id_number style_period culture subject relation description source rights");
		$this->_vra = count($this->_vraArray);
		
		$this->openTopXML();
	// ===== GO THROUGH EACH MEDIA SET(COLLECTION) ===== //
		while ($collections->hasMoreRows()) {
			$collection = $collections->next();
		// ===== ASSETS QUERY ===== //
			$assetsQuery = new SelectQuery();
			$assetsQuery->addTable("media");
			$assetsQuery->addColumn("*");
			$assetsQuery->addWhere("id_set = ".$collection['id']);
			
			$assets =$dbHandler->query($assetsQuery, $mdbIndex);
		// ===== MAKE SURE THE COLLECTION HAS ASSETS ===== //
			if ($assets->getNumberOfRows() > 0) {
			// ===== NEW XML FOR THE COLLECTION ITSELF ===== //
				$this->openCollectionXML($collection);
				$this->exportCollection($collection);
			// ===== WRITE THE MEDIADB RECORDSTRUCTURE INTO THIS XML ===== //
				$rSArray = $this->prepareRS($collection);
				$this->createRS($this->_currentXML, $rSArray, 
 					$collection['id']);
 				$this->exportAssets($assets, $rSArray, $collection['id']);
 				$this->closeCollectionXML();
			}
		}
		$this->closeTopXML();
	}
	
	function openTopXML() {
		$this->_allXML = fopen("/tmp/".$this->_folder."/metadata.xml", "w");
		fwrite($this->_allXML,
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<import>\n");
	}
	
	function closeTopXML() {
		fwrite($this->_allXML, "</import>");
		fclose($this->_allXML);
	}
	
	function openCollectionXML($collection) {
		if (!is_dir("/tmp/".$this->_folder."/".$collection['id']))
			mkdir("/tmp/".$this->_folder."/".$collection['id']);
		$this->_currentXML = fopen("/tmp/".$this->_folder."/".
			$collection['id']."/metadata.xml", "w");
		fwrite($this->_currentXML,
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<repository>\n");
	}
	
	function exportCollection($collection) {
	// ===== ADD THIS COLLECTION TO THE FULL IMPORT ===== //
		fwrite($this->_allXML, "\t<repositoryfile>".$collection['id'].
			"/metadata.xml</repositoryfile>\n");
	// ===== WRITE THIS COLLECTION INTO ITS OWN XML FILE ===== //
		fwrite($this->_currentXML,
"\t<name>".$collection['name']."</name>\n".
"\t<description>".$collection['description']."</description>\n".
"\t<type>\n".
"\t\t<domain>User Repositories</domain>\n".
"\t\t<authority>edu.middlebury.concerto</authority>\n".
"\t\t<keyword>Mediaset</keyword>\n".
"\t\t<description>Mediaset from MediaDB imported into Concerto</description>\n".
"\t</type>\n");
	}
	
	function createRS($fileHandle, $rSArray, $id) {
		if ($rSArray == $this->_dcArray)
			$this->createDC($fileHandle);
		else if ($rSArray == $this->_vraArray)
			$this->createVRA($fileHandle);
		else {
			fwrite($fileHandle,
"\t\t<recordstructure id=\"MDBRS-".$id."\" xml:id=\"MDBRS-".$id."\" isGlobal=\"FALSE\">\n".
"\t\t\t<name>Mediaset-".$id."</name>\n".
"\t\t\t<description><![CDATA[This is the Concerto 1.0 (MediaDB) RecordStructure for mediaset ".$id."]]></description>\n".
"\t\t\t<format>format</format>\n");
			foreach ($rSArray as $pS) {
				fwrite($fileHandle,
"\t\t\t<partstructure xml:id=\"".$pS."\">\n".
"\t\t\t\t<name>".$pS."</name>\n".
"\t\t\t\t<description><![CDATA[Mediaset-".$id."-".$pS."]]></description>\n".
"\t\t\t\t<type>\n\t\t\t\t\t<domain>Repository</domain>\n".
"\t\t\t\t\t<authority>edu.middlebury.harmoni</authority>\n".
"\t\t\t\t\t<keyword>string</keyword>\n\t\t\t\t</type>\n".
"\t\t\t</partstructure>\n");
			}
			fwrite($fileHandle, "\t\t</recordstructure>\n");
		}
	}
	
	function exportAssets($assets, $rSArray, $id) {
		$mimeManager = Services::getService("MIME");
		while($assets->hasMoreRows()) {
			$asset = $assets->next();
			
			$mime = $mimeManager->getMIMETypeForFileName(
				$asset['fname']);
			$mimeParts = explode("/", $mime);

			fwrite($this->_currentXML,
"\t<asset>\n".
"\t\t<name>".$asset['title']."</name>\n".
"\t\t<description><![CDATA[".$asset['description']."]]></description>\n".
"\t\t<type>\n\t\t\t<domain>Asset Types</domain>\n".
"\t\t\t<authority>edu.middlebury.concerto</authority>\n".
"\t\t\t<keyword>".$mimeParts[0]."</keyword>\n".
"\t\t\t<description>An asset with a(n) ".$mimeParts[0]." primary component.</description>\n".
"\t\t</type>\n");
			if ($rSArray == $this->_dcArray)
				$this->dcAssetRecord($asset);
			else {
				fwrite($this->_currentXML, 
"\t\t<record xml:id=\"MDBRS-".$id."\">\n");
				// mediadb parts here!!!
				foreach ($rSArray as $key => $pS) {
					if ($asset[$key] != "")
						fwrite($this->_currentXML,
"\t\t\t<part xml:id=\"".$pS."\"><![CDATA[".$asset[$key]."]]></part>\n");
				}
			}
			fwrite($this->_currentXML,
"\t\t</record>\n".
"\t\t<filerecord>\n".
"\t\t\t<filepathpart>http://maggot.middlebury.edu/mediadb_media/".
$id."/".rawurlencode($asset['fname'])."</filepathpart>\n".
"\t\t</filerecord>\n".
"\t</asset>\n");
		}
	}

	function closeCollectionXML() {
		fwrite($this->_currentXML,"</repository>");
		fclose($this->_currentXML);
	}
	
	function prepareRS($collection) {
		$fieldsArray = unserialize($collection['fieldlist']);
		$newArray = array();
		$dc = 0;
		$vra = 0;
		for ($i = 0; $i < count($fieldsArray); $i += 3) {
		// same key and value (both names match) then add the element
			if (in_array($fieldsArray[$i], $this->_dcArray) && 
					in_array(strtolower($fieldsArray[$i+1]), $this->_dcArray))
				$dc++;
			if (in_array($fieldsArray[$i], $this->_vraArray) && 
					in_array(strtolower($fieldsArray[$i+1]), $this->_vraArray))
				$vra++;
			if ($fieldsArray[$i] != "fname" && $fieldsArray[$i] != "url")
				$newArray[$fieldsArray[$i]] = $fieldsArray[$i+1];
		}
		if (($dc == count($newArray)) && ($dc == $this->_dc))
			return $this->_dcArray;
		else if (($vra == count($newArray)) && ($vra == $this->_vra))
			return $this->_vraArray;
		else
			return $newArray;
	}
	
	function createDC() {
		fwrite($this->_currentXML,
"	<recordstructure xml:id=\"dc\" isGlobal=\"TRUE\">
		<name>Dublin Core</name>
		<description><![CDATA[]]></description>
		<format>text/plain</format>
		<partstructure xml:id=\"dc-title\" isRepeatable=\"TRUE\">
			<name>Title</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>string</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-creator\" isRepeatable=\"TRUE\">
			<name>Creator</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>shortstring</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-subject\" isRepeatable=\"TRUE\">
			<name>Subject</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>shortstring</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-description\" isRepeatable=\"TRUE\">
			<name>Description</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>string</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-publisher\" isRepeatable=\"TRUE\">
			<name>Publisher</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>shortstring</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-contributor\" isRepeatable=\"TRUE\">
			<name>Contributor</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>shortstring</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-date\" isRepeatable=\"TRUE\">
			<name>Date</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>datetime</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-type\" isRepeatable=\"TRUE\">
			<name>Type</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>shortstring</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-format\" isRepeatable=\"TRUE\">
			<name>Format</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>shortstring</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-identifier\" isRepeatable=\"TRUE\">
			<name>Identifier</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>string</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-source\" isRepeatable=\"TRUE\">
			<name>Source</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>shortstring</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-language\" isRepeatable=\"TRUE\">
			<name>Language</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>shortstring</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-relation\" isRepeatable=\"TRUE\">
			<name>Relation</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>string</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-coverage\" isRepeatable=\"TRUE\">
			<name>Coverage</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>string</keyword>
			</type>
		</partstructure>
		<partstructure xml:id=\"dc-rights\" isRepeatable=\"TRUE\">
			<name>Rights</name>
			<description><![CDATA[]]></description>
			<type>
				<domain>Repository</domain>
				<authority>edu.middlebury.concerto</authority>
				<keyword>string</keyword>
			</type>
		</partstructure>
	</recordstructure>");
	}
	
	function dcAssetRecord($asset) {
		fwrite($this->_currentXML,
"\t\t<record xml:id=\"dc\">\n");
		foreach ($this->_dcArray as $part) {
			if ($asset[$part] != "")
				fwrite($this->_currentXML,
"\t\t\t<part xml:id=\"dc-".$part."\"><![CDATA[".$asset[$part]."]]></part>\n");
		}
	}
	
	function createVRA() {
		// not used
	}
}