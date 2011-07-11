<div id="nav">
	<ul id="subnav">
		
		
		{function name=draw_navigation_row}
				<li><a href="{$page->site->sys_root}/Page/view/{$node->get_path()}">{$node->get_child_page_version()->get_title()}</a></li>
				{if $node->has_child_pages()}
					{foreach from=$node->get_child_pages() item=child}
						{call name=draw_navigation_row node=$child}
					{/foreach}
				{/if}
		{/function}
		
		{function name=draw_navigation}
			{foreach from=$nav item=page_node}
				{call name=draw_navigation_row node=$page_node}
			{/foreach} 
		{/function}
		
		{call name=draw_navigation nav=$page->navigation}
		
	</ul>
</div>

