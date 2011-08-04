{if $page->settings->ogp_enable == 1}
	<!-- Begin The Open Graph Protocol items -->
	<meta property="og:title" content="{sprintf($page->site->title,$page->title)}" />
	<meta property="og:type" content="{$page->settings->ogp_type}" />
	<meta property="og:url" content="{MagicUtils::canonical()}" />
	<!-- End The Open Graph Protocol items -->
{else}
	<!-- Open Graph Protocol headers disabled -->
{/if}