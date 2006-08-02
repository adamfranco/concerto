<?php

/**
 * Set up the GUIManager
 *
 * USAGE: Copy this file to gui.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

//require_once(dirname(__FILE__)."/../themes/SimpleTheme/MutableSimpleTheme.class.php");

// require_once(dirname(__FILE__)."/../themes/SimpleTheme/SimpleTheme.class.php");
// 
// 
// // :: GUIManager setup ::
// 	define("LOGO_URL", MYPATH."/themes/SimpleTheme/images/logo.gif");
// 	
// 	$configuration =& new ConfigurationProperties;
// 	$configuration->addProperty('database_index', $dbID);
// 	$configuration->addProperty('database_name', $dbName);
// 	$configuration->addProperty('default_theme', new SimpleTheme);
// 	$configuration->addProperty('character_set', $arg0 = 'utf-8');
// 	$configuration->addProperty('document_type', $arg1 = 'text/html');
// 	$configuration->addProperty('document_type_definition', $arg2 = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
// 	unset($arg0, $arg1, $arg2);
// 	Services::startManagerAsService("GUIManager", $context, $configuration);



// require_once(dirname(__FILE__)."/../themes/SimpleThemeWhite/SimpleThemeWhite.class.php");
// 
// 
// // :: GUIManager setup ::
// 	define("LOGO_URL", MYPATH."/themes/SimpleThemeWhite/images/logo.gif");
// 	
// 	$configuration =& new ConfigurationProperties;
// 	$configuration->addProperty('database_index', $dbID);
// 	$configuration->addProperty('database_name', $dbName);
// 	$configuration->addProperty('default_theme', new SimpleThemeWhite);
// 	$configuration->addProperty('character_set', $arg0 = 'utf-8');
// 	$configuration->addProperty('document_type', $arg1 = 'text/html');
// 	$configuration->addProperty('document_type_definition', $arg2 = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
// 	unset($arg0, $arg1, $arg2);
// 	Services::startManagerAsService("GUIManager", $context, $configuration);



	require_once(HARMONI."GUIManager/Themes/GenericTheme.class.php");
	require_once(HARMONI."GUIManager/Themes/SimpleTheme.class.php");
	require_once(HARMONI."GUIManager/Themes/SimpleTheme1.class.php");
	require_once(HARMONI."GUIManager/Themes/SimpleLinesTheme.class.php");
	require_once(dirname(__FILE__)."/../themes/SimpleThemeBlack/SimpleThemeBlack.class.php");
	define("LOGO_URL", MYPATH."/themes/SimpleThemeBlack/images/logo.gif");
	
	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
    $configuration->addProperty('default_theme', new SimpleThemeBlack);
	$configuration->addProperty('character_set', $arg0 = 'utf-8');
	$configuration->addProperty('document_type', $arg1 = 'text/html');
	$configuration->addProperty('document_type_definition', $arg2 = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
	
	$arrayOfThemes[] = array("Simple Black Theme","SimpleBlackTheme");
	$arrayOfThemes[] = array("Generic Theme","GenericTheme");
	$arrayOfThemes[] = array("Simple Theme","SimpleTheme");
	$arrayOfThemes[] = array("Simple Theme One","SimpleTheme1");
	$arrayOfThemes[] = array("Simple Lines Theme","SimpleLinesTheme");
	$configuration->addProperty('array_of_default_themes', $arrayOfThemes);
	
	unset($arg0, $arg1, $arg2);
	Services::startManagerAsService("GUIManager", $context, $configuration);
