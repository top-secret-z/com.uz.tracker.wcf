<?php
namespace wcf\system\event\listener;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * Listen to reaction action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerReactionListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER || !MODULE_LIKE) return;
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		
		$tracker = $trackers[$user->userID];
		if (!$tracker->contentLike) return;
		
		$action = $eventObj->getActionName();
		if ($action != 'create' && $action != 'update' && $action != 'delete') return;
		
		// get data
		switch ($action) {
			case 'create':
				$returnValues = $eventObj->getReturnValues();
				$like = $returnValues['returnValues'];
				break;
				
			case 'update':
				$objects = $eventObj->getObjects();
				$like = $objects[0]->getDecoratedObject();
				break;
				
			case 'delete':
				$objects = $eventObj->getObjects();
				$like = $objects[0]->getDecoratedObject();
				break;
		}
		
		try {
			$objectType = ObjectTypeCache::getInstance()->getObjectType($like->objectTypeID);
			if (!$objectType) return;
			
			$likeObject = ReactionHandler::getInstance()->getLikeableObject($objectType->objectType, $like->objectID);
			$link = $likeObject->getURL();
		}
		catch (\Exception $e) {
			$link = '';
		}
		
		$description = '';
		switch ($action) {
			case 'create':
				$description = 'wcf.uztracker.description.like.given';
				break;
				
			case 'update':
				$description = 'wcf.uztracker.description.like.changed';
				break;
				
			case 'delete':
				$description = 'wcf.uztracker.description.like.withdrawn';
				break;
		}
		
		if (!empty($description)) {
			TrackerLogEditor::create([
					'description' => $description,
					'link' => $link,
					'trackerID' => $tracker->trackerID,
					'type' => 'wcf.uztracker.type.content'
			]);
		}
	}
}
