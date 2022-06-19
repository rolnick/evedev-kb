<?php
require_once("common/includes/db.php");
require_once("common/includes/class.killlist.php");
require_once("common/includes/class.bargraph.php");
require_once("common/includes/class.pagesplitter.php");
require_once("class.helpers.php");

class Battle
{
	function __construct($battle)
	{
		$this->involved_ = 0;
		$this->ctr_id_ = $battle['id'];
		$this->ctr_kill_id_ = $battle['id'];

		$this->system_ = $battle['system'];
		$this->ctr_started_ = $battle['start'];

		$this->ctr_ended_ = $battle['end'];
		$this->executed_ = False;
                
        $this->ownerPilotIdsInvolved_ = $battle["ownerPilotIds"];

		$this->klist_ = new KillList();
		$this->llist_ = new KillList();
		involved::load($this->klist_,'kill');
		involved::load($this->llist_,'loss');

	}

	function execQuery()
	{   
		if ($this->executed_)
			return;

		$this->executed_ = True;

		$this->klist_->addSystem($this->system_);
		$this->llist_->addSystem($this->system_);

		$this->klist_->setStartDate($this->ctr_started_);
		$this->klist_->setEndDate($this->ctr_ended_);

		$this->llist_->setStartDate($this->ctr_started_);
		$this->llist_->setEndDate($this->ctr_ended_);

		
		
                 // if manual side assignment is enabled
                if(config::get("fleet_battles_mod_sideassign"))
                {
                    // get possible side assignments
                    $sideAssignments = getSideAssignments($this->system_->getID(), $this->ctr_started_, $this->ctr_ended_);
                    
                    // apply
                    foreach($sideAssignments AS $sideAssignment)
                    {
                        // entity is an alliance
                        if($sideAssignment["entity_type"] == "alliance")
                        {
                            // alliance is an enemy
                            if($sideAssignment["side"] == "e")
                            {
                                $this->klist_->addVictimAlliance($sideAssignment["entity_id"]);
                                $this->llist_->addInvolvedAlliance($sideAssignment["entity_id"]);
                            }
                            
                            // alliance is an ally
                            else
                            {
                                $this->klist_->addInvolvedAlliance($sideAssignment["entity_id"]);
                                $this->llist_->addVictimAlliance($sideAssignment["entity_id"]);
                            }
                        }
                        
                        // entity is a corporation
                        else
                        {
                            // alliance is an enemy
                            if($sideAssignment["side"] == "e")
                            {
                                $this->klist_->addVictimCorp($sideAssignment["entity_id"]);
                                $this->llist_->addInvolvedCorp($sideAssignment["entity_id"]);
                            }
                            
                            // alliance is an ally
                            else
                            {
                                $this->klist_->addInvolvedCorp($sideAssignment["entity_id"]);
                                $this->llist_->addVictimCorp($sideAssignment["entity_id"]);
                            }
                        }
                    }
                }
                
                $this->pilots_ = array('a' => array(), 'e' => array());
                $destroyed = array();
                $sides = array();
		while ($kill = $this->klist_->getKill())
		{                        
			handle_involved($kill, 'a', $this->pilots_, FALSE);
                        
			handle_destroyed($kill, 'e', $destroyed, $this->pilots_, FALSE);

		}
		

		while ($kill = $this->llist_->getKill())
		{
			handle_involved($kill, 'e', $this->pilots_, FALSE);
			handle_destroyed($kill, 'a', $destroyed, $this->pilots_, FALSE);
		}
                
                $this->llist_->rewind();
                $this->klist_->rewind();
		$this->involved_ = count($this->pilots_['a']) + count($this->pilots_['e']);
                
	}

	function getID()
	{
		return $this->ctr_id_;
	}
        
        function getNumberOfOwnersInvolved()
	{
		return count($this->ownerPilotIdsInvolved_);
	}
        
        function getOwnersInvolved()
        {
            return $this->ownerPilotIdsInvolved_;
        }

	function getName()
	{
		$this->execQuery();
		return $this->system_->getName();
	}

	function getInvolved()
	{
		$this->execQuery();
		return $this->involved_;
	}

	function getKillID()
	{
		$this->execQuery();
		return $this->ctr_kill_id_;
	}

	function getStartDate()
	{
		$this->execQuery();
		return $this->ctr_started_;
	}

	function getEndDate()
	{
		$this->execQuery();
		return $this->ctr_ended_;
	}

	function getRunTime()
	{
		if (!$datet = $this->getEndDate())
		{
			$datet = 'now';
		}

		$diff = strtotime($datet) - strtotime($this->getStartDate());
		return floor($diff/86400);
	}

	function getCampaign()
	{
		$this->execQuery();
		return $this->campaign_;
	}

	function getKills()
	{
		$this->execQuery();
		return $this->klist_->getCount();
	}

	function getLosses()
	{
		$this->execQuery();
		return $this->llist_->getCount();
	}

	function getKillISK()
	{
            
		$this->execQuery();
		if (!$this->klist_->getISK()) $this->klist_->getAllKills();
		return $this->klist_->getISK();
	}

	function getLossISK()
	{
		$this->execQuery();
		if (!$this->llist_->getISK()) $this->llist_->getAllKills();
		return $this->llist_->getISK();
	}

