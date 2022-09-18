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

use wcf\data\DatabaseObjectList;
use wcf\data\page\PageCache;

/**
 * Represents a list of Tracker Logs.
 */
class TrackerLogList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = TrackerLog::class;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'time DESC, trackerLogID DESC';

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        // assign page names
        foreach ($this->objects as $log) {
            if ($log->type == 'wcf.uztracker.type.page' && \intval($log->description)) {
                $page = PageCache::getInstance()->getPage(\intval($log->description));
                if ($page === null) {
                    $log->description = 'wcf.uztracker.page.unknown';
                } else {
                    $log->description = $page->getTitle();
                }
            }
        }
    }

    /**
     * Returns timestamp of oldest log fetched.
     */
    public function getLastLogTime()
    {
        $lastLogTime = 0;
        foreach ($this->objects as $log) {
            if (!$lastLogTime) {
                $lastLogTime = $log->time;
            }
            $lastLogTime = \min($lastLogTime, $log->time);
        }

        return $lastLogTime;
    }
}
