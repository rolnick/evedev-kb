<div id="pilots_and_ships">
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                <tr><td width="49%" valign="top">
                        <div class="kb-date-header">Friendly ({$lcount})</div>
                                <br/>

                        {assign var='valueList' value=$lossValues}
                        {assign var='valueClass' value='kl-loss'}
                        {include file="$battleLossValuesTableTemplate"}

                        </td><td width="55%" valign="top">
                        <div class="kb-date-header">Hostile ({$kcount})</div>
                        <br/>

                        {assign var='valueList' value=$killValues}
                        {assign var='valueClass' value='kl-kill'}
                        {include file="$battleLossValuesTableTemplate"}

                        </td>
                </tr>
        </table>
        <br/>
</div>

