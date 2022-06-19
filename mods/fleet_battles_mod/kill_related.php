<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/*
 * Build the related kills page
 * This file is part of the Combined Fleet Battles Mod
 * @author [FRRGL] Salvoxia
 * @package EDK
 */
class pKillRelatedFleetBattle extends pageAssembly
{

	/** @var array */
	protected $systems = array();
	/** @var boolean */
	protected $adjacent = false;
	/** @var array */
	protected $victimAll = array();
	/** @var array */
	protected $invAll = array();
	/** @var array */
	protected $victimCorp = array();
	/** @var array */
	protected $invCorp = array();
	/** @var integer */
	public $kll_id;
	/** @var Kill */
	protected $kill;
	/** @var Page */
	public $page;
	/** @var array */
	protected $menuOptions = array();
        /** @var string */
        protected $modDir;
        /** @var string */
        protected $includeDir;
        /** @var string */
        protected $cssDir;
        /** @var string */
        protected $templateDir;
        /** @var string */
        protected $jsDir;
        /** @var boolean */
        protected $displayingBattle;
        /** @var array */
        protected $lossValues;
        /** @var array */
        protected $lkillValues;
        /** @var array */
        protected $timeLine;
        /** @var KillSummaryTable */
        protected $summaryTable;
        /** @var KillList */
        protected $klist;
        /** @var KillList */
        protected $llist;
        /** @var array */
        protected $pods;
        /** @var int */
        protected $scl_id;
        /** @var array */
        protected $sides;
        /** @var string */
        protected $firstts;
        /** @var string */
        protected $lastts;
        /** @var array */
        protected $battlesToUpdate;
        /** @var array */
        protected $damageOverview;
        /** @var boolean */
        protected $isFixed;
        /** @var array */        
        protected $statusMessages = array();
        /** @var int */
        protected $damageTotalFriendly = 0;
        /** @var int */
        protected $damageTotalHostile = 0;
        /** @var int */
        protected $numberOfInvolvedOwners = 0;
        /** @var array */
        protected $involvedOwners;
	
	function __construct()
	{
		parent::__construct();
		
	}

	/**
	 *  Reset the assembly object to prepare for creating the context.
	 */
	function context()
	{
		parent::__construct();
		$this->queue("menuSetup");
		$this->queue("menu");
	}

	/**

	 * Start constructing the page.

	 * Prepare all the shared variables such as dates and check alliance ID.
	 *
	 */
	function start()
	{
                // set some directory paths for this mod first
                $this->modDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;;
                $this->includeDir = $this->modDir . 'include' . DIRECTORY_SEPARATOR;
                $this->cssDir = config::get("cfg_kbhost") . '/mods/' . basename(dirname(__FILE__)) . '/css/';
                $this->templateDir = $this->modDir . 'template' . DIRECTORY_SEPARATOR;
                $this->jsDir = config::get("cfg_kbhost") . '/mods/' . basename(dirname(__FILE__)) . '/js/';
                $this->imgDir = config::get("cfg_kbhost") . '/mods/' . basename(dirname(__FILE__)) . '/img/';
            
		$this->page = new Page('Related kills & losses');
		$this->page->addHeader('<meta name="robots" content="index, nofollow" />');

                $this->isFixed = FALSE;
		$this->kll_id = (int) edkURI::getArg('kll_id', 1);
		if (!$this->kll_id) {
			$this->kll_external_id = (int) edkURI::getArg('kll_ext_id');
			if (!$this->kll_external_id) {
				// internal and external ids easily overlap so we can't guess which
				$this->kll_id = (int) edkURI::getArg(null, 1);
				$this->kill = Kill::getByID($this->kll_id);
			} else {
				$this->kill = new Kill($this->kll_external_id, true);
				$this->kll_id = $this->kill->getID();
			}
		} else {
			$this->kill = Kill::getByID($this->kll_id);
		}
                
                // read url parameters
		$this->adjacent = (bool) edkURI::getArg('adjacent');
		$this->scl_id = (int) edkURI::getArg('scl_id');
                $this->displayingBattle = (bool) edkURI::getArg('battle');
		$this->menuOptions = array();

		if (!$this->kll_id || !$this->kill->exists()) {
			echo 'No valid kill id specified';
			exit;
		}
		if ($this->kill->isClassified()) {
			Header("Location: ".KB_HOST."/?a=kill_detail&kll_id=".$this->kll_id);
			die();
                
		}
                
                // set javascript headers
                $this->page->addHeader("<link rel=\"stylesheet\" href=\"".$this->cssDir."tabber.css\" TYPE=\"text/css\" MEDIA=\"screen\">");
                $this->page->addHeader("<script type=\"text/javascript\">var fleetBattlesLoadingImage = '".$this->imgDir."loading.gif';</script>");
                $this->page->addHeader("<script type=\"text/javascript\" src=\"".$this->jsDir."entity.js\"></script>");
                $this->page->addHeader("<script type=\"text/javascript\" src=\"".$this->jsDir."fleetBattles.js\"></script>");
                $this->page->addHeader("<script type=\"text/javascript\" src=\"".$this->jsDir."tabber.js\"></script>");
                $this->page->addHeader('<link rel="stylesheet" type="text/css" href="'.$this->cssDir.'style.css">');
                
                // include helpers
                include_once($this->includeDir."class.helpers.php");

	}

	public function getInvolved()
	{
		$this->victimAll = array();
		$this->invAll = array();
		$this->victimCorp = array();
		$this->invCorp = array();
		// Find all involved parties not in the same corp/alliance as the victim. If
		// the board has an owner swap sides if necessary so board owner is the killer
		foreach ($this->kill->getInvolved() as $inv) {
			if (strcasecmp($inv->getAlliance()->getName(), 'None')) {
				if ($inv->getAllianceID() != $this->kill->getVictimAllianceID()) {
					$this->invAll[$inv->getAllianceID()] = $inv->getAllianceID();
				}
			} elseif ($inv->getCorpID() != $this->kill->getVictimCorpID())
					$this->invCorp[$inv->getCorpID()] = $inv->getCorpID();
		}
		if (strcasecmp($this->kill->getVictimAllianceName(), 'None'))
				$this->victimAll[$this->kill->getVictimAllianceID()] = $this->kill->getVictimAllianceID();
		else
				$this->victimCorp[$this->kill->getVictimCorpID()] = $this->kill->getVictimCorpID();

		// Check which side board owner is on and make that the kill side. The other
		// side is the loss side. If board owner is on neither then victim is the loss
		// side.
		if (in_array($this->kill->getVictimAllianceID(), config::get('cfg_allianceid'))
				|| in_array($this->kill->getVictimCorpID(), config::get('cfg_corpid'))) {
			$tmp = $this->victimAll;
			$this->victimAll = $this->invAll;
			$this->invAll = $tmp;
			$tmp = $this->victimCorp;
			$this->victimCorp = $this->invCorp;
			$this->invCorp = $tmp;
		}
	}

