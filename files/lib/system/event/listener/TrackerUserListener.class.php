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
use wcf\data\user\tracker\TrackerEditor;
use wcf\data\user\User;
use wcf\system\cache\builder\TrackerCacheBuilder;
use wcf\system\WCF;

/**
 * Listen to user action.
 */
class TrackerUserListener implements IParameterizedEventListener
{
    /**
     * tracker and link
     */
    protected $tracker;

    protected $link = '';

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if (!MODULE_TRACKER) {
            return;
        }

        $action = $eventObj->getActionName();

        // auto track new users
        if ($action == 'create' && USER_TRACKER_AUTO_NEWUSER_DAYS) {
            $returnValues = $eventObj->getReturnValues();
            $user = $returnValues['returnValues'];

            $sql = "INSERT INTO    wcf" . WCF_N . "_user_tracker
                    (userID, username, isActive, days, time) VALUES (?, ?, ?, ?, ?)";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$user->userID, $user->username, 1, USER_TRACKER_AUTO_NEWUSER_DAYS, TIME_NOW]);

            $sql = "UPDATE    wcf" . WCF_N . "_user
                    SET        isTracked = 1
                    WHERE    userID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$user->userID]);

            // reset tracker cache
            TrackerEditor::resetCache();
        }

        // tracking
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
        $this->tracker = $trackers[$user->userID];

        // moderation
        if ($this->tracker->otherModeration) {
            if ($action == 'ban' || $action == 'unban') {
                $userIDs = $eventObj->getObjectIDs();
                foreach ($userIDs as $userID) {
                    $moderatedUser = new User($userID);
                    $this->link = $moderatedUser->getLink();
                    if ($action == 'ban') {
                        $this->store('wcf.uztracker.description.moderation.ban', 'wcf.uztracker.type.moderation');
                    } else {
                        $this->store('wcf.uztracker.description.moderation.unban', 'wcf.uztracker.type.moderation');
                    }
                }
            }

            if ($action == 'enableAvatar' || $action == 'disableAvatar') {
                $userIDs = $eventObj->getObjectIDs();
                foreach ($userIDs as $userID) {
                    $moderatedUser = new User($userID);
                    $this->link = $moderatedUser->getLink();
                    if ($action == 'enableAvatar') {
                        $this->store('wcf.uztracker.description.moderation.enableAvatar', 'wcf.uztracker.type.moderation');
                    } else {
                        $this->store('wcf.uztracker.description.moderation.disableAvatar', 'wcf.uztracker.type.moderation');
                    }
                }
            }

            if ($action == 'enableSignature' || $action == 'disableSignature') {
                $userIDs = $eventObj->getObjectIDs();
                foreach ($userIDs as $userID) {
                    $moderatedUser = new User($userID);
                    $this->link = $moderatedUser->getLink();
                    if ($action == 'enableSignature') {
                        $this->store('wcf.uztracker.description.moderation.enableSignature', 'wcf.uztracker.type.moderation');
                    } else {
                        $this->store('wcf.uztracker.description.moderation.disableSignature', 'wcf.uztracker.type.moderation');
                    }
                }
            }
        }

        // update for profile, account ...
        if ($action == 'update') {
            $this->link = $user->getLink();

            // get data
            $params = $eventObj->getParameters();
            $objects = $eventObj->getObjects();

            if (isset($params['data']['username']) && $this->tracker->accountUsername) {
                $this->store('wcf.uztracker.description.account.username', 'wcf.uztracker.type.account');
            }

            if (isset($params['data']['email']) && $this->tracker->accountEmail) {
                $this->store('wcf.uztracker.description.account.email', 'wcf.uztracker.type.account');
            }

            if (isset($params['data']['password']) && $this->tracker->accountPassword) {
                $this->store('wcf.uztracker.description.account.password', 'wcf.uztracker.type.account');
            }

            if ($this->tracker->accountDeletion) {
                if (isset($params['data']['quitStarted']) && $params['data']['quitStarted'] > 0) {
                    $this->store('wcf.uztracker.description.account.deletion.start', 'wcf.uztracker.type.account');
                }
                if (isset($params['data']['quitStarted']) && $params['data']['quitStarted'] == 0) {
                    $this->store('wcf.uztracker.description.account.deletion.end', 'wcf.uztracker.type.account');
                }
            }

            if (isset($params['data']['enableGravatar']) && $this->tracker->profileAvatar) {
                $this->store('wcf.uztracker.description.profile.avatar', 'wcf.uztracker.type.profile');
            }

            if (isset($params['data']['signature']) && $this->tracker->profileSignature) {
                $this->store('wcf.uztracker.description.profile.signature', 'wcf.uztracker.type.profile');
            }

            if (isset($params['data']['userTitle']) && isset($objects[0]->userTitle) && $this->tracker->profileTitle) {
                if ($objects[0]->userTitle != $params['data']['userTitle']) {
                    $this->store('wcf.uztracker.description.profile.title', 'wcf.uztracker.type.profile');
                }
            }

            if (isset($params['options']) && $this->tracker->profileOther) {
                $this->store('wcf.uztracker.description.profile.other', 'wcf.uztracker.type.profile');
            }
        }
    }

    /**
     * store log entry
     */
    protected function store($description, $type)
    {
        TrackerLogEditor::create([
            'description' => $description,
            'link' => $this->link,
            'trackerID' => $this->tracker->trackerID,
            'type' => $type,
        ]);
    }
}
