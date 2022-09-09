<?php
namespace wcf\data\user\tracker\log;
use wcf\data\DatabaseObject;
use \wcf\data\user\User;
use \wcf\data\user\UserProfile;
use wcf\data\user\tracker\Tracker;
use wcf\system\WCF;

/**
 * Represents a user Tracker Log entry.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerLog extends DatabaseObject {
	/**
	 * user profile object
	 */
	protected $userProfile = null;
	
	/**
	 * tracker
	 */
	protected $tracker = null;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_tracker_log';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'trackerLogID';
	
	/**
	 * Returns the number of logs for the given userID.
	 */
	public static function getCountByUserID($userID) {
		$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_user_tracker_log
				WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$userID]);
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	/**
	 * Returns the user profile for this entry.
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = new UserProfile(new User($this->userID));
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Returns the time of the related tracker
	 */
	public function getTracker() {
		if (!$this->trackerID) return null;
		
		if ($this->tracker === null) {
			$this->tracker = new Tracker($this->trackerID);
		}
		
		return $this->tracker;
	}
}
