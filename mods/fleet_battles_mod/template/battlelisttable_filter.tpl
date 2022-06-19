<form method="POST" action="?a=battles">
    <table class="kb-table" width="98%" align="center" cellspacing="1">
        <tr class="kb-table-header">
            <td class="kb-table-cell" colspan="6" align="left" id="battleFiltersHead" onclick="toggleFilters();" style="cursor: pointer;">[-] Filters</td>
        </tr>
        <tbody id="battleFilters">
            <tr class="kb-table-row-odd">
                <td class="kb-table-cell" width="11%" align="left">Region:</td>
                <td class="kb-table-cell" width="22%" align="left">
                    <select name="filterRegion" style="width: 123px;">
                        {foreach from=$filterRegions item=filterRegion}
                            <option value="{$filterRegion.id}" {if $filterRegionId == $filterRegion.id}selected="selected"{/if}>{$filterRegion.name}</option>
                        {/foreach}
                    </select>
                </td>
                <td class="kb-table-cell" width="11%" align="left">System:</td>
                <td class="kb-table-cell" width="22%" align="left">
                    <input type="text" name="filterSystem" style="width: 123px;" value="{$filterSystem}" />
                </td>
                <td class="kb-table-cell" width="11%" align="left">Month:</td>
                <td class="kb-table-cell" width="22%" align="left">
                    <select name="filterMonth" style="width: 123px;">
                        {foreach from=$filterMonths item=filterMonth}
                            <option value="{$filterMonth.month}" {if $filterMonthSelected == $filterMonth.month}selected="selected"{/if}>{$filterMonth.name}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr class="kb-table-row-odd">
                <td class="kb-table-cell" width="11%" align="left">Kills:</td>
                <td class="kb-table-cell" width="22%" align="left">
                    <select name="filterKillsComparator">
                        {foreach from=$filterKillsComparators item=filterKillsComparator}
                            <option value="{$filterKillsComparator.name}" {if $filterKillsComparatorSelected == $filterKillsComparator.name}selected="selected"{/if}>{$filterKillsComparator.symbol|htmlspecialchars}</option>
                        {/foreach}
                    </select>
                    <input type="text" name="filterKillsCount" style="width: 80px;" value="{$filterKillsCount}" />
                </td>
                <td class="kb-table-cell" width="11%" align="left">Losses:</td>
                <td class="kb-table-cell" width="22%" align="left">
                    <select name="filterLossesComparator">
                        {foreach from=$filterLossesComparators item=filterLossesComparator}
                            <option value="{$filterLossesComparator.name}" {if $filterLossesComparatorSelected == $filterLossesComparator.name}selected="selected"{/if}>{$filterLossesComparator.symbol|htmlspecialchars}</option>
                        {/foreach}
                    </select>
                    <input type="text" name="filterLossesCount" style="width: 80px;" value="{$filterLossesCount}" />
                </td>
                <td class="kb-table-cell" width="11%" align="left">Efficiency:</td>
                <td class="kb-table-cell" width="22%" align="left">
                    <select name="filterEfficiencyComparator">
                        {foreach from=$filterEfficiencyComparators item=filterEfficiencyComparator}
                            <option value="{$filterEfficiencyComparator.name}" {if $filterEfficiencyComparatorSelected == $filterEfficiencyComparator.name}selected="selected"{/if}>{$filterEfficiencyComparator.symbol|htmlspecialchars}</option>
                        {/foreach}
                    </select>
                    <input type="text" name="filterEfficiencyCount" style="width: 80px;" value="{$filterEfficiencyCount}" />
                </td>
            </tr>
            <tr class="kb-table-row-odd">
                <td class="kb-table-cell" width="11%" align="left">Inv Owners:</td>
                <td class="kb-table-cell" width="22%" align="left">
                    <select name="filterInvolvedOwnersComparator">
                        {foreach from=$filterInvolvedOwnersComparators item=filterInvolvedOwnersComparator}
                            <option value="{$filterInvolvedOwnersComparator.name}" {if $filterInvolvedOwnersComparatorSelected == $filterInvolvedOwnersComparator.name}selected="selected"{/if}>{$filterInvolvedOwnersComparator.symbol|htmlspecialchars}</option>
                        {/foreach}
                    </select>
                    <input type="text" name="filterInvolvedOwnersCount" style="width: 80px;" value="{$filterInvolvedOwnersCount}" />
                </td>
                <td class="kb-table-cell" colspan="4" width="67%" align="right"></td>
            </tr>
            <tr class="kb-table-row-odd">
                <td class="kb-table-cell" colspan="6" align="center">
                    <input type="submit" value="Filter" name="filter" />
                    <input type="submit" value="Reset" name="reset" />
                </td>
            </tr>
        </tbody>
    </table>
</form>
<script type="text/javascript" >initFilterToggle();</script>
<br/><br/>