<?php
namespace wcf\system\event\listener;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to attachment action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerAttachmentListener implements IParameterizedEventListener {
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
		if (!$tracker->contentAttachment) return;
		
		switch($eventObj->getActionName()) {
			case 'upload':
				$params = $eventObj->getParameters();
				$returnValues = $eventObj->getReturnValues();
				$attachments = $returnValues['returnValues']['attachments'];
				
				foreach ($attachments as $attachment) {
					TrackerLogEditor::create([
							'description' => 'wcf.uztracker.description.attachment.add',
							'link' => ($params['objectType'] != 'com.woltlab.wcf.conversation.message') ? $attachment['url'] : '',
							'name' => ($params['objectType'] == 'com.woltlab.wcf.conversation.message') ? 'wcf.uztracker.protected' : '',
							'trackerID' => $tracker->trackerID,
							'type' => 'wcf.uztracker.type.content'
					]);
				}
				break;
			
			case 'delete':
				$objects = $eventObj->getObjects();
				if ($objects[0]->userID == WCF::getUser()->userID) {
					TrackerLogEditor::create([
							'description' => 'wcf.uztracker.description.attachment.delete',
							'link' => WCF::getUser()->getLink(),
							'trackerID' => $tracker->trackerID,
							'type' => 'wcf.uztracker.type.content'
					]);
				}
				break;
		}
	}
}
