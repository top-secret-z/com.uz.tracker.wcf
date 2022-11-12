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

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Deletes tracker log entries
 */
class TrackerLogDeleteAction extends AbstractAction
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
    public $neededPermissions = ['mod.tracking.canModifyTracking'];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!isset($_GET['username']) || !isset($_GET['type'])) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        $conditionBuilder = new PreparedStatementConditionBuilder();
        if ($_GET['type'] == 'all') {
            $conditionBuilder->add('type LIKE ?', ['wcf.uztracker.type%']);
        } else {
            $conditionBuilder->add('type = ?', ['wcf.uztracker.type.' . $_GET['type']]);
        }
        if (!empty($_GET['username'])) {
            $conditionBuilder->add('username = ?', [$_GET['username']]);
        }

        $sql = "DELETE FROM wcf" . WCF_N . "_user_tracker_log " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());

        $this->executed();

        // forward to tracker list page
        HeaderUtil::redirect(LinkHandler::getInstance()->getLink('TrackerLogList'));

        exit;
    }
}
