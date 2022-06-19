<table class="kb-table" width="98%" align="center" cellspacing="1">
    <tr class="kb-table-header">
        <td class="kb-table-cell" width="15%" align=center>Total Battles</td>
        <td class="kb-table-cell" width="17%" align="center">Avg. Involved</td>
        <td class="kb-table-cell" width="17%" align="center">Avg. Inv. Owners</td>
        <td class="kb-table-cell" width="15%" align="center">Avg. Kills</td>
        <td class="kb-table-cell" width="15%" align="center">Avg. Losses</td>
        <td class="kb-table-cell" width="21%" align="center" colspan="2">Avg. Efficiency (ISK)</td>
    </tr>
    <tr class="kb-table-row-odd" onmouseover="this.className='kb-table-row-hover';"
    onmouseout="this.className='kb-table-row-odd';">
        <td class="kb-table-cell" align="center">{$tbattles}</td>
        <td class="kb-table-cell" align="center">{$ainvolved|string_format:"%d"}</td>
        <td class="kb-table-cell" align="center">{$ainvolvedowners|string_format:"%d"}</td>
        <td class="kl-kill" align="center">{$akills|string_format:"%d"}</td>
        <td class="kl-loss" align="center">{$alosses|string_format:"%d"}</td>
        <td class="kb-table-cell" align="center" width="40"><b>{$aefficiency}</b></td>
        <td class="kb-table-cell" align="left" width="75">{$abar}</td>
    </tr>
</table>
<br/><br/>
<table class="kb-table" width="98%" align="center" cellspacing="1">
    <tr class="kb-table-header"><td class="kb-table-cell" width="200">Date</td>
        <td class="kb-table-cell" width="80" align=center>System</td>
        <td class="kb-table-cell" width="45" align="center">Inv</td>
        <td class="kb-table-cell" width="30" align="center">#</td>
        <td class="kb-table-cell" width="50" align="center">Kills</td>
        <td class="kb-table-cell" width="70" align="center">ISK (B)</td>
        <td class="kb-table-cell" width="50" align="center">Losses</td>
        <td class="kb-table-cell" width="70" align="center">ISK (B)</td>
        <td class="kb-table-cell" width="70" align="center" colspan=2>Efficiency</td>
    </tr>
    {cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
    {foreach from=$battles item=i}
    <tr class="{cycle advance=false name=ccl}" onmouseover="this.className='kb-table-row-hover';"
        onmouseout="this.className='{cycle name=ccl}';" onClick="window.location.href='{$i.battle_url}';"> 
        <td class="kb-table-cell">{$i.startdate} - {$i.endtime}</td>
        <td class="kb-table-cell" align="center"><b>{$i.name}</b></td>
        <td class="kb-table-cell" align="center">{$i.involved}</td>
        <td class="kb-table-cell" align="center">{$i.numberOfOwnersInvolved}</td>
        <td class="kl-kill" align="center">{$i.kills}</td>
        <td class="kl-kill" align="center">{($i.killisk/1000000)|string_format:"%.2f"}</td>
        <td class="kl-loss" align="center">{$i.losses}</td>
        <td class="kl-loss" align="center">{($i.lossisk/1000000)|string_format:"%.2f"}</td>
        <td class="kb-table-cell" align="center" width="40"><b>{$i.efficiency}</b></td>
        <td class="kb-table-cell" align="left" width="75">{$i.bar}</td>
    </tr>
    {/foreach}
</table>
