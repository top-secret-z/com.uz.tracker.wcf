<?php
namespace wcf\system\cache\builder;
use wcf\data\user\tracker\TrackerList;

/**
 * Caches the active Trackers.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 3600;
	
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$trackers = new TrackerList();
		$trackers->getConditionBuilder()->add('isActive = 1');
		$trackers->readObjects();
		$temp = [];
		foreach($trackers as $tracker) {
			$temp[$tracker->userID] = $tracker;
		}
		
		return $temp;
	}
}