	public function buildStats()
	{
		// this is a fast query to get the system and timestamp
		$rqry = DBFactory::getDBQuery();
		if ($this->adjacent) {
			$rsql = 'SELECT kll_timestamp, sjp_to as sys_id from kb3_kills
				join kb3_systems a ON (a.sys_id = kll_system_id)
				join kb3_system_jumps on (sjp_from = a.sys_id)
				where kll_id = '.$this->kll_id.' UNION
				SELECT kll_timestamp, kll_system_id as sys_id from kb3_kills
				where kll_id = '.$this->kll_id;
		} else {
			$rsql = 'SELECT kll_timestamp, kll_system_id as sys_id from kb3_kills
				where kll_id = '.$this->kll_id;
		}
		$rqry->execute($rsql);
		while ($rrow = $rqry->getRow()) {
			$this->systems[] = $rrow['sys_id'];
			$basetime = $rrow['kll_timestamp'];
		}

		// now we get all kills in that system for +-4 hours
		$query = 'SELECT kll.kll_timestamp AS ts FROM kb3_kills kll WHERE kll.kll_system_id IN ('.implode(',', $this->systems).
				') AND kll.kll_timestamp <= "'.(date('Y-m-d H:i:s', strtotime($basetime) + (config::get('fleet_battles_mod_maxtime') * 60 * 60))).'"'.
				' AND kll.kll_timestamp >= "'.(date('Y-m-d H:i:s', strtotime($basetime) - (config::get('fleet_battles_mod_maxtime') * 60 * 60))).'"'.
				' ORDER BY kll.kll_timestamp ASC';
		$qry = DBFactory::getDBQuery();
		$qry->execute($query);
		$ts = array();
                $timestampsRaw = array();
		while ($row = $qry->getRow()) {
			$time = strtotime($row['ts']);
			$ts[intval(date('H', $time))][] = $row['ts'];
                        $timestampsRaw[] = $row["ts"];
		}
                
                // only for non-battles
                if(!$this->displayingBattle)
                {
                    // this tricky thing looks for gaps of more than 1 hour and creates an intersection
                    $baseh = date('H', strtotime($basetime));
                    $maxc = count($ts);
                    $times = array();
                    for ($i = 0; $i < $maxc; $i++) {
                            $h = ($baseh + $i) % 24;
                            if (!isset($ts[$h])) {
                                    break;
                            }
                            foreach ($ts[$h] as $timestamp) {
                                    $times[] = $timestamp;
                            }
                    }
                    for ($i = 0; $i < $maxc; $i++) {
                            $h = ($baseh - $i) % 24;
                            if ($h < 0) {
                                    $h += 24;
                            }
                            if (!isset($ts[$h])) {
                                    break;
                            }
                            foreach ($ts[$h] as $timestamp) {
                                    $times[] = $timestamp;
                            }
                    }
                    unset($ts);
                    asort($times);

                    // we got 2 resulting timestamps
                    $this->firstts = array_shift($times);
                    $this->lastts = array_pop($times);
                }
                
                // displaying a battle
                else
                {
                    // take the first and last timestamp from our original query
                    $this->firstts = array_shift($timestampsRaw);
                    $this->lastts = array_pop($timestampsRaw);
                }
                
                // get parameters for overriding start and end times
                // dirty hook for the enlightened circle ;)
                $overrideStartTime = str_replace('%20', ' ', edkURI::getArg('starttime'));
                $overrideEndTime = str_replace('%20', ' ', edkURI::getArg('endtime'));
                if($overrideStartTime && strtotime($overrideStartTime))
                {
                    $this->firstts = $overrideStartTime;          
                }
                if($overrideEndTime && strtotime($overrideEndTime))
                {
                    $this->lastts = $overrideEndTime;
                }
                
                // unfiltered kill list (no ship class filter applied)
		$this->kslist = new KillList();
		$this->kslist->setOrdered(true);
		foreach ($this->systems as $system)
			$this->kslist->addSystem($system);
		$this->kslist->setStartDate($this->firstts);
		$this->kslist->setEndDate($this->lastts);
		involved::load($this->kslist,'kill');

                
                // unfiltered loss list (no ship class filter applied)
		$this->lslist = new KillList();
		$this->lslist->setOrdered(true);
		foreach ($this->systems as $system)
			$this->lslist->addSystem($system);
		$this->lslist->setStartDate($this->firstts);
		$this->lslist->setEndDate($this->lastts);
		involved::load($this->lslist,'loss');

                // filtered kill list (shipclass filter applied)
		$this->klist = new KillList();
		$this->klist->setOrdered(true);
		$this->klist->setCountComments(true);
		$this->klist->setCountInvolved(true);
		foreach ($this->systems as $system)
			$this->klist->addSystem($system);
		$this->klist->setStartDate($this->firstts);
		$this->klist->setEndDate($this->lastts);
		involved::load($this->klist,'kill');


                // filtered loss list (ship class filter applied)
		$this->llist = new KillList();
		$this->llist->setOrdered(true);
		$this->llist->setCountComments(true);
		$this->llist->setCountInvolved(true);
		foreach ($this->systems as $system)
			$this->llist->addSystem($system);
		$this->llist->setStartDate($this->firstts);
		$this->llist->setEndDate($this->lastts);
		involved::load($this->llist,'loss');

                // apply ship class filters
		if ($this->scl_id) {

			$this->klist->addVictimShipClass($this->scl_id);
			$this->llist->addVictimShipClass($this->scl_id);
		}
                
                // if manual side assignment is enabled
                if(config::get("fleet_battles_mod_sideassign"))
                {
                    // get possible side assignments
                    $sideAssignments = getSideAssignments($this->systems[0], $this->firstts, $this->lastts);
                    $sideAssignmentMap = array();
                    // apply
                    foreach($sideAssignments AS $sideAssignment)
                    {
                        $this->isFixed = TRUE;
                        
                        $sideAssignmentMap[$sideAssignment["entity_type"]][$sideAssignment["entity_id"]] = $sideAssignment["side"];

                        // entity is an alliance
                        if($sideAssignment["entity_type"] == "alliance")
                        {
                            // alliance is an enemy
                            if($sideAssignment["side"] == "e")
                            {
                                $this->kslist->addVictimAlliance($sideAssignment["entity_id"]);
                                $this->lslist->addInvolvedAlliance($sideAssignment["entity_id"]);
                                $this->klist->addVictimAlliance($sideAssignment["entity_id"]);
                                $this->llist->addInvolvedAlliance($sideAssignment["entity_id"]);
                            }
                            
                            // alliance is an ally
                            else
                            {
                                $this->kslist->addInvolvedAlliance($sideAssignment["entity_id"]);
                                $this->lslist->addVictimAlliance($sideAssignment["entity_id"]);
								// also add as involved alliance for blue-on-blue kills
								$this->lslist->addInvolvedAlliance($sideAssignment["entity_id"]);
                                $this->klist->addInvolvedAlliance($sideAssignment["entity_id"]);
                                $this->llist->addVictimAlliance($sideAssignment["entity_id"]);
								// also add as involved alliance for blue-on-blue kills
								$this->llist->addInvolvedAlliance($sideAssignment["entity_id"]);
                            }
                        }
                        
                        // entity is a corporation
                        else
                        {
                            // alliance is an enemy
                            if($sideAssignment["side"] == "e")
                            {
                                $this->kslist->addVictimCorp($sideAssignment["entity_id"]);
                                $this->lslist->addInvolvedCorp($sideAssignment["entity_id"]);
                                $this->klist->addVictimCorp($sideAssignment["entity_id"]);
                                $this->llist->addInvolvedCorp($sideAssignment["entity_id"]);
                            }
                            
                            // alliance is an ally
                            else
                            {
                                $this->kslist->addInvolvedCorp($sideAssignment["entity_id"]);
                                $this->lslist->addVictimCorp($sideAssignment["entity_id"]);
								// also add as involved alliance for blue-on-blue kills
								$this->lslist->addInvolvedCorp($sideAssignment["entity_id"]);
                                $this->klist->addInvolvedCorp($sideAssignment["entity_id"]);
                                $this->llist->addVictimCorp($sideAssignment["entity_id"]);
								// also add as involved alliance for blue-on-blue kills
								$this->llist->addInvolvedCorp($sideAssignment["entity_id"]);
                            }
                        }
                    }
                }
                
                // we need a summary table first
                $this->summaryTable = new KillSummaryTable($this->kslist, $this->lslist);
                $this->summaryTable->generate();
                
		$this->destroyed = array();
                $this->lossValues = array();
                $this->killValues = array();
		$this->pilots = array('a' => array(), 'e' => array());
		$this->klist->rewind();
                
                $this->damageOverview = array('a' => array(), 'e' => array());
                
                $totalKillIsk = $this->summaryTable->getTotalKillISK();
                
                
		while ($kill = $this->klist->getKill()) {

                        handle_involved($kill, 'a', $this->pilots, $sideAssignmentMap, TRUE);
                        handle_destroyed($kill, 'e', $this->destroyed, $this->pilots, $sideAssignmentMap, TRUE);
                        
                        // gather data for battle timeline and loss value lists
                        // for better performance we use this loop so we won't have to loop over the same data again
                        
                        // ---------------------------------------------------------------------------------------
                        // gathering kill values
                        // ---------------------------------------------------------------------------------------
                        // we dont want our own people on the enemy's side!
                        if(in_array($kill->getVictimAllianceID(), config::get('cfg_allianceid')) ||
                                in_array($kill->getVictimCorpID(), config::get('cfg_corpid')) ||
                                in_array($kill->getVictimID(), config::get('cfg_pilotid')))
                        {
                            continue;
                        }

                        $lossValueRaw = $kill->getISKLoss();
                        $lossValue = self::formatIskValue($lossValueRaw);
                        if($totalKillIsk != 0)
                        {
                            $percentualLossValue = number_format(($lossValueRaw/$totalKillIsk)*100, 2);
                        }
                        else
                        {
                            $percentualLossValue = 0.00;
                        }
                        if($lossValueRaw > 0)
                        {
                            $this->killValues[] = array(
                                "victimName" => $kill->getVictimName(),
                                "victimID" => $kill->getVictimID(),
                                "victimUrl" => edkURI::page("pilot_detail", $kill->getVictimID(), "plt_id"),
                                "victimShipName" => $kill->getVictimShipName(),
                                "victimShipImage" => imageUrl::getURL("Ship", $kill->getVictimShipID(), 32),
                                "victimShipClass" => $kill->getVictimShipClassName(),
                                "victimCorpName" => $kill->getVictimCorpName(),
                                "victimCorpID" => $kill->getVictimCorpID(),
                                "victimCorpUrl" => edkURI::page("corp_detail", $kill->getVictimCorpID(), "crp_id"),
                                "victimAllianceName" => $kill->getVictimAllianceName(),
                                "victimAllianceID" => $kill->getVictimAllianceID(),
                                "victimAllianceUrl" => edkURI::page("alliance_detail", $kill->getVictimAllianceID(), "all_id"),
                                "killId" => $kill->getID(),
                                "killUrl" => edkURI::page("kill_detail", $kill->getID(), "kll_id"),
                                "lossValueRaw" => $lossValueRaw,
                                "lossValue" => $lossValue,
                                "lossValuePercentage" => $percentualLossValue
                            );
                        }
                        
                        // ---------------------------------------------------------------------------------------
                        // gathering timeline data
                        // ---------------------------------------------------------------------------------------
                        $killTimestamp = strtotime($kill->getTimeStamp());
                        // increase killtimestamp for pods so they appear AFTER the ship kill in the timeline
                        $shipClassId = $kill->getVictimShip()->getClass()->getID();
                        if($shipClassId == 18 || $shipClassId == 2)
                            $killTimestamp += 1;
                        
                        $this->timeLine[] = array("timestamp" => $killTimestamp, "loss" => NULL, "kill" => array(
                            "victimName" => $kill->getVictimName(),
                            "victimID" => $kill->getVictimID(),
                            "victimUrl" => edkURI::page("pilot_detail", $kill->getVictimID(), "plt_id"),
                            "victimShipName" => $kill->getVictimShipName(),
                            "victimShipImage" => imageUrl::getURL("Ship", $kill->getVictimShipID(), 32),
                            "victimShipClass" => $kill->getVictimShipClassName(),
                            "victimCorpName" => $kill->getVictimCorpName(),
                            "victimCorpID" => $kill->getVictimCorpID(),
                            "victimCorpUrl" => edkURI::page("corp_detail", $kill->getVictimCorpID(), "crp_id"),
                            "victimAllianceName" => $kill->getVictimAllianceName(),
                            "victimAllianceID" => $kill->getVictimAllianceID(),
                            "victimAllianceUrl" => edkURI::page("alliance_detail", $kill->getVictimAllianceID(), "all_id"),
                            "killId" => $kill->getID(),
                            "killUrl" => edkURI::page("kill_detail", $kill->getID(), "kll_id")
                        ));
                        
                        
                        // ---------------------------------------------------------------------------------------
                        // gathering damage overview data
                        // ---------------------------------------------------------------------------------------
                        $this->damageOverview["a"][] = array(
                            "victimName" => $kill->getVictimName(),
                            "victimID" => $kill->getVictimID(),
                            "victimUrl" => edkURI::page("pilot_detail", $kill->getVictimID(), "plt_id"),
                            "victimShipName" => $kill->getVictimShipName(),
                            "victimShipImage" => imageUrl::getURL("Ship", $kill->getVictimShipID(), 32),
                            "victimShipClass" => $kill->getVictimShipClassName(),
                            "victimCorpName" => $kill->getVictimCorpName(),
                            "victimCorpID" => $kill->getVictimCorpID(),
                            "victimCorpUrl" => edkURI::page("corp_detail", $kill->getVictimCorpID(), "crp_id"),
                            "victimAllianceName" => $kill->getVictimAllianceName(),
                            "victimAllianceID" => $kill->getVictimAllianceID(),
                            "victimAllianceUrl" => edkURI::page("alliance_detail", $kill->getVictimAllianceID(), "all_id"),
                            "killId" => $kill->getID(),
                            "killUrl" => edkURI::page("kill_detail", $kill->getID(), "kll_id"),
                            "lossValueRaw" => $lossValueRaw,
                            "lossValue" => $lossValue,
                            "lossValuePercentage" => $percentualLossValue
                        );
		}
             
		$this->llist->rewind();
                $totalLossIsk = $this->summaryTable->getTotalLossISK();
                
		while ($kill = $this->llist->getKill()) {

                        handle_involved($kill, 'e', $this->pilots, $sideAssignmentMap, TRUE);
                        handle_destroyed($kill, 'a',  $this->destroyed, $this->pilots, $sideAssignmentMap, TRUE);                        
                        // gather data for battle timeline and loss value lists
                        // for better performance we use this loop so we won't have to loop over the same data again
                        
                        // ---------------------------------------------------------------------------------------
                        // gathering loss values
                        // ---------------------------------------------------------------------------------------
                        $lossValueRaw = $kill->getISKLoss();
                        $lossValue = self::formatIskValue($lossValueRaw);
                        if($totalLossIsk != 0)
                        {
                            $percentualLossValue = number_format(($lossValueRaw/$totalLossIsk)*100, 2);
                        }
                        else
                        {
                            $percentualLossValue = 0.00;
                        }

                        if($lossValueRaw > 0)
                        {
                            $this->lossValues[] = array(
                                "victimName" => $kill->getVictimName(),
                                "victimID" => $kill->getVictimID(),
                                "victimUrl" => edkURI::page("pilot_detail", $kill->getVictimID(), "plt_id"),
                                "victimShipName" => $kill->getVictimShipName(),
                                "victimShipImage" => imageUrl::getURL("Ship", $kill->getVictimShipID(), 32),
                                "victimShipClass" => $kill->getVictimShipClassName(),
                                "victimCorpName" => $kill->getVictimCorpName(),
                                "victimCorpID" => $kill->getVictimCorpID(),
                                "victimCorpUrl" => edkURI::page("corp_detail", $kill->getVictimCorpID(), "crp_id"),
                                "victimAllianceName" => $kill->getVictimAllianceName(),
                                "victimAllianceID" => $kill->getVictimAllianceID(),
                                "victimAllianceUrl" => edkURI::page("alliance_detail", $kill->getVictimAllianceID(), "all_id"),
                                "killId" => $kill->getID(),
                                "killUrl" => edkURI::page("kill_detail", $kill->getID(), "kll_id"),
                                "lossValueRaw" => $lossValueRaw,
                                "lossValue" => $lossValue,
                                "lossValuePercentage" => $percentualLossValue
                            );
                        }
                        
                        // ---------------------------------------------------------------------------------------
                        // gathering timeline data
                        // ---------------------------------------------------------------------------------------
                        $killTimestamp = strtotime($kill->getTimeStamp());
                        $shipClassId = $kill->getVictimShip()->getClass()->getID();
                        $this->timeLine[] = array("timestamp" => strtotime($kill->getTimeStamp()), "loss" => array(
                            "victimName" => $kill->getVictimName(),
                            "victimID" => $kill->getVictimID(),
                            "victimUrl" => edkURI::page("pilot_detail", $kill->getVictimID(), "plt_id"),
                            "victimShipName" => $kill->getVictimShipName(),
                            "victimShipImage" => imageUrl::getURL("Ship", $kill->getVictimShipID(), 32),
                            "victimShipClass" => $kill->getVictimShipClassName(),
                            "victimCorpName" => $kill->getVictimCorpName(),
                            "victimCorpID" => $kill->getVictimCorpID(),
                            "victimCorpUrl" => edkURI::page("corp_detail", $kill->getVictimCorpID(), "crp_id"),
                            "victimAllianceName" => $kill->getVictimAllianceName(),
                            "victimAllianceID" => $kill->getVictimAllianceID(),
                            "victimAllianceUrl" => edkURI::page("alliance_detail", $kill->getVictimAllianceID(), "all_id"),
                            "killId" => $kill->getID(),
                            "killUrl" => edkURI::page("kill_detail", $kill->getID(), "kll_id")
                            ), "kill" => NULL);
		}
//echo "<pre>"; var_dump($this->pilots); echo "</pre>";
		// sort pilot ships, order pods after ships
		foreach ($this->pilots as $side => $pilot) {
			foreach ($pilot as $id => $kll) {
				usort($this->pilots[$side][$id], array($this, 'cmp_ts_func'));
			}
		}

		// sort arrays, ships with high points first
		uasort($this->pilots['a'], array($this, 'cmp_func'));
		uasort($this->pilots['e'], array($this, 'cmp_func'));

		// now get the pods out and mark the ships the've flown as podded
		foreach ($this->pilots as $side => $pilot) 
		{
			foreach ($pilot as $id => $kll) 
			{
				$max = count($kll);
				for ($i = 0; $i < $max; $i++) 
				{
					// add up total damage for each side
					if($side == "a")
					{
						if(isset($kll[$i]["damage"])) $this->damageTotalFriendly += $kll[$i]["damage"];
					}

					else
					{
						if(isset($kll[$i]["damage"])) $this->damageTotalHostile += $kll[$i]["damage"];
					}
                    
					// this kill has a pod as ship
					if ($kll[$i]['shipClass'] == 'Capsule') 
					{
						// this pilot made previous kills in another ship
						if (isset($kll[$i - 1]['sid'])) 
						{
							// this kill is a pod loss
							if(isset($kll[$i]['destroyed']))
							{
								$this->pilots[$side][$id][$i - 1]['podded'] = true;
								$this->pilots[$side][$id][$i - 1]['podid'] = $kll[$i]['kll_id'];
								$this->pilots[$side][$id][$i - 1]['pod_url'] = edkURI::page("kill_detail", $kll[$i]['kll_id'], "kll_id");

								unset($this->pilots[$side][$id][$i]);
							}
							
							// the pilot was involved in this kill, but flew a pod
							else 
							{
								// update stats for previously used ship
								$this->pilots[$side][$id][$i - 1]['times'] += $this->pilots[$side][$id][$i]['times'];
								$this->pilots[$side][$id][$i - 1]['damage'] += $this->pilots[$side][$id][$i]['damage'];
								unset($this->pilots[$side][$id][$i]);
							}
						} 
						
					}
				}
			}
		}
                // update battles with current stats
                $this->updateBattles();
	}

