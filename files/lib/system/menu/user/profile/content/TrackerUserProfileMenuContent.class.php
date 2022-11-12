<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace wcf\system\menu\user\profile\content;

use wcf\data\user\tracker\log\TrackerLog;
use wcf\data\user\tracker\log\TrackerLogList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles tracker list in user profiles.
 */
class TrackerUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent
{
    /**
     * @inheritDoc
     */
    public function getContent($userID)
    {
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
            'type' => 'all',
        ]);

        return WCF::getTPL()->fetch('userProfileTracker');
    }

    /**
     * @inheritDoc
     */
    public function isVisible($userID)
    {
        // need permission
        if (!WCF::getSession()->getPermission('mod.tracking.canSeeTracking')) {
            return false;
        }

        // tracking allowed for user
        $user = UserProfileRuntimeCache::getInstance()->getObject($userID);
        if ($user === null) {
            return false;
        }
        if ($user->getPermission('mod.tracking.noTracking')) {
            return false;
        }

        // always show exisiting entries
        if (TrackerLog::getCountByUserID($userID)) {
            return true;
        }

        if ($user->isTracked) {
            return true;
        }

        return false;
    }
}
