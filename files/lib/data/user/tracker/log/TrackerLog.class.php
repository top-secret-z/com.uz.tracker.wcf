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
namespace wcf\data\user\tracker\log;

use wcf\data\DatabaseObject;
use wcf\data\user\tracker\Tracker;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\WCF;

/**
 * Represents a user Tracker Log entry.
 */
class TrackerLog extends DatabaseObject
{
    /**
     * user profile object
     */
    protected $userProfile;

    /**
     * tracker
     */
    protected $tracker;

    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'user_tracker_log';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'trackerLogID';

    /**
     * Returns the number of logs for the given userID.
     */
    public static function getCountByUserID($userID)
    {
        $sql = "SELECT    COUNT(*) AS count
                FROM    wcf" . WCF_N . "_user_tracker_log
                WHERE    userID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$userID]);
        $row = $statement->fetchArray();

        return $row['count'];
    }

    /**
     * Returns the user profile for this entry.
     */
    public function getUserProfile()
    {
        if ($this->userProfile === null) {
            $this->userProfile = new UserProfile(new User($this->userID));
        }

        return $this->userProfile;
    }

    /**
     * Returns the time of the related tracker
     */
    public function getTracker()
    {
        if (!$this->trackerID) {
            return null;
        }

        if ($this->tracker === null) {
            $this->tracker = new Tracker($this->trackerID);
        }

        return $this->tracker;
    }
}