	function getEfficiency()
	{
		$this->execQuery();
		if ($this->klist_->getISK())
			$efficiency = round($this->klist_->getISK() / ($this->klist_->getISK() + $this->llist_->getISK()) * 100, 2);
		else
			$efficiency = 0;

		return $efficiency;
	}

	function getKillList()
	{
		$this->execQuery();
		return $this->klist_;
	}

	function getLossList()
	{
		$this->execQuery();
		return $this->llist_;
	}

	function validate()
	{
		$qry = new DBQuery();

		$qry->execute("select * from kb3_contracts
                       where ctr_id = ".$this->ctr_id_."
		         and ctr_site = '".KB_SITE."'");
		return ($qry->recordCount() > 0);
	}
        
        /**
         * used to update the stats for a specific battle without completely recalculating the cache
         * @param int $battleId
         * @param float $killIsk
         * @param float $lossIsk
         * @param int $kills
         * @param int $losses
         * @param int $involved
         * @param String $timestampStart
         * @param String $timestampEnd 
         * @param int $numberOfOwnersInvolved
         */
        public static function updateCacheForBattle($battleId, $killIsk, $lossIsk, $kills, $losses, $involved, $timestampStart, $timestampEnd, $numberOfOwnersInvolved, $involvedOwnerIds)
        {
            $qry = DBFactory::getDBQuery();
            if($killIsk)
            {
                $efficiency = round(($killIsk / ($killIsk+$lossIsk))*100, 2);
            }
            
            else
            {
                $efficiency = 0;
            }
            
            $killIsk = $killIsk / 1000;
            $lossIsk = $lossIsk / 1000;
            
            $bar = new BarGraph($efficiency, 100);
            $barGenerated = $bar->generate();
            
            $sql = "UPDATE kb3_battles_cache SET
                        killIsk = {$killIsk},
                        lossIsk = {$lossIsk},
                        efficiency = {$efficiency},
                        bar = '{$barGenerated}',
                        kills = {$kills},
                        losses = {$losses},
                        involved = {$involved},
                        start = '{$timestampStart}',
                        end = '{$timestampEnd}',
                        ownersInvolved = {$numberOfOwnersInvolved}
                     WHERE battle_id = {$battleId}";
                     
            $qry->execute($sql);
            
            // update involved owners
            $sql = "DELETE FROM kb3_battles_owner_pilots
                WHERE battle_id = {$battleId}";
            $qry->execute($sql);
            
            // write involved owner pilots to special table
			if(isset($involvedOwnerIds) && is_array($involvedOwnerIds))
			{
				$sqlValues = array();
				foreach($involvedOwnerIds AS $ownerPilotId)
				{
					$sqlValues[] = "({$battleId}, {$ownerPilotId})";
				}
				$sql = "REPLACE INTO kb3_battles_owner_pilots (battle_id, plt_id) VALUES ".implode(",", $sqlValues);
				$qry->execute($sql);
			}
        }
        
        /**
         * deletes a battle from the cache table
         * @param int $battleId 
         */
        public static function deleteBattleFromCache($battleId)
        {
            $qry = DBFactory::getDBQuery();
            
            $sql = "DELETE FROM kb3_battles_owner_pilots
                        WHERE battle_id = {$battleId}";
            $qry->execute($sql);
            
            $sql = "DELETE FROM kb3_battles_cache
                        WHERE battle_id = {$battleId}";
            $qry->execute($sql);
           
        }
        
        /**
         * get battles which's timeframe is in or overlaps with the given time interval
         * @param long $systemId
         * @param String $timestampStart
         * @param String $timestampEnd 
         */
        public static function getBattlesInTimeframe($systemId, $timestampStart, $timestampEnd)
        {
            $qry = DBFactory::getDBQuery();
            $System = SolarSystem::getByID($systemId);
            $sql = "
              SELECT
                        battle_id, start, end
                    FROM kb3_battles_cache
                WHERE (( start >= '{$timestampStart}' AND start <= '{$timestampEnd}')
                OR (end >= '{$timestampStart}' AND end <= '{$timestampEnd}'))
                    AND system = '{$System->getName()}'
            ";
                
            $battles = array();
            $qry->execute($sql);
            while($row = $qry->getRow())
            {
                $battles[] = $row;
            }
            
            return $battles;
        }
        
}

class BattleList
{
    
	function __construct($system=0)
	{
		$this->qry_ = new DBQuery();
		$this->battles_ = array();
		$this->system = $system;
	}

