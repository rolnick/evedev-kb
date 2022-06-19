<div class="kb-kills-header">Enemy's loot</div>
<div id="enemy_loot">
    {assign var='lootOverview' value=$lootHostile}
    {include file="$lootTableTemplate"}
</div>
<br/>
<div class="kb-kills-header">Friendly's loot</div>
<div id="friendly_loot">
    {assign var='lootOverview' value=$lootFriendly}
    {include file="$lootTableTemplate"}
</div>