	/**
	 * @return string HTML string for the summary overview of the battle.
	 */
	public function overview()
	{
		global $smarty;
		$smarty->assignByRef('pilots_a', $this->pilots['a']);
		$smarty->assignByRef('pilots_e', $this->pilots['e']);

		$pod = Ship::getByID(670);
		$smarty->assign('podpic', $pod->getImage(32));
		$smarty->assign('friendlycnt', count($this->pilots['a']));
		$smarty->assign('hostilecnt', count($this->pilots['e']));
		if ($this->kill->isClassified()) {
			$smarty->assign('system', 'Classified System');
		} else {
			if (!$this->adjacent) {
				$smarty->assign('system', $this->kill->getSolarSystemName());
			} else {
				$sysnames = array();
				foreach ($this->systems as $sys_id) {
					$system = SolarSystem::getByID($sys_id);
					$sysnames[] = $system->getName();
				}
				$smarty->assign('system', implode(', ', $sysnames));
			}
                }
		$smarty->assign('firstts', $this->firstts);
		$smarty->assign('lastts', $this->lastts);
                $smarty->assign("battleOverviewTableTemplate", $this->templateDir . "battle_overview_table.tpl");
                
		return $smarty->fetch($this->templateDir . "battle_overview.tpl");
	}

        
        /**
         * @return string HTML for the timeline tab 
         */
        public function timeLine()
        {
            global $smarty;
            uasort($this->timeLine, array("self", "compareTimelineEntry"));

            $smarty->assign("timeline", $this->timeLine);
            $this->klist->rewind();
            $this->llist->rewind();
            $summaryTable = new KillSummaryTable($this->klist, $this->llist);
            $summaryTable->generate();

            $smarty->assign('kcount', $summaryTable->getTotalKills());
            $smarty->assign('lcount', $summaryTable->getTotalLosses());

            return $smarty->fetch($this->templateDir . "battle_timeline.tpl");
            
        }
        