	function execQuery()
	{
		if ($this->qry_->executed())
			return;

		if (config::get('fleet_battles_mod_cache') && $this->system == 0)
			return;

		$sys_sql = "";
		if ($this->system == 0)
			$sys_sql .= "select count(*) as cnt, kll_system_id from kb3_kills kll
                            INNER JOIN kb3_ships shp ON shp.shp_id = kll.kll_ship_id
                            WHERE shp_class != 38
                        group by kll_system_id
                        having cnt > ".config::get('fleet_battles_mod_minkills')." order by kll_system_id";
		else
			$sys_sql .= "select kll_system_id from kb3_kills
                            kll
                            INNER JOIN kb3_ships shp ON shp.shp_id = kll.kll_ship_id
			where kll_system_id = ".$this->system." AND shp_class != 38 group by kll_system_id";
		$this->qry_->execute($sys_sql);

		$kllq = new DBQuery(true);
		while($s_row = $this->qry_->getRow())
		{
			$b_battle = True;
			$limit = 1;
			$next_timestamp = (config::get('fleet_battles_mod_cache')) ? $this->getNextTimestamp($s_row['kll_system_id']) : '1970-01-01 00:00:00';
                        $lastKillWasBattle = FALSE;
			while ($b_battle == True)
			{
				$kll_sql = "select kll_id, kll_timestamp
					from kb3_kills where kll_system_id=".$s_row['kll_system_id']."
					and kll_timestamp > '".$next_timestamp."'
					order by kll_timestamp asc limit ".$limit;
				$kllq->execute($kll_sql);
				if ($k_row = $kllq->getRow())
				{
                                        $slidingTimeSeconds = config::get('fleet_battles_mod_maxtime')*3600;
					$next_timestamp = date('Y-m-d H:i:s',strtotime($k_row['kll_timestamp']) + $slidingTimeSeconds);
					if($lastKillWasBattle)
                                        {
                                            $next_sql = "select kll_timestamp from kb3_kills
                                                    where kll_system_id=".$s_row['kll_system_id']."
                                                    and kll_timestamp > '".$k_row['kll_timestamp']."'
                                                    and kll_timestamp <= '".$next_timestamp."'
                                                    order by kll_timestamp desc limit ".$limit;
                                        }
                                        
                                        else
                                        {
                                            $next_sql = "select kll_timestamp from kb3_kills
                                                    where kll_system_id=".$s_row['kll_system_id']."
                                                    and kll_timestamp >= '".$k_row['kll_timestamp']."'
                                                    and kll_timestamp <= '".$next_timestamp."'
                                                    order by kll_timestamp desc limit ".$limit;
                                        }
//                                        echo "<br/>".$next_sql."<br/>";
					$kllq->execute($next_sql);
					if ($n_row = $kllq->getRow())
					{
						$count_sql = "select count(*) as cnt from kb3_kills kll
                                                              INNER JOIN kb3_ships shp ON shp.shp_id = kll.kll_ship_id
							where kll_system_id=".$s_row['kll_system_id']."
							and kll_timestamp >= '".$k_row['kll_timestamp']."'
							and kll_timestamp <= '".$next_timestamp."' AND shp_class != 38";
                                                
						$kllq->execute($count_sql);
						if ($count = $kllq->getRow())
						{
							if ($count['cnt'] >= config::get('fleet_battles_mod_minkills'))
							{
                                                            // build SQL filter for filtering for board owners
                                                            $killFilters = $involvedFilters = array();
                                                            
                                                            $ownerAlliances = config::get('cfg_allianceid');
                                                            if(!empty($ownerAlliances))
                                                            {
                                                                $involvedFilters[] = "ind.ind_all_id IN (".implode(",", $ownerAlliances).")";
                                                                $killFilters[] = "kll.kll_all_id IN (".implode(",", $ownerAlliances).")";
                                                            }
 
                                                            $ownerCorps = config::get('cfg_corpid');
                                                            if(!empty($ownerCorps))
                                                            {
                                                                $involvedFilters[] = "ind.ind_crp_id IN (".implode(",", $ownerCorps).")";
                                                                $killFilters[] = "kll.kll_crp_id IN (".implode(",", $ownerCorps).")";
                                                            }
   
                                                            
                                                            $ownerPilots = config::get('cfg_pilotid');
                                                            if(!empty($ownerPilots))
                                                            {
                                                                $involvedFilters[] = "ind.ind_plt_id IN (".implode(",", $ownerPilots).")";
                                                                $killFilters[] = "kll.kll_victim_id IN (".implode(",", $ownerPilots).")";
                                                            }
                                                            
                                                            if(!empty($involvedFilters))
                                                            {
                                                                $involvedFilter = " AND (".implode(" OR ", $involvedFilters).")";
                                                            }
                                                            
                                                            else
                                                            {
                                                                $involvedFilter = "";
                                                            }
                                                            
                                                            if(!empty($killFilters))
                                                            {
                                                                $killFilter = " AND (".implode(" OR ", $killFilters).")";
                                                            }
                                                            
                                                            else
                                                            {
                                                                $killFilter = "";
                                                            }
           
                                                            
                                                            // count involved board owners ind.ind_plt_id AS pilotId, ind.ind_all_id AS allianceId, ind.ind_crp_id AS corpId
                                                            $involvedPilotsSql = "SELECT 
                                                                    pilotId
                                                                FROM (
                                                                        (
                                                                            SELECT DISTINCT ind.ind_plt_id AS pilotId
                                                                            FROM kb3_inv_detail ind
                                                                            WHERE ind_kll_id IN (
                                                                                select kll_id
                                                                                    from kb3_kills kll
                                                                                    where kll_system_id=".$s_row['kll_system_id']."
                                                                                        and kll_timestamp >= '".$k_row['kll_timestamp']."'
                                                                                        and kll_timestamp <= '".$next_timestamp."'
                                                                            ) {$involvedFilter}
                                                                         )
                                                                         UNION DISTINCT
                                                                        (
                                                                            select kll.kll_victim_id AS pilotId
                                                                                from kb3_kills kll
                                                                                where kll_system_id=".$s_row['kll_system_id']."
                                                                                    and kll_timestamp >= '".$k_row['kll_timestamp']."'
                                                                                    and kll_timestamp <= '".$next_timestamp."'
                                                                                    {$killFilter}
                                                                        )
                                                                ) pilots";

                                                            $kllq->execute($involvedPilotsSql);
                                                            
                                                            $involvedOwnerPilots = array();
                                                            while($involvedOwner = $kllq->getRow())
                                                            {
                                                                $involvedOwnerPilots[] = $involvedOwner["pilotId"];
                                                            }
                                                            $numberOfInvolvedOwners = count($involvedOwnerPilots);

								$system = new SolarSystem($s_row['kll_system_id']);
								$_battle = array('id' => $k_row['kll_id'],
									'system' => $system,
									'start' => $k_row['kll_timestamp'],
									'end' => $n_row['kll_timestamp'],
                                                                        'ownerPilotIds' => $involvedOwnerPilots);
								array_push($this->battles_,$_battle);
                                                                $lastKillWasBattle = TRUE;
							}
						}
                                                else
                                                {
                                                    $lastKillWasBattle = FALSE;
                                                }
					}
				}
				else
				{
					$b_battle = False;
				}
			}
		}
		usort($this->battles_,array($this,"cmp"));
	}

