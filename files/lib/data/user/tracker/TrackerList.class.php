<?php
namespace wcf\data\user\tracker;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of Trackers.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Tracker::class;
}
