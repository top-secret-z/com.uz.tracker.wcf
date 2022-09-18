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

use wcf\data\conversation\ConversationAction;
use wcf\data\conversation\message\ConversationMessageAction;
use wcf\data\user\tracker\log\TrackerLogEditor;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to conversation action.
 */
class TrackerConversationListener implements IParameterizedEventListener
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
        if (!$tracker->contentConversation) {
            return;
        }

        // get conversation actions
        if ($eventObj instanceof ConversationMessageAction) {
            //    $action = $eventObj->getActionName();
            $description = '';
            switch ($eventObj->getActionName()) {
                case 'create':
                    // WCF does not allow to recognize publishing of drafts
                    $params = $eventObj->getParameters();
                    $conversation = $params['conversation'];
                    $description = $conversation->isDraft ? 'wcf.uztracker.description.conversation.addDraft' : 'wcf.uztracker.description.conversation.add';
                    break;

                case 'update':
                    $description = 'wcf.uztracker.description.conversation.edit';
                    break;

                case 'quickReply':
                    $description = 'wcf.uztracker.description.conversation.answer';
                    break;
            }

            TrackerLogEditor::create([
                'description' => $description,
                'link' => $user->getLink(),
                'trackerID' => $tracker->trackerID,
                'type' => 'wcf.uztracker.type.content',
            ]);
        } elseif ($eventObj instanceof ConversationAction) {
            $description = '';
            switch ($eventObj->getActionName()) {
                case 'hideConversation':
                    $description = 'wcf.uztracker.description.conversation.hide';
                    break;

                case 'delete':
                    $description = 'wcf.uztracker.description.conversation.delete';
                    break;

                case 'add Participants':
                    $description = 'wcf.uztracker.description.conversation.addParticipants';
                    break;
            }

            TrackerLogEditor::create([
                'description' => $description,
                'link' => $user->getLink(),
                'trackerID' => $tracker->trackerID,
                'type' => 'wcf.uztracker.type.content',
            ]);
        }
    }
}
