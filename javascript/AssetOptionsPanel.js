/**
 * @since 3/28/07
 * @package 
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

AssetOptionsPanel.prototype = new Panel();
AssetOptionsPanel.prototype.constructor = AssetOptionsPanel;
AssetOptionsPanel.superclass = Panel.prototype;

/**
 * <##>
 * 
 * @since 3/28/07
 * @package <##>
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function AssetOptionsPanel ( repositoryId, assetId, positionElement, toShow, viewerUrl ) {
	if ( arguments.length > 0 ) {
		this.init( repositoryId, assetId, positionElement, toShow, viewerUrl );
	}
}

	/**
	 * <##>
	 * 
	 * @param string qualifierId
	 * @param object positionElement
	 * @param array toShow
	 * @return void
	 * @access public
	 * @since 3/28/07
	 */
	AssetOptionsPanel.prototype.init = function ( repositoryId, assetId, positionElement, toShow, viewerUrl ) {
		AuthZViewer.superclass.init.call(this, 
								"Asset Options",
								200,
								300,
								positionElement);
		
		this.repositoryId = repositoryId;
		this.assetId = assetId;
		this.toShow = toShow;
		this.viewerUrl = viewerUrl
				
		this.options = this.contentElement.appendChild(document.createElement('table'));
				
		if (toShow.elementExists('view')) {
			this.addPopupButton(this.viewerUrl, 'View', 'View this Asset in the pop-up viewer.');
			this.addButton ('view', 'Details', 'View the detail screen for this Asset.');
		}
		
		if (toShow.elementExists('browse')) {
			this.addButton ('browse', 'Browse', 'Browse the Assets contained by this Asset.');
		}
		
		if (toShow.elementExists('edit')) {
			this.addButton ('edit', 'Edit', 'Modify this Asset.', {'collection_id': this.repositoryId, 'assets': this.assetId});
		}
		
		if (toShow.elementExists('delete')) {
			this.addConfirmButton ('delete', 'Delete', 'Delete this Asset.', 'Are you sure that you want to delete this Asset and any Assets within it?');
		}
		
		if (toShow.elementExists('add_children')) {
			this.addButton ('add', 'Add Child', 'Add an Asset below this Asset.', {'collection_id': this.repositoryId, 'parent': this.assetId});
		}
		
	}
	
	/**
	 * Initialize and run the AuthZViewer
	 * 
	 * @param string qualifierId
	 * @param object positionElement
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	AssetOptionsPanel.run = function ( repositoryId, assetId, positionElement, toShow, viewerUrl ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			positionElement.panel = new AssetOptionsPanel( repositoryId, assetId, positionElement, toShow, viewerUrl );
		}
	}
	
	/**
	 * Add a button and description to the panel
	 * 
	 * @param string action
	 * @param string title
	 * @param string description
	 * @param object params
	 * @return void
	 * @access public
	 * @since 4/2/07
	 */
	AssetOptionsPanel.prototype.addButton = function (action, title, description, params) {
		if (params) {
			var url = Harmoni.quickUrl('asset', action, params);
		} else {
			var url = Harmoni.quickUrl('asset', action, {'collection_id': this.repositoryId, 'asset_id': this.assetId});
		}
		this.addOnclickButton (
			function () { window.location = url.urlDecodeAmpersands(); },
			title, description, params);
	}
	
	/**
	 * Add a button with confirmation and description to the panel
	 * 
	 * @param string action
	 * @param string title
	 * @param string description
	 * @param object params
	 * @return void
	 * @access public
	 * @since 4/2/07
	 */
	AssetOptionsPanel.prototype.addConfirmButton = function (action, title, description, confirmation, params) {
		if (params) {
			var url = Harmoni.quickUrl('asset', action, params);
		} else {
			var url = Harmoni.quickUrl('asset', action, {'collection_id': this.repositoryId, 'asset_id': this.assetId});
		}
		this.addOnclickButton (
			function () { if (confirm(confirmation)) { window.location = url.urlDecodeAmpersands(); } },
			title, description, params);
	}
	
	/**
	 * Add a button that triggers a popup window and description to the panel
	 * 
	 * @param string url
	 * @param string title
	 * @param string description
	 * @param object params
	 * @return void
	 * @access public
	 * @since 4/2/07
	 */
	AssetOptionsPanel.prototype.addPopupButton = function (url, title, description, params) {
		this.addOnclickButton (
			function () { window.open(url.urlDecodeAmpersands(), '_blank', 
				"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500");},
			title, description, params);
	}
	
	/**
	 * Add a button and description to the panel
	 * 
	 * @param string onclick
	 * @param string title
	 * @param string description
	 * @param object params
	 * @return void
	 * @access public
	 * @since 4/2/07
	 */
	AssetOptionsPanel.prototype.addOnclickButton = function (onclick, title, description, params) {
		var row = this.options.appendChild(document.createElement('tr'));
		var col1 = row.appendChild(document.createElement('td'));
		var col2 = row.appendChild(document.createElement('td'));
		
		col1.style.paddingBottom = '10px';
		col2.style.paddingBottom = '10px';
		
		var button = document.createElement('input');
		button.type = 'button';
		button.value = title;
		
		button.onclick = onclick;
		col1.appendChild(button);
				
		col2.style.fontSize = 'smaller';
		col2.innerHTML = description;
	}
	
	
