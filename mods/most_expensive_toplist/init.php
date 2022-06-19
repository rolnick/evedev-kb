<?php

define('MOST_EXP_TOPLIST_VERSION', 1.1);

$modInfo['most_expensive_toplist']['name'] = "Most Expensive Toplist v".MOST_EXP_TOPLIST_VERSION;
$modInfo['most_expensive_toplist']['abstract'] = "Adds alltime/monthly toplists for most expensive kills/losses to corp/alliance detail view";
$modInfo['most_expensive_toplist']['about'] = "by <a href=\"http://gate.eveonline.com/Profile/Salvoxia\">Salvoxia</a>";


// hooks for alliance detail page
event::register('allianceDetail_assembling', 'MostExpensiveToplist::initAlliance');
event::register('allianceDetail_context_assembling', 'MostExpensiveToplist::initContextAlliance');

// hooks for corp detail page
event::register('corpDetail_assembling', 'MostExpensiveToplist::initCorp');
event::register('corpDetail_context_assembling', 'MostExpensiveToplist::initContextCorp');

class MostExpensiveToplist
{
	/**
	 * callback for injecting our custom views to the corp detail page
	 * @param \pCorpDetail the corp detail page
	 */
	public static function initCorp($corporationDetail)
	{
		$corporationDetail->addView('expensive_kills', 'MostExpensiveToplist::killListCorp');
		$corporationDetail->addView('expensive_losses', 'MostExpensiveToplist::killListCorp');
	}
	
	/**
	 * callback for injecting our custom menu options into the corp detail context menu
	 * @param \pCorpDetail the corp detail page
	 */
	public static function initContextCorp($corporationDetail)
	{
		$corporationDetail->addBehind("menuSetup", 'MostExpensiveToplist::menuSetupMostExpToplistCorp');
	}
	
	/**
	 * callback for injecting our custom views to the alliance detail page
	 * @param \pAllianceDetail the alliance detail page
	 */
	public static function initAlliance($allianceDetail)
	{
		$allianceDetail->addView('expensive_kills', 'MostExpensiveToplist::killListAlliance');
		$allianceDetail->addView('expensive_losses', 'MostExpensiveToplist::killListAlliance');
	}
	
	/**
	 * callback for injecting our custom menu options into the alliance detail context menu
	 * @param \pAllianceDetail the alliance detail page
	 */
	public static function initContextAlliance($allianceDetail)
	{
		$allianceDetail->addBehind("menuSetup", 'MostExpensiveToplist::menuSetupMostExpToplistAlliance');
	}
	