        /**
         * @return string HTML for the losslists tab 
         */
        public function lossValueLists()
        {
            global $smarty;
            
            // sort loss value lists
            uasort($this->lossValues, array("self", "compareLossValueEntity"));
            uasort($this->killValues, array("self", "compareLossValueEntity"));
            
            $this->klist->rewind();
            $this->llist->rewind();
            $summaryTable = new KillSummaryTable($this->klist, $this->llist);
            $summaryTable->generate();
            
            $smarty->assign("lossValues", $this->lossValues);
            $smarty->assign("killValues", $this->killValues);
            $smarty->assign('kcount', $summaryTable->getTotalKills());
            $smarty->assign('lcount', $summaryTable->getTotalLosses());
            $smarty->assign("battleLossValuesTableTemplate", $this->templateDir . "battle_lossValues_table.tpl");
            
            return $smarty->fetch($this->templateDir . 'battle_lossValues.tpl');
        }
        
        
        /**
         * @return string HTML for the loot tab 
         */
        public function lootOverview()
        {
            global $smarty;
            
            // hostile Loot

            // initialize arrays
            $destroyedLoot = array();
            $droppedLoot = array();
            $lootOverview = array();
            
            // get loot
            $lootOverview["hostile"]["destroyed"]["totalValue"] = self::getDestroyedLoot($this->klist, $destroyedLoot);
            $lootOverview["hostile"]["dropped"]["totalValue"] = self::getDroppedLoot($this->klist, $droppedLoot);
            
            // sort loot by value desc
            uasort($destroyedLoot, array("self", "sortLoot"));
            uasort($droppedLoot, array("self", "sortLoot"));

            // save in overview
            $lootOverview["hostile"]["destroyed"]["list"] = $destroyedLoot;
            $lootOverview["hostile"]["dropped"]["list"] = $droppedLoot;

            // initialize arrays
            $destroyedLoot = array();
            $droppedLoot = array();
            
            // get loot
            $lootOverview["friendly"]["destroyed"]["totalValue"] = self::getDestroyedLoot($this->llist, $destroyedLoot);
            $lootOverview["friendly"]["dropped"]["totalValue"] = self::getDroppedLoot($this->llist, $droppedLoot);
            
            // sort loot by value desc
            uasort($destroyedLoot, array("self", "sortLoot"));
            uasort($droppedLoot, array("self", "sortLoot"));
            
            // save in overview
            $lootOverview["friendly"]["destroyed"]["list"] = $destroyedLoot;
            $lootOverview["friendly"]["dropped"]["list"] = $droppedLoot;
            
            // assign values
            $smarty->assign("lootFriendly", $lootOverview["friendly"]);
            $smarty->assign("lootHostile", $lootOverview["hostile"]);
            $smarty->assign("lootTableTemplate", $this->templateDir . "loot_table.tpl");

            return $smarty->fetch($this->templateDir . "loot_overview.tpl");
        }
        
        
        /**
         * @return string HTML for the footer
         */
        public function footer()
        {
            $html="<br/>";
            $html.="<ul><li><i>Combined Fleet Battles Mod ".config::get("fleet_battles_mod_version")."</i> Original Code by Quebnaric Deile</li>";
            $html.="<li><i>Enhanced and updated for EDK4 by <a href=\"http://gate.eveonline.com/Profile/Salvoxia\">Salvoxia</a></li></ul>";
            return $html;
        }
        

	/**
	 *
	 * @return string HTML for the summary table.
	 */
	public function summaryTable()
	{       
		if(!$this->summaryTable)
                {
                    $this->kslist->rewind();
                    $this->lslist->rewind();
                    $this->summaryTable = new KillSummaryTable($this->kslist, $this->lslist);
                }
                    

		return $this->summaryTable->generate();
	}
        
        /**
         *
         * @return string HTML for the header of battle (Battle in ...)
         */
        public function battleHeader()
        {
            $sysnames = $regionNames = array();
                
            // gather system and region names
            foreach ($this->systems as $sys_id) 
            {
                // add system
                $system = SolarSystem::getByID($sys_id);
                $sysnames[] = $system->getName();

                // check if region is already known
                $regionNameIncluded = FALSE;
                foreach($regionNames AS $regionName)
                {
                    if($regionName == $system->getRegionName())
                    {
                        $regionNameIncluded = TRUE;
                        break;
                    }
                }

                // new region, add to list
                if(!$regionNameIncluded)
                {
                    $regionNames[] = $system->getRegionName();
                }
            }
            $html = "";

            $html .= "<div class=\"kb-kills-header\">Battle in " . implode(', ', $sysnames) . " (" . implode(', ', $regionNames) . "), "
                . substr($this->firstts, 8, 2) . substr($this->firstts, 4, 4) . substr($this->firstts, 0, 4) . " (" . substr($this->firstts, 11, 5) . " - " 
                . substr($this->lastts, 11, 5) . ")</div>";

            // show fixed notification
            if($this->isFixed)
            {
                $html .= "<div class=\"brFixedNotification\">This battle report has been fixed manually.</div>";
            }
            
            
            foreach($this->statusMessages AS $statusMessage)
            {
                $html .= "<br/>{$statusMessage}";
            }
            
            return $html;
        }
        
        
        
        
        /**
         * generate Balance of Power Table
         * @return String HTML for the Balance Of Power tab
         */
        public function balanceOfPower()
        {
            global $smarty;
            $this->sides = array();
            include($this->includeDir . "class.killlisttable.php");
            $BadShips = $BadAllies = $GoodShips = $GoodAllies = array();
            $numberOfFriendlyPilots = 0;
            $numberOfHostilePilots = 0;
            $pilotsCounted = array();
            
            $involvedOwners = array();
            $this->numberOfInvolvedOwners = 0;
            
            $ownerAlliances = config::get('cfg_allianceid');
            $ownerCorps = config::get('cfg_corpid');
            $ownerPilots = config::get('cfg_pilotid');
            
            foreach ($this->pilots as $side => $pilotA)
            {
                foreach ($pilotA as $pilotId => $kll)
                {
                    foreach ($kll as $pilota)
                    {
                        // determine numbers of involved Owners
                        if(in_array($pilota["aid"], $ownerAlliances))
                        {
                            $involvedOwners[$pilotId] = 1;
                        }
                        
                        elseif(in_array($pilota["cid"], $ownerCorps))
                        {
                            $involvedOwners[$pilotId] = 1;
                        }
                        
                        elseif(in_array($pilotId, $ownerPilots))
                        {
                            $involvedOwners[$pilotId] = 1;
                        }
                        
                        $shippa = TestPilotName($pilota["ship"]);    
                        if ($side == 'a')
                        {
                            
                            $GoodShips[$shippa]["shipClass"]=$pilota["shipClass"];
                            $GoodShips[$shippa]["sortWeight"]=  getShipClassSortWeight($pilota["shipClassObject"]);
                            $GoodShips[$shippa]["times"]+=1;
                            $GoodShips[$shippa]["color"]=$pilota["color"];
                            if ($pilota["destroyed"]==1) 
                                    $GoodShips[$shippa]["destroyed"]+=1;   
                                else
                                    $GoodShips[$shippa]["destroyed"]+=0;   

                            // check if we already got that pilot
                            if(in_array($pilota["name"], $pilotsCounted))
                            {
                               continue; 
                            }
                            
                            $pilotsCounted[] = $pilota["name"];
                            
                            $numberOfFriendlyPilots++;
                            
                            $GoodAllies[$pilota["alliance"]]["quantity"]+=1;
                            $GoodAllies[$pilota["alliance"]]["corps"][$pilota["corp"]]+=1;

                            
                            // now set up sides for BR Setup tab
                            // entity type: alliance
                                
                            if(strcasecmp($pilota["alliance"], "None") != 0)
                            {
                                $allianceName = addslashes($pilota["alliance"]);
                                if(!isset($this->sides["a"][$pilota["alliance"]]))
                                {
                                    $this->sides["a"][$allianceName]["type"] = "alliance";
                                    $this->sides["a"][$allianceName]["id"] = $pilota["aid"];
                                    $this->sides["a"][$allianceName]["numberOfPilots"] = 1;
                                    $this->sides["a"][$allianceName]["logoUrl"] = html_entity_decode(Alliance::getByID($pilota["aid"])->getPortraitURL(32));
                                    $this->sides["a"][$allianceName]["infoUrl"] = html_entity_decode($pilota["alliance_url"]);
                                }

                                else
                                {
                                    $this->sides["a"][$allianceName]["numberOfPilots"] += 1;
                                }
                            }

                            // entity type: corp
                            else
                            {
                                $corpName = addslashes($pilota["corp"]);
                                if(!isset($this->sides["a"][$pilota["corp"]]))
                                {
                                    $this->sides["a"][$corpName]["type"] = "corp";
                                    $this->sides["a"][$corpName]["id"] = $pilota["cid"];
                                    $this->sides["a"][$corpName]["numberOfPilots"] = 1;
                                    $this->sides["a"][$corpName]["logoUrl"] = html_entity_decode(Corporation::getByID($pilota["cid"])->getPortraitURL(32));
                                    $this->sides["a"][$corpName]["infoUrl"] = html_entity_decode(($pilota["crp_url"]));
                                }

                                else
                                {
                                    $this->sides["a"][$corpName]["numberOfPilots"] += 1;
                                }
                            }
                        }
                        else
                        {
                            $BadShips[$shippa]["shipClass"]=$pilota["shipClass"];
                            $BadShips[$shippa]["sortWeight"]=  getShipClassSortWeight($pilota["shipClassObject"]);
                            $BadShips[$shippa]["times"]+=1;
                            $BadShips[$shippa]["color"]=$pilota["color"];
                            if ($pilota["destroyed"]==1)
                                    $BadShips[$shippa]["destroyed"]+=1;   
                                else
                                    $BadShips[$shippa]["destroyed"]+=0;   
                            
                            // check if we already got that pilot
                            if(in_array($pilota["name"], $pilotsCounted))
                            {
                               continue; 
                            }
                            
                            $pilotsCounted[] = $pilota["name"];
                            
                            $numberOfHostilePilots++;
                             // adjust numbers for the same pilots in different ships
			    if (isset($pilota[0]["times"])) {
                            	if($pilota[0]["times"] > 1)
                            	{
                                	$numberOfHostilePilots -= ($pilota[0]["times"]-1);
                            	}
			    }
                            $BadAllies[$pilota["alliance"]]["quantity"]+=1;
                            $BadAllies[$pilota["alliance"]]["corps"][$pilota["corp"]]+=1;

                            // now set up sides for BR Setup tab
                            // entity type: alliance
                            if(strcasecmp($pilota["alliance"], "None") != 0)
                            {
                                $allianceName = addslashes($pilota["alliance"]);
                                if(!isset($this->sides["e"][$pilota["alliance"]]))
                                {
                                    $this->sides["e"][$allianceName]["type"] = "alliance";
                                    $this->sides["e"][$allianceName]["id"] = $pilota["aid"];
                                    $this->sides["e"][$allianceName]["numberOfPilots"] = 1;
                                    $this->sides["e"][$allianceName]["logoUrl"] = html_entity_decode(Alliance::getByID($pilota["aid"])->getPortraitURL(32));
                                    $this->sides["e"][$allianceName]["infoUrl"] = html_entity_decode(($pilota["alliance_url"]));
                                }

                                else
                                {
                                    $this->sides["e"][$allianceName]["numberOfPilots"] += 1;
                                }
                            }

                            // entity type: corp
                            else
                            {
                                $corpName = addslashes($pilota["corp"]);
                                if(!isset($this->sides["e"][$pilota["corp"]]))
                                {
                                    $this->sides["e"][$corpName]["type"] = "corp";
                                    $this->sides["e"][$corpName]["id"] = $pilota["cid"];
                                    $this->sides["e"][$corpName]["numberOfPilots"] = 1;
                                    $this->sides["e"][$corpName]["logoUrl"] = html_entity_decode(Corporation::getByID($pilota["cid"])->getPortraitURL(32));
                                    $this->sides["e"][$corpName]["infoUrl"] = html_entity_decode($pilota["crp_url"]);
                                }

                                else
                                {
                                    $this->sides["e"][$corpName]["numberOfPilots"] += 1;
                                }
                            }

                        }
                    }
                    
                }
            }
            
            $this->numberOfInvolvedOwners = count($involvedOwners);
			$this->involvedOwners = array();
            foreach($involvedOwners AS $involvedOwnerId => $one)
            {
				if(!in_array($involvedOwnerId, $this->involvedOwners))
				{
					$this->involvedOwners[] = $involvedOwnerId;
				}
            }
 

            // calculate percentages
            foreach($GoodAllies AS $name => &$info)
            {
                $info["percentage"] = round(($info["quantity"]/$numberOfFriendlyPilots)*100, 1);
                arsort($info["corps"]);
            }
            
            foreach($BadAllies AS $name => &$info)
            {
                $info["percentage"] = round(($info["quantity"]/$numberOfHostilePilots)*100, 1);
                arsort($info["corps"]);
            }

            if(!is_null($GoodShips))
            {
                    uasort ($GoodShips, array($this, 'sortShipClasses'));
            }

            if(!is_null($GoodAllies))
            {
                    arsort ($GoodAllies);
            }
            $a1 = count($GoodAllies);
            $smarty->assignByRef('GAlliesCount', $a1);
            $smarty->assignByRef('GoodAllies', $GoodAllies);
            $smarty->assignByRef('GoodShips', $GoodShips);

            if(!is_null($BadShips))
            {
                    uasort ($BadShips, array($this, 'sortShipClasses'));
            }

            if(!is_null($BadAllies))
            {
                    arsort ($BadAllies);
            }
            $b1 = count($BadAllies);
            $smarty->assignByRef('BAlliesCount', $b1);
            $smarty->assignByRef('BadAllies', $BadAllies);
            $smarty->assignByRef('BadShips', $BadShips);
            $smarty->assign("numberOfFriendlyPilots", $numberOfFriendlyPilots);
            $smarty->assign("numberOfHostilePilots", $numberOfHostilePilots);

            return $smarty->fetch($this->templateDir."battle_balance.tpl");
            
        }

