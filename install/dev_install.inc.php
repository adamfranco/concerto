<?php
/**
 * @package concerto.install
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/*********************************************************
 * Please note that this install script should be included
 * by the config
 * 		- After the HierarchyManager has been started
 * 		- Before the RepositoryManager is started
 * This script will print out a series of values that you 
 * will then need to copy to your config.
 *********************************************************/

if (!isset($_SESSION['table_setup_complete'])) {
	
	/*********************************************************
	 * Check for existing data in the database
	 *********************************************************/
	$dbHandler = Services::getService("DatabaseManager");
	$query = new GenericSQLQuery();
	$query->addSQLQuery("SHOW TABLES");
	$genericResult =& $dbHandler->query($query, $dbID);
	$result =& $genericResult->returnAsSelectQueryResult();
	if ($result->hasNext()) {
		print "<h2>Tables exist in the database. Not creating tables.</h2>";
		print "<h2>If you have just run the installer, comment out it's line in the config to start using Concerto.</h2>";
		$_SESSION['table_setup_complete'] = TRUE;
	
		RequestContext::locationHeader($_SERVER['PHP_SELF']);
	}
	
	print "<h1>Creating tables and default data set.</h1>";
	
	/*********************************************************
	 * Create the needed database tables
	 *********************************************************/
	$sqlFiles = array (
		HARMONI_BASE."/SQL/Agent/MySQL_Agent.sql",
		HARMONI_BASE."/SQL/AuthN/MySQL_Example_Authentication.sql",
		HARMONI_BASE."/SQL/AuthN/MySQL_AgentTokenMapping.sql",
		HARMONI_BASE."/SQL/AuthZ/MySQL_AuthZ.sql",
		HARMONI_BASE."/SQL/dataManager/MySQL_dataManager.sql",
		HARMONI_BASE."/SQL/DigitalRepository/MySQL_DigitalRepository.sql",
		HARMONI_BASE."/SQL/hierarchy/MySQL_hierarchy.sql",
		HARMONI_BASE."/SQL/Id/MySQL_Id.sql",
		HARMONI_BASE."/SQL/sets/MySQL_sets.sql",
		HARMONI_BASE."/SQL/shared/MySQL_shared.sql",
	);
	
	foreach ($sqlFiles as $file) {
		SQLUtils::runSQLfile($file, $dbID);
	}
	
	
	/*********************************************************
	 * Script for setting up the Authorization Hierarchy
	 *********************************************************/
			$hierarchyManager =& Services::getService("HierarchyManager");
			$idManager =& Services::getService("IdManager");
			
			// Create the Hierarchy
			$nodeTypes = array();
			$authorizationHierarchyId =& $idManager->getId("edu.middlebury.authorization.hierarchy");
			$authorizationHierarchy =& $hierarchyManager->createHierarchy(
				"Concerto Qualifier Hierarchy", 
				$nodeTypes,
				"A Hierarchy to hold all Qualifiers known to Concerto.",
				TRUE,
				FALSE,
				$authorizationHierarchyId);
	
			// Create nodes for Qualifiers
			$allOfConcertoId =& $idManager->getId("edu.middlebury.authorization.root");
			$authorizationHierarchy->createRootNode($allOfConcertoId, new DefaultQualifierType, "All of Concerto", "The top level of all of Concerto.");
			
	
	
	/*********************************************************
	 * Script for setting up the RepositoryManager Hierarchy
	 *********************************************************/	
			// Create nodes for Qualifiers
			$collectionsId =& $idManager->getId("edu.middlebury.concerto.collections_root");
			$authorizationHierarchy->createNode($collectionsId, $allOfConcertoId, new DefaultQualifierType, "Concerto Collections", "All Collections in Concerto.");
	
	/*********************************************************
	 * Script for setting up the AgentManager Hierarchy
	 *********************************************************/	
			// Create nodes for Qualifiers
			$systemAgentType =& new Type ("Agents", "edu.middlebury.harmoni", "System", "Agents/Groups required by the Agent system.");
			
			$everyoneId =& $idManager->getId("edu.middlebury.agents.everyone");
			$authorizationHierarchy->createNode($everyoneId, $allOfConcertoId, $systemAgentType, "Everyone", "All Agents and Groups in the system.");
			
			$allGroupsId =& $idManager->getId("edu.middlebury.agents.all_groups");
			$authorizationHierarchy->createNode($allGroupsId, $everyoneId, $systemAgentType, "All Groups", "All Groups in the system.");
			
			$allAgentsId =& $idManager->getId("edu.middlebury.agents.all_agents");;
			$authorizationHierarchy->createNode($allAgentsId, $everyoneId, $systemAgentType, "All Agents", "All Agents in the system.");
			
			
			if (file_exists(MYDIR.'/config/agent.conf.php'))
				require_once (MYDIR.'/config/agent.conf.php');
			else
				require_once (MYDIR.'/config/agent_default.conf.php');
		
			// The anonymous Agent
			$agentManager =& Services::getService("AgentManager");
			$anonymousId =& $idManager->getId("edu.middlebury.agents.anonymous");
			require_once(HARMONI."oki2/shared/NonReferenceProperties.class.php");
			$agentProperties =& new NonReferenceProperties($systemAgentType);
			$agentManager->createAgent("Anonymous", $systemAgentType, $agentProperties, $anonymousId);
	
	/*********************************************************
	 * Script for setting up some default Groups and Users
	 *********************************************************/
			$agentManager =& Services::getService("AgentManager");
			$idManager =& Services::getService("IdManager");			
	
			$groupType =& new Type ("System", "edu.middlebury.harmoni", "SystemGroups", "Groups for administrators and others with special privledges.");
			$nullType =& new Type ("System", "edu.middlebury.harmoni", "NULL");
			$properties =& new HarmoniProperties($nullType);
			$adminGroup =& $agentManager->createGroup("Administrators", $groupType, "Users that have access to every function in the system.", $properties);
			$auditorGroup =& $agentManager->createGroup("Auditors", $groupType, "Users that can view all content in the system but not modify it.", $properties);
			
			// default administrator account
			$authNMethodManager =& Services::getService("AuthNMethodManager");
			$dbAuthType =& new Type ("Authentication", "edu.middlebury.harmoni", "Concerto DB");
			$dbAuthMethod =& $authNMethodManager->getAuthNMethodForType($dbAuthType);
			// Create the representation
			$tokensArray = array("username" => "jadministrator",
							"password" => "password");
			$adminAuthNTokens =& $dbAuthMethod->createTokens($tokensArray);
			// Add it to the system
			$dbAuthMethod->addTokens($adminAuthNTokens);
			
			// Create an agent
			$agentType =& new Type ("System", "edu.middlebury.harmoni", "Default Agents", "Default agents created for install and setup. They should be removed on production systems.");
			require_once(HARMONI."oki2/shared/NonReferenceProperties.class.php");
			$agentProperties =& new NonReferenceProperties($agentType);
			$agentProperties->addProperty("name", "Administrator, John");
			$agentProperties->addProperty("first_name", "John");
			$agentProperties->addProperty("last_name", "Administrator");
			$agentProperties->addProperty("email", "jadministrator@xxxxxxxxx.edu");
			$agentProperties->addProperty("status", "Not a real person.");
			$adminAgent =& $agentManager->createAgent("John Administrator", $agentType, $agentProperties);
			
			// map the agent to the tokens
			$agentTokenMappingManager =& Services::getService("AgentTokenMappingManager");
			$agentTokenMappingManager->createMapping($adminAgent->getId(), $adminAuthNTokens, $dbAuthType);
			
			// Add the agent to the Administrators group.
			$adminGroup->add($adminAgent);
	
	
	/*********************************************************
	 * Script for setting up the AuthorizationFunctions that Concerto will use
	 *********************************************************/
			$authZManager =& Services::getService("AuthorizationManager");
			$idManager =& Services::getService("IdManager");
			$qualifierHierarchyId =& $authorizationHierarchyId; // Id from above
			
			
		// View/Use Functions
			$type =& new Type ("Authorization", "edu.middlebury.harmoni", "View/Use", "Functions for viewing and using.");
		
			$id =& $idManager->getId("edu.middlebury.authorization.access");
			$function =& $authZManager->createFunction($id, "Access", "Access a qualifier.", $type, $qualifierHierarchyId);
			$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
			
			$id =& $idManager->getId("edu.middlebury.authorization.view");
			$function =& $authZManager->createFunction($id, "View", "View a qualifier.", $type, $qualifierHierarchyId);
			$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
			
			$id =& $idManager->getId("edu.middlebury.authorization.comment");
			$function =& $authZManager->createFunction($id, "Comment", "Comment on a qualifier.", $type, $qualifierHierarchyId);
			$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
			
			
		// Editing Functions
			$type =& new Type ("Authorization", "edu.middlebury.harmoni", "Editing", "Functions for editing.");
		
			$id =& $idManager->getId("edu.middlebury.authorization.modify");
			$function =& $authZManager->createFunction($id, "Modify", "Modify a qualifier.", $type, $qualifierHierarchyId);
			$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
			
			$id =& $idManager->getId("edu.middlebury.authorization.delete");
			$function =& $authZManager->createFunction($id, "Delete", "Delete a qualifier.", $type, $qualifierHierarchyId);
			$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
			
			$id =& $idManager->getId("edu.middlebury.authorization.add_children");
			$function =& $authZManager->createFunction($id, "Add Children", "Add children to this qualifier.", $type, $qualifierHierarchyId);
			$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
			
			$id =& $idManager->getId("edu.middlebury.authorization.remove_children");
			$function =& $authZManager->createFunction($id, "Remove Children", "Remove children from this qualifier.", $type, $qualifierHierarchyId);
			$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
			
			
		// Administration Functions
			$type =& new Type ("Authorization", "edu.middlebury.harmoni", "Administration", "Functions for administering.");
		
			$id =& $idManager->getId("edu.middlebury.authorization.view_authorizations");
			$function =& $authZManager->createFunction($id, "View Authorizations", "View Authorizations at a qualifier.", $type, $qualifierHierarchyId);
			$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
			
			$id =& $idManager->getId("edu.middlebury.authorization.modify_authorizations");
			$function =& $authZManager->createFunction($id, "Modify Authorizations", "Modify Authorizations at qualifier.", $type, $qualifierHierarchyId);
			$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);

	
	print "\n<br> ...done";
	$_SESSION['table_setup_complete'] = TRUE;
	
	RequestContext::locationHeader($_SERVER['PHP_SELF']);
}
?>