<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\DatabaseObjectList;
use wcf\data\user\UserList;
use wcf\data\user\tracker\log\TrackerLogList;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Bulk processing action implementation for exporting tracker log entries.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerExportBulkProcessingAction extends AbstractUserBulkProcessingAction {
	/**
	 * separator for the exported data and enclosure
	 */
	public $trackerSeparator = ',';
	public $trackerTextSeparator = '"';
	
	/**
	 * @inheritDoc
	 */
	public function executeAction(DatabaseObjectList $objectList) {
		if (!($objectList instanceof UserList)) return;
		
		$count = count($objectList);
		if ($count) {
			// get log entries
			$userIDs = $objectList->getObjectIDs();
			$entryList = new TrackerLogList();
			$entryList->getConditionBuilder()->add('userID IN (?)', [$userIDs]);
			$entryList->readObjects();
			
			$language = WCF::getLanguage();
			
			// send content type
			header('Content-Type: text/csv; charset=UTF-8');
			header('Content-Disposition: attachment; filename=tracker.csv');
			echo $this->trackerTextSeparator.$language->get('wcf.uztracker.username').$this->trackerTextSeparator.$this->trackerSeparator;
			echo $this->trackerTextSeparator.$language->get('wcf.uztracker.date').$this->trackerTextSeparator.$this->trackerSeparator;
			echo $this->trackerTextSeparator.$language->get('wcf.uztracker.time').$this->trackerTextSeparator.$this->trackerSeparator;
			echo $this->trackerTextSeparator.$language->get('wcf.uztracker.ipAddress').$this->trackerTextSeparator.$this->trackerSeparator;
			echo $this->trackerTextSeparator.$language->get('wcf.uztracker.type').$this->trackerTextSeparator.$this->trackerSeparator;
			echo $this->trackerTextSeparator.$language->get('wcf.uztracker.description').$this->trackerTextSeparator.$this->trackerSeparator;
			echo $this->trackerTextSeparator.$language->get('wcf.uztracker.linkName').$this->trackerTextSeparator.$this->trackerSeparator;
			echo $this->trackerTextSeparator.$language->get('wcf.uztracker.content').$this->trackerTextSeparator.$this->trackerSeparator;
			echo "\r\n";
			
			foreach ($entryList->getObjects() as $entry) {
				echo $this->trackerTextSeparator.$entry->username.$this->trackerTextSeparator.$this->trackerSeparator;
				echo $this->trackerTextSeparator.DateUtil::format(DateUtil::getDateTimeByTimestamp($entry->time), DateUtil::DATE_FORMAT).$this->trackerTextSeparator.$this->trackerSeparator;
				echo $this->trackerTextSeparator.DateUtil::format(DateUtil::getDateTimeByTimestamp($entry->time), DateUtil::TIME_FORMAT).$this->trackerTextSeparator.$this->trackerSeparator;
				echo $this->trackerTextSeparator.$entry->ipAddress.$this->trackerTextSeparator.$this->trackerSeparator;
				echo $this->trackerTextSeparator.$language->get($entry->type).$this->trackerTextSeparator.$this->trackerSeparator;
				echo $this->trackerTextSeparator.$language->get($entry->description).$this->trackerTextSeparator.$this->trackerSeparator;
				echo $this->trackerTextSeparator.(empty($entry->link) ? $language->get($entry->name) : $language->get($entry->link)).$this->trackerTextSeparator.$this->trackerSeparator;
				echo $this->trackerTextSeparator.$entry->content.$this->trackerTextSeparator.$this->trackerSeparator;
				echo "\r\n";
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		return WCF::getTPL()->fetch('trackerExportUserBulkProcessing', 'wcf', [
			'trackerSeparator' => $this->trackerSeparator,
			'trackerTextSeparator' => $this->trackerTextSeparator
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		$userList = parent::getObjectList();
		
		return $userList;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['trackerSeparator'])) $this->trackerSeparator = $_POST['trackerSeparator'];
		if (isset($_POST['trackerTextSeparator'])) $this->trackerTextSeparator = $_POST['trackerTextSeparator'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		exit;
	}
}
