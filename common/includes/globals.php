<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

// current subversion revision
$svnrevision = '$Revision$';
$svnrevision = trim(substr($svnrevision, 10, strlen($svnrevision)-11));

define('SVN_REV', "Dev ".$svnrevision);

if(!defined('LATEST_DB_UPDATE')) define('LATEST_DB_UPDATE',"022");

define('KB_CACHEDIR', 'cache');
define('KB_PAGECACHEDIR', KB_CACHEDIR.'/page');
define('KB_MAILCACHEDIR', KB_CACHEDIR.'/mails');
define('KB_QUERYCACHEDIR', KB_CACHEDIR.'/SQL');
define('KB_UPDATE_URL', 'http://evedev-kb.googlecode.com/files');
define('API_SERVER', "api.eveonline.com");
define('IMG_SERVER', "image.eveonline.com");
define ("APIVERSION", "V3.3");

// current version: major.minor.sub
// even numbers for minor = development version
define('KB_VERSION', '3.2.1');
define('KB_RELEASE', '(Incursion)');

// Make sure the core functions are loaded.
require_once('common/includes/class.edkloader.php');
// Set up the external class files with the autoloader.
edkloader::register('Smarty', 'common/smarty/Smarty.class.php');

// Ugly hacks to make things work until other changes are made with the file structure
edkloader::register('APIChar', 'common/includes/api/class.char.php');
edkloader::register('AllianceAPI', 'common/includes/api/class.alliance.php');
edkloader::register('BarGraph', 'common/includes/class.graph.php');
edkloader::register('TopList', 'common/includes/class.toplist.php');
edkloader::register('TopKillsList', 'common/includes/class.toplist.php');
edkloader::register('TopCorpKillsList', 'common/includes/class.toplist.php');
edkloader::register('TopScoreList', 'common/includes/class.toplist.php');
edkloader::register('TopLossesList', 'common/includes/class.toplist.php');
edkloader::register('TopCorpLossesList', 'common/includes/class.toplist.php');
edkloader::register('TopFinalBlowList', 'common/includes/class.toplist.php');
edkloader::register('TopDamageDealerList', 'common/includes/class.toplist.php');
edkloader::register('TopSoloKillerList', 'common/includes/class.toplist.php');
edkloader::register('TopPodKillerList', 'common/includes/class.toplist.php');
edkloader::register('TopGrieferList', 'common/includes/class.toplist.php');
edkloader::register('TopCapitalShipKillerList', 'common/includes/class.toplist.php');
edkloader::register('TopContractKillsList', 'common/includes/class.toplist.php');
edkloader::register('TopContractScoreList', 'common/includes/class.toplist.php');
edkloader::register('TopPilotTable', 'common/includes/class.toplist.php');
edkloader::register('TopCorpTable', 'common/includes/class.toplist.php');
edkloader::register('TopShipList', 'common/includes/class.toplist.php');
edkloader::register('TopShipListTable', 'common/includes/class.toplist.php');
edkloader::register('TopWeaponList', 'common/includes/class.toplist.php');
edkloader::register('TopWeaponListTable', 'common/includes/class.toplist.php');
edkloader::register('thumbInt', 'common/includes/class.thumb.php');

require_once('common/includes/db.php');

/* ---------------------------------------------------------------- */
/* Utility functions */

function shorten($shorten, $by = 22)
{
	if (strlen($shorten) > $by)
	{
		$s = substr($shorten, 0, $by) . "...";
	}
	else $s = $shorten;

	return $s;
}

function slashfix($fix)
{
	return addslashes(stripslashes($fix));
}

function roundsec($sec)
{
	if ($sec <= 0)
		$s = 0.0;
	else
		$s = $sec;

	return number_format(round($s, 1), 1);
}
//! Check if a version of this template exists in this theme or for the igb.

/*! If client is igb check if theme has an igb version. If not check in default
 *  theme for one. If client is not igb check if the theme has the template.
 *  If not then again return the default template.
 *
 *  \param $name string containing the name of the template.
 */
