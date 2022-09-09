<?php
namespace wcf\action;
use wcf\system\exception\IllegalLinkException;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Provides IP Whois query to Tracker
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerWhoisAction extends AbstractAction {
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
	public $neededPermissions = ['mod.tracking.canSeeTracking', 'admin.user.canViewIpAddress'];
	
	/**
	 * IP
	 */
	public $ipAddress = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// must be a valid IPv4 or 6 address
		if (empty($_GET['ip']) || (filter_var($_GET['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false && filter_var($_GET['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)) {
			throw new IllegalLinkException();
		}
		$this->ipAddress = $_GET['ip'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		// get whoisURL from configuration / set default and ensure placeholder
		$whoisUrl = StringUtil::trim(USER_TRACKER_WHOIS_URL);
		if (empty($whoisUrl)) {
			$whoisUrl = 'https://apps.db.ripe.net/search/query.html?searchtext=%s';
		}
		else {
			if (mb_strpos($whoisUrl, '%s') === false) {
				throw new IllegalLinkException();
			}
		}
		
		$whoisUrl = sprintf($whoisUrl, $this->ipAddress);
		HeaderUtil::redirect($whoisUrl);
		
		$this->executed();
		exit;
	}
}
