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

use wcf\data\DatabaseObjectEditor;
use wcf\data\package\PackageCache;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Provides functions to edit Tracker Logs.
 */
class TrackerLogEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    public static $baseClass = TrackerLog::class;

    /**
     * @inheritDoc
     */
    public static function create(array $data = [])
    {
        $parameters = [
            'time' => TIME_NOW,
            'description' => $data['description'],
            'ipAddress' => UserUtil::convertIPv6To4(WCF::getSession()->ipAddress),
            'link' => (!empty($data['link']) ? $data['link'] : ''),
            'name' => (!empty($data['name']) ? $data['name'] : ''),
            'time' => TIME_NOW,
            'trackerID' => $data['trackerID'],
            'type' => $data['type'],
            'userID' => WCF::getUser()->userID,
            'username' => WCF::getUser()->username,
            'packageID' => ($data['packageID'] ?? PackageCache::getInstance()->getPackageID('com.woltlab.wcf')),
            'userAgent' => UserUtil::getUserAgent(),
            'content' => ($data['content'] ?? ''),
        ];
        parent::create($parameters);
    }
}
