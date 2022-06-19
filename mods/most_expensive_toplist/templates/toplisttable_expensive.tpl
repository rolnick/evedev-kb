<!-- toplisttable.tpl -->
<link rel="stylesheet" type="text/css" href="mods/most_expensive_toplist/css/expensive_toplist.css">
<table class='kb-table toplist-table'>
	<col class="kb-table-imgcell-wide"/>
	<col class="toplist-name"/>
	<col class="toplist-rank"/>
	<tr class='kb-table-header'>
		<td colspan='2'>{$tl_name}</td>
		<td class="toplist-expensive-value">{$tl_type}</td>
	</tr>
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{section name=i loop=$tl_rows}
	<tr class='{cycle name=ccl}'>
		<td class="kb-table-imgcell-wide">
			{if $tl_rows[i].portrait}
                            {if $tl_rows[i].uri}
                                <a href="{$tl_rows[i].uri}">
                                    <img src="{$tl_rows[i].portrait}" alt="{$tl_rows[i].name}" style="width: 32px;" />
                                </a>
                            {else}
                                <img src="{$tl_rows[i].portrait}" alt="{$tl_rows[i].name}" style="width: 32px;" />
                            {/if}
			{else}
                            {$tl_rows[i].icon}
                        {/if}
                        {if $tl_rows[i].shipImage}
                            {if $tl_rows[i].uri}
                                <a href="{$tl_rows[i].uri}">
                                    <img src="{$tl_rows[i].shipImage}" alt="{$tl_rows[i].shipName}" style="width: 32px;" />
                                </a>
                            {else}
                                <img src="{$tl_rows[i].shipImage}" alt="{$tl_rows[i].shipName}" style="width: 32px;" />
                            {/if}
                        {/if}
		</td>
		<td>
			{if $tl_rows[i].rank}{$tl_rows[i].rank}.&nbsp;{/if}{if isset($tl_rows[i].subname)}{/if}{if $tl_rows[i].uri}<a class='kb-shipclass' href="{$tl_rows[i].uri}">{$tl_rows[i].name}</a>{else}{$tl_rows[i].name}{/if}{if $tl_rows[i].subname}{/if}
			{if isset($tl_rows[i].subname)}<br />{$tl_rows[i].subname}{/if}
                        {if $tl_rows[i].shipURI}<br/><a class='kb-shipclass' href="{$tl_rows[i].shipURI}">{$tl_rows[i].shipName}</a>{else}{$tl_rows[i].shipName}{/if}
		</td>
		<td class="{$value_class}">
			{$tl_rows[i].isk}
		</td>
	</tr>
{/section}
</table>
<!-- /toplisttable.tpl -->
