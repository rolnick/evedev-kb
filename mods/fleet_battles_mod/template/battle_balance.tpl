<!-- table for friendly parties -->
<table class=kb_table_involved_big width=100% border=0 cellspacing="1">
    <tr> 
        <th class="friendlyParty" colspan=3>Friendly ({$numberOfFriendlyPilots})</th>
    </tr>
    <tr class=kb-table-header>
        <th>Alliances</th> <th>Corporations</th> <th>Ships (Number/destroyed)</th>
    </tr>  

    {assign var="first" value="true"}

    {foreach from=$GoodAllies key=allianceName item=l}
        <tr class=kb-table-row-even>
            <td class=kb-table-cell>
                ({$l.quantity} / {$l.percentage} %) {$allianceName} <br/>
            </td>
            <td class=kb-table-cell>
                {foreach from=$l.corps key=corpName item=numberOfPilots}
                    ({$numberOfPilots}) {$corpName} <br/>
                {/foreach}  
            </td>       
            {if $first == "true"}
                <td rowspan={$GAlliesCount} class=kb-table-cell NOWRAP>
                    {foreach from=$GoodShips key=shipType item=l}
                        <font class={$l.color}>({$l.times})({$l.destroyed}) {$shipType} ({$l.shipClass}) </font><br/>
                    {/foreach}  
                </td>
                {assign var="first" value="false"}
            {/if}
        </tr>
    {/foreach}
</table>

<br/>

<!-- table for hostile parties -->
<table class=kb_table_involved_big width=100% border=0 cellspacing="1">
    <tr>
            <th class="hostileParty" colspan=3>Hostile ({$numberOfHostilePilots})</th>
    </tr>
    <tr class=kb-table-header>
            <th>Alliances</th> <th>Corporations</th> <th>Ships (Number/destroyed)</th>
    </tr>  

    {assign var="first" value="true"}

    {foreach from=$BadAllies key=allianceName item=l}
        <tr class=kb-table-row-even>
            <td class=kb-table-cell>
                ({$l.quantity} / {$l.percentage} %) {$allianceName} <br/>
            </td>
            <td class=kb-table-cell>
                {foreach from=$l.corps key=corpName item=numberOfPilots}
                    ({$numberOfPilots}) {$corpName} <br/>
                {/foreach}  
            </td>       
            {if $first == "true"}
                <td rowspan={$BAlliesCount} class=kb-table-cell NOWRAP>
                    {foreach from=$BadShips key=shipType item=l}
                        <font class={$l.color}>({$l.times})({$l.destroyed}) {$shipType} ({$l.shipClass}) </font><br/>
                    {/foreach}  
                </td>
                {assign var="first" value="false"}
            {/if}
        </tr>
    {/foreach}
</table>