        /**
         * updates battles with the same stats as the current related kills
         */
        protected function updateBattles()
        {
            if(!isset($this->battlesToUpdate) || empty($this->battlesToUpdate))
            {
                return;
            }
            
            $killIsk = $this->summaryTable->getTotalKillISK();
            $lossIsk = $this->summaryTable->getTotalLossISK();
            $kills = $this->summaryTable->getTotalKills();
            $losses = $this->summaryTable->getTotalLosses();
            $involved = count($this->pilots["a"]) + count($this->pilots["e"]);
            
            foreach($this->battlesToUpdate AS $battleId)
            {   
                Battle::updateCacheForBattle($battleId, $killIsk, $lossIsk, $kills, $losses, $involved, $this->firstts, $this->lastts, $this->numberOfInvolvedOwners, $this->involvedOwners);
            }

        }
        
        /**
         * loads the battle setup tab
         * @return String 
         */
        public function battleSetup()
        {
            if(!isset($this->sides))
            {
                $this->balanceOfPower();
            }

            global $smarty;
            $smarty->assignByRef("sideAllied", $this->sides["a"]);
            $smarty->assignByRef("sideHostile", $this->sides["e"]);
            $smarty->assign("systemIds", implode(",", $this->systems));
            $smarty->assign("numberOfInvolvedOwners", $this->numberOfInvolvedOwners);
            if(!$this->involvedOwners) $this->involvedOwners = array();
			$smarty->assign("involvedOwners", implode(",", $this->involvedOwners));
            
            return $smarty->fetch($this->templateDir."battle_setup.tpl");
        }
        
        /**
         * @return string HTML string for the damage overview tab.
         */
        public function damageOverview()
        {
                global $smarty;
                $numberOfPilotsHavingDamageDone = array("a" => 0, "e" => 0);
                foreach($this->pilots AS $side => $pilotList)
                {
                    foreach($pilotList AS $pilotId => $kills)
                    {
                        if(count($kills) > 1)
                        {
                            foreach($kills AS $index => $kill)
                            {
                                if($index > 0)
                                {
                                    $this->pilots[$side][$pilotId][0]["damage"] += $kill["damage"];
                                }
                            }
                        }
                        
                        // count pilots who have actually done any damage
                        if($this->pilots[$side][$pilotId][0]["damage"] > 0)
                        {
                            $numberOfPilotsHavingDamageDone[$side] += 1;
                        }
                    }
                }
                 
                // sort by damage done
                uasort($this->pilots['a'], array($this, 'cmp_dmg_func'));
		uasort($this->pilots['e'], array($this, 'cmp_dmg_func'));
                
                $smarty->assignByRef('pilotsAllied', $this->pilots['a']);
                $smarty->assignByRef('pilotsHostile', $this->pilots['e']);

                $smarty->assign("damageTotalFriendly", $this->damageTotalFriendly);
                $smarty->assign("damageTotalHostile", $this->damageTotalHostile);

                $smarty->assign('friendlycnt', $numberOfPilotsHavingDamageDone['a']);
                $smarty->assign('hostilecnt', $numberOfPilotsHavingDamageDone['e']);

                $smarty->assign("damageOverviewTableTemplate", $this->templateDir . "battle_damageOverview_table.tpl");

                return $smarty->fetch($this->templateDir . "battle_damageOverview.tpl");
        }
        
        /**
         * opens the tabbed area
         * @return string 
         */
        public function beginTabberArea()
        {
            return "<div class=\"tabber\">";
        }
        
        /**
         * closes the tabbed area and creates the base request URI for ajax requests
         * @return string 
         */
        public function endTabberArea()
        {
            // build ajax request URI
            if($this->adjacent) 
                $ajaxAdjacent = 1;
            else
                $ajaxAdjacent = 0;
            
            if($this->displayingBattle) 
                $ajaxBattle = 1;
            else
                $ajaxBattle = 0;
            
            $ajaxRequestUrl =  edkURI::build(array('kll_id', $this->kll_id, true),
                                                            array('adjacent', $ajaxAdjacent, TRUE),
                                                          array('battle', $ajaxBattle, TRUE));
            
            // apply ship class filter parameter to ajax request URI
            if($this->scl_id)
            {
                // ship class filters are always applied as GET parameter
               if(strpos($ajaxRequestUrl, "?") === FALSE)
               {
                   $ajaxRequestUrl .= "?scl_id=".$this->scl_id;
               }
               
               else
               {
                   $ajaxRequestUrl .= "&scl_id=".$this->scl_id;
               }
                
            }

            $html = "<input type=\"hidden\" id=\"ajaxRequestUrl\" value=\"{$ajaxRequestUrl}\" />";
            return "</div>".$html;
        }
        
        
        /**
         * launches the tabber
         * @return string 
         */
        public function initTabber()
        {
            $html =  "<script type=\"text/javascript\">tabberAutomatic(tabberOptions);</script>";
            return $html;
        }
        
