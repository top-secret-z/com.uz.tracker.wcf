<?php
namespace wcf\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Exports user data iwa Gdpr.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class UzTrackerGdprExportListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		// only IP addresses
		$eventObj->data['com.uz.tracker.wcf'] = [
				'ipAddresses' => $eventObj->exportIpAddresses('wcf'.WCF_N.'_user_tracker_log', 'ipAddress', 'time', 'userID')
		];
	}
}
