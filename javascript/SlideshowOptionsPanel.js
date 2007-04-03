/**
 * @since 3/28/07
 * @package 
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

SlideshowOptionsPanel.prototype = new AssetOptionsPanel();
SlideshowOptionsPanel.prototype.constructor = SlideshowOptionsPanel;
SlideshowOptionsPanel.superclass = AssetOptionsPanel.prototype;

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
function SlideshowOptionsPanel ( exhibitionId, assetId, positionElement, toShow, viewerUrl ) {
	if ( arguments.length > 0 ) {
		this.init( exhibitionId, assetId, positionElement, toShow, viewerUrl );
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
	SlideshowOptionsPanel.prototype.init = function (exhibitionId, assetId, positionElement, toShow, viewerUrl ) {
		AuthZViewer.superclass.init.call(this, 
								"Slideshow Options",
								200,
								300,
								positionElement);
		
		this.repositoryId = 'edu.middlebury.concerto.exhibition_repository';
		this.exhibitionId = exhibitionId;
		this.assetId = assetId;
		this.toShow = toShow;
		this.viewerUrl = viewerUrl;
		this.defaultParams = {'asset_id': this.assetId};
		this.options = this.contentElement.appendChild(document.createElement('table'));
				
		if (toShow.elementExists('view')) {
			this.addPopupButton(this.viewerUrl, 'View', 'View this Slideshow in the pop-up viewer.');
			this.addButton ('exhibitions', 'browseSlideshow', 'Browse', 'Browse the slides in this Slideshow.');
		}
		
		if (toShow.elementExists('edit')) {
			this.addButton ('exhibitions', 'modify_slideshow', 'Edit', 'Modify this Slideshow.', {'slideshow_id': this.assetId}, 'modify_slideshow');
		}
		
		if (toShow.elementExists('delete')) {
			this.addConfirmButton ('exhibitions', 'delete_slideshow', 'Delete', 'Delete this Slideshow.', 'Are you sure that you want to delete this Slideshow?', {'exhibition_id': this.exhibitionId, 'slideshow_id': this.assetId});
		}
		
		if (toShow.elementExists('duplicate')) {
			this.addButton ('exhibitions', 'duplicate_slideshow', 'Duplicate', 'Make a copy of this Slideshow.', {'exhibition_id': this.exhibitionId, 'slideshow_id': this.assetId});
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
	SlideshowOptionsPanel.run = function ( exhibitionId, assetId, positionElement, toShow, viewerUrl ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			positionElement.panel = new SlideshowOptionsPanel( exhibitionId, assetId, positionElement, toShow, viewerUrl );
		}
	}