<?php
namespace wcf\system\event\listener;
use wcf\data\package\PackageCache;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to article action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerArticleListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!MODULE_TRACKER || !MODULE_ARTICLE) return;
		
		// only if user is to be tracked
		$user = WCF::getUser();
		if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) return;
		
		// only if trackers
		$trackers = TrackerCacheBuilder::getInstance()->getData();
		if (!isset($trackers[$user->userID])) return;
		
		$this->tracker = $trackers[$user->userID];
		if (!$this->tracker->contentArticle && !$this->tracker->otherModeration) return;
		
		// actions / data
		$action = $eventObj->getActionName();
		
		if ($this->tracker->contentArticle) {
			if ($action == 'create') {
				$returnValues = $eventObj->getReturnValues();
				$article = $returnValues['returnValues'];
				$this->link = $article->getLink();
				
				if (!$article->publicationStatus) $this->store('wcf.uztracker.description.article.addUnpublished', 'wcf.uztracker.type.article');
				else $this->store('wcf.uztracker.description.article.add', 'wcf.uztracker.type.article');
			}
		}
		
		if ($this->tracker->otherModeration) {
			if ($action == 'delete') {
				$objects = $eventObj->getObjects();
				foreach ($objects as $article) {
					$this->link = '';
					$name = $article->getTitle();
					$content = $article->getFormattedContent();
					$this->store('wcf.uztracker.description.article.delete', 'wcf.uztracker.type.moderation', $name, $content);
				}
			}
		}
		
		if ($action == 'trash' || $action == 'restore') {
			$objects = $eventObj->getObjects();
			foreach ($objects as $article) {
				$this->link = $article->getLink();
				if ($action == 'trash') {
					if ($article->userID == $user->userID) {
						if ($this->tracker->contentArticle) $this->store('wcf.uztracker.description.article.trash', 'wcf.uztracker.type.article');
					}
					else {
						if ($this->tracker->otherModeration) $this->store('wcf.uztracker.description.article.trash', 'wcf.uztracker.type.moderation');
					}
				}
				else {
					if ($article->userID == $user->userID) {
						if ($this->tracker->contentArticle) $this->store('wcf.uztracker.description.article.restore', 'wcf.uztracker.type.article');
					}
					else {
						if ($this->tracker->otherModeration) $this->store('wcf.uztracker.description.article.restore', 'wcf.uztracker.type.moderation');
					}
				}
			}
		}
		
		if ($action == 'update') {
			$objects = $eventObj->getObjects();
			foreach ($objects as $article) {
				$this->link = $article->getLink();
				if ($article->userID == $user->userID) {
					if ($this->tracker->contentArticle) $this->store('wcf.uztracker.description.article.update', 'wcf.uztracker.type.article');
				}
				else {
					if ($this->tracker->otherModeration) $this->store('wcf.uztracker.description.article.update', 'wcf.uztracker.type.moderation');
				}
			}
		}
	}
	
	/**
	 * store log entry
	 */
	protected function store ($description, $type, $name = '', $content = '') {
		$packageID = PackageCache::getInstance()->getPackageID('com.uz.tracker.calendar');
		TrackerLogEditor::create([
				'description' => $description,
				'link' => $this->link,
				'name' => $name,
				'trackerID' => $this->tracker->trackerID,
				'type' => $type,
				'packageID' => $packageID,
				'content' => $content
		]);
	}
}