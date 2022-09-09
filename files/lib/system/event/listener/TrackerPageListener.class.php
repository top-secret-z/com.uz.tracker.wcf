<?php
namespace wcf\system\event\listener;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\PageCache;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\cache\builder\TrackerPageCacheBuilder;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Listen to Page views.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerPageListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER) return;
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		$tracker = $trackers[$user->userID];
		if (!$tracker->otherPage) return;
		
		// get page from cache
		$description = '';
		$page = PageCache::getInstance()->getPageByController($className);
		if ($page !== null && !empty($page->getTitle())) $description = $page->pageID;
		
		$public = 1;
		$name = $link = '';
		if (empty($description)) {
			$pages = TrackerPageCacheBuilder::getInstance()->getData();
			
			if (isset($pages[$className])) {
				$description = 'wcf.uztracker.page.' . $pages[$className]['page'];
				$public = $pages[$className]['isPublic'];
			}
			else {
				$description = 'wcf.uztracker.page.unknown';
				$public = 1;
			}
		}
		
		// exclude media
		if ($className == 'wcf\page\MediaPage' && USER_TRACKER_EXCLUDE_MEDIA) return;
		
		// attachments may be private
		if ($className == 'wcf\page\AttachmentPage') {
			$attachment = $eventObj->attachment;
			$objectType = ObjectTypeCache::getInstance()->getObjectType($attachment->objectTypeID);
			if ($objectType->private) {
				$public = 2;
			}
		}
		
		// strip session links, security tokens and access tokens
		switch ($public) {
			case 0:
				$link = $user->getLink();
				break;
			case 1:
				$link = WCF::getRequestURI();
				$link = Regex::compile('(?<=\?|&)([st]=[a-f0-9]{40}|at=\d+-[a-f0-9]{40})')->replace($link, '');
				break;
			case 2:
				$name = 'wcf.uztracker.protected';
				break;
		}
		
		TrackerLogEditor::create([
				'description' => $description,
				'link' => (empty($name) ? $link : ''),
				'name' => (empty($name) ? '' : $name),
				'trackerID' => $tracker->trackerID,
				'type' => 'wcf.uztracker.type.page'
		]);
	}
}
