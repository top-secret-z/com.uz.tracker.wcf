<?php
namespace wcf\system\event\listener;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\poll\Poll;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\poll\PollManager;
use wcf\system\WCF;

/**
 * Listen to poll action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerPollListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER || !MODULE_POLL) return;
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		
		$tracker = $trackers[$user->userID];
		if (!$tracker->contentPoll) return;
		
		// action
		$action = $eventObj->getActionName();
		
		if ($action == 'create') {
			// no idea on how to get the poll related object for a link
			$returnValues = $eventObj->getReturnValues();
			$poll = $returnValues['returnValues'];
			$type = ObjectTypeCache::getInstance()->getObjectType($poll->objectTypeID);
			TrackerLogEditor::create([
					'description' => 'wcf.uztracker.description.poll.add',
					'link' => '',
					'name' => ($type !== null) ? $type->objectType : 'wcf.uztracker.unknown',
					'trackerID' => $tracker->trackerID,
					'type' => 'wcf.uztracker.type.content'
			]);
		}
		
		if ($action == 'vote') {
			$objectIDs = $eventObj->getObjectIDs();
			foreach ($objectIDs as $objectID) {
				$poll = new Poll($objectID);
				$object = PollManager::getInstance()->getRelatedObject($poll);
				if ($object !== null) {
					$link = $object->getLink();
				}
				else {
					$link = 'wcf.uztracker.unknown';
				}
				TrackerLogEditor::create([
						'description' => 'wcf.uztracker.description.poll.vote',
						'link' => $link,
						'trackerID' => $tracker->trackerID,
						'type' => 'wcf.uztracker.type.content'
				]);
			}
		}
	}
}
