<?php
namespace wcf\system\event\listener;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Listen to report action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerReportListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER) return;
		
		// only listen to report
		$action = $eventObj->getActionName();
		if ($action != 'report') return;
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		
		$tracker = $trackers[$user->userID];
		if (!$tracker->usersReport) return;
		
		// get object
		$params = $eventObj->getParameters();
		$link = 'wcf.uztracker.unknown';
		try {
			$definition = ObjectTypeCache::getInstance()->getDefinitionByName('com.woltlab.wcf.moderation.report');
			if ($definition->definitionID) {
				$sql = "SELECT	className
						FROM	wcf".WCF_N."_object_type
						WHERE	definitionID = ?
								AND objectType = ?";
				$statement = WCF::getDB()->prepareStatement($sql, 1);
				$statement->execute([$definition->definitionID, $params['objectType']]);
				$row = $statement->fetchArray();
		
				$handler = new $row['className'];
				$object = $handler->getReportedObject($params['objectID']);
				$link = $object->getLink();
			}
		}
		catch (SystemException $e) {
			// nothing to do
		}
		
		TrackerLogEditor::create([
				'description' => 'wcf.uztracker.description.report',
				'link' => $link,
				'trackerID' => $tracker->trackerID,
				'type' => 'wcf.uztracker.type.users'
		]);
	}
}
