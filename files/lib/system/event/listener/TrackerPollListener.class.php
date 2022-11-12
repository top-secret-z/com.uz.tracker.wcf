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
use wcf\data\poll\Poll;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\poll\PollManager;
use wcf\system\WCF;

/**
 * Listen to poll action.
 */
class TrackerPollListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if (!MODULE_TRACKER || !MODULE_POLL) {
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
        if (!$tracker->contentPoll) {
            return;
        }

        // action
        $action = $eventObj->getActionName();

        if ($action == 'create') {
            // no idea on how to get the poll related object for a link
            $returnValues = $eventObj->getReturnValues();
            $poll = $returnValues['returnValues'];
            $type = ObjectTypeCache::getInstance()->getObjectType($poll->objectTypeID);
            TrackerLogEditor::create([
                'description' => 'wcf.uztracker.description.poll.add',
                'link' => '',
                'name' => ($type !== null) ? $type->objectType : 'wcf.uztracker.unknown',
                'trackerID' => $tracker->trackerID,
                'type' => 'wcf.uztracker.type.content',
            ]);
        }

        if ($action == 'vote') {
            $objectIDs = $eventObj->getObjectIDs();
            foreach ($objectIDs as $objectID) {
                $poll = new Poll($objectID);
                $object = PollManager::getInstance()->getRelatedObject($poll);
                if ($object !== null) {
                    $link = $object->getLink();
                } else {
                    $link = 'wcf.uztracker.unknown';
                }
                TrackerLogEditor::create([
                    'description' => 'wcf.uztracker.description.poll.vote',
                    'link' => $link,
                    'trackerID' => $tracker->trackerID,
                    'type' => 'wcf.uztracker.type.content',
                ]);
            }
        }
    }
}
