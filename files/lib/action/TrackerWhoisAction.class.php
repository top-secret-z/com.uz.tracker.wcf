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

use wcf\system\exception\IllegalLinkException;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;

/**
 * Provides IP Whois query to Tracker
 */
class TrackerWhoisAction extends AbstractAction
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
    public $neededPermissions = ['mod.tracking.canSeeTracking', 'admin.user.canViewIpAddress'];

    /**
     * IP
     */
    public $ipAddress = '';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // must be a valid IPv4 or 6 address
        if (empty($_GET['ip']) || (\filter_var($_GET['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false && \filter_var($_GET['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)) {
            throw new IllegalLinkException();
        }
        $this->ipAddress = $_GET['ip'];
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        // get whoisURL from configuration / set default and ensure placeholder
        $whoisUrl = StringUtil::trim(USER_TRACKER_WHOIS_URL);
        if (empty($whoisUrl)) {
            $whoisUrl = 'https://apps.db.ripe.net/search/query.html?searchtext=%s';
        } else {
            if (\mb_strpos($whoisUrl, '%s') === false) {
                throw new IllegalLinkException();
            }
        }

        $whoisUrl = \sprintf($whoisUrl, $this->ipAddress);
        HeaderUtil::redirect($whoisUrl);

        $this->executed();

        exit;
    }
}
