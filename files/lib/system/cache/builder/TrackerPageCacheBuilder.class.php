<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches the known Tracker pages.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
class TrackerPageCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 3600;
	
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$pages = [];
		$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_tracker_page";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$pages[$row['class']] = [
					'page' => $row['page'],
					'isPublic' => $row['isPublic']
			];
		}
		
		return $pages;
	}
}
