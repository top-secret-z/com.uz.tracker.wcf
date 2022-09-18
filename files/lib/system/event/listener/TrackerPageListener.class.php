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
namespace wcf\system\event\listener;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\PageCache;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\cache\builder\TrackerPageCacheBuilder;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Listen to Page views.
 */
class TrackerPageListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if (!MODULE_TRACKER) {
            return;
        }

        // only if user is to be tracked
        $user = WCF::getUser();
        if (!$user->userID || !$user->isTracked || WCF::getSession()->getPermission('mod.tracking.noTracking')) {
            return;
        }

        // only if trackers
        $trackers = TrackerCacheBuilder::getInstance()->getData();
        if (!isset($trackers[$user->userID])) {
            return;
        }
        $tracker = $trackers[$user->userID];
        if (!$tracker->otherPage) {
            return;
        }

        // get page from cache
        $description = '';
        $page = PageCache::getInstance()->getPageByController($className);
        if ($page !== null && !empty($page->getTitle())) {
            $description = $page->pageID;
        }

        $public = 1;
        $name = $link = '';
        if (empty($description)) {
            $pages = TrackerPageCacheBuilder::getInstance()->getData();

            if (isset($pages[$className])) {
                $description = 'wcf.uztracker.page.' . $pages[$className]['page'];
                $public = $pages[$className]['isPublic'];
            } else {
                $description = 'wcf.uztracker.page.unknown';
                $public = 1;
            }
        }

        // exclude media
        if ($className == 'wcf\page\MediaPage' && USER_TRACKER_EXCLUDE_MEDIA) {
            return;
        }

        // attachments may be private
        if ($className == 'wcf\page\AttachmentPage') {
            $attachment = $eventObj->attachment;
            $objectType = ObjectTypeCache::getInstance()->getObjectType($attachment->objectTypeID);
            if ($objectType->private) {
                $public = 2;
            }
        }

        // strip session links, security tokens and access tokens
        switch ($public) {
            case 0:
                $link = $user->getLink();
                break;
            case 1:
                $link = WCF::getRequestURI();
                $link = Regex::compile('(?<=\?|&)([st]=[a-f0-9]{40}|at=\d+-[a-f0-9]{40})')->replace($link, '');
                break;
            case 2:
                $name = 'wcf.uztracker.protected';
                break;
        }

        TrackerLogEditor::create([
            'description' => $description,
            'link' => (empty($name) ? $link : ''),
            'name' => (empty($name) ? '' : $name),
            'trackerID' => $tracker->trackerID,
            'type' => 'wcf.uztracker.type.page',
        ]);
    }
}
