<?php
/*
 * $Id $
 */

// cached query class will be loaded additionally once we received the config
// see common/index.php for details
require_once('common/includes/class.db.php');
require_once('common/includes/class.dbfactory.php');
require_once('common/includes/class.config.php');

$value = (float) mysqli_get_server_info(DBConnection::id());

if ($value < 5)
{
	die("EDK 3 requires MySQL version 5.0+. Your version is ".$value);
}

// Check if caching has been configured already.
if(defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true)
{
	if(!defined('DB_MEMCACHE_SERVER') || !defined('DB_MEMCACHE_PORT'))
		die("DB_MEMCACHE_SERVER and DB_MEMCACHE_PORT not defined. Memcache not started.");

	$mc = new Memcache();
	if(!@$mc->pconnect(DB_MEMCACHE_SERVER, DB_MEMCACHE_PORT))
		die("ERROR: Unable to connect to memcached server, disabling
			memcached. Please check your settings (server, port) and make
			sure the memcached server is running");
}
// If DB_USE_QCACHE is defined then it needs no further setup.
else if(defined('DB_USE_QCACHE')) define('DB_USE_MEMCACHE', false);
else
{
	if(!isset($config)) $config = new Config(KB_SITE);

	// DB_HALTONERROR may have been defined externally for sensitive operations.
	if(!defined('DB_HALTONERROR')) define('DB_HALTONERROR', (bool)config::get('cfg_sqlhalt'));

	define('DB_USE_QCACHE', (bool)config::get('cfg_qcache'));

	if (!DB_USE_QCACHE && (bool)config::get('cfg_memcache'))
	{
		if(!method_exists(Memcache, 'pconnect'))
		{
			$boardMessage = "ERROR: Memcache extension not installed. memcaching disabled.";
			define("DB_USE_MEMCACHE", false);
		}
		else
		{
			$mc = new Memcache();
			if(!@$mc->pconnect(config::get('cfg_memcache_server'), config::get('cfg_memcache_port')))
			{
				$boardMessage = "ERROR: Unable to connect to memcached server, disabling memcached. Please check your settings (server, port) and make sure the memcached server is running";
				define("DB_USE_MEMCACHE", false);
			}
			else define("DB_USE_MEMCACHE", true);
		}
	} else
	{
		define("DB_USE_MEMCACHE", false);
	}
}
