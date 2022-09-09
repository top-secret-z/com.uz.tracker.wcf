{if MODULE_TRACKER && $__wcf->session->getPermission('mod.tracking.canModifyTracking') && !$user->getPermission('mod.tracking.noTracking')}
	<li class="jsTrackerListConfig jsOnly" data-object-id="{@$user->userID}"><a href="#" title="{lang}wcf.uztracker.button.profile.text{/lang}" class="button {if $user->isTracked}active {/if}jsTooltip"><span class="icon icon16 fa-hdd-o"></span></a></li>
{/if}