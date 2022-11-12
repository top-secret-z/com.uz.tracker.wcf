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
namespace wcf\system\bulk\processing\user;

use PDO;
use wcf\data\DatabaseObjectList;
use wcf\data\user\tracker\TrackerEditor;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for adding trackers.
 */
class TrackerTrackBulkProcessingAction extends AbstractUserBulkProcessingAction
{
    /**
     * Tracker data
     */
    public $configPreset = [];

    public $config = [];

    public $days = 7;

    /**
     * @inheritDoc
     */
    public function executeAction(DatabaseObjectList $objectList)
    {
        if (!($objectList instanceof UserList)) {
            return;
        }

        $users = $objectList->getObjects();

        // create trackers
        if (!empty($users)) {
            $set = $values = $userIDs = [];
            foreach ($this->config as $key => $value) {
                if ($key != 'trackerID') {
                    $set[] = $key;
                    $values[$key] = $value;
                }
            }

            foreach ($users as $user) {
                $userIDs[] = $user->userID;
                $setString = '(' . \implode(',', $set) . ')';
                $values['userID'] = $user->userID;
                $values['username'] = $user->username;
                $valuesString = '(' . \str_repeat('?,', \count($values) - 1) . '?)';
                $sql = "INSERT INTO    wcf" . WCF_N . "_user_tracker
                        " . $setString . " VALUES " . $valuesString;
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute(\array_values($values));
            }

            // update user
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("userID IN (?)", [$userIDs]);
            $sql = "UPDATE    wcf" . WCF_N . "_user
                    SET        isTracked = 1
                " . $conditions;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditions->getParameters());

            // reset tracker cache
            TrackerEditor::resetCache();
        }
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        // get column names and values
        $sql = "SHOW COLUMNS FROM wcf" . WCF_N . "_user_tracker";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $temp = $row['Field'];
            $this->configPreset[$temp] = 1;
        }

        return WCF::getTPL()->fetch('trackerUserBulkProcessing', 'wcf', [
            'configPreset' => $this->configPreset,
            'days' => $this->days,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        // get column names and values
        $sql = "SHOW COLUMNS FROM wcf" . WCF_N . "_user_tracker";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $temp = $row['Field'];
            $configPreset[$temp] = 1;
        }

        // read tracker config
        foreach ($configPreset as $key => $value) {
            if ($key == 'trackerID') {
                continue;
            }

            $this->config[$key] = 0;
            if (isset($_POST[$key])) {
                $this->config[$key] = \intval($_POST[$key]);
            }
            if ($key == 'time') {
                $this->config[$key] = TIME_NOW;
            }
            if ($key == 'isActive') {
                $this->config[$key] = 1;
            }
            if ($key == 'days') {
                $this->config[$key] = \intval($_POST['days']);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getObjectList()
    {
        $userList = parent::getObjectList();

        // only trackable users
        $sql = "SELECT    optionID
                FROM    wcf" . WCF_N . "_user_group_option
                WHERE    optionName = ?";
        $statement = WCF::getDB()->prepareStatement($sql, 1);
        $statement->execute(['mod.tracking.noTracking']);
        $row = $statement->fetchArray();
        $optionID = $row['optionID'];

        $sql = "SELECT        groupID
                FROM        wcf" . WCF_N . "_user_group_option_value
                WHERE        optionID = ? AND optionValue = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$optionID, 1]);
        $groupIDs = $statement->fetchAll(PDO::FETCH_COLUMN);

        if (\count($groupIDs)) {
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("groupID IN (?)", [$groupIDs]);
            $sql = "SELECT        userID
                    FROM        wcf" . WCF_N . "_user_to_group
                    " . $conditions;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditions->getParameters());
            $userIDs = $statement->fetchAll(PDO::FETCH_COLUMN);

            if (\count($userIDs)) {
                $userList->getConditionBuilder()->add('user_table.userID NOT IN (?)', [$userIDs]);
            }
        }

        // only untracked users
        $userList->getConditionBuilder()->add('user_table.isTracked = 0');

        return $userList;
    }
}
