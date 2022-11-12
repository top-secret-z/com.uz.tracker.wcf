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

use wcf\data\DatabaseObjectList;
use wcf\data\user\tracker\TrackerEditor;
use wcf\data\user\UserList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for updating trackers without touching log entries.
 */
class TrackerUpdateBulkProcessingAction extends AbstractUserBulkProcessingAction
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
        $userIDs = $objectList->getObjectIDs();

        if (!empty($users)) {
            // update trackers
            $set = $values = [];
            foreach ($this->config as $key => $value) {
                if ($key != 'trackerID' && $key != 'userID' && $key != 'username') {
                    $set[] = $key . '= ?'; // . $value;
                    $values[] = $value;
                }
            }

            $setString = \implode(',', $set);
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("userID IN (?)", [$userIDs]);
            $sql = "UPDATE    wcf" . WCF_N . "_user_tracker
                    SET        " . $setString . " 
                    " . $conditions;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute(\array_merge($values, $conditions->getParameters()));

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

        return WCF::getTPL()->fetch('trackerUpdateUserBulkProcessing', 'wcf', [
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
            $updateKey = 'update' . $key;
            if (isset($_POST[$updateKey])) {
                $this->config[$key] = $_POST[$updateKey];
            }
            if ($key == 'time') {
                $this->config[$key] = TIME_NOW;
            }
            if ($key == 'isActive') {
                $this->config[$key] = 1;
            }
            if ($key == 'days') {
                $this->config[$key] = \intval($_POST['updatedays']);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getObjectList()
    {
        $userList = parent::getObjectList();

        // only tracked users
        $userList->getConditionBuilder()->add('user_table.isTracked = 1');

        return $userList;
    }
}
