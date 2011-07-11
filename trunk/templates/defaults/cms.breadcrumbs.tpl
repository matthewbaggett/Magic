
<div class="breadcrumb">
   <ul>
	   {function name=breadcrumb}
	      {foreach from=$nodes item=node}
	        {if $node['selected_path'] == 'yes'}
	        	{if $node['selected'] == 'yes'}
	        		<li>{$node['title']}</li>
	        	{else}
	       	  		<li><a href="{$page->site->sys_root}/Page/view/{$node['path']}">{$node['title']}</a></li>
	       	  	{/if}
	        {/if}
	        {call name=breadcrumb nodes=$node['children']}
	      {/foreach}
	   {/function}
	   {call name=breadcrumb nodes=$page->navigation_structure}
   </ul>
</div>