	/**
	 *
	 * @return string HTML string for the list of kills and losses.
	 */
	public function killList()
	{       
                $html = '<div class="tabbertab" title="Kill Lists">';
		$html .= '<div class="kb-kills-header">Related kills</div>';

		$ktable = new KillListTable($this->klist);
		$html .= $ktable->generate();
		$html .= '<div class="kb-losses-header">Related losses</div>';

		$ltable = new KillListTable($this->llist);
		$html .= $ltable->generate();
                $html .= '</div>';
		return $html;
	}

	private function cmp_func($a, $b)
	{
		// select the biggest fish of that pilot
		$t_scl = 0;
		foreach ($a as $i => $ai) {
			if ($ai['scl'] > $t_scl) {
				$t_scl = $ai['scl'];
				$cur_i = $i;
			}
		}
		$a = $a[$cur_i];

		$t_scl = 0;
		foreach ($b as $i => $bi) {
			if ($bi['scl'] > $t_scl) {
				$t_scl = $bi['scl'];
				$cur_i = $i;
			}
		}
		$b = $b[$cur_i];

		if ($a['scl'] > $b['scl']) {
			return -1;
		}
		// sort after points, shipname, pilotname
		elseif ($a['scl'] == $b['scl']) {
			if ($a['ship'] == $b['ship']) {
				if ($a['name'] > $b['name']) {
					return 1;
				}
				return -1;
			} elseif ($a['ship'] > $b['ship']) {
				return 1;
			}
			return -1;
		}
		return 1;
	}
        
        /**
         * formats an ISK value for displaying
         * @param float $value
         * @return string 
         */
        protected static function formatIskValue($value)
        {
            if((int) number_format($value, 0, "","")>1000000000)
            {
                    $isk = number_format($value/1000000000, 2, ".","") . " b";
            } elseif((int) number_format($value, 0, "","")>1000000)
            {
                    $isk = number_format($value/1000000, 1, ".","") . " m";
            } else
            {
                    $isk = number_format($value, 0, ".",",");
            }

            return $isk;
        }
        
        /**
         * custom compare function for sorting the timeline
         * @param array $a timelineEntry
         * @param array $b timelineEntry
         * @return int  
         */
        protected static function compareTimelineEntry($a, $b) 
        {

            // same timestamp
            if($a["timestamp"] ==  $b["timestamp"])
            {

                // comparing two losses -> equal
                if(!is_null($a["loss"]) && !is_null($b["loss"]))
                {
                    return 0;
                }

                // comparing two kills -> equal
                if(!is_null($a["kill"]) && !is_null($b["kill"]))
                {
                    return 0;
                }

                // comparing a loss and a kill -> loss is less ;)
                if(!is_null($a["loss"]) && !is_null($b["kill"]))
                {
                    return -1;
                }
                elseif(!is_null($a["kill"]) && !is_null($b["loss"]))
                {
                    return 1;
                }

            }

            else {
                return ($a["timestamp"] < $b["timestamp"])?-1:1;
            }
        }
        
        /**
         * custom compare function for sorting loss value lists
         * @param type $a losslistEntry
         * @param type $b losslistEntry
         * @return int  
         */
        protected static function compareLossValueEntity($a, $b)
        {
            if($a["lossValueRaw"] == $b["lossValueRaw"])
                return 0;

            if($a["lossValueRaw"] > $b["lossValueRaw"])
                return -1;
            else
                return 1;
        }
        
	
	/**
	 * Compare two involved pilots by timestamp of involvement.
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private function cmp_ts_func($a, $b)
	{
		if ($a['ts'] < $b['ts']) {
			return -1;
		}
		return 1;
	}


        /**
         *
         * @param type $killList
         * @param type $destroyedLoot
         * @return type 
         */
        function getDestroyedLoot($killList, &$destroyedLoot)
        {
            $killList->rewind();

            $totalValue = 0;
            $qry  = DBFactory::getDBQuery();;

            while ($kill=$killList->getKill())
            {
                $query = "SELECT kb3_items_destroyed.itd_itm_id AS 'ID', kb3_invtypes.typeName AS 'Name', SUM( kb3_items_destroyed.itd_quantity ) AS 'Quantity', AVG( kb3_item_price.price ) AS Price
                FROM kb3_items_destroyed, kb3_invtypes, kb3_item_price
                WHERE 
                (
                    (
                        kb3_items_destroyed.itd_itm_id = kb3_invtypes.typeID
                    )
                    AND 
                    (
                        kb3_items_destroyed.itd_itm_id = kb3_item_price.typeID
                    )
                    AND 
                    (
                        kb3_items_destroyed.itd_kll_id = " . $kill->getID() . ")
                )
                GROUP BY kb3_items_destroyed.itd_itm_id";

                $qry->execute($query);

                while ($row=$qry->getRow())
                {
                    if(isset($destroyedLoot[$row['Name']]['Quantity']) && is_numeric($destroyedLoot[$row['Name']]['Quantity']))
                    {
                        $destroyedLoot[$row['Name']]['Quantity'] += $row['Quantity'];
                    }
                    
                    else
                    {
                        $destroyedLoot[$row['Name']]['Quantity'] = $row['Quantity'];
                    }

                    if (config::get('item_values'))
                    {
                        $destroyedLoot[$row['Name']]['TValue']=self::formatIskValue($destroyedLoot[$row['Name']]['Quantity'] * $row['Price']);
                        $destroyedLoot[$row['Name']]['Value'] =self::formatIskValue($row['Price']);
                        $destroyedLoot[$row['Name']]['RawValue'] = $row['Price'];
                        if(!isset($destroyedLoot[$row['Name']]['Icon']))
                        {
                            $item = Cacheable::factory('Item', $row['ID']);
                            $destroyedLoot[$row['Name']]['Icon'] = $item->getIcon(24, false);				
                        }
                        $totalValue+= ($row['Quantity'] * $row['Price']);
                    }
                }
            }

            return self::formatIskValue($totalValue);
        }

        
        /**
         * gets all the dropped loot int $dest_array
         * @param KillList $killList
         * @param array $droppedLoot
         * @return string 
         */
        protected static function getDroppedLoot($killList, &$droppedLoot)
        {
            $killList->rewind();

            $totalValue = 0;
            $qry = DBFactory::getDBQuery();;

            while ($kill=$killList->getKill())
            {
                $query = "SELECT kb3_items_dropped.itd_itm_id AS 'ID', kb3_invtypes.typeName AS 'Name', SUM( kb3_items_dropped.itd_quantity ) AS 'Quantity', AVG( kb3_item_price.price ) AS Price
                FROM kb3_items_dropped, kb3_invtypes, kb3_item_price
                WHERE 
                (
                    (
                        kb3_items_dropped.itd_itm_id = kb3_invtypes.typeID
                    )
                    AND 
                    (
                        kb3_items_dropped.itd_itm_id = kb3_item_price.typeID
                    )
                    AND 
                    (
                        kb3_items_dropped.itd_kll_id = " . $kill->getID() . ")
                )
                GROUP BY kb3_items_dropped.itd_itm_id";

                $qry->execute($query);

                while ($row=$qry->getRow())
                {
                    
                    if(isset($droppedLoot[$row['Name']]['Quantity']) && is_numeric($droppedLoot[$row['Name']]['Quantity']))
                    {
                        $droppedLoot[$row['Name']]['Quantity'] += $row['Quantity'];
                    }
                    
                    else
                    {
                        $droppedLoot[$row['Name']]['Quantity'] = $row['Quantity'];
                    }

                    if (config::get('item_values'))
                    {
                        $droppedLoot[$row['Name']]['TValue']=self::formatIskValue($row['Price']*$droppedLoot[$row['Name']]['Quantity']);
                        $droppedLoot[$row['Name']]['Value'] =self::formatIskValue($row['Price']);
                        $droppedLoot[$row['Name']]['RawValue'] =$row['Price'];
                        if(!isset($droppedLoot[$row['Name']]['Icon']))
                        {
                            $item = Cacheable::factory('Item', $row['ID']);
                            $droppedLoot[$row['Name']]['Icon'] = $item->getIcon(24, false);				
                        }
                        $totalValue+= ($row['Quantity'] * $row['Price']);
                    }
                }
            }

            return self::formatIskValue($totalValue);
        }
//        /**
//         *
//         * @param type $killList
//         * @param type $destroyedLoot
//         * @return type 
//         */
//        function getDestroyedLoot($killList, &$destroyedLoot)
//        {
//            $killList->rewind();
//
//            $totalValue = 0;
//
//            while ($kill=$killList->getKill())
//            {
//                $kill = Cacheable::factory("Kill", $kill->getId());
//                $destroyedItems = $kill->getDestroyedItems();
//
//                foreach($destroyedItems AS $destroyed)
//                {
//                    $item = $destroyed->getItem();
//                    $itemName = $item->getName();
//                    $quantity = $destroyed->getQuantity();
//                    $price = $item->getAttribute("price");
//                    
//                    if(isset($destroyedLoot[$itemName]['Quantity']) && is_numeric($destroyedLoot[$itemName]['Quantity']))
//                    {
//                        $destroyedLoot[$itemName]['Quantity'] += $quantity;
//                    }
//
//                    else
//                    {
//                        $destroyedLoot[$itemName]['Quantity'] = $quantity;
//                    }
//
//                    if (config::get('item_values'))
//                    {
//                        $destroyedLoot[$itemName]['TValue']=self::formatIskValue($destroyedLoot[$itemName]['Quantity'] * $price);
//                        $destroyedLoot[$itemName]['Value'] =self::formatIskValue($price);
//                        $destroyedLoot[$itemName]['RawValue'] = $price;
//                        if(!isset($destroyedLoot[$row['Name']]['Icon']))
//                        {
//                            $destroyedLoot[$itemName]['Icon'] = $item->getIcon(24, false);				
//                        }
//                        $totalValue+= ($quantity * $price);
//                    }
//                }
//                
//            }
//
//            return self::formatIskValue($totalValue);
//        }
//
//        
//        /**
//         * gets all the dropped loot int $dest_array
//         * @param KillList $killList
//         * @param array $droppedLoot
//         * @return string 
//         */
//        protected static function getDroppedLoot($killList, &$droppedLoot)
//        {
//            $killList->rewind();
//
//            $totalValue = 0;
//
//            while ($kill=$killList->getKill())
//            {
//               // $kill = Cacheable::factory("Kill", $kill->getId());
//                $droppedItems = $kill->getDroppedItems();
//
//                foreach($droppedItems AS $dropped)
//                {
//                    $item = $dropped->getItem();
//                    $itemName = $item->getName();
//                    $quantity = $dropped->getQuantity();
//                    $price = $item->getAttribute("price");
//                    
//                    if(isset($droppedLoot[$itemName]['Quantity']) && is_numeric($droppedLoot[$itemName]['Quantity']))
//                    {
//                        $droppedLoot[$itemName]['Quantity'] += $quantity;
//                    }
//
//                    else
//                    {
//                        $droppedLoot[$itemName]['Quantity'] = $quantity;
//                    }
//
//                    if (config::get('item_values'))
//                    {
//                        $droppedLoot[$itemName]['TValue']=self::formatIskValue($droppedLoot[$itemName]['Quantity'] * $price);
//                        $droppedLoot[$itemName]['Value'] =self::formatIskValue($price);
//                        $droppedLoot[$itemName]['RawValue'] = $price;
//                        if(!isset($droppedLoot[$row['Name']]['Icon']))
//                        {
//                            $droppedLoot[$itemName]['Icon'] = $item->getIcon(24, false);				
//                        }
//                        $totalValue+= ($quantity * $price);
//                    }
//                }
//                
//            }
//        }
        
