<?php
define("FLEET_BATTLES_MOD_VERSION", "0.2.3");

$modInfo['fleet_battles_mod']['name'] = "Combined Fleet Battle Overview";
$modInfo['fleet_battles_mod']['abstract'] = "Fleet battle overview and strongly enhanced related kills page (balance of power, battle overview, battle timeline, loss toplists, loot overview, JS tabs)";
$modInfo['fleet_battles_mod']['about'] = "battles_mod combined with fleet_battles;<br/>original code by Quebnaric Deile;<br/>updated and enhanced for EDK4 by <a href=\"http://gate.eveonline.com/Profile/Salvoxia\">Salvoxia</a>";

// initialize config
// default: display metrics
if(!config::get('fleet_battles_mod_displaymetrics'))
{
    config::set('fleet_battles_mod_displaymetrics', 1);
}

// default: caching disabled

if(is_null(config::get('fleet_battles_mod_cache')))
{
    config::set('fleet_battles_mod_cache', 0);
}

// default: minum 60 kills+losses
if(is_null(config::get('fleet_battles_mod_minkills')))
{
    config::set('fleet_battles_mod_minkills', 60);
}

// default: minum 100M in kill+lossvalues
if(is_null(config::get('fleet_battles_mod_minisk')))
{
    config::set('fleet_battles_mod_minisk', 100);
}

// default: 12h for determination of related kills
if(is_null(config::get('fleet_battles_mod_maxtime')))
{
    config::set('fleet_battles_mod_maxtime', 12);
}

// default: show timeline
if(is_null(config::get('fleet_battles_mod_showtimeline')))
{
    config::set('fleet_battles_mod_showtimeline', 1);
}

// default: show loss value lists
if(is_null(config::get('fleet_battles_mod_showlossvalues')))
{
    config::set('fleet_battles_mod_showlossvalues', 1);
}

// default: show damage overview
if(is_null(config::get('fleet_battles_mod_damagelists')))
{
    config::set('fleet_battles_mod_damagelists', 1);
}

// default: don't show killlists
if(is_null(config::get('fleet_battles_mod_showkilllists')))
{
    config::set('fleet_battles_mod_showkilllists', 0);
}

// default: don't show loot overview
if(is_null(config::get('fleet_battles_mod_showloot')))
{
    config::set('fleet_battles_mod_showloot', 0);
}

// default: don't use manual side assignment
if(is_null(config::get('fleet_battles_mod_sideassign')))
{
    config::set('fleet_battles_mod_sideassign', 0);
}

// set/update mod version
if (!config::get('fleet_battles_mod_version') || config::get('fleet_battles_mod_version') != FLEET_BATTLES_MOD_VERSION)
	config::set('fleet_battles_mod_version', FLEET_BATTLES_MOD_VERSION);
