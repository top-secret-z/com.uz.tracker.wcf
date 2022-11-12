/**
 * Loader for tracker entries in profile
 * 
 * @author        2016-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.tracker.wcf
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, Ajax, Language, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;

    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
    Util_1 = tslib_1.__importDefault(Util_1);

    class UzTrackerProfileLoader {
        constructor(userID) {
            this._userID = userID;
            this._list = document.querySelector('.profileTrackerList');
            this._logType = 'all';
            this._typeSelector = document.getElementById('typeSelector');

            var showMoreItem = document.createElement('li');
            showMoreItem.className = 'showMore';
            this._showMoreItem = showMoreItem;

            if (this._list.childElementCount) {
                this._showMoreItem.innerHTML = '<button class="small">' + Language.get('wcf.uztracker.button.more') + '</button>';
                this._showMoreItem.children[0].addEventListener("click", (ev) => this._showMore(ev));
            }
            else {
                this._showMoreItem.innerHTML = '<small>' + Language.get('wcf.uztracker.button.noMoreEntries') + '</small>';
            }

            this._list.appendChild(this._showMoreItem);

            this._typeSelector.addEventListener("change", (ev) => this._changeType(ev));
        }

        _changeType(event) {
            event.preventDefault();

            this._list.innerHTML = '';
            this._logType = this._typeSelector.value;

            Ajax.api(this, {
                actionName: 'load',
                parameters: {
                    lastLogID:         0,
                    lastLogTime:    0,
                    logType:        this._logType,
                    userID:            this._userID
                }
            });
        }

        _showMore(event) {
            event.preventDefault();

            this._showMoreItem.children[0].disabled = true;

            Ajax.api(this, {
                actionName: 'load',
                parameters: {
                    lastLogID:         this._list.dataset.lastLogID,
                    lastLogTime:    this._list.dataset.lastLogTime,
                    logType:        this._logType,
                    userID:            this._userID
                }
            });
        }

        _ajaxSuccess(data) {
            if (data.returnValues.template) {

                this._showMoreItem.innerHTML = '<button class="small">' + Language.get('wcf.uztracker.button.more') + '</button>';
                this._showMoreItem.children[0].addEventListener("click", (ev) => this._showMore(ev));
                this._list.appendChild(this._showMoreItem);

                Util_1.default.insertHtml(data.returnValues.template, this._showMoreItem, 'before');

                this._list.dataset.lastLogTime = data.returnValues.lastLogTime;
                this._list.dataset.lastLogId = data.returnValues.lastLogID;

                this._showMoreItem.children[0].disabled = false;
            }
            else {
                this._showMoreItem.innerHTML = '<small>' + Language.get('wcf.uztracker.button.noMoreEntries') + '</small>';
                this._list.appendChild(this._showMoreItem);
            }
        }

        _ajaxSetup() {
            return {
                data: {
                    className: 'wcf\\data\\user\\tracker\\log\\TrackerLogAction'
                }
            };
        }
    }

    let uzTrackerProfileLoader;
    function init(userID) {
        if (!uzTrackerProfileLoader) {
            uzTrackerProfileLoader = new UzTrackerProfileLoader(userID);
        }
    }
    exports.init = init;
});
