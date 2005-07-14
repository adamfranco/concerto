<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */


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
if (!$authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.delete"), $assetId)) {
	// Get the Layout compontents. See core/modules/moduleStructure.txt
	// for more info. 
	return new Block(_("You are not authorized to delete this <em>Asset</em> here."), 2);
}

// Delete the asset
$repository->deleteAsset($assetId);

$harmoni->history->goBack("concerto/asset/delete-return");