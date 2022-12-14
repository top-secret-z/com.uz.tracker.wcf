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

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Executes Tracker Log related actions.
 */
class TrackerLogAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = TrackerLogEditor::class;

    /**
     * Validates parameters to load recent activity entries.
     */
    public function validateLoad()
    {
        WCF::getSession()->checkPermissions(['mod.tracking.canSeeTracking']);
        $this->readInteger('lastLogTime', true);
        $this->readInteger('lastLogID', true);
        $this->readInteger('userID', true);
    }

    /**
     * Loads a list of log entries.
     */
    public function load()
    {
        $logList = new TrackerLogList();

        if ($this->parameters['lastLogID'] || $this->parameters['lastLogTime']) {
            if ($this->parameters['lastLogID']) {
                $logList->getConditionBuilder()->add("time <= ?", [$this->parameters['lastLogTime']]);
                $logList->getConditionBuilder()->add("trackerLogID < ?", [$this->parameters['lastLogID']]);
            } else {
                $logList->getConditionBuilder()->add("time < ?", [$this->parameters['lastLogTime']]);
            }
        }

        if ($this->parameters['userID']) {
            $logList->getConditionBuilder()->add("userID = ?", [$this->parameters['userID']]);
        }

        if ($this->parameters['logType'] != 'all') {
            $logList->getConditionBuilder()->add("type LIKE ?", ['wcf.uztracker.type.' . $this->parameters['logType']]);
        }

        $logList->sqlLimit = USER_TRACKER_LOGLIST_PER_PAGE;

        $logList->readObjects();

        if (!\count($logList)) {
            return [];
        }

        // parse template
        WCF::getTPL()->assign([
            'logList' => $logList,
            'user' => new UserProfile(new User($this->parameters['userID'])),
            'type' => $this->parameters['logType'],
        ]);

        $logs = $logList->getObjects();

        return [
            'lastLogID' => \end($logs)->trackerLogID,
            'lastLogTime' => $logList->getLastLogTime(),
            'template' => WCF::getTPL()->fetch('userProfileTrackerListItem'),
        ];
    }

    /**
     * Validates parameters to load recent activity entries.
     */
    public function validateGetContent()
    {
        WCF::getSession()->checkPermissions(['mod.tracking.canSeeTracking']);
    }

    /**
     * Loads a list of log entries.
     */
    public function getContent()
    {
        $trackerLog = new TrackerLog($this->parameters['objectID']);

        WCF::getTPL()->assign([
            'content' => $trackerLog->content,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('trackerContent'),
        ];
    }

    /**
     * Validates parameters to load connections.
     */
    public function validateLoadConnections()
    {
        WCF::getSession()->checkPermissions(['mod.tracking.canSeeTracking']);
        $this->readInteger('userID', true);
    }

    /**
     * Loads a list of connections.
     */
    public function loadConnections()
    {
        // get registration IP
        $sql = "SELECT    registrationIpAddress
                FROM    wcf" . WCF_N . "_user
                WHERE    userID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->parameters['userID']]);
        $row = $statement->fetchArray();
        if ($row !== false && $row['registrationIpAddress']) {
            $regIpAddress = UserUtil::convertIPv6To4($row['registrationIpAddress']);
            WCF::getTPL()->assign([
                'regIpAddress' => [
                    'hostname' => @\gethostbyaddr($regIpAddress),
                    'ipAddress' => $regIpAddress,
                ],
            ]);
        }

        // get other IPs and user agents
        $ipAddresses = $temp = $userAgents = [];
        $sql = "SELECT        ipAddress
                FROM        wcf" . WCF_N . "_user_tracker_log
                WHERE        userID = ? AND ipAddress <> ''
                GROUP BY    ipAddress
                ORDER BY    MAX(time) DESC, MAX(trackerLogID) DESC";
        $statement = WCF::getDB()->prepareStatement($sql, 50);
        $statement->execute([$this->parameters['userID']]);
        while ($row = $statement->fetchArray()) {
            $ipAddresses[] = $row['ipAddress'];
        }

        $sql = "SELECT        userAgent
                FROM        wcf" . WCF_N . "_user_tracker_log
                WHERE        userID = ? AND userAgent <> ''
                GROUP BY    userAgent
                ORDER BY    MAX(time) DESC, MAX(trackerLogID) DESC";
        $statement = WCF::getDB()->prepareStatement($sql, 50);
        $statement->execute([$this->parameters['userID']]);
        while ($row = $statement->fetchArray()) {
            $userAgents[] = $row['userAgent'];
        }

        // resolve IPs
        foreach ($ipAddresses as $ipAddress) {
            $temp[] = [
                'hostname' => @\gethostbyaddr($ipAddress),
                'ipAddress' => $ipAddress,
            ];
        }
        $ipAddresses = $temp;

        WCF::getTPL()->assign([
            'ipAddresses' => $ipAddresses,
            'userAgents' => $userAgents,
        ]);

        return [
            'template' => WCF::getTPL()->fetch('trackerConnectionsDialog'),
        ];
    }
}
