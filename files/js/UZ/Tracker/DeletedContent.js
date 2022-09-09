/**
 * Dialog to display deleted content
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Dialog"], function (require, exports, tslib_1, Ajax, Language, Dialog_1) {
	"use strict";
	Object.defineProperty(exports, "__esModule", { value: true });
	exports.init = void 0;
	
	Ajax = tslib_1.__importStar(Ajax);
	Language = tslib_1.__importStar(Language);
	Dialog_1 = tslib_1.__importDefault(Dialog_1);
	
	class UzTrackerDeletedContent {
		constructor() {
			var buttons = document.querySelectorAll('.jsTrackerContentView');
			for (var i = 0, length = buttons.length; i < length; i++) {
				buttons[i].addEventListener("click", (ev) => this._click(ev));
			}
		}
		
		_click(event) {
			event.preventDefault();
			
			var objectId = event.currentTarget.dataset.objectId;
			
			Ajax.api(this, {
				actionName:	'getContent',
				parameters:	{
					objectID: objectId
				}
			});
		}
		
		_dialogSetup() {
			return {
				id: 		'trackerContentDialog',
				options: 	{ title: Language.get('wcf.uztracker.dialog.content') },
				source: 	null
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
	
	let uzTrackerDeletedContent;
	function init() {
		if (!uzTrackerDeletedContent) {
			uzTrackerDeletedContent = new UzTrackerDeletedContent();
		}
	}
	exports.init = init;
});