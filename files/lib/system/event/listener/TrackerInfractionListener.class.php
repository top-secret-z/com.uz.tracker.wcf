<?php
namespace wcf\system\event\listener;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to infraction action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerInfractionListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER) return;
		
		// only listen to create and warning
		$action = $eventObj->getActionName();
		if ($action != 'create') return;
		if ($className != 'wcf\data\user\infraction\warning\UserInfractionWarningAction') return;
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		
		$tracker = $trackers[$user->userID];
		if (!$tracker->usersInfraction) return;
		
		// get warning
		// Null if warned in profile :-(
		$returnValues = $eventObj->getReturnValues();
		$warning = $returnValues['returnValues'];
		$object = $warning->getObject();
		if ($object !== null) {
			$link = $object->getLink();
		}
		else {
			$user = $warning->getUser();
			$link = $user->getLink();
		}
		
		TrackerLogEditor::create([
				'description' => 'wcf.uztracker.description.warning',
				'link' => $link,
				'trackerID' => $tracker->trackerID,
				'type' => 'wcf.uztracker.type.users'
		]);
	}
}
