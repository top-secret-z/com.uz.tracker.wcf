{foreach from=$logList item=log}
	<li>
		<div class="box48">
			<a href="{link controller='User' object=$user}{/link}" title="{$user->username}" class="framed">{@$user->getAvatar()->getImageTag(48)}</a>
			
			<div>
				<div class="containerHeadline">
					<h3>{lang}{$log->type}{/lang}<small> - {@$log->time|time}</small></h3> 
					<p><strong>{lang}{@$log->description}{/lang}</strong></p>
					{if $__wcf->session->getPermission('admin.user.canViewIpAddress')}
						<small class="containerContentType"><a href="{link controller='TrackerWhois' isEmail=true}ip={$log->ipAddress}{/link}" title="{lang}wcf.uztracker.query.ip{/lang}" class="jsTooltip">{$log->ipAddress}</a></small>
					{/if}
				</div>
				
				{if $log->link|empty}
					<small><strong>{lang}{$log->name}{/lang}</strong></small>
				{else}
					<small><a href="{$log->link}">{$log->link}</a></small>
				{/if}
				{if !$log->content|empty}
					<span class="jsTrackerContentView" data-object-id="{@$log->trackerLogID}"><a href="#" title="{lang}wcf.uztracker.content.deleted.show{/lang}">&nbsp;&nbsp;&nbsp;{lang}wcf.uztracker.content.deleted.show{/lang}</a></span>
				{/if}
			</div>
		</div>
	</li>
{/foreach}

<script data-relocate="true">
	require(['Language', 'UZ/Tracker/DeletedContent'], function (Language, UzTrackerDeletedContent) {
		Language.addObject({
			'wcf.uztracker.dialog.content':	'{jslang}wcf.uztracker.dialog.content{/jslang}'
		});
		
		UzTrackerDeletedContent.init();
	});
</script>
