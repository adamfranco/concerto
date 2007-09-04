<?php

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

// apd_set_pprof_trace(); 

class authtransAction 
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
		return _("Authorization Transfer");
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
		$rm = Services::getService("Repository");
		$agentM = Services::getService("Agent");
		$idManager = Services::getService("Id");


print "lines 46 - 49 in authtrans.act.php need to be modified for database access";
//		$mdbIndex = $dbHandler->addDatabase(
//			new MySQLDatabase("host", "db", "uname", "password"));
//		$dbHandler->connect($mdbIndex);
exit();

		$searchTypes =$agentM->getAgentSearchTypes();
		$searchType =$searchTypes->next();
		
		$centerPane =$this->getActionRows();
		ob_start();
		
		$unamesQuery = new SelectQuery;
		$unamesQuery->addTable("users");
		$unamesQuery->addColumn("uname");
		$unamesQuery->addColumn("email");
		$unamesQuery->addOrderBy("uname");
		
		$unamesResults =$dbHandler->query($unamesQuery, $mdbIndex);

		$unameToId = array(); // key on mdb uname value to c_beta id

		while ($unamesResults->hasMoreRows()) {
			$unamePair = $unamesResults->next();
			
			if (!is_null($unamePair['email'])) {
				$agents =$agentM->getAgentsBySearch($unamePair['email'],
					$searchType);
				if ($agents->hasNext())
					$agent =$agents->next();
				$unameToId[$unamePair['uname']] =$agent->getId();
			}
		}
	// ===== at this point we have unames and ids
	
		$mediasetsQuery = new SelectQuery;
		$mediasetsQuery->addTable("mediasets");
		$mediasetsQuery->addColumn("title");
		$mediasetsQuery->addColumn("editors");
		$mediasetsQuery->addColumn("presenters");
		$mediasetsQuery->addColumn("search");
		$mediasetsQuery->addOrderBy("title");
		
		$mediasetsResults =$dbHandler->query($mediasetsQuery, $mdbIndex);
		
		$repositories =$rm->getRepositories();
		$reps = array();
		while ($repositories->hasNext()) {
			$rep =$repositories->next();
			
			$reps[$rep->getDisplayName()] = array('id' => $rep->getId());
		}
		unset($reps['All Exhibitions']);
	// ===== at this point we have repository id's associated with displaynames
	
		while ($mediasetsResults->hasMoreRows()) {
			$mediasetInfo = $mediasetsResults->next();
			if (isset($reps[$mediasetInfo['title']])) {
				$editors = unserialize($mediasetInfo['editors']);
				$presenters = unserialize($mediasetInfo['presenters']);

				if (count($editors) > 0) {
					$reps[$mediasetInfo['title']]['editors'] = array();
				}
				foreach ($editors as $editor) {
					if (isset($unameToId[trim($editor)]))
						$reps[$mediasetInfo['title']]['editors'][] =
							$unameToId[trim($editor)];
				}
				if (count($presenters) > 0)
					$reps[$mediasetInfo['title']]['presenters'] = array();
				foreach ($presenters as $presenter) {
					if (isset($unameToId[trim($presenter)]))
						$reps[$mediasetInfo['title']]['presenters'][] =
							$unameToId[trim($presenter)];
				}
				switch ($mediasetInfo['search']) {
					case '1':
						$reps[$mediasetInfo['title']]['search'] = 'edu.middlebury.agents.everyone';
						break;
					case '2':
						$reps[$mediasetInfo['title']]['search'] = 'edu.middlebury.agents.users';
						break;
					case '3':
						$reps[$mediasetInfo['title']]['search'] = 'CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu';
						break;
					default:
						break;
				}
			}
		}
	// ===== at this point reps has presenters and editors for each mediaset

		$pressetsQuery = new SelectQuery;
		$pressetsQuery->addTable("pressets");
		$pressetsQuery->addColumn("title");
		$pressetsQuery->addColumn("presenters");
		$pressetsQuery->addColumn("owner");
		$pressetsQuery->addColumn("view");
		$pressetsQuery->addOrderBy("title");
		
		$pressetsResults =$dbHandler->query($pressetsQuery, $mdbIndex);

		$erep =$rm->getRepository(
			$idManager->getId("edu.middlebury.concerto.exhibition_repository"));

		$exhibitions =$erep->getAssets();
		$exhibits = array();
		while ($exhibitions->hasNext()) {
			$exhibit =$exhibitions->next();
			
			$exhibits[$exhibit->getDisplayName()] = array('id' => $exhibit->getId());
		}
	// ===== at this point we have exhibition id's associated with display names
		
		while ($pressetsResults->hasMoreRows()) {
			$pressetInfo = $pressetsResults->next();
			if (isset($exhibits[$pressetInfo['title']])) {
				$presenters = unserialize($pressetInfo['presenters']);

				if (!is_null($pressetInfo['owner'])) {
					$owners =$agentM->getAgentsBySearch(
						$pressetInfo['owner'], $searchType);
					if ($owners->count() == 1) {
						$owner =$owners->next();
						$exhibits[$pressetInfo['title']]['owner'] = 
							$owner->getId();
					}
				}
				if (count($presenters) > 0)
					$exhibits[$pressetInfo['title']]['presenters'] = array();
				foreach ($presenters as $presenter) {
					if (isset($unameToId[trim($presenter)]))
						$exhibits[$pressetInfo['title']]['presenters'][] =
							$unameToId[trim($presenter)];
				}
				switch ($pressetInfo['view']) {
					case '1':
						$reps[$pressetInfo['title']]['view'] = 'everyone';
						break;
					case '2':
						$reps[$pressetInfo['title']]['view'] = 'users';
						break;
					case '3':
						$reps[$mediasetInfo['title']]['search'] = 'CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu';
						break;
					default:
						break;
				}
			}
		}
		
	// ===== at this point all the necessary authz's should be queued up
		
		$uIdToAuths = array(); // key on c_beta uId's value to assoc array

