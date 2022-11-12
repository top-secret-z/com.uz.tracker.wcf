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

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\user\tracker\log\TrackerLog;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\WCF;

/**
 * Executes Tracker related actions.
 */
class TrackerAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $className = TrackerEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['mod.tracking.canModifyTracking'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['mod.tracking.canModifyTracking'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['mod.tracking.canModifyTracking'];

    /**
     * @inheritDoc
     */
    protected $tracker;

    /**
     * @inheritDoc
     */
    protected $user;

    /**
     * @inheritDoc
     */
    public function create()
    {
        return parent::create();
    }

    /**
     * @inheritDoc
     */
    public function validateToggle()
    {
        parent::validateUpdate();
    }

    /**
     * @inheritDoc
     */
    public function toggle()
    {
        foreach ($this->objects as $tracker) {
            $tracker->update([
                'isActive' => $tracker->isActive ? 0 : 1,
            ]);
        }
    }

    /**
     * Validates prepareConfiguration action.
     */
    public function validatePrepareConfiguration()
    {
        WCF::getSession()->checkPermissions(['mod.tracking.canModifyTracking']);
        $this->readInteger('userID');
        $this->tracker = Tracker::getTrackerByUserID($this->parameters['userID']);
        $this->user = new User($this->parameters['userID']);
    }

    /**
     * Prepares the configuration.
     */
    public function prepareConfiguration()
    {
        // create new tracker, unless existing
        if (!$this->tracker->trackerID) {
            $sql = "INSERT INTO    wcf" . WCF_N . "_user_tracker
                    (userID, username, isActive) VALUES (?, ?, ?)";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$this->parameters['userID'], $this->user->username, 0]);
            $this->tracker = Tracker::getTrackerByUserID($this->parameters['userID']);
        }

        // get column names and values
        $columns = [];
        $sql = "SHOW COLUMNS FROM wcf" . WCF_N . "_user_tracker";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $temp = $row['Field'];
            $columns[$temp] = $this->tracker->{$temp};
        }

        // get tracking logs count
        $count = TrackerLog::getCountByUserID($this->user->userID);

        WCF::getTPL()->assign([
            'tracker' => $columns,
            'isTracked' => $this->user->isTracked,
            'count' => $count,
            'lastConfig' => $this->tracker->time,
            'userID' => $this->user->userID,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('trackerConfigurationDialog'),
            'tracker' => $columns,
        ];
    }

    /**
     * Validates saveConfiguration action.
     */
    public function validateSaveConfiguration()
    {
        WCF::getSession()->checkPermissions(['mod.tracking.canModifyTracking']);
    }

    /**
     * Prepares the configuration.
     */
    public function saveConfiguration()
    {
        $trackerArray = $this->parameters['tracker'];

        // update time if not yet done. Always restart, if active, and set to 0 if not
        if ($trackerArray['isActive']) {
            $trackerArray['time'] = TIME_NOW;
        } else {
            $trackerArray['time'] = 0;
        }

        $set = $params = [];
        foreach ($trackerArray as $key => $value) {
            if ($key != 'trackerID') {
                $set[] = $key . '=?';
                $params[] = $value;
            }
        }
        $params[] = $trackerArray['trackerID'];
        $sql = "UPDATE    wcf" . WCF_N . "_user_tracker
                SET        " . \implode(',', $set) . "
                WHERE    trackerID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($params);

        // update user
        $userEditor = new UserEditor(new User($trackerArray['userID']));
        $userEditor->update([
            'isTracked' => $trackerArray['isActive'] ? 1 : 0,
        ]);

        // reset tracker cache
        TrackerEditor::resetCache();

        return [
            'configured' => 1,
        ];
    }

    /**
     * Validates deleteTracker action.
     */
    public function validateDeleteTracker()
    {
        WCF::getSession()->checkPermissions(['mod.tracking.canModifyTracking']);
        $this->readInteger('userID');
    }

    /**
     * Deletes the tracker and its entries.
     */
    public function deleteTracker()
    {
        $sql = "DELETE FROM    wcf" . WCF_N . "_user_tracker
                WHERE        userID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->parameters['userID']]);

        // reset tracker cache
        TrackerEditor::resetCache();

        // update user
        $userEditor = new UserEditor(new User($this->parameters['userID']));
        $userEditor->update([
            'isTracked' => 0,
        ]);

        // clear log
        $sql = "DELETE FROM    wcf" . WCF_N . "_user_tracker_log
                WHERE        userID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->parameters['userID']]);
    }
}
