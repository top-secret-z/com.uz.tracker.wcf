<?php
namespace wcf\system\event\listener;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Listen to user login action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerLoginListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER) return;
		
		if (!WCF::getUser()->userID) return;
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		
		$tracker = $trackers[$user->userID];
		if (!$tracker->usersLogin) return;
		
		$link = Regex::compile('(?<=\?|&)([st]=[a-f0-9]{40}|at=\d+-[a-f0-9]{40})')->replace($user->getLink(), '');
		
		TrackerLogEditor::create([
				'description' => 'wcf.uztracker.description.login',
				'link' => $link,
				'trackerID' => $tracker->trackerID,
				'type' => 'wcf.uztracker.type.users'
		]);
	}
}
