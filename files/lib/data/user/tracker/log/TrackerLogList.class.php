<?php
namespace wcf\data\user\tracker\log;
use wcf\data\DatabaseObjectList;
use wcf\data\page\PageCache;

/**
 * Represents a list of Tracker Logs.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerLogList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = TrackerLog::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'time DESC, trackerLogID DESC';
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
	
		// assign page names
		foreach ($this->objects as $log) {
			if ($log->type == 'wcf.uztracker.type.page' && intval($log->description)) {
				$page = PageCache::getInstance()->getPage(intval($log->description));
				if ($page === null) $log->description = 'wcf.uztracker.page.unknown';
				else $log->description = $page->getTitle();
			}
		}
	}
	
	/**
	 * Returns timestamp of oldest log fetched.
	 */
	public function getLastLogTime() {
		$lastLogTime = 0;
		foreach ($this->objects as $log) {
			if (!$lastLogTime) {
				$lastLogTime = $log->time;
			}
			$lastLogTime = min($lastLogTime, $log->time);
		}
		
		return $lastLogTime;
	}
}
