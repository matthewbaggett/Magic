<div class="page">
    <h1>{$page->cmspage->get_title()}</h1>
    <div class="metadata">
        {t}Written by{/t} <a href="#" class="author">{$page->cmspage->get_parent_user()->get_firstname()} {$page->cmspage->get_parent_user()->get_surname()}</a>, <time datetime="{date(DateTime::ATOM,$page->cmspage->get_timestamp())}" title="{date('l jS \of F Y h:i:s A',$page->cmspage->get_timestamp())}">{MagicUtils::fuzzyTime($page->cmspage->get_timestamp())}</time>
    </div>
    <div class="text_area">
        {$page->cmspage->get_content()}
    </div>
</div>
