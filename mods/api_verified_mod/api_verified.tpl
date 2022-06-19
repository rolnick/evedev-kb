<table class="kb-table" width="150" cellspacing="1">
  <tr>
    <td class="kb-table-header" colspan="2" align="center">API Verification</td>
  </tr>
  <tr class="kb-table-row-even">
    {if $api_verified_status}
    <td align="center" width="64"><img src="{$kb_host}/mods/api_verified_mod/img/yes_1.png" title="Kill verified, ID: {$api_verified_id}" /></td>
    {else}
    <td align="center" width="64"><img src="{$kb_host}/mods/api_verified_mod/img/no_1.png" title="Kill not verified!" /></td>
    {/if}
  </tr>
</table>
<table class="kb-table" width="150" cellspacing="1" id="source">
  <tr>
    <td class="kb-table-header" colspan="2" align="center">Source</td>
  </tr>
  <tr class="kb-table-row-even">
    {if $type == "API"}
    <td align="center" width="64"><img src="{$kb_host}/mods/api_verified_mod/img/source/api.png" title="Kill verified, ID: {$source}, Date: {$postedDate}" /></td>
    {else if $type == "IP"}
    <td align="center" width="64"><img src="{$kb_host}/mods/api_verified_mod/img/source/ip.png" title="Manually posted on {$postedDate}{if $source} from {$source}{/if}" /></td>
    {else if $type == "URL"}
    <td align="center" width="64"><a href="{$source}"><img src="{$kb_host}/mods/api_verified_mod/img/source/url.png" title="Fetched on {$postedDate}" /></a></td>{/if}
  </tr>
</table>
