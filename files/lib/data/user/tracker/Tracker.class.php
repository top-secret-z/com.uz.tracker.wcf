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
namespace wcf\data\user\tracker;

use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a user Tracker.
 */
class Tracker extends DatabaseObject implements IRouteController
{
    /**
     * separator for exported data and enclosure
     */
    const SEPARATOR = ',';

    const TEXT_SEPARATOR = '"';

    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'user_tracker';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'trackerID';

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->username;
    }

    /**
     * Returns the tracker for the given userID.
     */
    public static function getTrackerByUserID($userID)
    {
        $sql = "SELECT    *
                FROM    wcf" . WCF_N . "_user_tracker
                WHERE    userID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$userID]);
        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }
}
