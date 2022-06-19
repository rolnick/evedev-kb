#!/usr/bin/php
<?php
/**
 * check your php folder is correct as defined by the first line of this file
 * 
 * Simple Cronjob script - recommended to run once every 1-2 days
 * this cronjob is part of the Combined Fleet Battles Mod
 * 
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 * @author [FRRGL] Salvoxia
 */

if(function_exists("set_time_limit"))
	@set_time_limit(0);

if (!substr_compare(PHP_OS, 'win', 0, 3, true))
{
	@ini_set('include_path', ini_get('include_path').';.\\common\\includes');
}
else
{
	@ini_set('include_path', ini_get('include_path').':./common/includes');
}

$cronStartTime = microtime(true);
@error_reporting(E_ERROR);

// Has to be run from the KB main directory for nested includes to work
if(file_exists(getcwd().'/cron_fleet_battles_update.php'))
{
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
}
elseif(file_exists(__FILE__))
{
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_idfeed\.php$/', '', __FILE__);
}
else die("Set \$KB_HOME to the killboard root in cron/cron_fleet_battles_update.php.");

// If the above doesn't work - place your working directory path to killboard root below - comment out the above two lines and uncomment the two below

// Edit the path below with your webspace directory to the killboard root folder - also check your php folder is correct as defined by the first line of this file
//$KB_HOME = "/home/yoursite/public_html/kb";

chdir($KB_HOME);

require_once('kbconfig.php');
require_once('common/includes/globals.php');
require_once('common/includes/db.php');
require_once ('common/includes/class.edkerror.php');
require_once('mods/fleet_battles_mod/init.php');
require_once('mods/fleet_battles_mod/include/class.battles.php');


$smarty = new Smarty();
if(!session::isAdmin()) {
	// Disable checking of timestamps for templates to improve performance.
	$smarty->compile_check = false;
}

$themename = config::get('theme_name');
if(!file_exists("themes/".$themename."/".$stylename.".css")) {
	$stylename = 'default';
}

$smarty->template_dir = "./themes/$themename/templates";

if(!is_dir(KB_CACHEDIR.'/templates_c/'.$themename)) {
	mkdir(KB_CACHEDIR.'/templates_c/'.$themename);
}
$smarty->compile_dir = KB_CACHEDIR.'/templates_c/'.$themename;

$smarty->cache_dir = KB_CACHEDIR.'/data';
$smarty->assign('theme_url', THEME_URL);
if ($stylename != 'default' || $themename != 'default') {
	$smarty->assign('style', $stylename);
}


if (config::get('fleet_battles_mod_cache')) {
    $dbq = new DBQuery();
    $system_sql = "select count(*) as cnt, kll_system_id from kb3_kills
                    group by kll_system_id
                    having cnt > ".config::get('fleet_battles_mod_minkills')." order by cnt";
    $dbq->execute($system_sql);

    while($system = $dbq->getRow()) {

        $battlelist = new BattleList((int)$system['kll_system_id']);
        $battlelist->execQuery();

        $table = new BattleListTable($battlelist);

        $table->getTableStats();

        unset($battlelist);
        unset($table);
    } 
    $html .= "Built battle cache with mod version ".config::get("fleet_battles_mod_version").".<br/>";
}
else
{
    $html .= "Caching must be enabled to build the battle cache.<br/>";
}

$html .= "Time taken = ".(microtime(true) - $cronStartTime)." seconds.";

if (php_sapi_name() == 'cli') {
	$html = str_replace("</div>","</div>\n",$html);
	$html = str_replace("<br>","\n",$html);
	$html = str_replace("<br />","\n",$html);
	$html = strip_tags($html);
}
echo $html."\n";