        /**
         * custom compare function for loot sorting
         * @param type $a
         * @param type $b
         * @return int 
         */
        protected static function sortLoot($a, $b)
        {
            if($a["RawValue"] == $b["RawValue"])
                return 0;

            if($a["RawValue"] > $b["RawValue"])
                return -1;
            else
                return 1;
        }
        
        /**
         * sort balance of power ship classes
         * @param type $a
         * @param type $b
         * @return int 
         */
        protected static function sortShipClasses($a, $b)
        {
            if($a["sortWeight"] == $b["sortWeight"])
                return 0;

            if($a["sortWeight"] > $b["sortWeight"])
                return -1;
            else
                return 1;
        }
        
        
        /**
         * custom compare function for damage overview sorting
         * @param array $a
         * @param array $b
         * @return int 
         */
        protected function cmp_dmg_func($a, $b)
        {
                // select the biggest fish of that pilot
                $t_scl = 0;
                foreach ($a as $i => $ai) {
                        if ($ai['damage'] > $t_scl) {
                                $t_scl = $ai['damage'];
                                $cur_i = $i;
                        }
                }
                $a = $a[$cur_i];

                $t_scl = 0;
                foreach ($b as $i => $bi) {
                        if ($bi['damage'] > $t_scl) {
                                $t_scl = $bi['damage'];
                                $cur_i = $i;
                        }
                }
                $b = $b[$cur_i];

                if ($a['damage'] > $b['damage']) {
                        return -1;
                }
                // sort after points, shipname, pilotname
                elseif ($a['damage'] == $b['damage']) {
                        if ($a['ship'] == $b['ship']) {
                                if ($a['name'] > $b['name']) {
                                        return 1;
                                }
                                return -1;
                        } elseif ($a['ship'] > $b['ship']) {
                                return 1;
                        }
                        return -1;
                }
                return 1;
        }
        
        public function addDefaultTabs()
        {
            $html = "";
            // Balance of Power tab
            //$killRelated->queue("balanceOfPower");
            $html .= $this->addTab("Balance Of Power");

            // Battle Overview tab
            $html .= $this->overview();

            // if configured: Timeline tab
            if(config::get("fleet_battles_mod_showtimeline"))
            {
                //$killRelated->queue("timeLine");
                $html .= $this->addTab("Battle Timeline");
            }

            // if configured: Loss Toplists tab
            if(config::get("fleet_battles_mod_showlossvalues"))
            {
                //$killRelated->queue("lossValueLists");
                $html .= $this->addTab("Loss Values");
            }
            
            // if configured: Damage Overview tab
            if(config::get("fleet_battles_mod_damagelists"))
            {
                //$killRelated->queue("lossValueLists");
                $html .= $this->addTab("Damage Overview");
            }

            // if configured: Kill Lists tab
            if(config::get("fleet_battles_mod_showkilllists"))
            {
                //$killRelated->queue("killList");
                $html .= $this->addTab("Kill Lists");
            }

            // if configured: loot tab
            if(config::get("fleet_battles_mod_showloot"))
            { 
                //$killRelated->queue("lootOverview");
                $html .= $this->addTab("Loot");
            }
            
            return $html;
            
        }
        
        
        public function addTab($title)
        {   $innerTabTitle = str_replace(" ", "", $title);
            return '<div class="tabbertab" title="'.$title.'"><div id="'.$innerTabTitle.'"></div></div>';
        }
        

        
	public function menuSetup()
	{
		$this->addMenuItem("caption", "View");
                if(!$this->displayingBattle)
                {
                    if ($this->adjacent) {
                            $this->addMenuItem("link", "Remove adjacent",
                                            edkURI::build(array('kll_id', $this->kll_id, true)));
                    } else {
                            $this->addMenuItem("link", "Include adjacent",
                                            edkURI::build(array('kll_id', $this->kll_id, true),
                                                            array('adjacent', true, true)));
                    }
                    $this->addMenuItem("link", "Back to Killmail",
                                    edkURI::build(array('a', 'kill_detail', true),
                                                    array('kll_id', $this->kll_id, true)));
                }
                
                else
                {
                    $this->addMenuItem("link", "Back to Fleet Battles", edkURI::page("battles"));
                }
	}

	public function menu()
	{
		$menubox = new Box("Menu");
		$menubox->setIcon("menu-item.gif");
		foreach ($this->menuOptions as $options) {
			if (isset($options[2]))
					$menubox->addOption($options[0], $options[1], $options[2]);
			else $menubox->addOption($options[0], $options[1]);
		}

		return $menubox->generate();
	}
	
	/** 
	 * adds meta tags for Twitter Summary Card and OpenGraph tags
	 * to the HTML header
	 */
	function metaTags()
	{

		$referenceSystem = SolarSystem::getByID(reset($this->systems));
		// meta tag: title
		$metaTagTitle = $referenceSystem->getName() . " | " . $referenceSystem->getRegionName() . " | Battle Report";
		$this->page->addHeader('<meta name="og:title" content="'.$metaTagTitle.'">');
		$this->page->addHeader('<meta name="twitter:title" content="'.$metaTagTitle.'">');

		// build description
		$date = gmdate("Y-m-d", strtotime($this->firstts));
		$startTime = gmdate("H:i", strtotime($this->firstts));
		$endTime = gmdate("H:i", strtotime($this->lastts));
		$totalIskDestroyedM = round(($this->summaryTable->getTotalKillISK() + $this->summaryTable->getTotalLossISK()) / 1000000, 2);
		$metaTagDescription = "Battle Report for ".$referenceSystem->getName() . " (".$referenceSystem->getRegionName().") from ".$date." (".$startTime." - ".$endTime."): ";
		$metaTagDescription .= "Involved Pilots: ".(count($this->pilots['a'])+count($this->pilots['e'])).", Total ISK destroyed: ".$totalIskDestroyedM."M ISK";

		$this->page->addHeader('<meta name="description" content="'.$metaTagDescription.'">');
		$this->page->addHeader('<meta name="og:description" content="'.$metaTagDescription.'">');
			
		// meta tag: image
		$this->page->addHeader('<meta name="og:image" content="'.imageURL::getURL('Type', 3802, 64).'">');
		$this->page->addHeader('<meta name="twitter:image" content="'.imageURL::getURL('Type', 3802, 64).'">');

		$this->page->addHeader('<meta name="og:site_name" content="EDK - '.config::get('cfg_kbtitle').'">');
		
		// meta tag: URL
		$this->page->addHeader('<meta name="og:url" content="'.edkURI::build(array('kll_id', $this->kll_id, true)).'">');
		// meta tag: Twitter summary
		$this->page->addHeader('<meta name="twitter:card" content="summary">');
	}

	/**
	 * Add an item to the menu in standard box format.
	 *
	 * Only links need all 3 attributes
	 * @param string $type Types can be caption, img, link, points.
	 * @param string $name The name to display.
	 * @param string $url Only needed for URLs.
	 */
	function addMenuItem($type, $name, $url = '')
	{
		$this->menuOptions[] = array($type, $name, $url);
	}
        
