<table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td colspan="3" style="min-width: 45%; width: 45%; max-width: 45%;">
            <div class="kb-date-header" align="left">Friendly ({$lcount})</div>
            <br/>
        </td>
        <td align="center" style="min-width: 10%; width: 10%; max-width: 10%;"></td>
        <td colspan="3">
            <div class="kb-date-header" align="left">Hostile ({$kcount})</div>
            <br/>
        </td>
    </tr>
</table>
<table class="kb-table" width="97%" align="center">
    <tr class="kb-table-header">
        <td class="kb-table-header" colspan="2" align="center" style="min-width: 20%; width: 20%; max-width: 20%;">Pilot/Ship</td>
        <td class="kb-table-header" align="center" style="min-width: 23%; width: 23%; max-width: 23%;">Corp/Alliance</td>
        <td class="kb-table-header" align="center" style="min-width: 10%; width: 10%; max-width: 11%;">Time</td>
        <td class="kb-table-header" colspan="2" align="center" style="min-width: 20%; width: 20%; max-width: 20%;">Pilot/Ship</td>
        <td class="kb-table-header" align="center" style="min-width: 23%; width: 23%; max-width: 23%;">Corp/Alliance</td>
    </tr>


{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$timeline item=timelineEntry}
    <tr class="{cycle name=ccl}">
        {if !is_null($timelineEntry.loss)}
            <td class="br-destroyed" width="32" height="32" style="max-width: 32px; width: 32px;">
            <a href="{$timelineEntry.loss.killUrl}"><img src="{$timelineEntry.loss.victimShipImage}" width="32" height="32" border="0"></a>
            </td>
            <td class="kb-table-cell br-destroyed" >
            <b><a href="{$timelineEntry.loss.victimUrl}">{$timelineEntry.loss.victimName}</a></b><br/>{$timelineEntry.loss.victimShipName}
            </td>
            <td class="kb-table-cell br-destroyed" >
            <b><a href="{$timelineEntry.loss.victimCorpUrl}">{$timelineEntry.loss.victimCorpName}</a></b>
            <br/>
            <a href="{$timelineEntry.loss.victimAllianceUrl}" style="font-weight: normal;">{$timelineEntry.loss.victimAllianceName}</a>
            </td>
        {else}
        <td colspan="3"></td>
        {/if}

    <td class="kb-table-cell" align="center">{$timelineEntry.timestamp|date_format:"%H:%M:%S"}</td>

    {if !is_null($timelineEntry.kill)}
            <td class="br-destroyed" width="32" height="32" style="max-width: 32px; width: 32px;">
            <a href="{$timelineEntry.kill.killUrl}"><img src="{$timelineEntry.kill.victimShipImage}" width="32" height="32" border="0"></a>
            </td>
            <td class="kb-table-cell br-destroyed" >
            <b><a href="{$timelineEntry.kill.victimUrl}">{$timelineEntry.kill.victimName}</a></b><br/>{$timelineEntry.kill.victimShipName}
            </td>
            <td class="kb-table-cell br-destroyed">
            <b><a href="{$timelineEntry.kill.victimCorpUrl}">{$timelineEntry.kill.victimCorpName}</a></b>
            <br/>
            <a href="{$timelineEntry.kill.victimAllianceUrl}" style="font-weight: normal;">{$timelineEntry.kill.victimAllianceName}</a>
            </td>
        {else}
        <td colspan="3"></td>
        {/if}
{/foreach}
</table>
