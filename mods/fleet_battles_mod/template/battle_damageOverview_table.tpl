<table class="kb-table" width="95%" align="center">
    <tr class="kb-table-header">
      <td class="kb-table-header" colspan="3" align="center">Pilot/Ship/Damage</td>
      <td class="kb-table-header" align="center" style="min-width: 45%; width: 45%; max-width: 45%;">Corp/Alliance</td>
    </tr>


{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}
{foreach from=$damageToplist key=pilotId item=damageToplistEntry}
    {assign var=pilot value=$damageToplistEntry[0]}
    {if $pilot.damage > 0}
        <tr class="{cycle name=ccl}">
            <td width="32" height="32" style="max-width: 32px;">
                <img src="{$pilot.spic}" width="32" height="32" border="0">
            </td>
            <td class="kb-table-cell">
                <b><a href="{$pilot.plt_url}">{$pilot.name}</a></b><br/>{$pilot.ship}
            </td>
            <td class="kb-table-cell kl-lossIsk" style="min-width: 20%; width: 20%; max-width: 20%;">
                <b>{$pilot.damage|number_format:0:".":","}</b>
                {if $damageTotal > 0}
                    <br/>
                    {assign var="damagePilot" value=$pilot.damage}
                    {math equation="(x/y)*100" x=$damagePilot y=$damageTotal format="%.2f"} %
                {/if}
            </td>
            <td class="kb-table-cell">
                <b><a href="{$pilot.crp_url}">{$pilot.corp}</a></b>
                <br/>
                <a href="{$pilot.alliance_url}" style="font-weight: normal;">{$pilot.alliance}</a>
            </td>
        </tr>
    {/if}
{/foreach}
</table>
