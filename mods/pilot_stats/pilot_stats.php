<?php
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
	} else {
	    //else we grab the base internally set ID
	    $id = config::get('cfg_corpid');
	    $id = $id[0];
	}
	
	$corpStats = new PilotStats($id);
	
	if (isset($_GET['ctr_id']) && is_numeric($_GET['ctr_id']))
	{
		$corpStats->setCampaign($_GET['ctr_id']);
	}
	
	
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
	
	
	$url_ext = '&crp_id='.$id;
	$smarty->assign('crp_id',$id);
	if (@$_GET['no_n00bs'] == 'true') {
	    $url_ext = '&no_n00bs=true&crp_id='.$id;
	}
	if ($_GET['no_pos'] == 'true') {
	    $url_ext .= '&no_pos=true';
	}
	$smarty->assign('no_n00bs', (@$_GET['no_n00bs'] == 'true'));

	if($ctr_id = $corpStats->getCampaign())
	{
		$ctr_ext .= '&ctr_id='.$ctr_id;
		$smarty->assign('ctr_id',$ctr_id);
	}
	
	$menubox = new box('Menu');
	$menubox->setIcon('menu-item.gif');
	
	$tblx = (!empty($_GET['w']) ? '&w=' . (int)$_GET['w'] : '') . (!empty($_GET['m']) ?
	    '&m=' . (int)$_GET['m'] : '') . (!empty($_GET['y']) ? '&y=' . (int)$_GET['y'] :
	    '') . (!empty($_GET['daterange']) ? '&daterange=' . (string )$_GET['daterange'] :
	    '') . (!empty($_GET['order']) ? '&order=' . (string )$_GET['order'] : '');
	
	if (@$_GET['no_n00bs'] != 'true') {
	    $menubox->addOption('link', '<b>Remove</b> Noobship, Shuttle, Capsule',
	        '?a=pilot_stats&no_n00bs=true&crp_id='. $id . $tblx . $ctr_ext . (!empty($_GET['no_pos']) ? '&no_pos=true' : ''));
	} else {
	    $menubox->addOption('link', '<b>Show</b> Noobship, Shuttle, Capsule',
	        '?a=pilot_stats&crp_id='. $id . $tblx . $ctr_ext . (!empty($_GET['no_pos']) ? '&no_pos=true' : ''));
	}
	
	if(config::get('corppilotstatspage_showpos')!='hide')
	{
		if (@$_GET['no_pos'] != 'true') {
		    $menubox->addOption('link', '<b>Remove</b> POS Modules',
		        '?a=pilot_stats&no_pos=true&crp_id='. $id . $tblx . $ctr_ext . (!empty($_GET['no_n00bs']) ? '&no_n00bs=true' : ''));
		} else {
		    $menubox->addOption('link', '<b>Show</b> POS Modules',
		        '?a=pilot_stats&crp_id='. $id . $tblx . $ctr_ext . (!empty($_GET['no_n00bs']) ? '&no_n00bs=true' : ''));
		}
	}
	
	if (config::get('corpilotstatspage_contracts')=='showcontracts')
	{
		$campaigns = new ContractList();
		if ($campaigns->getCount())
		{
			$menubox->addOption('caption','Campaign Corp Stats');
			while($ctr = $campaigns->getContract())
			{
				if (!$ctr->getEndDate())
				{
					$menubox->addOption('link',$ctr->getName(),'?a=pilot_stats&daterange=alltime&ctr_id='.$ctr->getID().$url_ext);
					
					if ($_GET['ctr_id']==$ctr->getID())
						$smarty->assign('campaign_name',$ctr->getName());
				}
			}
			
			$menubox->addOption('link','No Campaign Filter','?a=pilot_stats&daterange=alltime'.$url_ext);//clear the contract id from the url
		}
	}
	$menubox->addOption('caption', 'Corp Stats');
	$menubox->addOption('link', 'All-Time', '?a=pilot_stats&daterange=alltime' .$ctr_ext.$url_ext);
	$menubox->addOption('link', 'Weekly', '?a=pilot_stats&daterange=weekly' .$ctr_ext.$url_ext);
	$menubox->addOption('link', 'Monthly', '?a=pilot_stats&daterange=monthly' .$ctr_ext.$url_ext);
	$menubox->addOption('link', 'Yearly', '?a=pilot_stats&daterange=yearly' .$ctr_ext.$url_ext);
	
	if ($daterange_year || $daterange_month || $daterange_week) {
	
	    $menubox->addOption('caption', 'Date Navigation');
	
	    //
	    if ($daterange_week) { // week(s) year = 53
	        $next_week = $week == 53 ? 1 : $week + 1;
	        $next_year = $week == 53 ? $year + 1 : $year;
	
	        $prev_week = $week == 1 ? 53 : $week - 1;
	        $prev_year = $week == 1 ? $year - 1 : $year;
	
	        if (($next_year > (int)date('Y')) || (($next_year == (int)date('Y')) && ($next_week <= (int)kbdate('W') && true))) 
	        {
	            $menubox->addOption('link', 'Next Week', "?a=pilot_stats&daterange=weekly&w={$next_week}&y={$next_year}" .$ctr_ext.$url_ext);
	        }
	        
	        $menubox->addOption('link', 'Previous Week', "?a=pilot_stats&daterange=weekly&w={$prev_week}&y={$prev_year}" .$ctr_ext.$url_ext);
	    }
	
	    if ($daterange_month) {
	        $next_month = $month == 12 ? 1 : $month + 1;
	        $next_year = $month == 12 ? $year + 1 : $year;
	
	        $prev_month = $month == 1 ? 12 : $month - 1;
	        $prev_year = $month == 1 ? $year - 1 : $year;
	
	        $menubox->addOption('link', 'Previous Month', "?a=pilot_stats&daterange=monthly&m={$prev_month}&y={$prev_year}" .$ctr_ext.$url_ext);
	
	        if (($next_year > (int)date('Y')) || (($next_year == (int)date('Y')) && ($next_month <= (int)date('m') && true))) 
	        {
	            $menubox->addOption('link', 'Next Month', "?a=pilot_stats&daterange=monthly&m={$next_month}&y={$next_year}" .$ctr_ext.$url_ext);
	        }
	    }
	
	    if ($daterange_year) {
	        $next_year = $year + 1;
	        $prev_year = $year - 1;
	
	        //	if( $next_year >= date('Y') ) {
	        $menubox->addOption('link', 'Previous Year', "?a=pilot_stats&daterange=yearly&y={$prev_year}" .$ctr_ext.$url_ext);
	        //	}
	
	        if ($next_year <= (int)date('Y')) {
	            $menubox->addOption('link', 'Next Year', "?a=pilot_stats&daterange=yearly&y={$next_year}" .$ctr_ext.$url_ext);
	        }
	    }
	
	}
	
	$html = $corpStats->generate();
	
	$page = new Page($corpStats->corpname . ' Pilot Statistics',false);
	$page->setContent($html);
	
	
	$page->addContext($menubox->generate());
	
	
	
	$page->generate();
?>