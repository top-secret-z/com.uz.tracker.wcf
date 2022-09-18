{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{$__wcf->getActivePage()->getTitle()} <span class="badge">{#$items}</span>{/capture}

{capture assign='contentInteractionPagination'}
    {assign var='linkParameters' value=''}
    {if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
    {if $type}{capture append=linkParameters}&type={@$type|rawurlencode}{/capture}{/if}

    {pages print=true assign=pagesLinks controller="TrackerLogList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
{/capture}

{capture assign='contentInteractionDropdownItems'}
    {if $objects|count}
        {if $__wcf->session->getPermission('mod.tracking.canModifyTracking')}
            <li><a href="{link controller='TrackerLogDelete' type=$type username=$username}{/link}" onclick="WCF.System.Confirmation.show('{lang}wcf.uztracker.button.log.delete.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;">{lang}wcf.uztracker.button.log.delete{if $username=='' && $type=='all'}.all{/if}{/lang}</a></li>
        {/if}
        <li><a href="{link controller='TrackerLogExport' type=$type username=$username}{/link}">{lang}wcf.uztracker.button.log.export{if $username=='' && $type=='all'}.all{/if}{/lang}</a></li>
    {/if}
{/capture}

{include file='header'}

<form method="post" action="{link controller='TrackerLogList'}{/link}">
    <section class="section">
        <h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>

        <div class="row rowColGap formGrid">
            <dl class="col-xs-12 col-md-4">
                <dt></dt>
                <dd>
                    <input type="text" id="username" name="username" value="{$username}" placeholder="{lang}wcf.user.username{/lang}" class="long">
                </dd>
            </dl>

            <dl class="col-xs-12 col-md-4">
                <dt></dt>
                <dd>
                    <select name="type" id="type">
                        <option value="all"{if $type == 'all'} selected="selected"{/if}>{lang}wcf.uztracker.filter.all{/lang}</option>
                        <option value="users"{if $type == 'users'} selected="selected"{/if}>{lang}wcf.uztracker.type.users{/lang}</option>
                        <option value="account"{if $type == 'account'} selected="selected"{/if}>{lang}wcf.uztracker.type.account{/lang}</option>
                        <option value="profile"{if $type == 'profile'} selected="selected"{/if}>{lang}wcf.uztracker.type.profile{/lang}</option>
                        <option value="content"{if $type == 'content'} selected="selected"{/if}>{lang}wcf.uztracker.type.content{/lang}</option>
                        <option value="moderation"{if $type == 'moderation'} selected="selected"{/if}>{lang}wcf.uztracker.type.moderation{/lang}</option>
                        <option value="page"{if $type == 'page'} selected="selected"{/if}>{lang}wcf.uztracker.type.page{/lang}</option>

                        {event name='trackerFilterOption'}
                    </select>
                </dd>
            </dl>

            <dl class="col-xs-12 col-md-4">
                <dt></dt>
                <dd>
                    <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
                    {csrfToken}
                </dd>
            </dl>
        </div>
    </section>
</form>

{if $objects|count}
    <div class="section tabularBox trackerLogList">
        <table class="table">
            <thead>
                <tr>
                    <th colspan="2" class="columnUser{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='TrackerLogList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.uztracker.username{/lang}</a></th>
                    <th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='TrackerLogList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.uztracker.time{/lang}</a></th>
                    <th class="columnType{if $sortField == 'type'} active {@$sortOrder}{/if}"><a href="{link controller='TrackerLogList'}pageNo={@$pageNo}&sortField=type&sortOrder={if $sortField == 'type' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.uztracker.type{/lang}</a>
                    <th class="columnDescription{if $sortField == 'description'} active {@$sortOrder}{/if}"><a href="{link controller='TrackerLogList'}pageNo={@$pageNo}&sortField=description&sortOrder={if $sortField == 'description' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.uztracker.description{/lang}</a></th>
                    <th class="columnLink{if $sortField == 'link'} active {@$sortOrder}{/if}"><a href="{link controller='TrackerLogList'}pageNo={@$pageNo}&sortField=link&sortOrder={if $sortField == 'link' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.uztracker.linkName{/lang}</a></th>
                    {if $__wcf->session->getPermission('admin.user.canViewIpAddress')}
                        <th class="columnIP{if $sortField == 'ipAddress'} active {@$sortOrder}{/if}"><a href="{link controller='TrackerLogList'}pageNo={@$pageNo}&sortField=ipAddress&sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.uztracker.ipAddress{/lang}</a></th>
                    {/if}

                    {event name='columnHeads'}
                </tr>
            </thead>

            <tbody>
                {foreach from=$objects item=entry}
                    <tr>
                        <td class="columnIcon columnAvatar">
                            <p>{@$entry->getUserProfile()->getAvatar()->getImageTag(48)}</p>
                        </td>

                        <td class="columnUser">
                            <h3>
                                {if $entry->userID}
                                    {user object=$entry->getUserProfile()}
                                {else}
                                    {$entry->username}
                                {/if}
                            </h3>

                            {if $entry->getTracker()}
                                <small>{lang}wcf.uztracker.tracked{/lang}</small>
                            {else}
                                <small>{lang}wcf.uztracker.untracked{/lang}</small>
                            {/if}
                        </td>

                        <td class="columnTime">{@$entry->time|time}</td>
                        <td class="columnType">{lang}{$entry->type}{/lang}</td>

                        {if $entry->content}
                            <td class="columnDescription jsTrackerContentView" data-object-id="{@$entry->trackerLogID}"><a href="#" title="{lang}wcf.uztracker.content.deleted.show{/lang}">{lang}{$entry->description}{/lang}</a></td>
                        {else}
                            <td class="columnDescription">{lang}{$entry->description}{/lang}</td>
                        {/if}

                        {if $entry->link}
                            <td class="columnLink"><a href="{$entry->link}">{$entry->link}</a></td>
                        {else}
                            <td class="columnLink">{lang}{$entry->name}{/lang}</td>
                        {/if}
                        {if $__wcf->session->getPermission('admin.user.canViewIpAddress')}
                            <td class="columnIP"><a href="{link controller='TrackerWhois' isEmail=true}ip={$entry->ipAddress}{/link}">{$entry->ipAddress}</a></td>
                        {/if}
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{else}
    <p class="info">{lang}wcf.uztracker.noEntries{/lang}</p>
{/if}

    <footer class="contentFooter">
        {hascontent}
            <div class="paginationBottom">
                {content}{@$pagesLinks}{/content}
            </div>
        {/hascontent}

        {hascontent}
            <nav class="contentFooterNavigation">
                <ul>
                    {content}

                        {event name='contentFooterNavigation'}
                    {/content}
                </ul>
            </nav>
        {/hascontent}
    </footer>


<script data-relocate="true">
    require(['WoltLabSuite/Core/Ui/User/Search/Input'], function(UiUserSearchInput) {
        new UiUserSearchInput(elBySel('input[name="username"]'));
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

{include file='footer'}