	function cmp($a, $b)
	{
		$t1 = strtotime($a['start']);
		$t2 = strtotime($b['start']);
		if ($t1 == $t2) return 0;
		return ($t1 < $t2) ? 1 : -1;
	}

	function setPage($page)
	{
		$this->page_ = $page;
		$this->offset_ = ($page * $this->limit_) - $this->limit_;
	}

	function getBattle($index)
	{
		$this->execQuery();
		$battle = $this->battles_[$index];
		return new Battle($battle);
	}

	function getNextTimestamp($system_id)
	{
		$system_lookup = "select sys_name from kb3_systems where sys_id = ".$system_id." limit 1";
		$sysq = new DBQuery();
		$sysq->execute($system_lookup);

		if ($system_name = $sysq->getRow())
			$sql = "select end from kb3_battles_cache where system = '".$system_name['sys_name']."' order by end desc limit 1";

		$timeq = new DBQuery();
		$timeq->execute($sql);
		if ($ts = $timeq->getRow())
			return $ts['end'];
		else
			return '1970-01-01 00:00:00';

	}

	function getCount()
	{
		$this->execQuery();
		return count($this->battles_);
	}
}

class BattleListTable
{
    
        private $plimit_ = 0;
	private $poffset_ = 0;
        
        // filtering
        private $isFiltered = FALSE;
        private $filterRegionId;
        private $filterSystemName;
        private $filterMonth;
        private $filterKillsComparator;
        private $filterKillsCount;
        private $filterLossesComparator;
        private $filterLossesCount;
        private $filterEfficiencyComparator;
        private $filterEfficiencyCount;
        private $filterInvolvedOwnersComparator;
        private $filterInvolvedOwnersCount;
        
        
        
        
	function __construct($contractlist)
	{
		$this->contractlist_ = $contractlist;
		$this->inv_all_time = 0;
		$this->kll_all_time = 0;
		$this->lss_all_time = 0;
		$this->kll_isk_all_time = 0;
		$this->lss_isk_all_time = 0;
		$this->metric_total = 0;
                if(isset($_POST["filter"]))
                {
                    $this->isFiltered = TRUE;
                    
                    // region
                    $this->filterRegionId = $_POST["filterRegion"];
                    
                    // system
                    $this->filterSystemName = trim($_POST["filterSystem"]);

                    // month
                    $this->filterMonth = $_POST["filterMonth"];


                    // kills
                    $this->filterKillsCount = trim($_POST["filterKillsCount"]);
                    $this->filterKillsComparator = $_POST["filterKillsComparator"];


                    // losses
                    $this->filterLossesCount = trim($_POST["filterLossesCount"]);
                    $this->filterLossesComparator = $_POST["filterLossesComparator"];


                    // efficiency
                    $this->filterEfficiencyCount = trim($_POST["filterEfficiencyCount"]);
                    $this->filterEfficiencyComparator = $_POST["filterEfficiencyComparator"];


                    // involved owners
                    $this->filterInvolvedOwnersCount = trim($_POST["filterInvolvedOwnersCount"]);
                    $this->filterInvolvedOwnersComparator = $_POST["filterInvolvedOwnersComparator"];
                }
                
                if(!$this->isFiltered || isset($_POST["reset"]))
                {
                    // region
                    $this->filterRegionId = -1;
                    
                    // system
                    $this->filterSystemName = "";

                    // month
                    $this->filterMonth = "1-1";

                    // kills
                    $this->filterKillsCount = "";
                    $this->filterKillsComparator = "gt";

                    // losses
                    $this->filterLossesCount = "";
                    $this->filterLossesComparator = "gt";

                    // efficiency
                    $this->filterEfficiencyCount = "";
                    $this->filterEfficiencyComparator = "gt";

                    // involved owners
                    $this->filterInvolvedOwnersCount = "";
                    $this->filterInvolvedOwnersComparator = "gt";
                }
	}
        
