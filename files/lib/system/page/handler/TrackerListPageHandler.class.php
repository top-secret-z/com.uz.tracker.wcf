<?php
namespace wcf\system\page\handler;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\WCF;

/**
 * Menu page handler for the tracker list page.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler {
	use TOnlineLocationPageHandler;
	
	/**
	 * @inheritDoc
	 */
	public function getLink($objectID) {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function isValid($objectID) {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return WCF::getSession()->getPermission('mod.tracking.canSeeTracking');
	}
	
	/**
	 * @inheritDoc
	 */
	public function lookup($searchString) {
		return [];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOnlineLocation(Page $page, UserOnline $user) {
		if (!USER_TRACKER_ONLINE_HIDE) {
			return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.'.$page->identifier);
		}
		
		if (WCF::getSession()->getPermission('mod.tracking.canSeeTracking')) {
			return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.'.$page->identifier);
		}
		
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function prepareOnlineLocation(Page $page, UserOnline $user) {
		// do nothing
	}
}
