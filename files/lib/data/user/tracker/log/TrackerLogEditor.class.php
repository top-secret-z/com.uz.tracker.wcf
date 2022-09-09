<?php
namespace wcf\data\user\tracker\log;
use wcf\data\DatabaseObjectEditor;
use wcf\data\package\PackageCache;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Provides functions to edit Tracker Logs.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerLogEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = TrackerLog::class;
	
	/**
	 * @inheritDoc
	 */
	public static function create(array $data = []) {
		$parameters = [
				'time' => TIME_NOW,
				'description' => $data['description'],
				'ipAddress' => UserUtil::convertIPv6To4(WCF::getSession()->ipAddress),
				'link' => (!empty($data['link']) ? $data['link'] : ''),
				'name' => (!empty($data['name']) ? $data['name'] : ''),
				'time' => TIME_NOW,
				'trackerID' => $data['trackerID'],
				'type' => $data['type'],
				'userID' => WCF::getUser()->userID,
				'username' => WCF::getUser()->username,
				'packageID' => (isset($data['packageID']) ? $data['packageID'] : PackageCache::getInstance()->getPackageID('com.woltlab.wcf')),
				'userAgent' => UserUtil::getUserAgent(),
				'content' => (isset($data['content']) ? $data['content'] : '')
		];
		parent::create($parameters);
	}
}