        public function setPageSplitter($pagesplitter)
	{
		if (isset($_GET['page'])) $page = $_GET['page'];
		else $page = 1;
		$this->plimit_ = $pagesplitter->getSplit();
		$this->poffset_ = ($page * $this->plimit_) - $this->plimit_;
	}

	public function setPageSplit($split)
	{
		if (isset($_GET['page'])) $page = $_GET['page'];
		else $page = 1;
		$this->plimit_ = $split;
		$this->poffset_ = ($page * $this->plimit_) - $this->plimit_;
	}

	function paginate($paginate, $page = 1)
	{
		if (!$page) $page = 1;
		$this->paginate_ = $paginate;
		//$this->contractlist_->setLimit($paginate);
		$this->contractlist_->setPage($page);
	}

	function getTableStats()
	{
		// Don't use caching to save memory. These queries are once only.
		$qry = new DBQuery(true);

		$this->metric_total = $this->contractlist_->getCount();
                
		for ($i=0; $i < $this->contractlist_->getCount(); $i++)
		{   
			$contract = $this->contractlist_->getBattle($i);
                        
			if (!(($contract->getKillISK() + $contract->getLossISK()) > (config::get('fleet_battles_mod_minisk') * 1000000) ))
			{
				$this->metric_total = $this->metric_total - 1;
				continue 1;
			}
                        
			// generate all necessary objects within the contract
			$contract->execQuery();
			for ($j = 0; $j < 2; $j++)
			{
				if ($j == 0)
				{
					$list = &$contract->llist_;
				}
				else
				{
					$list = &$contract->klist_;
				}
                                $isk = $list->getISK();

				if ($j == 0)
				{
					$ldata = array('losses' => $list->getCount(), 'lossisk' => $isk / 1000 );
					$this->lss_isk_all_time += $isk;
				}
				else
				{
					$kdata = array('kills' => $list->getCount(), 'killisk' => $isk / 1000 );
					$this->kll_isk_all_time += $isk;
				}
			}
			if ($kdata['killisk'])
			{
				$efficiency = round($kdata['killisk'] / ($kdata['killisk']+$ldata['lossisk']) *100, 2);
			}
			else
			{
				$efficiency = 0;
			}
			$bar = new BarGraph($efficiency, 100);

			if (!config::get('fleet_battles_mod_cache'))
			{
				$this->inv_all_time += $contract->getInvolved();
				$this->kll_all_time += $contract->getKills();
				$this->lss_all_time += $contract->getLosses();
			}
    
                        
			$battle = array_merge(array('name' => $contract->getName(), 'startdate' => $contract->getStartDate(), 'enddate' => $contract->getEndDate(),
				'bar' => $bar->generate(), 'endtime' => date('H:i:s', strtotime($contract->getEndDate())),
				'efficiency' => $efficiency, 'involved' => $contract->getInvolved(),
				'kll_id' => $contract->getKillID(), 'id' => $contract->getID(), 'numberOfOwnersInvolved' => $contract->getNumberOfOwnersInvolved(), 'ownerPilotIds' => $contract->getOwnersInvolved()), $kdata, $ldata);
                        
			if (config::get('fleet_battles_mod_cache'))
				$this->cacheBattle($battle);
			else
				$tbldata[] = $battle;
		}
               
		if (config::get('fleet_battles_mod_cache'))
		{
			
			$cacheq = DBFactory::getDBQuery();
                        $whereSql = "";
                        if($this->isFiltered)
                        {
                            $filterTerms = $this->getFilterArgumentsWhereSql();

                            // build filter-string
                            if(!empty($filterTerms))
                            {
                                $whereSql = "WHERE ".implode(" AND ", $filterTerms);
                            }
                        }
                        
                        $sql = "SELECT * 
                                    FROM kb3_battles_cache bc
                                        INNER JOIN kb3_systems sys ON sys.sys_name = bc.system
                                        INNER JOIN kb3_constellations con ON con.con_id = sys.sys_con_id
                                        INNER JOIN kb3_regions reg ON reg.reg_id = con.con_reg_id
                                    {$whereSql} 
                                    ORDER BY end DESC";
			$cacheq->execute($sql);
                        
			while($cb = $cacheq->getRow())
			{
                                $args = array();
                                $args[] = array('a', 'kill_related', true);

				$tbldata[] = array('name' => $cb['system'], 'kll_id' => $cb['kll_id'], 'id' => $cb['battle_id'], 'enddate' => $cb['end'],
					'startdate' => $cb['start'], 'endtime' => date('H:i:s', strtotime($cb['end'])), 'kills' => $cb['kills'],
					'losses' => $cb['losses'], 'efficiency' => $cb['efficiency'], 'involved' => $cb['involved'],
					'killisk' => $cb['killisk'], 'lossisk' => $cb['lossisk'], 'bar' => $cb['bar'], 'numberOfOwnersInvolved' => $cb['ownersInvolved'], 'battle_url' => edkURI::build($args, array('kll_id', $cb['kll_id'], true), array('battle', true, true)));
			}
		}
		return $tbldata;
	}

