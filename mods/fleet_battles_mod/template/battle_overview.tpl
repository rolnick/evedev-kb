<div class="tabbertab tabbertabdefault" title="Battle Overview">
    <input type="hidden" id="timestampStart" value="{$firstts}" />
    <input type="hidden" id="timestampEnd" value="{$lastts}" />
    <div id="pilots_and_ships">
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr><td width="49%" valign="top">
                <div class="kb-date-header">Friendly ({$friendlycnt})</div>
                        <br/>

                {assign var='loop' value=$pilots_a}
                {assign var='tipo' value='a'}
                {include file="$battleOverviewTableTemplate"}

                </td><td width="55%" valign="top">
                <div class="kb-date-header">Hostile ({$hostilecnt})</div>
                <br/>

                {assign var='loop' value=$pilots_e}
                {assign var='tipo' value='e'}
                {include file="$battleOverviewTableTemplate"}

                </td>
            </tr>
        </table>
        <br/>
    </div>
</div>