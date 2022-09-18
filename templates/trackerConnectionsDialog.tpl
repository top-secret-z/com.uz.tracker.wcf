{if $regIpAddress|isset}
    <section class="section tabularBox">
        <h2 class="sectionTitle">{lang}wcf.uztracker.connection.registration{/lang}</h2>

        <ul>
            <li>{$regIpAddress[ipAddress]} ({$regIpAddress[hostname]})</li>
        </ul>
    </section>
{/if}

{hascontent}
    <section class="section tabularBox">
        <h2 class="sectionTitle">{lang}wcf.uztracker.connection.ipAddresses{/lang}</h2>

        <ul class="containerList">
            {content}
                {foreach from=$ipAddresses item=ipAddress}
                    <li><a href="{link controller='TrackerWhois' isEmail=true}ip={$ipAddress[ipAddress]}{/link}" title="{lang}wcf.uztracker.query.ip{/lang}" class="jsTooltip">{$ipAddress[ipAddress]}</a></li>
                {/foreach}
            {/content}
        </ul>
    </section>
{/hascontent}

{hascontent}
    <section class="section tabularBox">
        <h2 class="sectionTitle">{lang}wcf.uztracker.connection.userAgents{/lang}</h2>

        <ul class="containerList">
            {content}
                {foreach from=$userAgents item=userAgent}
                    <li>{$userAgent}</li>
                {/foreach}
            {/content}
        </ul>
    </section>
{/hascontent}
