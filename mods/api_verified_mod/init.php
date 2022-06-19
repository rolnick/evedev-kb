<?php
	if(!defined('KB_SITE')) die ("Go Away!");
	
	$modInfo['api_verified_mod']['name'] = "Api verified Mod";
	$modInfo['api_verified_mod']['abstract'] = "Show if a kill is api verified or not and where its from.";
	$modInfo['api_verified_mod']['about'] = "by <a href=\"http://www.back-to-yarrr.de\" target=\"_blank\">Sir Quentin</a>";

	event::register("killDetail_context_assembling", "api_verified::add");

	class api_verified
	{
		public static function add($page)
	{
		$page->addBehind("points", "api_verified::show");
	}
  
  	public static function show(){
  	global $smarty;
 		include_once('mods/api_verified_mod/api_verified.php');
  	$html .= $smarty->fetch("../../../mods/api_verified_mod/api_verified.tpl");
    return $html;
  }
}
?>
