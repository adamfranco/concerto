<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

// Check for our authorization function definitions
if (!defined("AZ_DELETE"))
	throwError(new Error("You must define an id for AZ_ACCESS", "concerto.collection", true));

// Get the Repository
$repositoryManager =& Services::getService("Repository");
$idManager =& Services::getService("Id");
$assetId =& $idManager->getId($harmoni->request->get('asset_id'));
$asset =& $repositoryManager->getAsset($assetId);
$repository =& $asset->getRepository();
$repositoryId =& $repository->getId();

// Check that the user can delete this asset
$authZ =& Services::getService("AuthZ");
$idManager =& Services::getService("Id");
if (!$authZ->isUserAuthorized($idManager->getId(AZ_DELETE), $assetId)) {
	// Get the Layout compontents. See core/modules/moduleStructure.txt
	// for more info. 
	return new Block(_("You are not authorized to delete this <em>Asset</em> here."), 2);
}

// Delete the asset
$repository->deleteAsset($assetId);

$harmoni->history->goBack("concerto/asset/delete-return");