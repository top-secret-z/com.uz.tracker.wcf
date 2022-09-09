/**
 * Dialog to display and change/delete user's configuration data
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.tracker.wcf
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Dialog", "WoltLabSuite/Core/Ui/Notification", "WoltLabSuite/Core/Ui/Confirmation"], function (require, exports, tslib_1, Ajax, Language, Dialog_1, UiNotification, UiConfirmation) {
	"use strict";
	Object.defineProperty(exports, "__esModule", { value: true });
	exports.init = void 0;
	
	Ajax = tslib_1.__importStar(Ajax);
	Language = tslib_1.__importStar(Language);
	Dialog_1 = tslib_1.__importDefault(Dialog_1);
	UiNotification = tslib_1.__importStar(UiNotification);
	UiConfirmation = tslib_1.__importStar(UiConfirmation);
	
	class UzTrackerConfiguration {
		constructor() {
			this._tracker = null;
			var buttons = document.querySelectorAll('.jsTrackerListConfig');
			for (var i = 0, length = buttons.length; i < length; i++) {
				buttons[i].addEventListener("click", (ev) => this._click(ev));
			}
		}
		
		_click(event) {
			event.preventDefault();
			
			var userID = event.currentTarget.dataset.objectId;
			
			Ajax.api(this, {
				actionName:	'prepareConfiguration',
				parameters:	{
					userID: userID
				}
			});
		}
		
		_submit() {
			var keys = Object.keys(this._tracker);
			for (var i = 0; i < keys.length; i++) {
				if (keys[i] == 'trackerID' || keys[i] == 'userID' || keys[i] == 'username' || keys[i] == 'time') continue;
				if (keys[i] == 'days') {
					this._tracker['days'] = document.getElementById('days').value;
					continue;
				}
				
				this._tracker[keys[i]] = 0;
				if (document.getElementById(keys[i]).checked == true) {
					this._tracker[keys[i]] = 1;
				}
			}
			
			Ajax.api(this, {
				actionName:	'saveConfiguration',
				parameters:	{
					tracker: this._tracker
				}
			});
		}
		
		_delete() {
			var userID = parseInt(document.getElementById('affectedUserID').value);
			
			UiConfirmation.show({
				confirm: function() {
					document.querySelector('.jsSubmitConfiguration').disabled = true;
					document.querySelector('.jsDeleteConfiguration').disabled = true;
					
					Ajax.apiOnce({
						data: {
							actionName: 'deleteTracker',
							className:  'wcf\\data\\user\\tracker\\TrackerAction',
							parameters: {
								userID: userID
							}
						},
						success: function() {
							UiNotification.show(Language.get('wcf.uztracker.delete.deleted'), function() {
								window.location.reload();
							});
						}
					});
				},
				message: Language.get('wcf.uztracker.delete.confirm')
			});
		}
		
		_ajaxSuccess(data) {
			switch (data.actionName) {
				case 'prepareConfiguration':
					this._tracker = data.returnValues.tracker;
					this._render(data);
					break;
				case 'saveConfiguration':
					UiNotification.show(Language.get('wcf.uztracker.success'));
					Dialog_1.default.close(this);
					break;
			}
		}
		
		_render(data) {
			Dialog_1.default.open(this, data.returnValues.template);
			
			var submitButton = document.querySelector('.jsSubmitConfiguration');
			submitButton.addEventListener("click", (ev) => this._submit(ev));
			
			var deleteButton = document.querySelector('.jsDeleteConfiguration');
			deleteButton.addEventListener("click", (ev) => this._delete(ev));
		}
		
		_ajaxSetup() {
			return {
				data: {
					className: 'wcf\\data\\user\\tracker\\TrackerAction',
				}
			};
		}
		
		_dialogSetup() {
			return {
				id: 		'trackerConfigurationDialog',
				options:	{ title: Language.get('wcf.uztracker.dialog.title') },
				source: 	null
			};
		}
	}
	
	let uzTrackerConfiguration;
	function init() {
		if (!uzTrackerConfiguration) {
			uzTrackerConfiguration = new UzTrackerConfiguration();
		}
	}
	exports.init = init;
});
