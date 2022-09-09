<?php
namespace wcf\action;
use wcf\data\user\tracker\Tracker;
use wcf\data\user\tracker\log\TrackerLogList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Exports tracker log entries
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerLogExportAction extends AbstractAction {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_TRACKER'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['mod.tracking.canSeeTracking'];
	
	/**
	 * @inheritDoc
	 */
	public $separator = Tracker::SEPARATOR;
	public $textSeparator = Tracker::TEXT_SEPARATOR;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!isset($_GET['username']) || !isset($_GET['type'])) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		$entryList = new TrackerLogList();
		if ($_GET['type'] == 'all') {
			$entryList->getConditionBuilder()->add('type LIKE ?', ['wcf.uztracker.type%']);
		}
		else {
			$entryList->getConditionBuilder()->add('type = ?', ['wcf.uztracker.type.' . $_GET['type']]);
		}
		if (!empty($_GET['username'])) {
			$entryList->getConditionBuilder()->add('username = ?', [$_GET['username']]);
		}
		$entryList->readObjects();
		
		$language = WCF::getLanguage();
		
		// send content type
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename=tracker.csv');
		echo $this->textSeparator.$language->get('wcf.uztracker.username').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('wcf.uztracker.date').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('wcf.uztracker.time').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('wcf.uztracker.ipAddress').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('wcf.uztracker.type').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('wcf.uztracker.description').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('wcf.uztracker.linkName').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('wcf.uztracker.content').$this->textSeparator.$this->separator;
		echo "\r\n";
		
		foreach ($entryList->getObjects() as $entry) {
			echo $this->textSeparator.$entry->username.$this->textSeparator.$this->separator;
			echo $this->textSeparator.DateUtil::format(DateUtil::getDateTimeByTimestamp($entry->time), DateUtil::DATE_FORMAT).$this->textSeparator.$this->separator;
			echo $this->textSeparator.DateUtil::format(DateUtil::getDateTimeByTimestamp($entry->time), DateUtil::TIME_FORMAT).$this->textSeparator.$this->separator;
			echo $this->textSeparator.$entry->ipAddress.$this->textSeparator.$this->separator;
			echo $this->textSeparator.$language->get($entry->type).$this->textSeparator.$this->separator;
			echo $this->textSeparator.$language->get($entry->description).$this->textSeparator.$this->separator;
			echo $this->textSeparator.(empty($entry->link) ? $language->get($entry->name) : $language->get($entry->link)).$this->textSeparator.$this->separator;
			echo $this->textSeparator.$entry->content.$this->textSeparator.$this->separator;
			echo "\r\n";
		}
		
		$this->executed();
		
		exit;
	}
}
