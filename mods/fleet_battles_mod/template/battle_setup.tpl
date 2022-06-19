<div class="tabbertab" title="Setup">
    <form id="battle_setup" name="battle_setup" method="post" action="">
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="49%" valign="top">
                    <table class=kb_table_involved_big width=100% border=0 cellspacing="1" id="alliedTable">
                            <tr class="kb-table-header friendlyParty">
                                    <th colspan="3">Friendly</th>
                            </tr>  
                            {* invovled parties will be inserted by javascript*}
                    </table>
                </td>
                <td width="49%" valign="top">
                    <table class=kb_table_involved_big width=100% border=0 cellspacing="1" id="hostileTable">
                            <tr class="kb-table-header hostileParty">
                                    <th colspan="3">Hostile</th>
                            </tr>  
                            {* invovled parties will be inserted by javascript*}
                    </table>
                </td>
            </tr>
        </table>
        <input type="hidden" name="systemIds" value="{$systemIds}" />
        <input type="hidden" name="timestampStart" value="{$firstts}" />
        <input type="hidden" name="timestampEnd" value="{$lastts}" />
        <input type="hidden" name="numberOfInvolvedOwners" value="{$numberOfInvolvedOwners}" />
        <input type="hidden" name="involvedOwners" value="{$involvedOwners}" />
        <hr/>
        <input type="submit" name="saveBattleSetup" value="save" />
        <input type="submit" name="deleteSideAssignments" value="reset" onclick="return confirm('Do you really want to delete all side assignments?')" />
    </form>
    <script type="text/javascript" >
    {foreach from=$sideAllied key=entityName item=entityInfo}
        FleetBattles.addEntityToSide(new Entity({$entityInfo.id}, '{$entityInfo.type}', '{$entityName}', '{$entityInfo.logoUrl}', '{$entityInfo.infoUrl}', {$entityInfo.numberOfPilots}, 'a'), 'a');
    {/foreach}

    {foreach from=$sideHostile key=entityName item=entityInfo}
        FleetBattles.addEntityToSide(new Entity({$entityInfo.id}, '{$entityInfo.type}', '{$entityName}', '{$entityInfo.logoUrl}', '{$entityInfo.infoUrl}', {$entityInfo.numberOfPilots}, 'e'), 'e');
    {/foreach}
    </script>
</div>