	function cacheBattle($battle)
	{
		$sql = "insert into kb3_battles_cache VALUES(null,";
		$sql .= $battle['kll_id'].",".$battle['killisk'].",".$battle['lossisk'].",";
		$sql .= $battle['efficiency'].",'".$battle['bar']."',";
		$sql .= $battle['kills'].",".$battle['losses'].",".$battle['involved'].",'";
		$sql .= $battle['name']."','".$battle['startdate']."','".$battle['enddate']."', ".count($battle["ownerPilotIds"]).")";

		$cacheq = DBFactory::getDBQuery();
		$cacheq->execute($sql);
                $battleId = $cacheq->getInsertID();
                
                // write involved owner pilots to special table
                $sqlValues = array();
                foreach($battle["ownerPilotIds"] AS $ownerPilotId)
                {
                    $sqlValues[] = "({$battleId}, {$ownerPilotId})";
                }
                
                $sql = "REPLACE INTO kb3_battles_owner_pilots (battle_id, plt_id) VALUES ".implode(",", $sqlValues);
                $cacheq->execute($sql);
                
		return;
	}
        
        /**
         * returns the filter arguments as SQL statements for concatenation in the WHERE clause 
         */
        public function getFilterArgumentsWhereSql()
        {
            $query = DBFactory::getDBQuery();
            $filterTerms = array();
            if($this->isFiltered)
            {
                // system
                if(trim($this->filterSystemName) != "")
                {
                    $filterTerms[] = "system LIKE '{$query->escape($this->filterSystemName)}%'";
                }

                // region
                if($this->filterRegionId != -1 && is_numeric($this->filterRegionId))
                {
                    $filterTerms[] = "reg.reg_id={$query->escape($this->filterRegionId)}";
                }

                // month
                if($this->filterMonth != "1-1")
                {
                    $filterTerms[] = "DATE_FORMAT(start, '%Y-%m')='{$query->escape($this->filterMonth)}'";
                }

                // kill count
                if($this->filterKillsCount != "" && is_numeric($this->filterKillsCount))
                {
                    if($this->filterKillsComparator == "gt")
                    {
                        $filterTerms[] = "kills>={$query->escape($this->filterKillsCount)}";
                    }

                    else
                    {
                        $filterTerms[] = "kills<={$query->escape($this->filterKillsCount)}";
                    }
                }
                
                else
                {
                    $this->filterKillsCount = "";
                }

                // loss count
                if($this->filterLossesCount != "" && is_numeric($this->filterLossesCount))
                {
                    if($this->filterLossesComparator == "gt")
                    {
                        $filterTerms[] = "losses>={$query->escape($this->filterLossesCount)}";
                    }

                    else
                    {
                        $filterTerms[] = "losses<={$query->escape($this->filterLossesCount)}";
                    }
                }
                
                else
                {
                   $this->filterLossesCount = "";
                }

                // efficiency 
                if($this->filterEfficiencyCount != "" && is_numeric($this->filterEfficiencyCount))
                {
                    if($this->filterEfficiencyComparator == "gt")
                    {
                        $filterTerms[] = "efficiency>={$query->escape($this->filterEfficiencyCount)}";
                    }

                    else
                    {
                        $filterTerms[] = "efficiency<={$query->escape($this->filterEfficiencyCount)}";
                    }
                }
                
                else
                {
                    $this->filterEfficiencyCount = "";
                }

                // invovled owners 
                if($this->filterInvolvedOwnersCount != "" && is_numeric($this->filterInvolvedOwnersCount))
                {
                    if($this->filterInvolvedOwnersComparator == "gt")
                    {
                        $filterTerms[] = "ownersInvolved>={$query->escape($this->filterInvolvedOwnersCount)}";
                    }

                    else
                    {
                        $filterTerms[] = "ownersInvolved<={$query->escape($this->filterInvolvedOwnersCount)}";
                    }
                }
                
                else
                {
                   $this->filterInvolvedOwnersCount = ""; 
                }
            }
            return $filterTerms;
        }
        
        function getCount()
        {
            if (!config::get('fleet_battles_mod_cache'))
            {
                return 0;
            }
            
            $countSql = 'select COUNT(*) as numberOfBattles
				from kb3_battles_cache';
            $numberOfBattlesQuery = new DBQuery();
            $numberOfBattlesQuery->execute($countSql);
            $numberOfBattles = $numberOfBattlesQuery->getRow();
            return $numberOfBattles["numberOfBattles"];
            
        }
        
