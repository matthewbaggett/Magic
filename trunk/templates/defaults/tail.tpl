
{if $page->settings->late_load_scripts == 1}
	<!-- Late loading scripts... -->
	{foreach from=$page->site->scripts item=script}
	<script type="text/javascript" src="{$script}"></script>
	{/foreach}
{else}
	<!-- Not late loading scripts... -->
{/if}