<?php

/**
 * Set up the AgentManager
 *
 * 
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
// :: Set up the AgentManager ::
	$configuration =& new ConfigurationProperties;
	// default agent Flavor is one that can be editted
	$agentFlavor="HarmoniEditableAgent";
	
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
	$configuration->addProperty('defaultAgentFlavor', $agentFlavor);
	Services::startManagerAsService("AgentManager", $context, $configuration);

// :: Set up PropertyManager ::
	//the property manager operates in the same context as the AgentManager and is more or less an adjunct to it
	Services::startManagerAsService("PropertyManager", $context, $configuration);