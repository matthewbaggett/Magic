<div class="page">
    <h1>{$page->oPage->get_title()}</h1>
    <div class="metadata">
        {t}Written by{/t} <a href="#" class="author">{$page->oPage->get_parent_user()->get_firstname()} {$page->oPage->get_parent_user()->get_surname()}</a>, <time datetime="{date(DateTime::ATOM,$page->oPage->get_timestamp())}" title="{date('l jS \of F Y h:i:s A',$page->oPage->get_timestamp())}">{MagicUtils::fuzzyTime($page->oPage->get_timestamp())}</time>
    </div>
    <textarea style="width: 500px; height: 200px;">
        {$page->oPage->get_content()}
    </textarea>
    <div class="cblocks">
    	{$page->oPage->get_content()|transform_xslt:"cblocks.xsl"}
    </div>
</div>
