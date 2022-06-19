<table class="kb-table" width="95%" align="center">
    <tr class="kb-table-header">
      <td class="kb-table-header" colspan="3" align="center">Pilot/Ship/Kills</td>
      <td class="kb-table-header" align="center" style="min-width: 45%; width: 45%; max-width: 45%;">Corp/Alliance</td>
    </tr>


{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$loop item=a key=pilot}
{foreach from=$a item=i key=b}
    <tr class="{cycle name=ccl} {if $i.destroyed}br-destroyed{/if}">
      <td width="32" height="32" style="max-width: 32px;">
{if $i.destroyed}
        <a href="{$i.kll_url}"><img src="{$i.spic}" width="32" height="32" border="0"></a>
{else}
        <img src="{$i.spic}" width="32" height="32" border="0">
{/if}
      </td>

{if $i.podded}
    {if $config->get('bs_podlink')}
      <td class="kb-table-cell">
        <b><a href="{$i.plt_url}">{$i.name}</a>&nbsp;<a href="{$i.pod_url}">[Pod]</a></b><br/>{$i.ship}
      </td>
    {else}
      <td class="kb-table-cell">
          <div style="float: right;"><a href="{$i.pod_url}" ><img src="{$podpic}" width="32" height="32"></a></div>
          <div style="position: absolute;"><b>
            <a href="{$i.plt_url}">{$i.name}</a>
            </b><br/>{$i.ship}
          </div>
      </td>
    {/if}
    <td class="kb-table-cell" align="right">N°&nbsp;{$i.times}<br/>       
    {if $tipo =='a'}
        {if $kcount ==0}
            0&nbsp;%
        {else}
            {($i.times*100/$kcount)|string_format:"%.1f"}&nbsp;%
        {/if}
    {else}
        {if $lcount ==0}0&nbsp;%{else}{($i.times*100/$lcount)|string_format:"%.1f"}&nbsp;%{/if}  
    {/if}</td>

{else}
    <td class="kb-table-cell"><b><a href="{$i.plt_url}">{$i.name}</a></b><br/>{$i.ship} </td> 
    <td class="kb-table-cell" align="right">N°&nbsp;{$i.times}<br/>
    {if $tipo =='a'}
        {if $kcount ==0}
            0&nbsp;%
        {else}
            {($i.times*100/$kcount)|string_format:"%.1f"}&nbsp;%
        {/if}
    {else}
        {if $lcount ==0}0&nbsp;%{else}{($i.times*100/$lcount)|string_format:"%.1f"}&nbsp;%{/if}  
    {/if}</td>
{/if}
      <td class="kb-table-cell"><b><a href="{$i.crp_url}">{$i.corp}</a></b><br/><a href="{$i.alliance_url}" style="font-weight: normal;">{$i.alliance}</a></td>
    </tr>
{/foreach}
{/foreach}
</table>
