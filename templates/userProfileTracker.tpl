<script data-relocate="true">
    $(function() {
        $('#jsRefreshButton').click(function() {
            window.location.reload();
        });

    });
</script>

<script data-relocate="true">
    require(['Language', 'UZ/Tracker/ProfileLoader'], function (Language, UzTrackerProfileLoader) {
        Language.addObject({
            'wcf.uztracker.button.more':            '{jslang}wcf.uztracker.button.more{/jslang}',
            'wcf.uztracker.button.noMoreEntries':    '{jslang}wcf.uztracker.button.noMoreEntries{/jslang}'
        });

        UzTrackerProfileLoader.init('{@$userID}');
    });
</script>

<script data-relocate="true">
    require(['Language', 'UZ/Tracker/Connections'], function (Language, UzTrackerConnections) {
        Language.addObject({
            'wcf.uztracker.dialog.connection':    '{jslang}wcf.uztracker.dialog.connection{/jslang}'
        });

        UzTrackerConnections.init('{@$userID}');
    });
</script>

<script data-relocate="true">
    require(['Language', 'UZ/Tracker/DeletedContent'], function (Language, UzTrackerDeletedContent) {
        Language.addObject({
            'wcf.uztracker.dialog.content':    '{jslang}wcf.uztracker.dialog.content{/jslang}'
        });

        UzTrackerDeletedContent.init();
    });
</script>

<ul class="buttonList" style="margin-top:15px; margin-bottom:15px;">
    <li>
        <select name="type" id="typeSelector">
            <option value="all"{if $type == 'all'} selected="selected"{/if}>{lang}wcf.uztracker.filter.all{/lang}</option>
            <option value="users"{if $type == 'users'} selected="selected"{/if}>{lang}wcf.uztracker.type.users{/lang}</option>
            <option value="account"{if $type == 'account'} selected="selected"{/if}>{lang}wcf.uztracker.type.account{/lang}</option>
            <option value="profile"{if $type == 'profile'} selected="selected"{/if}>{lang}wcf.uztracker.type.profile{/lang}</option>
            <option value="content"{if $type == 'content'} selected="selected"{/if}>{lang}wcf.uztracker.type.content{/lang}</option>
            <option value="moderation"{if $type == 'moderation'} selected="selected"{/if}>{lang}wcf.uztracker.type.moderation{/lang}</option>
            <option value="page"{if $type == 'page'} selected="selected"{/if}>{lang}wcf.uztracker.type.page{/lang}</option>

            {event name='trackerFilterOption'}
        </select>
    </li>
    <li><span id="jsRefreshButton" class="button small">{lang}wcf.uztracker.button.refresh{/lang}</span></li>
    {if $__wcf->session->getPermission('admin.user.canViewIpAddress')}
        <li><span id="jsConnectionButton" class="button small" data-object-id="{@$user->userID}">{lang}wcf.uztracker.button.connection{/lang}</span></li>
    {/if}
</ul>

<ul id="trackerLogs" class="containerList profileTrackerList" data-last-log-time="{@$lastLogTime}">
    {include file='userProfileTrackerListItem'}
</ul>