// ===== GIVE THE AUTHORIZATIONS
		$authZ = Services::getService("AuthZ");

		$viewFunctions = array(
			$idManager->getId('edu.middlebury.authorization.access'),
			$idManager->getId('edu.middlebury.authorization.view'),
			$idManager->getId('edu.middlebury.authorization.comment'));

		$editFunctions = array(
			$idManager->getId('edu.middlebury.authorization.modify'),
			$idManager->getId('edu.middlebury.authorization.delete'),
			$idManager->getId('edu.middlebury.authorization.add_children'),
			$idManager->getId('edu.middlebury.authorization.remove_children'));
			
		$adminFunctions = array(
	$idManager->getId('edu.middlebury.authorization.view_authorizations'),
	$idManager->getId('edu.middlebury.authorization.modify_authorizations'));


		foreach ($reps as $repAuths) {
			$repId =$repAuths['id'];
			if (isset($repAuths['editors'])) {
				foreach ($repAuths['editors'] as $editorId) {
					foreach ($viewFunctions as $vfnId) {
						$this->addAuth($editorId, $vfnId, $repId, $uIdToAuths);
// 					$authZ->createAuthorization(
// 						$editorId,
// 						$vfnId,
// 						$repId);
						}
					foreach ($editFunctions as $efnId) {
						$this->addAuth($editorId, $efnId, $repId, $uIdToAuths);
// 					$authZ->createAuthorization(
// 						$editorId,
// 						$efnId,
// 						$repId);
					}
					foreach ($adminFunctions as $afnId) {
						$this->addAuth($editorId, $afnId, $repId, $uIdToAuths);
// 					$authZ->createAuthorization(
// 						$editorId,
// 						$afnId,
// 						$repId);
					}
				}
			}
			if (isset($repAuths['presenters'])) {
				foreach ($repAuths['presenters'] as $presenterId) {
					foreach ($viewFunctions as $vfnId) {
						$this->addAuth($presenterId, $vfnId, $repId, $uIdToAuths);
// 					$authZ->createAuthorization(
// 						$presenterId,
// 						$vfnId,
// 						$repId);
					}
				}
			}
			if (isset($repAuths['search'])) {
				$grpId =$idManager->getId($repAuths['search']);
				foreach ($viewFunctions as $vfnId) {
						$this->addAuth($grpId, $vfnId, $repId, $uIdToAuths);
// 					$authZ->createAuthorization(
// 						$grpId,
// 						$vfnId,
// 						$repId);
				}
			}
		}
	// ===== all authorizations for collections should be set
			
		foreach ($exhibits as $exAuths) {
			$exId =$exAuths['id'];
			if (isset($exAuths['presenters'])) {
				foreach ($exAuths['presenters'] as $editorId) {
					foreach ($viewFunctions as $vfnId) {
						$this->addAuth($editorId, $vfnId, $exId, $uIdToAuths);
// 					$authZ->createAuthorization(
// 						$editorId,
// 						$vfnId,
// 						$exId);
					}
					foreach ($editFunctions as $efnId) {
						$this->addAuth($editorId, $efnId, $exId, $uIdToAuths);
// 					$authZ->createAuthorization(
// 						$editorId,
// 						$efnId,
// 						$exId);
					}
				}
			}
			if (isset($exAuths['owner'])) {
				foreach ($viewFunctions as $vfnId) {
						$this->addAuth($editorId, $vfnId, $exId, $uIdToAuths);
// 					$authZ->createAuthorization(
// 						$editorId,
// 						$vfnId,
// 						$exId);
				}
				foreach ($editFunctions as $efnId) {
						$this->addAuth($editorId, $efnId, $exId, $uIdToAuths);
// 					$authZ->createAuthorization(
// 						$editorId,
// 						$efnId,
// 						$exId);
				}
				foreach ($adminFunctions as $afnId) {
						$this->addAuth($editorId, $afnId, $exId, $uIdToAuths);
// 					$authZ->createAuthorization(
// 						$editorId,
// 						$afnId,
// 						$exId);
				}
			}
		}
		
// this is where the magic happens (if you have a lot of old authz's you may want to add statusstars to this loop)

		foreach ($uIdToAuths as $uidstring => $fids) {
			foreach ($fids as $fidstring => $qids) {
				foreach ($qids as $qidstring => $true) {
					$authZ->createAuthorization(
						$idManager->getId($uidstring),
						$idManager->getId($fidstring),
						$idManager->getId($qidstring));
				}
			}
		}

	// ===== all authorizations for exhibitions should be set

		$centerPane->add(new Block(ob_get_contents(), 1));
		ob_end_clean();

		$dbHandler->disconnect($mdbIndex);

		return true;
	}
	
	function addAuth ($uid, $fid, $qid, $uIdToAuths) {
		if (!isset($uIdToAuths[$uid->getIdString()]))
			$uIdToAuths[$uid->getIdString()] = array();
		if (!isset($uIdToAuths[$uid->getIdString()][$fid->getIdString()]))
			$uIdToAuths[$uid->getIdString()][$fid->getIdString()] = array();
		if (!isset($uIdToAuths[$uid->getIdString()][$fid->getIdString()][$qid->getIdString()]))
				$uIdToAuths[$uid->getIdString()][$fid->getIdString()][$qid->getIdString()] = true;
//		}
	}
}
?>