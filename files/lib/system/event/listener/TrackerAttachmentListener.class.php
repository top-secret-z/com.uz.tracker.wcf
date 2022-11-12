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

use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to attachment action.
 */
class TrackerAttachmentListener implements IParameterizedEventListener
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
        if (!$tracker->contentAttachment) {
            return;
        }

        switch($eventObj->getActionName()) {
            case 'upload':
                $params = $eventObj->getParameters();
                $returnValues = $eventObj->getReturnValues();
                $attachments = $returnValues['returnValues']['attachments'];

                foreach ($attachments as $attachment) {
                    TrackerLogEditor::create([
                        'description' => 'wcf.uztracker.description.attachment.add',
                        'link' => ($params['objectType'] != 'com.woltlab.wcf.conversation.message') ? $attachment['url'] : '',
                        'name' => ($params['objectType'] == 'com.woltlab.wcf.conversation.message') ? 'wcf.uztracker.protected' : '',
                        'trackerID' => $tracker->trackerID,
                        'type' => 'wcf.uztracker.type.content',
                    ]);
                }
                break;

            case 'delete':
                $objects = $eventObj->getObjects();
                if ($objects[0]->userID == WCF::getUser()->userID) {
                    TrackerLogEditor::create([
                        'description' => 'wcf.uztracker.description.attachment.delete',
                        'link' => WCF::getUser()->getLink(),
                        'trackerID' => $tracker->trackerID,
                        'type' => 'wcf.uztracker.type.content',
                    ]);
                }
                break;
        }
    }
}
