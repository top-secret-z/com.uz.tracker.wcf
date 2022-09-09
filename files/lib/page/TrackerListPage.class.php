<?php
namespace wcf\page;
use wcf\data\search\Search;
use wcf\data\user\User;
use wcf\data\user\UserProfileList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the tracked users.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.uztracker.menu';
	
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
	public $objectListClassName = UserProfileList::class;
	
	/**
	 * @inheritDoc
	 */
	public $enableTracking = false;
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = MEMBERS_LIST_USERS_PER_PAGE;
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'username';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = MEMBERS_LIST_DEFAULT_SORT_ORDER;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['username', 'registrationDate', 'activityPoints', 'likesReceived', 'lastActivityTime'];
	
	/**
	 * available letters
	 */
	public static $availableLetters = '#ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	/**
	 * letter
	 */
	public $letter = '';
	
	/**
	 * id of a user search
	 */
	public $searchID = 0;
	
	/**
	 * user search
	 */
	public $search = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// letter
		if (isset($_REQUEST['letter']) && mb_strlen($_REQUEST['letter']) == 1 && mb_strpos(self::$availableLetters, $_REQUEST['letter']) !== false) {
			$this->letter = $_REQUEST['letter'];
		}
		
		if (!empty($_REQUEST['id'])) {
			$this->searchID = intval($_REQUEST['id']);
			$this->search = new Search($this->searchID);
			if (!$this->search->searchID || $this->search->userID != WCF::getUser()->userID || $this->search->searchType != 'users') {
				throw new IllegalLinkException();
			}
		}
		
		if (!empty($_POST)) {
			$parameters = http_build_query($_POST, '', '&');
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('TrackerList', [], $parameters));
			exit;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add("user_table.isTracked = 1");
		
		if ($this->search !== null) {
			$searchData = unserialize($this->search->searchData);
			$this->objectList->getConditionBuilder()->add("user_table.userID IN (?)", [$searchData['matches']]);
			unset($searchData);
		}
		
		if (!empty($this->letter)) {
			if ($this->letter == '#') {
				$this->objectList->getConditionBuilder()->add("SUBSTRING(username,1,1) IN ('0', '1', '2', '3', '4', '5', '6', '7', '8', '9')");
			}
			else {
				$this->objectList->getConditionBuilder()->add("username LIKE ?", [$this->letter.'%']);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		parent::readObjects();
		
		$userIDs = [];
		/** @var User $user */
		foreach ($this->objectList as $user) {
			$userIDs[] = $user->userID;
		}
		
		if (!empty($userIDs)) {
			// preload user storage to avoid reading storage for each user separately at runtime
			UserStorageHandler::getInstance()->loadStorage($userIDs);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'allowSpidersToIndexThisPage' => false,
				'letters' => str_split(self::$availableLetters),
				'letter' => $this->letter,
				'searchID' => $this->searchID,
				'validSortFields' => $this->validSortFields,
		]);
		
		if (count($this->objectList) === 0) {
			@header('HTTP/1.0 404 Not Found');
		}
	}
}
