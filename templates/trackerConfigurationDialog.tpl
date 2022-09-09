<section class="section">
	<h2 class="sectionTitle">{lang}wcf.uztracker.dialog.status{/lang}</h2>
	
	<p>{lang}wcf.uztracker.dialog.status.tracked{/lang}</p>
	<p>{lang}wcf.uztracker.dialog.status.count{/lang}</p>
	{if $lastConfig}
		<p>{lang}wcf.uztracker.dialog.status.time{/lang}</p>
	{/if}
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.uztracker.dialog.general{/lang}</h2>
	
	<dl>
		<dt></dt>
		<dd>
			<label><input type="checkbox" id="isActive" value="1"{if $tracker.isActive} checked="checked"{/if} /> {lang}wcf.uztracker.config.isActive{/lang}</label>
		</dd>
	</dl>
	<dl>
		<dt><label for="days">{lang}wcf.uztracker.config.days{/lang}</label></dt>
		<dd>
			<input type="number" id="days" value="{$tracker.days}" class="medium" min="0" />
			<small>{lang}wcf.uztracker.config.days.description{/lang}</small>
		</dd>
	</dl>
</section>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.uztracker.dialog.tracking{/lang}</h2>
	
	{assign var=type value=''}
	{foreach from=$tracker key=name item=track}
		{if $name != 'username' && $name != 'userID' && $name != 'isActive' && $name != 'days' && $name != 'time' && $name != 'trackerID'}
			{assign var=typeNew value=$name|substr:0:5}
			
			<dl>
				<dt>{if $type != $typeNew}{lang}wcf.uztracker.header.type.{$typeNew}{/lang}{/if}</dt>
				<dd>
					<label><input type="checkbox" id="{$name}" value="1"{if $track} checked="checked"{/if} /> {lang}wcf.uztracker.config.{$name}{/lang}</label>
				</dd>
			</dl>
			
			{if $type != $typeNew}{assign var=type value=$typeNew}{/if}
			
		{/if}
	{/foreach}
</section>

<div class="formSubmit">
	<button class="jsSubmitConfiguration buttonPrimary" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
	<button class="jsDeleteConfiguration" accesskey="s">{lang}wcf.uztracker.button.delete{/lang}</button>
	<input type="hidden" name="userID" id="affectedUserID" value="{$userID}" />
</div>
