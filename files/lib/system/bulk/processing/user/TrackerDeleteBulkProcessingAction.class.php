<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\DatabaseObjectList;
use wcf\data\user\UserList;
use wcf\data\user\tracker\TrackerEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for deleting trackers without touching log entries.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerDeleteBulkProcessingAction extends AbstractUserBulkProcessingAction {
	/**
	 * @inheritDoc
	 */
	public function executeAction(DatabaseObjectList $objectList) {
		if (!($objectList instanceof UserList)) return;
		
		$userIDs = $objectList->getObjectIDs();
		
		if (!empty($userIDs)) {
			// remove tracker
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("userID IN (?)", [$userIDs]);
			
			$sql = "DELETE FROM	wcf".WCF_N."_user_tracker
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			
			// update user
			$sql = "UPDATE	wcf".WCF_N."_user
					SET		isTracked = 0
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			
			// reset tracker cache
			TrackerEditor::resetCache();
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
