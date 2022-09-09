<?php
namespace wcf\data\user\tracker;
use wcf\data\DatabaseObjectEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;

/**
 * Provides functions to edit Trackers.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = Tracker::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		TrackerCacheBuilder::getInstance()->reset();
	}
}
