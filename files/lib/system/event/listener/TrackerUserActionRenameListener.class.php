<?php
namespace wcf\system\event\listener;

/**
 * Updates the stored username on user rename.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerUserActionRenameListener extends AbstractUserActionRenameListener {
	/**
	 * @inheritDoc
	 */
	protected $databaseTables = ['wcf{WCF_N}_user_tracker_log'];
}
