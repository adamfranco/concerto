#!/usr/local/bin/php
<?php
/**
 * A nightly processing script that does the following operations:
 * - deletes any temporary files that were not deleted during upload due to errors.
 * - cleans up old OAI resumption tokens
 * - updates the OAI data tables with the latest Asset data.
 * 
 * @since 10/30/07
 * @package concerto
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 


/*********************************************************
 * Clean up OAI resumption tokens
 *********************************************************/
 require(dirname(__FILE__)."/oai_cleanup_tokens.php");

/*********************************************************
 * Update the OAI data tables
 *********************************************************/
 require(dirname(__FILE__)."/oai_update_data.php");


?>