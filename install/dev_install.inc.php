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
	exit;
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
 * Script for setting up the RepositoryManager Hierarchy
 *********************************************************/
		$hierarchyManager =& Services::getService("HierarchyManager");
		$idManager =& Services::getService("IdManager");
		
		// Create the Hierarchy
		$nodeTypes = array();
		$hierarchy =& $hierarchyManager->createHierarchy(
			"Concerto Qualifier Hierarchy", 
			$nodeTypes,
			"A Hierarchy to hold all Qualifiers known to Concerto.",
			TRUE,
			FALSE);
		$hierarchyId =& $hierarchy->getId();
		print "<h2>RepositoryHierarchy Id, to enter in config: ".$hierarchyId->getIdString()."</h2>";
		// Create nodes for Qualifiers
		$allOfConcertoId =& $idManager->createId();
		$collectionsId =& $idManager->createId();
		print "<h2>Repository-DefaultParent Id, to enter in config: ".$collectionsId->getIdString()."</h2>";
		$hierarchy->createRootNode($allOfConcertoId, new DefaultQualifierType, "All of Concerto", "The top level of all of Concerto.");
		$hierarchy->createNode($collectionsId, $allOfConcertoId, new DefaultQualifierType, "Concerto Collections", "All Collections in Concerto.");

/*********************************************************
 * Script for setting up some default Groups and Users
 *********************************************************/
		$agentManager =& Services::getService("AgentManager");
		$idManager =& Services::getService("IdManager");
		
		$groupType =& new Type ("System", "Concerto", "SystemGroups", "Groups for administrators and others with special privledges.");
		$nullType =& new Type ("System", "Concerto", "NULL");
		$properties =& new HarmoniProperties($nullType);
		$adminGroup =& $agentManager->createGroup("Administrators", $groupType, "Users that have access to every function in the system.", $properties);
		$auditorGroup =& $agentManager->createGroup("Auditors", $groupType, "Users that can view all content in the system but not modify it.", $properties);
		
		// default administrator account
		$authNMethodManager =& Services::getService("AuthNMethodManager");
		$dbAuthType =& new Type ("Authentication", "Middlebury College", "Concerto DB");
		$dbAuthMethod =& $authNMethodManager->getAuthNMethodForType($dbAuthType);
		// Create the representation
		$tokensArray = array("username" => "jadministrator",
						"password" => "password");
		$adminAuthNTokens =& $dbAuthMethod->createTokens($tokensArray);
		// Add it to the system
		$dbAuthMethod->addTokens($adminAuthNTokens);
		
		// Create an agent
		$agentType =& new Type ("System", "Concerto", "Default Agents", "Default agents created for install and setup. They should be removed on production systems.");
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
		$qualifierHierarchyId =& $hierarchyId; // Id from above
		
		
	// View/Use Functions
		$type =& new Type ("Authorization", "Concerto", "View/Use", "Functions for viewing and using.");
	
		$id =& $idManager->createId();
		print "<h2>Function id for Access: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Access", "Access a qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		$id =& $idManager->createId();
		print "<h2>Function id for View: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "View", "View a qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		$id =& $idManager->createId();
		print "<h2>Function id for Comment: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Comment", "Comment on a qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		
	// Editing Functions
		$type =& new Type ("Authorization", "Concerto", "Editing", "Functions for editing.");
	
		$id =& $idManager->createId();
		print "<h2>Function id for Edit: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Edit", "Edit a qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		$id =& $idManager->createId();
		print "<h2>Function id for Delete: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Delete", "Delete a qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		$id =& $idManager->createId();
		print "<h2>Function id for Add Children: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Add Children", "Add children to this qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		
	// Administration Functions
		$type =& new Type ("Authorization", "Concerto", "Administration", "Functions for administering.");
	
		$id =& $idManager->createId();
		print "<h2>Function id for View Authorizations: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "View Authorizations", "View Authorizations at a qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		$id =& $idManager->createId();
		print "<h2>Function id for Modify Authorizations: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Modify Authorizations", "Modify Authorizations at qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		$id =& $idManager->createId();
		print "<h2>Function id for Create Agents: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Create Agents", "Create Agents at qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		$id =& $idManager->createId();
		print "<h2>Function id for Modify Agents: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Modify Agents", "Modify Agents at qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		$id =& $idManager->createId();
		print "<h2>Function id for Delete Agents: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Delete Agents", "Delete Agents at qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		$id =& $idManager->createId();
		print "<h2>Function id for Create Groups: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Create Groups", "Create Groups at qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
				
		$id =& $idManager->createId();
		print "<h2>Function id for Modify Groups: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Modify Groups", "Modify Groups at qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);
		
		$id =& $idManager->createId();
		print "<h2>Function id for Delete Groups: ".$id->getIdString()."</h2>\n";
		$function =& $authZManager->createFunction($id, "Delete Groups", "Delete Groups at qualifier.", $type, $qualifierHierarchyId);
		$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfConcertoId);

exit;
?>