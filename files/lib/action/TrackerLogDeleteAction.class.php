<?php
namespace wcf\action;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Deletes tracker log entries
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerLogDeleteAction extends AbstractAction {
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
	public $neededPermissions = ['mod.tracking.canModifyTracking'];
	
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
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		if ($_GET['type'] == 'all') {
			$conditionBuilder->add('type LIKE ?', ['wcf.uztracker.type%']);
		}
		else {
			$conditionBuilder->add('type = ?', ['wcf.uztracker.type.' . $_GET['type']]);
		}
		if (!empty($_GET['username'])) {
			$conditionBuilder->add('username = ?', [$_GET['username']]);
		}
		
		$sql = "DELETE FROM wcf".WCF_N."_user_tracker_log " . $conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		$this->executed();
		
		// forward to tracker list page
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('TrackerLogList'));
		exit;
	}
}