        function getRegions()
        {
            $regionsQuery = DBFactory::getDBQuery();
            $sql = "SELECT DISTINCT reg.reg_name AS name, reg.reg_id AS id
                        FROM kb3_battles_cache bc
                            INNER JOIN kb3_systems sys ON sys.sys_name = bc.system
                            INNER JOIN kb3_constellations const ON const.con_id = sys.sys_con_id
                            INNER JOIN kb3_regions reg ON reg.reg_id = const.con_reg_id
                        ORDER BY name ASC";
            $regionsQuery->execute($sql);
            
            $regions = array();
            
            while($region = $regionsQuery->getRow())
            {
                $regions[] = array("name" => $region["name"], "id" => $region["id"]);
            }
            
            return $regions;
        }
        
        
        function getMonths()
        {
            $monthsQuery = DBFactory::getDBQuery();
            $sql = "SELECT DISTINCT DATE_FORMAT(start, '%Y/%m') AS name, DATE_FORMAT(start, '%Y-%m') AS month
                        FROM kb3_battles_cache
                        ORDER BY name DESC";
            $monthsQuery->execute($sql);
            
            $months = array();
            
            while($month = $monthsQuery->getRow())
            {
                $months[] = array("name" => $month["name"], "month" => $month["month"]);
            }
            
            return $months;
        }

