<?php
namespace wcf\system\event\listener;
use wcf\data\user\User;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to follow action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerFollowListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER) return;
		
		// only actions follow and unfollow
		$action = $eventObj->getActionName();
		if ($action != 'follow' && $action != 'unfollow') return;
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		
		$tracker = $trackers[$user->userID];
		if (!$tracker->usersFollow) return;
		
		// get data
		$params = $eventObj->getParameters();
		$user = new User($params['data']['userID']);
		
		TrackerLogEditor::create([
				'description' => ($action == 'follow') ? 'wcf.uztracker.description.follow' : 'wcf.uztracker.description.unfollow',
				'link' => $user->getLink(),
				'trackerID' => $tracker->trackerID,
				'type' => 'wcf.uztracker.type.users'
		]);
	}
}
