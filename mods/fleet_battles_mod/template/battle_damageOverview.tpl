<div id="damageOverviewList">
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                <tr><td width="49%" valign="top">
                        <div class="kb-date-header">Friendly ({$friendlycnt})</div>
                                <br/>

                        {assign var='damageToplist' value=$pilotsAllied}
                        {assign var='damageTotal' value=$damageTotalFriendly}
                        {include file="$damageOverviewTableTemplate"}

                        </td><td width="55%" valign="top">
                        <div class="kb-date-header">Hostile ({$hostilecnt})</div>
                        <br/>

                        {assign var='damageToplist' value=$pilotsHostile}
                        {assign var='damageTotal' value=$damageTotalHostile}
                        {include file="$damageOverviewTableTemplate"}

                        </td>
                </tr>
        </table>
        <br/>
</div>

