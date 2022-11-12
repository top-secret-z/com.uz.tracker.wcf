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
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Listen to report action.
 */
class TrackerReportListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if (!MODULE_TRACKER) {
            return;
        }

        // only listen to report
        $action = $eventObj->getActionName();
        if ($action != 'report') {
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
        if (!$tracker->usersReport) {
            return;
        }

        // get object
        $params = $eventObj->getParameters();
        $link = 'wcf.uztracker.unknown';
        try {
            $definition = ObjectTypeCache::getInstance()->getDefinitionByName('com.woltlab.wcf.moderation.report');
            if ($definition->definitionID) {
                $sql = "SELECT    className
                        FROM    wcf" . WCF_N . "_object_type
                        WHERE    definitionID = ?
                                AND objectType = ?";
                $statement = WCF::getDB()->prepareStatement($sql, 1);
                $statement->execute([$definition->definitionID, $params['objectType']]);
                $row = $statement->fetchArray();

                $handler = new $row['className'];
                $object = $handler->getReportedObject($params['objectID']);
                $link = $object->getLink();
            }
        } catch (SystemException $e) {
            // nothing to do
        }

        TrackerLogEditor::create([
            'description' => 'wcf.uztracker.description.report',
            'link' => $link,
            'trackerID' => $tracker->trackerID,
            'type' => 'wcf.uztracker.type.users',
        ]);
    }
}
