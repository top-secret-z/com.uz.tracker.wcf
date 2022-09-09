<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\DatabaseObjectList;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for removing tracker log entries.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerLogDeleteBulkProcessingAction extends AbstractUserBulkProcessingAction {
	/**
	 * @inheritDoc
	 */
	public function executeAction(DatabaseObjectList $objectList) {
		if (!($objectList instanceof UserList)) return;
		
		$userIDs = $objectList->getObjectIDs();
		
		if (!empty($userIDs)) {
			// remove tracker and log entries
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("userID IN (?)", [$userIDs]);
			
			$sql = "DELETE FROM	wcf".WCF_N."_user_tracker_log
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		$userList = parent::getObjectList();
		
		// only tracked users
		$userList->getConditionBuilder()->add('user_table.isTracked = 1');
		
		return $userList;
	}
}
