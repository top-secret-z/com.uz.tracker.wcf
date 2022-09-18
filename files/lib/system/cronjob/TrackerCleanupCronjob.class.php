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
namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\user\tracker\TrackerEditor;
use wcf\system\WCF;

/**
 * Tracker cleanup cronjob.
 */
class TrackerCleanupCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        // set expired trackers to inactive
        $sql = "UPDATE    wcf" . WCF_N . "_user_tracker
                SET        isActive = 0
                WHERE    isActive = 1 AND time > 0 AND days > 0 AND (time + days * 86400) < ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([TIME_NOW]);

        // reset tracker cache
        TrackerEditor::resetCache();

        // delete old logs
        if (USER_TRACKER_CLEANUP_DAYS) {
            $sql = "DELETE FROM    wcf" . WCF_N . "_user_tracker_log
                    WHERE         time < ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([TIME_NOW - USER_TRACKER_CLEANUP_DAYS * 86400]);
        }
    }
}
