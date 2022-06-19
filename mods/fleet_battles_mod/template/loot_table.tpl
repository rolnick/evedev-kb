{cycle reset=true print=false name=ccl values="kb-table-row-even,kb-table-row-odd"}

<table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td width="50%" valign="top">
            <div class="kb-date-header">Dropped</div>
            <br/>
            <table class=kb_table_involved width=100% border=0 cellspacing="1">
                <tr class=kb-table-header>
                    <th colspan="2">Item</th> <th nowrap>Quantity</th> <th nowrap>Value</th> <th nowrap>Total Value</th>
                </tr>
                {foreach from=$lootOverview.dropped.list key=itemName item=l}
                    <tr class="{cycle name=ccl}">
                        <td class=kb-table-cell>
                            <img width="24" height="24" src="{$l.Icon}" />
                        </td>
                        <td class=kb-table-cell>
                            {$itemName}<br/>
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.Quantity}
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.Value}
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.TValue}
                        </td>
                    </tr>
                {/foreach}  
                <tr class="{cycle name=ccl}">
                    <td colspan="4"><b>Total</b></td>
                    <td class="kb-table-cell kl-killIsk" align="right" nowrap>
                        <b>{$lootOverview.dropped.totalValue}</b>
                    </td>
                </tr>
            </table>
        </td>
        <td width="50%" valign="top">
            <div class="kb-date-header">Destroyed</div>
            <br/>
            <table class=kb_table_involved width=100% border=0 cellspacing="1">
                <tr class=kb-table-header>
                    <th colspan="2">Item</th> <th nowrap>Quantity</th> <th nowrap>Value</th> <th nowrap>Total Value</th>
                </tr>
                {foreach from=$lootOverview.destroyed.list key=itemName item=l}
                    <tr class="{cycle name=ccl}">
                        <td class=kb-table-cell>
                            <img width="24" height="24" src="{$l.Icon}" />
                        </td>
                        <td class=kb-table-cell>
                            {$itemName}<br/>
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.Quantity}
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.Value}
                        </td>
                        <td class=kb-table-cell align="right" nowrap>
                            {$l.TValue}
                        </td>
                    </tr>
                {/foreach}  
                <tr class="{cycle name=ccl}">
                    <td colspan="4"><b>Total</b></td>
                    <td class="kb-table-cell kl-lossIsk" align="right" nowrap>
                        <b>{$lootOverview.destroyed.totalValue}</b>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
 
