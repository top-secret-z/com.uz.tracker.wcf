<dl>
    <dt><label for="days">{lang}wcf.uztracker.config.days{/lang}</label></dt>
    <dd>
        <input type="number" id="days" name="days" value="{$days}" class="medium" min="0" />
        <small>{lang}wcf.uztracker.config.days.description{/lang}</small>
    </dd>
</dl>

{assign var=type value=''}
{foreach from=$configPreset key=name item=track}
    {if $name != 'username' && $name != 'userID' && $name != 'isActive' && $name != 'days' && $name != 'time' && $name != 'trackerID'}
        {assign var=typeNew value=$name|substr:0:5}

        <dl>
            <dt>{if $type != $typeNew}{lang}wcf.uztracker.header.type.{$typeNew}{/lang}{/if}</dt>
            <dd>
                <label><input type="checkbox" id="{$name}" name="{$name}" value={$track}{if $track == 1} checked="checked"{/if} /> {lang}wcf.uztracker.config.{$name}{/lang}</label>
            </dd>
        </dl>

        {if $type != $typeNew}{assign var=type value=$typeNew}{/if}
    {/if}
{/foreach}
