<table class="kb-table" width="95%" align="center">
    <tr class="kb-table-header">
      <td class="kb-table-header" colspan="3" align="center">Pilot/Ship/Value</td>
      <td class="kb-table-header" align="center" style="min-width: 45%; width: 45%; max-width: 45%;">Corp/Alliance</td>
    </tr>


{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$valueList item=lossValueEntry}
    <tr class="{cycle name=ccl} br-destroyed">
    <td width="32" height="32" style="max-width: 32px;">
        <a href="{$lossValueEntry.killUrl}"><img src="{$lossValueEntry.victimShipImage}" width="32" height="32" border="0"></a>
    </td>
    <td class="kb-table-cell">
        <b><a href="{$lossValueEntry.victimUrl}">{$lossValueEntry.victimName}</a></b><br/>{$lossValueEntry.victimShipName}
    </td>
    <td class="kb-table-cell {$valueClass}" style="min-width: 20%; width: 20%; max-width: 20%;">
        <b>{$lossValueEntry.lossValue}</b>
        <br/>
        {$lossValueEntry.lossValuePercentage} %
    </td>
    <td class="kb-table-cell">
        <b><a href="{$lossValueEntry.victimCorpUrl}">{$lossValueEntry.victimCorpName}</a></b>
        <br/>
        <a href="{$lossValueEntry.victimAllianceUrl}" style="font-weight: normal;">{$lossValueEntry.victimAllianceName}</a>
    </td>
    </tr>
{/foreach}
</table>