function get_tpl($name)
{
	global $themename;
	event::call('get_tpl', $name);

	if($themename == 'default')
	{
		if (IS_IGB)
		{
			if (file_exists('./themes/default/templates/igb_'.$name.'.tpl'))
			{
				return 'igb_'.$name.'.tpl';
			}
		}
		return $name.'.tpl';
	}
	if (IS_IGB)
	{
		if(file_exists('./themes/'.$themename.'/templates/igb_'.$name.'.tpl'))
		{
			return 'igb_'.$name.'.tpl';
		}
		elseif(file_exists('./themes/default/templates/igb_'.$name.'.tpl'))
		{
			return '../../default/templates/igb_'.$name.'.tpl';
		}
	}
	if(file_exists('./themes/'.$themename.'/templates/'.$name.'.tpl'))
	{
		return $name.'.tpl';
	}
	elseif(file_exists('./themes/default/templates/'.$name.'.tpl'))
	{
		return '../../default/templates/'.$name.'.tpl';
	}
	return $name.'.tpl';
}

// this is currently only a wrapper but might get
// timestamp adjustment options in the future
function kbdate($format, $timestamp = null)
{
	if ($timestamp === null)
	{
		$timestamp = time();
	}

	if (config::get('date_gmtime'))
	{
		return gmdate($format, $timestamp);
	}
	return date($format, $timestamp);
}

function getYear()
{
	$test = kbdate('o');
	if ($test == 'o')
	{
		$test = kbdate('Y');
	}
	return $test;
}

//! Return the number of weeks in the given year.

/*! \param $year the year to count weeks for. Default is the current year.
 *  \return the number of weeks in the given year.
 */
function getWeeks($year = null)
{
	if(is_null($year)) $year = getYear();
	$weeks = date('W', mktime(1, 0, 0, 12, 31, $year));
	return $weeks == 1 ? 52 : $weeks;
}
//! Return start date for the given week, month, year or date.

/*!
 * weekno > monthno > startWeek > yearno
 * weekno > monthno > yearno
 * startDate and endDate are used if they restrict the date range further
 * monthno, weekno and startweek are not used if no year is set
 */
function makeStartDate($week = 0, $year = 0, $month = 0, $startweek = 0, $startdate = 0)
{
	$qstartdate=0;
	if(intval($year)>2000)
	{
		if($week)
		{
			if($week < 10) $week = '0'.$week;
			$qstartdate = strtotime($year.'W'.$week.' UTC');
		}
		elseif($month)
			$qstartdate = strtotime($year.'-'.$month.'-1 00:00 UTC');
		elseif($startweek)
		{
			$qstartdate = strtotime($year.'W'.$startweek.' UTC');
		}
		else
			$qstartdate = strtotime($year.'-1-1 00:00 UTC');
	}
	//If set use the latest startdate and earliest enddate set.
	if($startdate && $qstartdate < strtotime($startdate." UTC")) $qstartdate = strtotime($startdate." UTC");
	return $qstartdate;
}

//! Return end date for the given week, month, year or date.

/*!
 *  Priority order of date filters:
 * weekno > monthno > startWeek > yearno
 * weekno > monthno > yearno
 * startDate and endDate are used if they restrict the date range further
 * monthno, weekno and startweek are not used if no year is set
 */
function makeEndDate($week = 0, $year = 0, $month = 0, $enddate = 0)
{
	if($year)
	{
		if($week)
		{
			if($week < 10) $week = '0'.$week;
			$qenddate = strtotime($year.'W'.$week.' +7days -1second UTC');
		}
		else if($month)
			{
				if($month == 12) $qenddate = strtotime(($year).'-12-31 23:59 UTC');
				else $qenddate = strtotime(($year).'-'.($month + 1).'-1 00:00 - 1 minute UTC');
			}
			else
				$qenddate = strtotime(($year).'-12-31 23:59 UTC');
	}
	//If set use the earliest enddate.
	if($enddate && (!$qenddate || ($qenddate && $qenddate > strtotime($enddate." UTC")))) $qenddate = strtotime($enddate." UTC");

	return $qenddate;
}