	public static function menuSetupMostExpToplistCorp($corporationDetail)
	{
		$args = array();
		if ($corporationDetail->crp_external_id) 
		{
			$args[] = array('crp_ext_id', $corporationDetail->crp_external_id, true);
		} 
		
		else
		{
			$args[] = array('crp_id', $corporationDetail->crp_id, true);
		}
		
		$corporationDetail->addMenuItem("caption", "Most expensive lists");
		$corporationDetail->addMenuItem("link", "Most exp. kills",
		edkURI::build($args, array('view', 'expensive_kills', true)));
		$corporationDetail->addMenuItem("link", "Most exp. losses",
		edkURI::build($args, array('view', 'expensive_losses', true)));
	}
	
	
	public static function menuSetupMostExpToplistAlliance($allianceDetail)
	{
		$args = array();
		if ($allianceDetail->all_external_id) 
		{
			$args[] = array('all_ext_id', $allianceDetail->all_external_id, true);
		} 
		
		else
		{
			$args[] = array('all_id', $allianceDetail->all_id, true);
		}
		
		$allianceDetail->addMenuItem("caption", "Most expensive lists");
		$allianceDetail->addMenuItem("link", "Most exp. kills",
		edkURI::build($args, array('view', 'expensive_kills', true)));
		$allianceDetail->addMenuItem("link", "Most exp. losses",
		edkURI::build($args, array('view', 'expensive_losses', true)));
	}
	
	
	public static function killListCorp($corporationDetail)
	{
		require_once("toplist/class.expensivekills.php");
		require_once("toptable/class.expensivekill.php");
		global $smarty;
		
		// arguments for URL building
		$args = array();
		if ($corporationDetail->all_external_id) 
		{
			$args[] = array('crp_ext_id', $corporationDetail->crp_external_id, true);
		} 
		
		else
		{
			$args[] = array('crp_id', $corporationDetail->crp_id, true);
		}
		
		// we need to figure this out ourselves, older versions of the corp detail page don't have this information accessible
		$crp_id = $corporationDetail->getCorp()->getID();
		$view = preg_replace('/[^a-zA-Z0-9_-]/','', edkURI::getArg('view', 2));
		if ($view) {
			$year = (int)edkURI::getArg('y', 3);
			$month = (int)edkURI::getArg('m', 4);
		} else {
			$year = (int)edkURI::getArg('y', 2);
			$month = (int)edkURI::getArg('m', 3);
		}

		if (!$month) {
			$month = kbdate('m');
		}
		if (!$year) {
			$year = kbdate('Y');
		}

		if ($month == 12) {
			$nmonth = 1;
			$nyear = $year + 1;
		} else {
			$nmonth = $month + 1;
			$nyear = $year;
		}
		if ($month == 1) {
			$pmonth = 12;
			$pyear = $year - 1;
		} else {
			$pmonth = $month - 1;
			$pyear = $year;
		}
		$monthname = kbdate("F", strtotime("2000-".$month."-2"));
		
		if($view == 'expensive_kills')
		{
			$smarty->assign('title', "Most Expensive Kills");
			$smarty->assign('month', $monthname);
			$smarty->assign('year', $year);
			$smarty->assign('pmonth', $pmonth);
			$smarty->assign('pyear', $pyear);
			$smarty->assign('nmonth', $nmonth);
			$smarty->assign('nyear', $nyear);
			$smarty->assign('crp_id', $crp_id);
							 $smarty->assign('value_class', 'kl-kill');
			$smarty->assign('url_previous',
					edkURI::build($args, array('view', 'expensive_kills', true),
							array('y', $pyear, true),
							array('m', $pmonth, true)));
			$smarty->assign('url_next',
					edkURI::build($args, array('view', 'expensive_kills', true),
							array('y', $nyear, true),
							array('m', $nmonth, true)));                                
							
			$list = new TopList_ExpensiveKills();
			$list->addInvolvedCorp($corporationDetail->getCorp());
			$list->setPodsNoobShips(TRUE);
			$list->setMonth($month);
			$list->setYear($year);
			$table = new TopTable_ExpensiveKill($list, "ISK");
			$smarty->assign('monthly_stats', $table->generate());

			$list = new TopList_ExpensiveKills();
			$list->addInvolvedCorp($corporationDetail->getCorp());
			$list->setPodsNoobShips(TRUE);
			$table = new TopTable_ExpensiveKill($list, "ISK");
			$smarty->assign('total_stats', $table->generate());

			return $smarty->fetch(getcwd() . "/mods/most_expensive_toplist/templates/detail_kl_expensive.tpl");
		}
		
		else if($view == 'expensive_losses')
		{
			$smarty->assign('title', "Most Expensive Losses");
			$smarty->assign('month', $monthname);
			$smarty->assign('year', $year);
			$smarty->assign('pmonth', $pmonth);
			$smarty->assign('pyear', $pyear);
			$smarty->assign('nmonth', $nmonth);
			$smarty->assign('nyear', $nyear);
			$smarty->assign('crp_id', $crp_id);
							$smarty->assign('value_class', 'kl-loss');
			$smarty->assign('url_previous',
					edkURI::build($args, array('view', 'expensive_losses', true),
							array('y', $pyear, true),
							array('m', $pmonth, true)));
			$smarty->assign('url_next',
					edkURI::build($args, array('view', 'expensive_losses', true),
							array('y', $nyear, true),
							array('m', $nmonth, true)));

			$list = new TopList_ExpensiveKills();
			$list->addVictimCorp($corporationDetail->getCorp());
			$list->setPodsNoobShips(TRUE);
			$list->setMonth($month);
			$list->setYear($year);
			$table = new TopTable_ExpensiveKill($list, "ISK");
			$smarty->assign('monthly_stats', $table->generate());

			$list = new TopList_ExpensiveKills();
			$list->addVictimCorp($corporationDetail->getCorp());
			$list->setPodsNoobShips(TRUE);
			$table = new TopTable_ExpensiveKill($list, "ISK");
			$smarty->assign('total_stats', $table->generate());

			return $smarty->fetch(getcwd() . "/mods/most_expensive_toplist/templates/detail_kl_expensive.tpl");
		}
	}
	
