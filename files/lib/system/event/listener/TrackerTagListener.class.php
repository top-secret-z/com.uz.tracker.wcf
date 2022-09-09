<?php
namespace wcf\system\event\listener;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to tag action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerTagListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER || !MODULE_TAGGING) return;
		
		// only action create
		$action = $eventObj->getActionName();
		if ($action != 'create') return;
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		
		$tracker = $trackers[$user->userID];
		if (!$tracker->contentTag) return;
		
		$returnValues = $eventObj->getReturnValues();
		$tag = $returnValues['returnValues'];
		
		TrackerLogEditor::create([
				'description' => 'wcf.uztracker.description.tag',
				'name' => $tag->name,
				'trackerID' => $tracker->trackerID,
				'type' => 'wcf.uztracker.type.content'
		]);
	}
}
