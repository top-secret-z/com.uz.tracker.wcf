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
namespace wcf\system\page\handler;

use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\WCF;

/**
 * Menu page handler for the tracker list page.
 */
class TrackerListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TOnlineLocationPageHandler;

    /**
     * @inheritDoc
     */
    public function getLink($objectID)
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function isValid($objectID)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null)
    {
        return WCF::getSession()->getPermission('mod.tracking.canSeeTracking');
    }

    /**
     * @inheritDoc
     */
    public function lookup($searchString)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getOnlineLocation(Page $page, UserOnline $user)
    {
        if (!USER_TRACKER_ONLINE_HIDE) {
            return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.' . $page->identifier);
        }

        if (WCF::getSession()->getPermission('mod.tracking.canSeeTracking')) {
            return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.' . $page->identifier);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function prepareOnlineLocation(Page $page, UserOnline $user)
    {
        // do nothing
    }
}