	public static function killListAlliance($allianceDetail)
	{
		require_once("toplist/class.expensivekills.php");
		require_once("toptable/class.expensivekill.php");
		global $smarty;
		
		// arguments for URL building
		$args = array();
		if ($allianceDetail->all_external_id) 
		{
			$args[] = array('all_ext_id', $allianceDetail->all_external_id, true);
		} 
		
		else
		{
			$args[] = array('all_id', $allianceDetail->all_id, true);
		}
		
		// get context information
		$all_id = $allianceDetail->getAlliance()->getID();
		$view = $allianceDetail->getView();
		$month = $allianceDetail->getMonth();
		$year = $allianceDetail->getYear();
		$pmonth = $smarty->get_template_vars('pmonth');
		$pyear = $smarty->get_template_vars('pyear');
		$nmonth = $smarty->get_template_vars('nmonth');
		$nyear = $smarty->get_template_vars('nyear');
		
		$monthname = kbdate("F", strtotime("2000-".$month."-2"));
		
		if($view == 'expensive_kills')
		{
			$smarty->assign('title', "Most Expensive Kills");
			$smarty->assign('month', $monthname);
			$smarty->assign('year', $year);
			$smarty->assign('pmonth', $pmonth);
			$smarty->assign('pyear', $pyear);
			$smarty->assign('nmonth', $nmonth);
			$smarty->assign('nyear', $nyear);
			$smarty->assign('all_id', $all_id);
							 $smarty->assign('value_class', 'kl-kill');
			$smarty->assign('url_previous',
					edkURI::build($args, array('view', 'expensive_kills', true),
							array('y', $pyear, true),
							array('m', $pmonth, true)));
			$smarty->assign('url_next',
					edkURI::build($args, array('view', 'expensive_kills', true),
							array('y', $nyear, true),
							array('m', $nmonth, true)));                                
							
			$list = new TopList_ExpensiveKills();
			$list->addInvolvedAlliance($allianceDetail->getAlliance());
			$list->setPodsNoobShips(TRUE);
			$list->setMonth($month);
			$list->setYear($year);
			$table = new TopTable_ExpensiveKill($list, "ISK");
			$smarty->assign('monthly_stats', $table->generate());

			$list = new TopList_ExpensiveKills();
			$list->addInvolvedAlliance($allianceDetail->getAlliance());
			$list->setPodsNoobShips(TRUE);
			$table = new TopTable_ExpensiveKill($list, "ISK");
			$smarty->assign('total_stats', $table->generate());

			return $smarty->fetch(getcwd() . "/mods/most_expensive_toplist/templates/detail_kl_expensive.tpl");
		}
		
		else if($view == 'expensive_losses')
		{
			$smarty->assign('title', "Most Expensive Losses");
			$smarty->assign('month', $monthname);
			$smarty->assign('year', $year);
			$smarty->assign('pmonth', $pmonth);
			$smarty->assign('pyear', $pyear);
			$smarty->assign('nmonth', $nmonth);
			$smarty->assign('nyear', $nyear);
			$smarty->assign('all_id', $all_id);
							$smarty->assign('value_class', 'kl-loss');
			$smarty->assign('url_previous',
					edkURI::build($args, array('view', 'expensive_losses', true),
							array('y', $pyear, true),
							array('m', $pmonth, true)));
			$smarty->assign('url_next',
					edkURI::build($args, array('view', 'expensive_losses', true),
							array('y', $nyear, true),
							array('m', $nmonth, true)));

			$list = new TopList_ExpensiveKills();
			$list->addVictimAlliance($allianceDetail->getAlliance());
			$list->setPodsNoobShips(TRUE);
			$list->setMonth($month);
			$list->setYear($year);
			$table = new TopTable_ExpensiveKill($list, "ISK");
			$smarty->assign('monthly_stats', $table->generate());

			$list = new TopList_ExpensiveKills();
			$list->addVictimAlliance($allianceDetail->getAlliance());
			$list->setPodsNoobShips(TRUE);
			$table = new TopTable_ExpensiveKill($list, "ISK");
			$smarty->assign('total_stats', $table->generate());

			return $smarty->fetch(getcwd() . "/mods/most_expensive_toplist/templates/detail_kl_expensive.tpl");
		}
	}
	
}