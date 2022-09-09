<?php
namespace wcf\system\menu\user\profile\content;
use wcf\data\user\tracker\log\TrackerLog;
use wcf\data\user\tracker\log\TrackerLogList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\menu\user\profile\content\IUserProfileMenuContent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles tracker list in user profiles.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
	/**
	 * @inheritDoc
	 */
	public function getContent($userID) {
		$logList = new TrackerLogList();
		$logList->getConditionBuilder()->add("userID = ?", [$userID]);
		$logList->readObjects();
		
		$lastLogTime = $logList->getLastLogTime();
		
		WCF::getTPL()->assign([
				'logList' => $logList,
				'lastLogTime' => $lastLogTime,
				'placeholder' => WCF::getLanguage()->get('wcf.uztracker.noEntries'),
				'userID' => $userID,
				'user' => UserProfileRuntimeCache::getInstance()->getObject($userID),
				'type' => 'all'
		]);
		
		return WCF::getTPL()->fetch('userProfileTracker');
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($userID) {
		// need permission
		if (!WCF::getSession()->getPermission('mod.tracking.canSeeTracking')) return false;
		
		// tracking allowed for user
		$user = UserProfileRuntimeCache::getInstance()->getObject($userID);
		if ($user === null) return false;
		if ($user->getPermission('mod.tracking.noTracking')) return false;
		
		// always show exisiting entries
		if (TrackerLog::getCountByUserID($userID)) return true;
		
		if ($user->isTracked) return true;
		
		return false;
	}
}
