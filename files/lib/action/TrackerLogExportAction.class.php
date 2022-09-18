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
namespace wcf\action;

use wcf\data\user\tracker\log\TrackerLogList;
use wcf\data\user\tracker\Tracker;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Exports tracker log entries
 */
class TrackerLogExportAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TRACKER'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['mod.tracking.canSeeTracking'];

    /**
     * @inheritDoc
     */
    public $separator = Tracker::SEPARATOR;

    public $textSeparator = Tracker::TEXT_SEPARATOR;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!isset($_GET['username']) || !isset($_GET['type'])) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        $entryList = new TrackerLogList();
        if ($_GET['type'] == 'all') {
            $entryList->getConditionBuilder()->add('type LIKE ?', ['wcf.uztracker.type%']);
        } else {
            $entryList->getConditionBuilder()->add('type = ?', ['wcf.uztracker.type.' . $_GET['type']]);
        }
        if (!empty($_GET['username'])) {
            $entryList->getConditionBuilder()->add('username = ?', [$_GET['username']]);
        }
        $entryList->readObjects();

        $language = WCF::getLanguage();

        // send content type
        \header('Content-Type: text/csv; charset=UTF-8');
        \header('Content-Disposition: attachment; filename=tracker.csv');
        echo $this->textSeparator . $language->get('wcf.uztracker.username') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('wcf.uztracker.date') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('wcf.uztracker.time') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('wcf.uztracker.ipAddress') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('wcf.uztracker.type') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('wcf.uztracker.description') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('wcf.uztracker.linkName') . $this->textSeparator . $this->separator;
        echo $this->textSeparator . $language->get('wcf.uztracker.content') . $this->textSeparator . $this->separator;
        echo "\r\n";

        foreach ($entryList->getObjects() as $entry) {
            echo $this->textSeparator . $entry->username . $this->textSeparator . $this->separator;
            echo $this->textSeparator . DateUtil::format(DateUtil::getDateTimeByTimestamp($entry->time), DateUtil::DATE_FORMAT) . $this->textSeparator . $this->separator;
            echo $this->textSeparator . DateUtil::format(DateUtil::getDateTimeByTimestamp($entry->time), DateUtil::TIME_FORMAT) . $this->textSeparator . $this->separator;
            echo $this->textSeparator . $entry->ipAddress . $this->textSeparator . $this->separator;
            echo $this->textSeparator . $language->get($entry->type) . $this->textSeparator . $this->separator;
            echo $this->textSeparator . $language->get($entry->description) . $this->textSeparator . $this->separator;
            echo $this->textSeparator . (empty($entry->link) ? $language->get($entry->name) : $language->get($entry->link)) . $this->textSeparator . $this->separator;
            echo $this->textSeparator . $entry->content . $this->textSeparator . $this->separator;
            echo "\r\n";
        }

        $this->executed();

        exit;
    }
}
