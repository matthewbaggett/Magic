{if $settings->late_load_scripts == 1}
	{foreach from=$page->site->scripts item=script}
	<script type="text/javascript" src="{$script}"></script>
	{/foreach}
{/if}