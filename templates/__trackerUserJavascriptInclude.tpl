{if MODULE_TRACKER && $__wcf->session->getPermission('mod.tracking.canModifyTracking')}
	<script data-relocate="true">
		require(['Language', 'UZ/Tracker/Configuration'], function (Language, UzTrackerConfiguration) {
			Language.addObject({
				'wcf.uztracker.delete.confirm':	'{jslang}wcf.uztracker.delete.confirm{/jslang}',
				'wcf.uztracker.delete.deleted':	'{jslang}wcf.uztracker.delete.deleted{/jslang}',
				'wcf.uztracker.dialog.title':	'{jslang}wcf.uztracker.dialog.title{/jslang}',
				'wcf.uztracker.success':		'{jslang}wcf.uztracker.success{/jslang}'
			});
			
			UzTrackerConfiguration.init();
		});
	</script>
{/if}