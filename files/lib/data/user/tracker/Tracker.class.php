<?php
namespace wcf\data\user\tracker;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a user Tracker.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class Tracker extends DatabaseObject implements IRouteController {
	/**
	 * separator for exported data and enclosure
	 */
	const SEPARATOR = ',';
	const TEXT_SEPARATOR = '"';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_tracker';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'trackerID';
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->username;
	}
	
	/**
	 * Returns the tracker for the given userID.
	 */
	public static function getTrackerByUserID($userID) {
		$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_tracker
				WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$userID]);
		$row = $statement->fetchArray();
		if (!$row) $row = [];
		
		return new Tracker (null, $row);
	}
}