        /**
         * assigns side in a battle for a given entity in the given systems and the given time interval
         * @param array $systemIds
         * @param String $timestampStart
         * @param String $timestampEnd
         * @param int $entityId
         * @param String $entityType
         * @param String $side 
         */
        public function assignSideForEntity($systemIds, $timestampStart, $timestampEnd, $entityId, $entityType, $side)
        {
            if(!is_array($systemIds) || empty($systemIds))
            {
                return;
            }
            
            $insertQuerys = array();
            // build insert query
            foreach($systemIds AS $systemId)
            {
                // clean assignments inside our time interval, they will be overriden
                $this->deleteSideAssignment($systemId, $timestampStart, $timestampEnd, $entityId, $entityType);
                $insertQuerys[] = "({$systemId}, '{$timestampStart}', '{$timestampEnd}', {$entityId}, '{$entityType}', '{$side}')";
            }
            
            $dbqry = DBFactory::getDBQuery();
            $sql = "REPLACE INTO kb3_side_assignment
                    (system_id, timestamp_start, timestamp_end, entity_id, entity_type, side)
                        VALUES
                     ".implode(",", $insertQuerys);
            $dbqry->execute($sql);
        }
        
        /**
         * deletes the manual side assignment for a given entity in a system and time interval
         * @param long $systemId
         * @param String $timestampStart
         * @param String $timestampEnd
         * @param int $entityId
         * @param String $entityType 
         */
        public function deleteSideAssignment($systemId, $timestampStart, $timestampEnd, $entityId, $entityType)
        {
            $qry = DBFactory::getDBQuery();
                $sql = "DELETE FROM kb3_side_assignment
                        WHERE system_id = {$systemId}
                            AND entity_id = {$entityId}
                            AND entity_type = '{$entityType}'
                            AND (
                                (timestamp_start >= '{$timestampStart}' AND timestamp_start <= '{$timestampEnd}')
                                    OR
                                (timestamp_end >= '{$timestampStart}' AND timestamp_end <= '{$timestampEnd}')
                            )
                    
                ";
                $qry->execute($sql);
        }
        
        
        public function handleSaveBattleSetup()
        {
            if(!Session::isAdmin() || !config::get("fleet_battles_mod_sideassign") || !isset($_POST["saveBattleSetup"]) || $_POST["saveBattleSetup"] != "save")
            {
                return;
            }
            
            // get POST parameters
            $timestampStart = $_POST["timestampStart"];
            $timestampEnd = $_POST["timestampEnd"];
            $this->numberOfInvolvedOwners = $_POST["numberOfInvolvedOwners"];
            $this->involvedOwners = explode(",", $_POST["involvedOwners"]);
           
            $systemIds = explode(",", $_POST["systemIds"]);
    
            // set side for each entity
            foreach($_POST AS $elementName => $side)
            {
                // identify element representing 
                if(strpos($elementName, "side_") === 0)
                {
                    // name pattern: side_<entityType>-<entityId>
                    $elementName = substr($elementName, 5);
                    
                    // index 0 is entityType
                    // index 1 is entityId
                    $entityInfo = explode("-", $elementName);
                    $this->assignSideForEntity($systemIds, $timestampStart, $timestampEnd, $entityInfo[1], $entityInfo[0], $side);
                }
            }
          
            $this->checkForBattleCacheUpdate($systemIds, $timestampStart, $timestampEnd);
            
            $this->statusMessages[] = "Side assignments have been saved.";
            
        }
        
        protected function checkForBattleCacheUpdate($systemIds, $timestampStart, $timestampEnd)
        {
            if(!config::get("fleet_battles_mod_cache"))
            {
                return;
            }

            $battleIdsToUpdateWithCurrentStats = array();
            
            include_once("include/class.battles.php");
            $affectedBattles = array();
            foreach($systemIds AS $systemId)
            {
                $affectedBattles = array_merge($affectedBattles, Battle::getBattlesInTimeframe($systemId, $timestampStart, $timestampEnd));
            }

            foreach($affectedBattles AS $battle)
            { 
//                echo "<pre>";
//                    var_dump($timestampStart);
//                    var_dump($timestampEnd);
//                    var_dump($battle["start"]);
//                    var_dump($battle["end"]);
//                    echo "</pre>";
                // battle is in exactly the same time interval as the current displayed one -> can update with stats of current battle
                if($timestampStart == $battle["start"] && $timestampEnd == $battle["end"])
                {
                    $this->statusMessages[] = "Stats for a cached Battle have been updated.";
                    $battleIdsToUpdateWithCurrentStats[] = $battle["battle_id"];
                }

                // battle overlaps -> compelete recalculation necessary
                // we'll let the cronjob do that, too expensive to do now
                else
                {
                    $this->statusMessages[] =  "deleting battle {$battle["battle_id"]}";

                    $this->statusMessages[] = "Changes partially affect stats of a cached battle. The cache for this battle has been deleted. Please build your cache or wait for cronjob execution. </br>";
                    Battle::deleteBattleFromCache($battle["battle_id"]);
                }
            }
            
            $this->battlesToUpdate = $battleIdsToUpdateWithCurrentStats;        
        }
        
        
        protected function handleDeleteBattleSetup()
        {
            if(!Session::isAdmin() || !config::get("fleet_battles_mod_sideassign") || !isset($_POST["deleteSideAssignments"]) || $_POST["deleteSideAssignments"] != "reset")
            {
                return;
            }
            
            $timestampStart = $_POST["timestampStart"];
            $timestampEnd = $_POST["timestampEnd"];
            $systemIds = explode(",", $_POST["systemIds"]);
            foreach($_POST AS $elementName => $side)
            {
                if(strpos($elementName, "side_") === 0)
                {
                    $elementName = substr($elementName, 5);
                    // index 0 is entityType
                    // index 1 is entityId
                    $entityInfo = explode("-", $elementName);
                    // for each system
                    foreach($systemIds AS $systemId)
                    {
                        // delete side assignment
                        $this->deleteSideAssignment($systemId, $timestampStart, $timestampEnd, $entityInfo[1], $entityInfo[0]);
                    }
                }
            }
            
            $this->checkForBattleCacheUpdate($systemIds, $timestampStart, $timestampEnd);
            
            $this->statusMessages[] = "Side assignments have been deleted.";
        }
}

$killRelated = new pKillRelatedFleetBattle();
// read URI parameters so we can decide what we have to do
$balanceOfPowerTab = (bool) edkURI::getArg('BalanceOfPower');
$battleTimelineTab = (bool) edkURI::getArg('BattleTimeline');
$lossValuesTab = (bool) edkURI::getArg('LossValues');
$damageOverviewTab = (bool) edkURI::getArg('DamageOverview');
$killListsTab = (bool) edkURI::getArg('KillLists');
$lootTab = (bool) edkURI::getArg('Loot');
$setupTab = (bool) edkURI::getArg('Setup');

// show Balance of Power Tab only as per ajax request
if($balanceOfPowerTab)
{
    $killRelated->start();
    $killRelated->buildStats();
    echo $killRelated->balanceOfPower();
    exit();
}

// show Battle Timeline Tab only as per ajax request
elseif($battleTimelineTab)
{
    $killRelated->start();
    $killRelated->buildStats();
    echo $killRelated->timeLine();
    exit();
}

// show Loss Values Tab only as per ajax request
elseif($lossValuesTab)
{
    $killRelated->start();
    $killRelated->buildStats();
    echo $killRelated->lossValueLists();
    exit();
}

// show Damage Overview Tab only as per ajax request
elseif($damageOverviewTab)
{
    $killRelated->start();
    $killRelated->buildStats();
    echo $killRelated->damageOverview();
    exit();
}

// show Kill Lists Tab only as per ajax request
elseif($killListsTab)
{
    $killRelated->start();
    $killRelated->buildStats();
    echo $killRelated->killList();
    exit();
}

// show Loot Tab only as per ajax request
elseif($lootTab)
{
    $killRelated->start();
    $killRelated->buildStats();
    echo $killRelated->lootOverview();
    exit();
}

// show Battle Setup tab only as per ajax request
elseif($setupTab && Session::isAdmin())
{
    $killRelated->start();
    $killRelated->buildStats();
    echo $killRelated->battleSetup();
    exit();
}

// default: show related kills page
else
{
    // queue up callbacks in right order
    $killRelated->queue("start");
    //$this->queue("getInvolved");
    $killRelated->queue("handleSaveBattleSetup");
    $killRelated->queue("handleDeleteBattleSetup");
    $killRelated->queue("buildStats");
	$killRelated->queue("metaTags");
    $killRelated->queue("summaryTable");
    
    // Battle in ....
    $killRelated->queue("battleHeader");
    
    // open a tabbed area
    $killRelated->queue("beginTabberArea");

    $killRelated->queue("addDefaultTabs");
    // if configured and admin: battle setup tab
    if(config::get("fleet_battles_mod_sideassign") && Session::isAdmin())
    { 
        $killRelated->queue("battleSetup");
    }

    // close the tabbed area
    $killRelated->queue("endTabberArea");
    $killRelated->queue("footer");
    $killRelated->queue("initTabber");
    
    event::call("killRelated_assembling", $killRelated);
    $html = $killRelated->assemble();
    // we need jquery to load
    $killRelated->page->addJsLibs("jquery");
    $killRelated->page->setContent($html);
    
    $killRelated->context();
    event::call("killRelated_context_assembling", $killRelated);
    $context = $killRelated->assemble();
    $killRelated->page->addContext($context);

    $killRelated->page->generate();
}
