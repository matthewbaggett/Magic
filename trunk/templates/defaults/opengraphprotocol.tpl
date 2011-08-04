{if $page->settings->ogp_enable == 1}
<!-- Begin The Open Graph Protocol items -->
<meta property="og:title" content="{sprintf($page->site->title,$page->title)}" />
<meta property="og:site_name" content="{$page->settings->site_name}" />
<meta property="og:description" content="{$page->settings->site_description}" />
<meta property="og:type" content="{$page->settings->ogp_type}" />
<meta property="og:url" content="{MagicUtils::canonical()}" />

{if $page->settings->ogp_location_enable == 1}
<meta property="og:latitude" 		content="{$page->settings->ogp_location_latitude}" />
<meta property="og:longitude" 		content="{$page->settings->ogp_location_longitude}" />
<meta property="og:street-address" 	content="{$page->settings->ogp_location_street_address}" />
<meta property="og:locality" 		content="{$page->settings->ogp_location_locality}" />
<meta property="og:region" 			content="{$page->settings->ogp_location_region}" />
<meta property="og:postal-code" 	content="{$page->settings->ogp_location_postcode}" />
<meta property="og:country-name" 	content="{$page->settings->ogp_location_country}" />
{/if}
<!-- End The Open Graph Protocol items -->
{else}
<!-- Open Graph Protocol headers disabled -->
{/if}