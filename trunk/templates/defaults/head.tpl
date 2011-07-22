<meta charset="utf-8">
<meta name="author" content="TurboCRMS (www.turbocrms.com)" />
<meta name="Generator" content="Turbocrms 3.0 'Magic'">
<meta name="robots" content="INDEX, FOLLOW" />
<meta http-equiv="expires" content="0"/>
<meta http-equiv="pragma" content="no-cache"/>
<meta http-equiv="cache-control" content="no-cache"/>

<!-- Begin The Open Graph Protocol items -->
<meta property="og:title" content="{sprintf($page->site->title,$page->title)}" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{MagicUtils::canonical()}" />
<!-- <meta property="og:image" content="http://ia.media-imdb.com/images/rock.jpg" />-->

   {nocache}
      <title>{sprintf($page->site->title,$page->title)}</title>
   {/nocache}

   {foreach from=$page->site->csses item=css}
      <link href="{$css}" type="text/css" rel="stylesheet">
   {/foreach}

   <script type="text/javascript">
      var app_root = '{$page->site->app_root}';
   </script>

   {foreach from=$page->site->scripts item=script}
      <script type="text/javascript" src="{$script}"></script>
   {/foreach}