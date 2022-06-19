<?php

	if(!defined('KB_SITE')) die ("Go Away!");

	$modInfo['pilot_stats']['name'] = "Corp Pilot Stats";
	$modInfo['pilot_stats']['abstract'] = "based on <a href='' target='_blank'>allicorpstats_page</a> except shows stats on corp members.";
	$modInfo['pilot_stats']['about'] = "by <a href='http://kb.heretic-army.biz/?a=pilot_detail&plt_id=6' target='_blank'>MrWhitei God</a>";

	if (config::get('corppilotstatspage_location')=='corppage' && !$_GET['view'])
	{
		event::register("corpDetail_assembling", "pilotstats_view::addCorpDtl");
	}

	event::register("corpDetail_context_assembling", "pilotstats_view::add");


	class pilotstats_view {
		public static function add($page)
		{        
			if ($_GET['crp_ext_id'])
				$id = $_GET['crp_ext_id'];
			else
				$id =$_GET['crp_id'];
        	$page->addMenuItem( "caption", "Mods:");
			$page->addMenuItem( "link","Corp Pilot Stats", "?a=pilot_stats&crp_id=" . $id);
        
		}
		public static function addCorpDtl($page)
		{
			$page->addBehind("killList", 'pilotstats_view::addCorpDetail');
		}
		public static function addCorpDetail($page)
		{
			require_once ('class.pilotStats.php');
		
			$week = $_GET['w'];
			$year = $_GET['y'];
			$month = $_GET['m'];
		
			if ($week == '')
		    	$week = kbdate('W');
		
			if ($year == '')
		    	$year = kbdate('Y');
		
			if ($month == '')
		    	$month = kbdate('m');
	    
			if (isset($_GET['crp_id']) and is_numeric($_GET['crp_id'])){
		    	//trust user input, lol no
		    	$id = addslashes($_GET['crp_id']);
			} else if (isset($_GET['crp_ext_id']) and is_numeric($_GET['crp_ext_id'])){
				$id = $_GET['crp_ext_id'];
			}else {
		    	//else we grab the base internally set ID
		    	$id = config::get('cfg_corpid');
		    	$id = $id[0];
			}
		
			$corpStats = new PilotStats($id);
			global $smarty;
		
			$daterange_year = $daterange_month = $daterange_week = false;

			// start a switch to allow for viewing of other stats such as weekly, monthly etc
			switch ($_GET['daterange']) {
		    	case 'weekly':
		        	$corpStats->setWeek($week);
		        	$corpStats->setYear($year);
		        	$smarty->assign('datefilter', "Week {$week}");
		        	$daterange_week = true;
		        	break;		
		    	case 'monthly':
		        	$corpStats->setMonth($month);
		        	$corpStats->setYear($year);
		        	$timestamp = mktime(0, 0, 0, $month, 1, 2005);
		        	$smarty->assign('datefilter', date("F", $timestamp));
		        	$daterange_month = true;
		        	break;
		    	case 'yearly':
		        	$corpStats->setYear($year);
		        	$smarty->assign('datefilter', "{$year}");
		        	$daterange_year = true;
		        	break;
		    	case 'alltime':
		        	$corpStats->setStartDate('2003-01-01 00:00:00');
		        	$smarty->assign('datefilter', "All-Time");
		        	break;
		    	default:
		        	// get the date range based on what the admin selected in admin panel
		        	if (config::get('corppilotstatspage_datefilter') == 'weekly') {
		            	$corpStats->setWeek($week);
		            	$corpStats->setYear($year);
		            	$smarty->assign('datefilter', "Week {$week}");
		            	$daterange_week = true;
		        	} elseif (config::get('corppilotstatspage_datefilter') == 'monthly') {
		            	$corpStats->setMonth($month);
		            	$corpStats->setYear($year);
		            	$timestamp = mktime(0, 0, 0, $month, 1, 2005);
		            	$smarty->assign('datefilter', date("F", $timestamp));
		            	$daterange_month = true;
		        	} elseif (config::get('corppilotstatspage_datefilter') == 'yearly') {
		            	$corpStats->setYear($year);
		            	$smarty->assign('datefilter', "{$year}");
		            	$daterange_year = true;
		        	} else {
		            	$corpStats->setStartDate('2003-01-01 00:00:00');
		            	$smarty->assign('datefilter', "All-Time");
		        	}
		        	break;
			}
        
        	return( $corpStats->generate());
        
        
		}
	}
?>