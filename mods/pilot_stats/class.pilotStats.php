<?php
	class PilotStats
	{
		var $id=0;
		var $ctr_id=0;
		var $corpname="";
		
		function __construct($id)
		{
			$this->id=$id;
		}
		
		function generate()
		{
			global $smarty;
			
			$datefilter = $this->getDateFilter();
			$str_no_n00bs = '';
            if (@$_GET['no_n00bs'] == 'true') {
                // Noobship, Shuttle, Capsule
                $str_no_n00bs = " AND st.shp_class NOT IN (3,11,2) ";
            }
			
            if ((config::get('corppilotstatspage_showpos')=='hide')||($_GET['no_pos'] == 'true')) {
            	$str_no_pos = " AND st.shp_class NOT IN (38) ";
            	$smarty->assign('no_pos','true');
        	}
        	
            if ($this->id > 500000)
            	$corp = new Corporation($this->id,true);
            else
            	$corp = new Corporation($this->id,false);
            
        	$corp->fetchCorp();
        	$corp_id = $corp->getID();
        	  
        	$this->corpname = $corp->getName();
        	
        	$join_sql='';   
        	$kll_ctr_sql = '';
	        $loss_ctr_sql = '';       
        	if ($this->ctr_id)
        	{
	        	$smarty->assign('ctr_id',$this->ctr_id);
	        	$contract = new Contract($this->ctr_id);
	        	$ctr_reg = $contract->getRegions();
	        	$ctr_sys = $contract->getSystems();
	        	$ctr_ali = $contract->getAlliances();
	        	$ctr_corp = $contract->getCorps();
	        	$ctr_startDate = $contract->getStartDate();
	        	$ctr_endDate = $contract->getEndDate();
	        	
	        	if ($ctr_startDate)
	        	{
		        	$datefilter .= "AND kll.kll_timestamp >= '$ctr_startDate' ";
	        	}
	        	if ($ctr_endDate)
	        	{
		        	$datefilter .= "AND kll.kll_timestamp <= '$ctr_endDate' ";
	        	}
	        	if (count($ctr_reg))
	        	{
	        		$kll_ctr_sql .= ' AND reg.reg_id IN ('.implode(',', $ctr_reg).') ';
	        		$loss_ctr_sql .= ' AND reg.reg_id IN ('.implode(',', $ctr_reg).') ';
        		}
	        	if (count($ctr_sys))
	        	{
	        		$kll_ctr_sql .= ' AND sys.sys_id IN ('.implode(',', $ctr_sys).') ';
	        		$loss_ctr_sql .= ' AND sys.sys_id IN ('.implode(',', $ctr_sys).') ';
        		} 
	        	if (count($ctr_ali))
	        	{
	        		$kll_ctr_sql .= ' AND kll.kll_all_id IN ('.implode(',', $ctr_ali).') ';
	        		$loss_ctr_sql .= ' AND kll.kll_id IN (SELECT DISTINCT ind_kll_id FROM kb3_inv_detail inv WHERE inv.ind_all_id IN ('.implode(',', $ctr_ali).')) ';
        		}
	        	if (count($ctr_corp))
	        	{
	        		$kll_ctr_sql .= ' AND kll.kll_crp_id IN ('.implode(',', $ctr_corp).') ';
	        		$loss_ctr_sql .= ' AND kll.kll_id IN (SELECT DISTINCT ind_kll_id FROM kb3_inv_detail inv WHERE inv.ind_crp_id IN ('.implode(',', $ctr_corp).')) ';
        		}
        		
        		$join_sql ='
        			INNER JOIN kb3_systems sys ON sys.sys_id = kll.kll_system_id
					INNER JOIN kb3_constellations cons ON cons.con_id = sys.sys_con_id
					INNER JOIN kb3_regions reg ON reg.reg_id = cons.con_reg_id
					';
        	}
        	
            $qry = new DBQuery();

            // TODO: FIX-ME!  kll_isk_loss can't be calculated like this -- distinct may be a problem
            $sql = "SELECT count(kll_id) as kills, sum(kll_isk_loss) as isk_kill, ind_plt_id AS plt_id
					FROM kb3_kills kll
					INNER JOIN kb3_inv_detail inv ON inv.ind_kll_id = kll.kll_id
					INNER JOIN kb3_ships st ON st.shp_id = kll.kll_ship_id 
					{$join_sql}
					WHERE inv.ind_crp_id = {$corp_id} 
					{$datefilter}
					{$str_no_n00bs}
					{$str_no_pos} 
					{$kll_ctr_sql}
					GROUP BY ind_plt_id
					";

            $qry->execute($sql);
            
            $dataArr = array();
            
            while ($killData = $qry->getRow())
            {
	            $dataArr[$killData['plt_id']]['kills']=$killData['kills'];
	            $dataArr[$killData['plt_id']]['isk_kill']=$killData['isk_kill'];
			}
			
            $qry = new DBQuery();

            $sql = "SELECT count(kll_id) as losses, sum(kll_isk_loss) as isk_loss, kll_victim_id AS plt_id, st.shp_class
					FROM kb3_kills kll
					INNER JOIN kb3_ships st ON st.shp_id = kll.kll_ship_id 
					{$join_sql}
					WHERE kll.kll_crp_id = {$corp_id} 
					{$datefilter}
					{$str_no_n00bs} 
					{$str_no_pos}
					{$loss_ctr_sql}
					GROUP BY kll_victim_id
					";

            //$sql = "SELECT count(kll_id) as losses, sum(kll_isk_loss) as iskloss FROM ($sql) as st WHERE 1 {$str_no_n00bs} ";

            
            $qry->execute($sql);

            while($lossData = $qry->getRow())
            {
	            $dataArr[$lossData['plt_id']]['losses']=$lossData['losses'];
	            $dataArr[$lossData['plt_id']]['isk_loss']=$lossData['isk_loss'];
            }
            
            $CorpPilots=array();
            $cntlimit=0;
            $corpilotstatspage_reslimit=(int)config::get('corpilotstatspage_reslimit');
            
            
            foreach($dataArr as $plt_id=>$v)
            {
	            if ($corpilotstatspage_reslimit>0 && $corpilotstatspage_reslimit<=$cntlimit) continue;
	            	
	            $plt = new Pilot($plt_id);
	            if (!$plt->getExternalID()) continue;
	            
	            
	            if(!$v['kills']) $v['kills']=0;
	  			if(!$v['losses']) $v['losses']=0;
	  			if(!$v['isk_kill']) $v['isk_kill']=0;
	  			if(!$v['isk_loss']) $v['isk_loss']=0;
	  			
	  			if ((config::get('corppilotstatspage_filterkillcnt')>0)&&($v['kills']<config::get('corppilotstatspage_filterkillcnt'))) continue;
	  			
	  			$membercorp = array();	
	            // Build Data array
	            $membercorp['pilotName'] = $plt->getName();
	            $membercorp['pilotPortraitURL'] = $plt->getPortraitURL(32);
	            $membercorp['pilotID'] = $plt_id;
	            $membercorp['pilotDetailsURL'] = $plt->getDetailsURL();
	            $membercorp['pilotKills'] = $v['kills'];
	            $membercorp['pilotIskKill'] = $v['isk_kill'] / 1000;
	            $membercorp['pilotLosses'] = $v['losses'];
	            $membercorp['pilotIskLoss'] = $v['isk_loss'] / 1000;
	            if (config::get('corppilotstatspage_eff') == 'killlosseff') {
	                $membercorp['pilotEfficiency'] = round($v['kills'] / (($v['kills'] +
	                    $v['losses']) == 0 ? 1 : ($v['kills'] + $v['losses'])) *
	                    100, 2);
	            } else { // damagedone / (damagedone + damagereceived ) * 100
	                $membercorp['pilotEfficiency'] = round($v['isk_kill'] / (($v['isk_kill'] +
	                    $v['isk_loss']) == 0 ? 1 : ($v['isk_kill'] + $v['isk_loss'])) *
	                    100, 2);
	                #	$membercorp['corpEfficiency'] = (( $killData['iskkill'] / ($killData['iskkill'] + $lossData['iskloss']) ) * 100 );
	            }
	
	
	            $bar = new BarGraph($membercorp['pilotEfficiency'], 100, 75);
	            $membercorp['pilotBar'] = $bar->generate();
	            
	
	            // add all the data together into another array
	            $CorpPilots[] = $membercorp;
	            
	            
	            
				$cntlimit++;
        	}
        	
        	if (isset($_GET['order']) && in_array($_GET['order'], array('nameasc','nameasc', 'killsdesc', 'killiskdesc', 'lossesdesc', 'lossiskdesc', 'effdesc'))) 
        	{
	            $_order = $_GET['order'];
	        }
	        else
	        	$_order = config::get('corppilotstatspage_order');
	
	        if ($_GET['w'])
	        {
		        $smarty->assign('w',$_GET['w']);
	        }
	        if ($_GET['m'])
	        {
		        $smarty->assign('m',$_GET['m']);
	        }
	        if ($_GET['y'])
	        {
		        $smarty->assign('y',$_GET['y']);
	        }
	
	        if ($_order == 'nameasc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotName');
	        if ($_order == 'namedesc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotName', 'arsort');
	        if ($_order == 'killsasc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotKills');
	        if ($_order == 'killsdesc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotKills', 'arsort');
	        if ($_order == 'killiskasc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotIskKill');
	        if ($_order == 'killiskdesc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotIskKill', 'arsort');
	        if ($_order == 'lossesasc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotLosses');
	        if ($_order == 'lossesdesc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotLosses', 'arsort');
	        if ($_order == 'lossiskasc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotIskLoss');
	        if ($_order == 'lossiskdesc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotIskLoss', 'arsort');
	        if ($_order == 'effasc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotEfficiency');
	        if ($_order == 'effdesc')
	            $CorpPilots = $this->subvalSort($CorpPilots, 'pilotEfficiency', 'arsort');
	        
	
	        $smarty->assign('order', $_order);
        	
            if (config::get('corppilotstatspage_datefilter') == 'weekly') {
	            $smarty->assign('datefilter', "Week {$this->weekno_}" . " {$this->yearno_}");
	        } elseif (config::get('corppilotstatspage_datefilter') == 'monthly') {
	            $timestamp = mktime(0, 0, 0, $this->monthno_, 1, 2005);
	            $smarty->assign('datefilter', date("F", $timestamp) . " {$this->yearno_}");
	        } elseif (config::get('corppilotstatspage_datefilter') == 'yearly') {
	            $smarty->assign('datefilter', "{$this->yearno_}");
	        } elseif (config::get('corppilotstatspage_datefilter') == 'alltime') {
	            $smarty->assign('datefilter', "All-Time");
	        }
	
	        if (isset($_GET['daterange']) && $_GET['daterange'] == 'weekly') {
	            $smarty->assign('datefilter', "Week {$this->weekno_}" . " {$this->yearno_}");
	        } elseif (isset($_GET['daterange']) && $_GET['daterange'] == 'monthly') {
	            $timestamp = mktime(0, 0, 0, $this->monthno_, 1, 2005);
	            $smarty->assign('datefilter', date("F", $timestamp) . " {$this->yearno_}");
	        } elseif (isset($_GET['daterange']) && $_GET['daterange'] == 'yearly') {
	            $smarty->assign('datefilter', "{$this->yearno_}");
	        } elseif (isset($_GET['daterange']) && $_GET['daterange'] == 'alltime') {
	            $smarty->assign('datefilter', "All-Time");
	        }
        	$smarty->assign('corppilots', $CorpPilots);
        	$smarty->assign('crp_id',$corp->getID());
        	return $smarty->fetch(getcwd() . '/mods/pilot_stats/corppilotstats.tpl');
		}
		
		//! Filter results by week. Requires the year to also be set.
	    function setWeek($weekno)
	    {
	        $weekno = intval($weekno);
	        if ($weekno < 1)
	            $this->weekno_ = 1;
	        if ($weekno > 53)
	            $this->weekno_ = 53;
	        else
	            $this->weekno_ = $weekno;
	    }
	
	    //! Filter results by year.
	    function setYear($yearno)
	    {
	        // 1970-2038 is the allowable range for the timestamp code used
	        // Needs to be revisited in the next 30 years
	        $yearno = intval($yearno);
	        if ($yearno < 1970)
	            $this->yearno_ = 1970;
	        if ($yearno > 2038)
	            $this->yearno_ = 2038;
	        else
	            $this->yearno_ = $yearno;
	    }
	
	    //! Filter results by month
	    function setMonth($monthno)
	    {
	        $monthno = intval($monthno);
	        if ($monthno < 1)
	            $this->monthno_ = 1;
	        if ($monthno > 12)
	            $this->monthno_ = 12;
	        else
	            $this->monthno_ = $monthno;
	    }
	
	    //! Filter results by starting week. Requires the year to also be set.
	    function setStartWeek($weekno)
	    {
	        $weekno = intval($weekno);
	        if ($weekno < 1)
	            $this->startweekno_ = 1;
	        if ($weekno > 53)
	            $this->startweekno_ = 53;
	        else
	            $this->startweekno_ = $weekno;
	    }
	
	    //! Filter results by starting date/time.
	    function setStartDate($timestamp)
	    {
	        // Check timestamp is valid before adding
	        if (strtotime($timestamp))
	            $this->startDate_ = $timestamp;
	    }
	
	    //! Filter results by ending date/time.
	    function setEndDate($timestamp)
	    {
	        // Check timestamp is valid before adding
	        if (strtotime($timestamp))
	            $this->endDate_ = $timestamp;
	    }
	
	    function setCampaign($ctr_id)
	    {
			$this->ctr_id = $ctr_id;    
	    }
	    
	    function getCampaign()
	    {
		    return $this->ctr_id;
	    }
	    
	    //! \return string containing SQL date filter.
	    function getDateFilter()
	    {
	        $qstartdate = makeStartDate($this->weekno_, $this->yearno_, $this->monthno_, $this->
	            startweekno_, $this->startDate_);
	        $qenddate = makeEndDate($this->weekno_, $this->yearno_, $this->monthno_, $this->
	            endDate_);
	        if ($qstartdate)
	            $sql .= "AND kll.kll_timestamp >= '" . gmdate('Y-m-d H:i', $qstartdate) . "' ";
	        if ($qenddate)
	            $sql .= "AND kll.kll_timestamp <= '" . gmdate('Y-m-d H:i', $qenddate) . "' ";
	        return $sql;
	    }
	    
	    function getContracts()
	    {
		    
	    }
	    
	    function subvalSort($a, $subkey, $sort = asort)
	    {
		    if (!is_array($a)) return $a;
		    
		    $b=array();
	        foreach ($a as $k => $v) {
	            $b[$k] = strtolower($v[$subkey]);
	        }
	        $sort($b);
	
	        $c=array();
	        foreach ($b as $key => $val) {
	            $c[] = $a[$key];
	        }
	        return $c;
	    }
	    
	}
?>
