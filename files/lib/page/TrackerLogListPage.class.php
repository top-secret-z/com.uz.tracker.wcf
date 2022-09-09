<?php
namespace wcf\page;
use wcf\data\user\tracker\log\TrackerLogList;
use wcf\system\page\PageLocationManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the Tracker log entries.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerLogListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.uztracker.menu.log';
	
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
	public $objectListClassName = TrackerLogList::class;
	
	/**
	 * @inheritDoc
	 */
	public $enableTracking = false;
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = USER_TRACKER_LOGLIST_PER_PAGE;
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['time', 'ipAddress', 'username', 'type', 'description', 'link'];
	
	/**
	 * username
	 */
	public $username = '';
	
	/**
	 * type
	 */
	public $type = 'all';
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// add breadcrumbs
		if (MODULE_TRACKER) PageLocationManager::getInstance()->addParentLocation('com.uz.tracker.wcf.TrackerList');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		if (!empty($_REQUEST['type'])) $this->type = $_REQUEST['type'];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		// filter
		if (!empty($this->username)) {
			$this->objectList->getConditionBuilder()->add('username LIKE ?', ['%' . $this->username . '%']);
		}
		if ($this->type != 'all') {
			$this->objectList->getConditionBuilder()->add('type = ?', ['wcf.uztracker.type.' . $this->type]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'username' => $this->username,
				'type' => $this->type
		]);
	}
}
