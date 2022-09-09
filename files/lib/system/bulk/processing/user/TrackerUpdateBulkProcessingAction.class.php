<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\DatabaseObjectList;
use wcf\data\user\UserList;
use wcf\data\user\tracker\TrackerEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for updating trackers without touching log entries.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerUpdateBulkProcessingAction extends AbstractUserBulkProcessingAction {
	/**
	 * Tracker data
	 */
	public $configPreset = [];
	public $config = [];
	public $days = 7;
	
	/**
	 * @inheritDoc
	 */
	public function executeAction(DatabaseObjectList $objectList) {
		if (!($objectList instanceof UserList)) return;
		
		$users = $objectList->getObjects();
		$userIDs = $objectList->getObjectIDs();
		
		if (!empty($users)) {
			// update trackers
			$set = $values = [];
			foreach ($this->config as $key => $value) {
				if ($key != 'trackerID' && $key != 'userID' && $key != 'username') {
					$set[] = $key . '= ?';// . $value;
					$values[] = $value;
				}
			}
			
			$setString = implode(',', $set);
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("userID IN (?)", [$userIDs]);
			$sql = "UPDATE	wcf".WCF_N."_user_tracker
					SET		".$setString." 
					".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array_merge($values, $conditions->getParameters()));
			
			// reset tracker cache
			TrackerEditor::resetCache();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		// get column names and values
		$sql = "SHOW COLUMNS FROM wcf".WCF_N."_user_tracker";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$temp = $row['Field'];
			$this->configPreset[$temp] = 1;
		}
		
		return WCF::getTPL()->fetch('trackerUpdateUserBulkProcessing', 'wcf', [
				'configPreset' => $this->configPreset,
				'days' => $this->days
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		// get column names and values
		$sql = "SHOW COLUMNS FROM wcf".WCF_N."_user_tracker";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$temp = $row['Field'];
			$configPreset[$temp] = 1;
		}
		// read tracker config
		foreach ($configPreset as $key => $value) {
			if ($key == 'trackerID') continue;
			
			$this->config[$key] = 0;
			$updateKey = 'update'.$key;
			if (isset($_POST[$updateKey])) $this->config[$key] = $_POST[$updateKey];
			if ($key == 'time') $this->config[$key] = TIME_NOW;
			if ($key == 'isActive') $this->config[$key] = 1;
			if ($key == 'days') $this->config[$key] = intval($_POST['updatedays']);
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
