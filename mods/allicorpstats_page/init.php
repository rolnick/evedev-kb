<?php

/**
 * @author Andy Snowden
 * @copyright 2011
 * @version 1.0
 */
 
event::register("allianceDetail_context_assembling", "allicorpstats_view::add");

$modInfo['cc_detail_enhanced']['name'] = "Alliance Corp Stats";
$modInfo['cc_detail_enhanced']['abstract'] = "Shows kill statistics for all corps in an alliance";
$modInfo['cc_detail_enhanced']['about'] = "by <b>MrRx7</b>, Version 1.2 for EDK4.2 (updated by <a href=\"http://gate.eveonline.com/Profile/Salvoxia\">Salvoxia</a>)";

class allicorpstats_view {
	static function add($page)
	{
        $page->addMenuItem( "caption", "Mods:");
		$page->addMenuItem( "link","Alliance Corp Stats", "?a=corp_stats&amp;all_id=" . $page->alliance->getID());
        
	}
}
?>
