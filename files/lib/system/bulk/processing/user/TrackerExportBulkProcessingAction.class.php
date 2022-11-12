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
use wcf\data\user\tracker\log\TrackerLogList;
use wcf\data\user\UserList;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Bulk processing action implementation for exporting tracker log entries.
 */
class TrackerExportBulkProcessingAction extends AbstractUserBulkProcessingAction
{
    /**
     * separator for the exported data and enclosure
     */
    public $trackerSeparator = ',';

    public $trackerTextSeparator = '"';

    /**
     * @inheritDoc
     */
    public function executeAction(DatabaseObjectList $objectList)
    {
        if (!($objectList instanceof UserList)) {
            return;
        }

        $count = \count($objectList);
        if ($count) {
            // get log entries
            $userIDs = $objectList->getObjectIDs();
            $entryList = new TrackerLogList();
            $entryList->getConditionBuilder()->add('userID IN (?)', [$userIDs]);
            $entryList->readObjects();

            $language = WCF::getLanguage();

            // send content type
            \header('Content-Type: text/csv; charset=UTF-8');
            \header('Content-Disposition: attachment; filename=tracker.csv');
            echo $this->trackerTextSeparator . $language->get('wcf.uztracker.username') . $this->trackerTextSeparator . $this->trackerSeparator;
            echo $this->trackerTextSeparator . $language->get('wcf.uztracker.date') . $this->trackerTextSeparator . $this->trackerSeparator;
            echo $this->trackerTextSeparator . $language->get('wcf.uztracker.time') . $this->trackerTextSeparator . $this->trackerSeparator;
            echo $this->trackerTextSeparator . $language->get('wcf.uztracker.ipAddress') . $this->trackerTextSeparator . $this->trackerSeparator;
            echo $this->trackerTextSeparator . $language->get('wcf.uztracker.type') . $this->trackerTextSeparator . $this->trackerSeparator;
            echo $this->trackerTextSeparator . $language->get('wcf.uztracker.description') . $this->trackerTextSeparator . $this->trackerSeparator;
            echo $this->trackerTextSeparator . $language->get('wcf.uztracker.linkName') . $this->trackerTextSeparator . $this->trackerSeparator;
            echo $this->trackerTextSeparator . $language->get('wcf.uztracker.content') . $this->trackerTextSeparator . $this->trackerSeparator;
            echo "\r\n";

            foreach ($entryList->getObjects() as $entry) {
                echo $this->trackerTextSeparator . $entry->username . $this->trackerTextSeparator . $this->trackerSeparator;
                echo $this->trackerTextSeparator . DateUtil::format(DateUtil::getDateTimeByTimestamp($entry->time), DateUtil::DATE_FORMAT) . $this->trackerTextSeparator . $this->trackerSeparator;
                echo $this->trackerTextSeparator . DateUtil::format(DateUtil::getDateTimeByTimestamp($entry->time), DateUtil::TIME_FORMAT) . $this->trackerTextSeparator . $this->trackerSeparator;
                echo $this->trackerTextSeparator . $entry->ipAddress . $this->trackerTextSeparator . $this->trackerSeparator;
                echo $this->trackerTextSeparator . $language->get($entry->type) . $this->trackerTextSeparator . $this->trackerSeparator;
                echo $this->trackerTextSeparator . $language->get($entry->description) . $this->trackerTextSeparator . $this->trackerSeparator;
                echo $this->trackerTextSeparator . (empty($entry->link) ? $language->get($entry->name) : $language->get($entry->link)) . $this->trackerTextSeparator . $this->trackerSeparator;
                echo $this->trackerTextSeparator . $entry->content . $this->trackerTextSeparator . $this->trackerSeparator;
                echo "\r\n";
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        return WCF::getTPL()->fetch('trackerExportUserBulkProcessing', 'wcf', [
            'trackerSeparator' => $this->trackerSeparator,
            'trackerTextSeparator' => $this->trackerTextSeparator,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getObjectList()
    {
        return parent::getObjectList();
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        if (isset($_POST['trackerSeparator'])) {
            $this->trackerSeparator = $_POST['trackerSeparator'];
        }
        if (isset($_POST['trackerTextSeparator'])) {
            $this->trackerTextSeparator = $_POST['trackerTextSeparator'];
        }
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        exit;
    }
}