	function generate()
	{
                global $smarty;
                
		if ($table = $this->getTableStats())
		{
                        
                        // filtering
                        $solarSystem = SolarSystem::lookup($this->filterSystemName);
                        

			$smarty->assign('contract_getactive', 1);
			$smarty->assign('version', config::get('fleet_battles_mod_version'));

			$stats = array();
			if (!config::get('fleet_battles_mod_cache'))
			{
				$smarty->assign('caching',0);
				$stats['tbattles'] = $this->metric_total;
				$stats['ainvolved'] = $this->inv_all_time;
				$stats['akills'] = $this->kll_all_time;
				$stats['alosses'] = $this->lss_all_time;
				$stats['akillisk'] = $this->kll_isk_all_time;
				$stats['alossisk'] = $this->lss_isk_all_time;
			}
			else
			{
				$smarty->assign('caching',1);
                                $statq = DBFactory::getDBQuery();
                                $whereSql = "";
                                if($this->isFiltered)
                                {
                                    $filterTerms = $this->getFilterArgumentsWhereSql();
                                    
                                    // build filter-string
                                    if(!empty($filterTerms))
                                    {
                                        $whereSql = "WHERE ".implode(" AND ", $filterTerms);
                                    }

                                }
                                
				$statsql = "SELECT 
                                                    COUNT(*) as tbattles, SUM(involved) as ainvolved, SUM(losses) as alosses, SUM(kills) as akills, SUM(killisk) as akillisk, SUM(lossisk) as alossisk, SUM(ownersInvolved) as ainvolvedowners
                                                FROM kb3_battles_cache bc
                                                    INNER JOIN kb3_systems sys ON sys.sys_name = bc.system
                                                    INNER JOIN kb3_constellations con ON con.con_id = sys.sys_con_id
                                                    INNER JOIN kb3_regions reg ON reg.reg_id = con.con_reg_id
                                                {$whereSql}";

				$statq->execute($statsql);
				$stats = $statq->getRow();
			}
			$smarty->assign('tbattles', $stats['tbattles']);
			$smarty->assign('ainvolved', $stats['ainvolved'] / $stats['tbattles']);
			$smarty->assign('akills', $stats['akills'] / $stats['tbattles']);
			$smarty->assign('alosses', $stats['alosses'] / $stats['tbattles']);
                        $smarty->assign('ainvolvedowners', $stats['ainvolvedowners'] / $stats['tbattles']);

			$stats['aefficiency'] = round($stats['akillisk'] / ($stats['akillisk']+$stats['alossisk']) * 100, 2);
			$stats['abar'] = new BarGraph($stats['aefficiency'], 100, 75);

			$smarty->assign('abar', $stats['abar']->generate());
			$smarty->assign('aefficiency', $stats['aefficiency']);
                        
                        
                        // filter
                        $smarty->assign("filterSystem", $this->filterSystemName);
                        $smarty->assign("filterRegionId", $this->filterRegionId);
                        $smarty->assign("filterMonthSelected", $this->filterMonth);
                        
                        $smarty->assign("filterKillsComparatorSelected", $this->filterKillsComparator);
                        $smarty->assign("filterKillsCount", $this->filterKillsCount);
                        
                        $smarty->assign("filterLossesComparatorSelected", $this->filterLossesComparator);
                        $smarty->assign("filterLossesCount", $this->filterLossesCount);
                        
                        $smarty->assign("filterEfficiencyComparatorSelected", $this->filterEfficiencyComparator);
                        $smarty->assign("filterEfficiencyCount", $this->filterEfficiencyCount);
                        
                        $smarty->assign("filterInvolvedOwnersComparatorSelected", $this->filterInvolvedOwnersComparator);
                        $smarty->assign("filterInvolvedOwnersCount", $this->filterInvolvedOwnersCount);
                        
                        
                        
                        $smarty->assign("filterRegions", array_merge(array(array("name" => "All", "id" => -1)), $this->getRegions()));
                        $smarty->assign("filterMonths", array_merge(array(array("name" => "All", "month" => "1-1")), $this->getMonths()));
                        $smarty->assign("filterMonths", array_merge(array(array("name" => "All", "month" => "1-1")), $this->getMonths()));
                        
                        $filterKillsCompatators =  array(
                            array("name" => "gt", "symbol" => ">="),
                            array("name" => "lt", "symbol" => "<=")
                        );
                        $filterLossesComparators = array(
                            array("name" => "gt", "symbol" => ">="),
                            array("name" => "lt", "symbol" => "<=")
                        );
                        $filterEfficiencyComparators = array(
                            array("name" => "gt", "symbol" => ">="),
                            array("name" => "lt", "symbol" => "<=")
                        );
                        $filterInvolvedOwnersComparators = array(
                            array("name" => "gt", "symbol" => ">="),
                            array("name" => "lt", "symbol" => "<=")
                        );
                        
                        $smarty->assign("filterKillsComparators", $filterKillsCompatators);
                        $smarty->assign("filterLossesComparators", $filterLossesComparators);
                        $smarty->assign("filterEfficiencyComparators", $filterEfficiencyComparators);
                        $smarty->assign("filterInvolvedOwnersComparators", $filterInvolvedOwnersComparators);


			if (config::get('fleet_battles_mod_displaymetrics'))
			{
				$smarty->assign('minkills',config::get('fleet_battles_mod_minkills'));
				$smarty->assign('minisk',config::get('fleet_battles_mod_minisk'));
				$smarty->assign('maxtime',config::get('fleet_battles_mod_maxtime'));
			}
                       
                        if (config::get('fleet_battles_mod_cache') && !$this->isFiltered)
                        {
                            // paginate
                            if (is_numeric($this->poffset_) && is_numeric($this->plimit_)&& $this->plimit_ > 0)
                                    $table = array_slice($table, $this->poffset_, $this->plimit_);
                        }
                        
                        $smarty->assign_by_ref('battles', $table);
                        return $smarty->fetch(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'battlelisttable_filter.tpl').
                                $smarty->fetch(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'battlelisttable.tpl');
		}
                
                else
                {
                    $smarty->assign('tbattles', 0);
                    $smarty->assign('ainvolved', 0);
                    $smarty->assign('akills', 0);
                    $smarty->assign('alosses', 0);
                    $smarty->assign('ainvolvedowners', 0);

                    $stats['aefficiency'] = 0;
                    $stats['abar'] = new BarGraph(0, 100, 75);

                    $smarty->assign('abar', $stats['abar']->generate());
                    $smarty->assign('aefficiency', 0);
                    
                    $smarty->assign('battles', array());
                    
                    // filter
                        $smarty->assign("filterSystem", $this->filterSystemName);
                        $smarty->assign("filterRegionId", $this->filterRegionId);
                        $smarty->assign("filterMonthSelected", $this->filterMonth);
                        
                        $smarty->assign("filterKillsComparatorSelected", $this->filterKillsComparator);
                        $smarty->assign("filterKillsCount", $this->filterKillsCount);
                        
                        $smarty->assign("filterLossesComparatorSelected", $this->filterLossesComparator);
                        $smarty->assign("filterLossesCount", $this->filterLossesCount);
                        
                        $smarty->assign("filterEfficiencyComparatorSelected", $this->filterEfficiencyComparator);
                        $smarty->assign("filterEfficiencyCount", $this->filterEfficiencyCount);
                        
                        $smarty->assign("filterInvolvedOwnersComparatorSelected", $this->filterInvolvedOwnersComparator);
                        $smarty->assign("filterInvolvedOwnersCount", $this->filterInvolvedOwnersCount);
                        
                        
                        
                        $smarty->assign("filterRegions", array_merge(array(array("name" => "All", "id" => -1)), $this->getRegions()));
                        $smarty->assign("filterMonths", array_merge(array(array("name" => "All", "month" => "1-1")), $this->getMonths()));
                        $smarty->assign("filterMonths", array_merge(array(array("name" => "All", "month" => "1-1")), $this->getMonths()));
                        
                        $filterKillsCompatators =  array(
                            array("name" => "gt", "symbol" => ">="),
                            array("name" => "lt", "symbol" => "<=")
                        );
                        $filterLossesComparators = array(
                            array("name" => "gt", "symbol" => ">="),
                            array("name" => "lt", "symbol" => "<=")
                        );
                        $filterEfficiencyComparators = array(
                            array("name" => "gt", "symbol" => ">="),
                            array("name" => "lt", "symbol" => "<=")
                        );
                        $filterInvolvedOwnersComparators = array(
                            array("name" => "gt", "symbol" => ">="),
                            array("name" => "lt", "symbol" => "<=")
                        );
                        
                        $smarty->assign("filterKillsComparators", $filterKillsCompatators);
                        $smarty->assign("filterLossesComparators", $filterLossesComparators);
                        $smarty->assign("filterEfficiencyComparators", $filterEfficiencyComparators);
                        $smarty->assign("filterInvolvedOwnersComparators", $filterInvolvedOwnersComparators);
                    
                    return $smarty->fetch(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'battlelisttable_filter.tpl').
                                $smarty->fetch(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'battlelisttable.tpl');
                }
	}
        
        function getStatsHtml()
        {
            global $smarty;
            
            if (config::get('fleet_battles_mod_displaymetrics'))
            {
                    $smarty->assign('minkills',config::get('fleet_battles_mod_minkills'));
                    $smarty->assign('minisk',config::get('fleet_battles_mod_minisk'));
                    $smarty->assign('maxtime',config::get('fleet_battles_mod_maxtime'));
            }
            return $smarty->fetch(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'battlelisttable_stats.tpl');
        }
}
