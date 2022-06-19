<div>
<div class=kb-campaigns-header>Member Pilot Statistics for {$datefilter} {if $campaign_name} - Campaign: <i>{$campaign_name}</i>{/if}</div> 

{assign var='bgac' value='style="background:url(img/day.gif) repeat scroll 0 0 #003000;"'}

<table class="kb-table" width="99%" align="center" cellspacing="1">
 <tr class="kb-table-header">
	<td class="kb-table-cell" align="center" colspan="2"><a class="corp-stat-header" href="?a={$smarty.get.a}{if $smarty.get.daterange}&daterange={$smarty.get.daterange}{/if}{$ext_link}&order=nameasc&crp_id={$crp_id}{if $w}&w={$w}{/if}{if $m}&m={$m}{/if}{if $y}&y={$y}{/if}{if $ctr_id}&ctr_id={$ctr_id}{/if}{if $no_pos}&no_pos={$no_pos}{/if}">Pilot Name</a></td>
  <td class="kb-table-cell" align="center"><a class="corp-stat-header" href="?a={$smarty.get.a}{if $smarty.get.daterange}&daterange={$smarty.get.daterange}{/if}{$ext_link}&order=killsdesc&crp_id={$crp_id}{if $w}&w={$w}{/if}{if $m}&m={$m}{/if}{if $y}&y={$y}{/if}{if $ctr_id}&ctr_id={$ctr_id}{/if}{if $no_pos}&no_pos={$no_pos}{/if}">Kills</a></td>
  <td class="kb-table-cell" align="center"><a class="corp-stat-header" href="?a={$smarty.get.a}{if $smarty.get.daterange}&daterange={$smarty.get.daterange}{/if}{$ext_link}&order=killiskdesc&crp_id={$crp_id}{if $w}&w={$w}{/if}{if $m}&m={$m}{/if}{if $y}&y={$y}{/if}{if $ctr_id}&ctr_id={$ctr_id}{/if}{if $no_pos}&no_pos={$no_pos}{/if}">ISK (B)</a></td>
  <td class="kb-table-cell" align="center"><a class="corp-stat-header" href="?a={$smarty.get.a}{if $smarty.get.daterange}&daterange={$smarty.get.daterange}{/if}{$ext_link}&order=lossesdesc&crp_id={$crp_id}{if $w}&w={$w}{/if}{if $m}&m={$m}{/if}{if $y}&y={$y}{/if}{if $ctr_id}&ctr_id={$ctr_id}{/if}{if $no_pos}&no_pos={$no_pos}{/if}">Losses</a></td>
  <td class="kb-table-cell" align="center"><a class="corp-stat-header" href="?a={$smarty.get.a}{if $smarty.get.daterange}&daterange={$smarty.get.daterange}{/if}{$ext_link}&order=lossiskdesc&crp_id={$crp_id}{if $w}&w={$w}{/if}{if $m}&m={$m}{/if}{if $y}&y={$y}{/if}{if $ctr_id}&ctr_id={$ctr_id}{/if}{if $no_pos}&no_pos={$no_pos}{/if}">ISK (B)</a></td>
  <td class="kb-table-cell" align="center" colspan=2><a class="corp-stat-header" href="?a={$smarty.get.a}{if $smarty.get.daterange}&daterange={$smarty.get.daterange}{/if}{$ext_link}&order=effdesc&crp_id={$crp_id}{if $w}&w={$w}{/if}{if $m}&m={$m}{/if}{if $y}&y={$y}{/if}{if $ctr_id}&ctr_id={$ctr_id}{/if}{if $no_pos}&no_pos={$no_pos}{/if}">Efficiency</a></td>
 </tr>
 
{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$corppilots item=i}
 <tr class="{cycle advance=false name=ccl}" onmouseover="this.className='kb-table-row-hover';"
    onmouseout="this.className='{cycle name=ccl}';" onClick="window.location.href='{$i.pilotDetailsURL}';">
  <td class="kb-table-imgcell"><img width="32" height="32" src="{$i.pilotPortraitURL}"></img></td>
  <td class="kb-table-cell"><b>{$i.pilotName}</b> <br> </td>
  <td class="kl-kill" align="center">{$i.pilotKills}</td>
  <td class="kl-kill" align="center">{($i.pilotIskKill/1000000)|string_format:"%.2f"}</td>
  <td class="kl-loss" align="center">{$i.pilotLosses}</td>
  <td class="kl-loss" align="center">{($i.pilotIskLoss/1000000)|string_format:"%.2f"}</td>
  <td class="kb-table-cell" align="center" width="50"><b>{$i.pilotEfficiency}%</b></td>
  <td class="kb-table-cell" align="left" width="75">{$i.pilotBar}</td>
 </tr>
{/foreach}
</table>
{*
<i><b>Active members</b> - is member who has any kind of kb history in selected period of time (both kills and losses)</i>
*}
{if $no_n00bs}
<br><i>Noobships, Shuttles and Capsules are removed from statistics.</i>
{/if}
{if $no_pos}
<br><i>POS Modules are removed from statistics.</i>
{/if}
</div>