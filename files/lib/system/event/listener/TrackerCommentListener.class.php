<?php
namespace wcf\system\event\listener;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentAction;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to comment action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerCommentListener implements IParameterizedEventListener {
	/**
	 * tracker and link
	 */
	protected $tracker = null;
	protected $link = '';
	
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER) return;
		
		$action = $eventObj->getActionName();
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		
		$this->tracker = $trackers[$user->userID];
		if (!$this->tracker->contentComment && !$this->tracker->otherModeration) return;
		
		// comment
		if ($eventObj instanceof CommentAction) {
			$action = $eventObj->getActionName();
			
			if ($this->tracker->contentComment && $action == 'addComment') {
				$comment = $eventObj->createdComment;
				$this->link = $comment->getLink();
				$this->store('wcf.uztracker.description.comment.add', 'wcf.uztracker.type.content');
			}
			
			if ($action == 'delete') {
				$objects = $eventObj->getObjects();
				foreach($objects as $comment) {
					$this->link = $comment->getLink();
					$content = $comment->message;
					if ($this->tracker->contentComment && $user->userID == $comment->userID) { 
						$this->store('wcf.uztracker.description.comment.delete', 'wcf.uztracker.type.content', $content);
					}
					if ($this->tracker->otherModeration && $user->userID != $comment->userID) {
						$this->store('wcf.uztracker.description.comment.delete', 'wcf.uztracker.type.moderation', $content);
					}
				}
			}
			
			if ($action == 'edit') {
				$params = $eventObj->getParameters();
				
				if (isset($params['data']['commentID'])) { // comment is edited
					$comment = new Comment($params['data']['commentID']);
					
					$this->link = $comment->getLink();
					if ($this->tracker->contentComment && $user->userID == $comment->userID) {
						$this->store('wcf.uztracker.description.comment.edit', 'wcf.uztracker.type.content');
					}
					if ($this->tracker->otherModeration && $user->userID != $comment->userID) {
						$this->store('wcf.uztracker.description.comment.edit', 'wcf.uztracker.type.moderation');
					}
				}
				else { // response is edited
					$response = $eventObj->getResponse();
					$comment = new Comment($response->commentID);
					$this->link = $comment->getLink();
					
					if ($this->tracker->contentComment && $user->userID == $response->userID) {
						$this->store('wcf.uztracker.description.comment.response.edit', 'wcf.uztracker.type.content');
					}
					if ($this->tracker->otherModeration && $user->userID != $response->userID) {
						$this->store('wcf.uztracker.description.comment.response.edit', 'wcf.uztracker.type.moderation');
					}
				}
			}
			
			if ($this->tracker->contentComment && $action == 'addResponse') {
				$params = $eventObj->getParameters();
				$comment = new Comment($params['data']['commentID']);
				$this->link = $comment->getLink();
				$this->store('wcf.uztracker.description.comment.response.add', 'wcf.uztracker.type.content');
			}
			
			if ($action == 'remove') {
				$response = $eventObj->getResponse();
				if ($response !== null) {
					$comment = new Comment($response->commentID);
					$this->link = $comment->getLink();
					$content = $response->message;
					if ($this->tracker->contentComment && $user->userID == $comment->userID) {
						$this->store('wcf.uztracker.description.comment.response.delete', 'wcf.uztracker.type.content', $content);
					}
					if ($this->tracker->otherModeration && $user->userID != $comment->userID) {
						$this->store('wcf.uztracker.description.comment.response.delete', 'wcf.uztracker.type.moderation', $content);
					}
				}
			}
		}
	}
	
	/**
	 * store log entry
	 */
	protected function store ($description, $type, $content = '') {
		TrackerLogEditor::create([
				'description' => $description,
				'link' => $this->link,
				'trackerID' => $this->tracker->trackerID,
				'type' => $type,
				'content' => $content
		]);
	}
}
