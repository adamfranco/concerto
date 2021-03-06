<?php

/**
 * Set up the AgentManager
 *
 * USAGE: Copy this file to agent.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
// :: Set up the AgentManager ::
	$configuration = new ConfigurationProperties;
	// default agent Flavor is one that can be editted
	$agentFlavor="HarmoniEditableAgent";
	$agentHierarchyId = "edu.middlebury.authorization.hierarchy";
	$configuration->addProperty('hierarchy_id', $agentHierarchyId);
	$configuration->addProperty('defaultAgentFlavor', $agentFlavor);
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
	Services::startManagerAsService("AgentManager", $context, $configuration);

// :: Set up PropertyManager ::
	//the property manager operates in the same context as the AgentManager and is more or less an adjunct to it
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
	Services::startManagerAsService("PropertyManager", $context, $configuration);