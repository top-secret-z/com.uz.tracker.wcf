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
namespace wcf\page;

use wcf\data\user\tracker\log\TrackerLogList;
use wcf\system\page\PageLocationManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the Tracker log entries.
 */
class TrackerLogListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.uztracker.menu.log';

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
    public $objectListClassName = TrackerLogList::class;

    /**
     * @inheritDoc
     */
    public $enableTracking = false;

    /**
     * @inheritDoc
     */
    public $itemsPerPage = USER_TRACKER_LOGLIST_PER_PAGE;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'time';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['time', 'ipAddress', 'username', 'type', 'description', 'link'];

    /**
     * username
     */
    public $username = '';

    /**
     * type
     */
    public $type = 'all';

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // add breadcrumbs
        if (MODULE_TRACKER) {
            PageLocationManager::getInstance()->addParentLocation('com.uz.tracker.wcf.TrackerList');
        }
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['username'])) {
            $this->username = StringUtil::trim($_REQUEST['username']);
        }
        if (!empty($_REQUEST['type'])) {
            $this->type = $_REQUEST['type'];
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        // filter
        if (!empty($this->username)) {
            $this->objectList->getConditionBuilder()->add('username LIKE ?', ['%' . $this->username . '%']);
        }
        if ($this->type != 'all') {
            $this->objectList->getConditionBuilder()->add('type = ?', ['wcf.uztracker.type.' . $this->type]);
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'username' => $this->username,
            'type' => $this->type,
        ]);
    }
}
