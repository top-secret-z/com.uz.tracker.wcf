<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\tracker\TrackerEditor;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Tracker cleanup cronjob.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerCleanupCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// set expired trackers to inactive
		$sql = "UPDATE	wcf".WCF_N."_user_tracker
				SET		isActive = 0
				WHERE	isActive = 1 AND time > 0 AND days > 0 AND (time + days * 86400) < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([TIME_NOW]);
		
		// reset tracker cache
		TrackerEditor::resetCache();
		
		// delete old logs
		if (USER_TRACKER_CLEANUP_DAYS) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_tracker_log
					WHERE 		time < ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([TIME_NOW - USER_TRACKER_CLEANUP_DAYS * 86400]);
		}
	}
}
