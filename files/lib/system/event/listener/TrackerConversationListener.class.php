<?php
namespace wcf\system\event\listener;
use wcf\data\conversation\ConversationAction;
use wcf\data\conversation\message\ConversationMessageAction;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to conversation action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerConversationListener implements IParameterizedEventListener {
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
		if (!$tracker->contentConversation) return;
		
		// get conversation actions
		if ($eventObj instanceof ConversationMessageAction) {
		//	$action = $eventObj->getActionName();
			$description = '';
			switch ($eventObj->getActionName()) {
				case 'create':
					// WCF does not allow to recognize publishing of drafts
					$params = $eventObj->getParameters();
					$conversation = $params['conversation'];
					$description = $conversation->isDraft ? 'wcf.uztracker.description.conversation.addDraft' : 'wcf.uztracker.description.conversation.add';
					break;
				
				case 'update':
					$description = 'wcf.uztracker.description.conversation.edit';
					break;
				
				case 'quickReply':
					$description = 'wcf.uztracker.description.conversation.answer';
					break;
			}
			
			TrackerLogEditor::create([
					'description' => $description,
					'link' => $user->getLink(),
					'trackerID' => $tracker->trackerID,
					'type' => 'wcf.uztracker.type.content'
			]);
			
		}
		elseif ($eventObj instanceof ConversationAction) {
			$description = '';
			switch ($eventObj->getActionName()) {
				case 'hideConversation':
					$description = 'wcf.uztracker.description.conversation.hide';
					break;
					
				case 'delete':
					$description = 'wcf.uztracker.description.conversation.delete';
					break;
					
				case 'add Participants':
					$description = 'wcf.uztracker.description.conversation.addParticipants';
					break;
			}
			
			TrackerLogEditor::create([
					'description' => $description,
					'link' => $user->getLink(),
					'trackerID' => $tracker->trackerID,
					'type' => 'wcf.uztracker.type.content'
			]);
		}
	}
}
