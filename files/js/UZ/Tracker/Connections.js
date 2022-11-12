/**
 * Dialog to display user's connection data
 * 
 * @author        2016-2022 Zaydowicz
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package        com.uz.tracker.wcf
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Dialog"], function (require, exports, tslib_1, Ajax, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;

    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);

    class UzTrackerConnections {
        constructor(userID) {
            this._userID = userID;
            var button = document.getElementById('jsConnectionButton');
            button.addEventListener("click", (ev) => this._click(ev));
        }

        _click(event) {
            event.preventDefault();

            Ajax.api(this, {
                actionName:    'loadConnections',
                parameters:    {
                    userID: this._userID
                }
            });
        }

        _dialogSetup() {
            return {
                id:         'trackerConnectionsDialog',
                options:     { title: Language.get('wcf.uztracker.dialog.connection') },
                source:     null
            };
        }

        _ajaxSetup() {
            return {
                data: {
                    className: 'wcf\\data\\user\\tracker\\log\\TrackerLogAction'
                }
            };
        }

        _ajaxSuccess(data) {
            this._render(data);
        }

        _render(data) {
            Dialog_1.default.open(this, data.returnValues.template);
        }
    }

    let uzTrackerConnections;
    function init(userID) {
        if (!uzTrackerConnections) {
            uzTrackerConnections = new UzTrackerConnections(userID);
        }
    }
    exports.init = init;
});
