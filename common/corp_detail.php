<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */
use Swagger\Client\ApiException;
use EDK\ESI\ESI;
use EsiClient\UniverseApi;
/*
 * @package EDK
 */
class pCorpDetail extends pageAssembly
{
    /** @var Page */
    public $page = null;
    /** @var integer */
    public $crp_id = 0;
    /** @var integer */
    public $crp_external_id = 0;
    /** @var Corporation */
    public $corp = null;
    /** @var Alliance */
    public $alliance = null;
    /** @var array corpDetails array containing information from the public corp sheet.
     * Populated by stats() */
    protected $corpDetails = null;

    /** @var string The selected view. */
    protected $view = null;
    /** @var array The list of views and their callbacks. */
    protected $viewList = array();
    /** @var array The list of menu options to display. */
    protected $menuOptions = array();
    /** @var integer */
    protected $month;
    /** @var integer */
    protected $year;

    /** @var integer */
    private $nmonth;
    /** @var integer */
    private $nyear;
    /** @var integer */
    private $pmonth;
    /** @var integer */
    private $pyear;
    /** @var KillSummaryTable */
    private $kill_summary = null;
    /** @var double efficiency The corp's efficiency */
    protected $efficiency = 0;

    
    /**
     * Construct the Pilot Details object.
     * Set up the basic variables of the class and add the functions to the
     *  build queue.
     */
    function __construct()
    {
        parent::__construct();

        $this->queue("start");
        $this->queue("statSetup");
        $this->queue("stats");
        $this->queue("summaryTable");
        $this->queue("killList");
        $this->queue("metaTags");

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
        $this->page = new Page('Corporation details');

        $this->scl_id = (int)edkURI::getArg('scl_id');
        $this->crp_id = (int)edkURI::getArg('crp_id');
        if (!$this->crp_id) {
            $this->crp_external_id = (int)edkURI::getArg('crp_ext_id');
            if (!$this->crp_external_id) {
                $id = (int)edkURI::getArg('id', 1);
                // True for NPC corps too, but NPC alliances recorded as corps
                // fail here. Use Jedi mind tricks?
                if ($id > 1000000) {
                    $this->crp_external_id = $id;
                } else {
                    $this->crp_id = $id;
                }
            }
        }

        $this->view = preg_replace('/[^a-zA-Z0-9_-]/','', edkURI::getArg('view', 2));
        if($this->view) {
            $this->page->addHeader('<meta name="robots" content="noindex, nofollow" />');
        }

        if(!$this->crp_id) {
            if($this->crp_external_id) 
            {
                $this->corp = new Corporation($this->crp_external_id, true);
                $this->crp_id = $this->corp->getID();
            } 
            if(!$this->crp_id)
            {
                $html = 'That corporation does not exist.';
                $this->page->setContent($html);
                $this->page->generate();
                exit;
            }
        } else {
            $this->corp = Cacheable::factory('Corporation', $this->crp_id);
            $this->crp_external_id = $this->corp->getExternalID();
        }

        if($this->crp_external_id) {
            $this->page->addHeader("<link rel='canonical' href='"
                    .edkURI::build(array('crp_ext_id', $this->crp_external_id,
                        true))."' />");
        } else {
            $this->page->addHeader("<link rel='canonical' href='"
                    .edkURI::build(array('crp_id', $this->crp_id,
                        true))."' />");
        }

        $this->alliance = $this->corp->getAlliance();

        if ($this->view) {
            $this->year = (int)edkURI::getArg('y', 3);
            $this->month = (int)edkURI::getArg('m', 4);
        } else {
            $this->year = (int)edkURI::getArg('y', 2);
            $this->month = (int)edkURI::getArg('m', 3);
        }

        if (!$this->month) {
            $this->month = kbdate('m');
        }
        if (!$this->year) {
            $this->year = kbdate('Y');
        }

        if ($this->month == 12) {
            $this->nmonth = 1;
            $this->nyear = $this->year + 1;
        } else {
            $this->nmonth = $this->month + 1;
            $this->nyear = $this->year;
        }
        if ($this->month == 1) {
            $this->pmonth = 12;
            $this->pyear = $this->year - 1;
        } else {
            $this->pmonth = $this->month - 1;
            $this->pyear = $this->year;
        }
        $this->monthname = kbdate("F", strtotime("2000-".$this->month."-2"));
                
                global $smarty;
        $smarty->assign('monthname', $this->monthname);
                $smarty->assign('month', $this->monthname);
        $smarty->assign('year', $this->year);
        $smarty->assign('pmonth', $this->pmonth);
        $smarty->assign('pyear', $this->pyear);
        $smarty->assign('nmonth', $this->nmonth);
        $smarty->assign('nyear', $this->nyear);
    }
    /**
     *  Set up the stats used by the stats and summary table functions
     */
    function statSetup()
    {
        $this->kill_summary = new KillSummaryTable();
        $this->kill_summary->addInvolvedCorp($this->crp_id);
        $this->kill_summary->generate();
    }
    /**
     *  Build the summary table showing all kills and losses for this corporation.
     */
    function summaryTable()
    {
        if($this->view != '' && $this->view != 'kills'
            && $this->view != 'losses') return '';
        // The summary table is also used by the stats. Whichever is called
        // first generates the table.
        return $this->kill_summary->generate();
    }
    /**
     *  Show the overall statistics for this corporation.
     */
    function stats()
    {
        global $smarty;
        // The summary table is also used by the stats. Whichever is called
        // first generates the table.
        $this->page->setTitle('Corporation details - '.$this->corp->getName());
        // update the corp's details
        try
        {
            $EsiCorp = $this->corp->fetchCorp();
            if($EsiCorp)
            {
                $this->alliance = $this->corp->getAlliance(); 
                $this->corpDetails = array('ticker' => $EsiCorp->getTicker());
                $this->page->setTitle('Corporation details - '.$this->corp->getName() . " [" . $EsiCorp->getTicker() . "]");
            }
        }
        
        catch(ApiException $e)
        {
            EDKError::log(ESI::getApiExceptionReason($e) . PHP_EOL . $e->getTraceAsString());
            $this->page->setTitle('Corporation details - '.$this->corp->getName() . " []");
        }

        $smarty->assign('portrait_url', $this->corp->getPortraitURL(128));

        if($this->alliance->getName() == "None") 
        {
            $smarty->assign('alliance_url', false);
        } 
        
        else if($this->alliance->getExternalID()) 
        {
            $this->corpDetails['allianceName'] = $this->alliance->getName();
            $smarty->assign('alliance_url', edkURI::build(
                    array('a', 'alliance_detail', true),
                    array('all_ext_id', $this->alliance->getExternalID(), true)));
        } 
        
        else 
        {
            $this->corpDetails['allianceName'] = $this->alliance->getName();
            $smarty->assign('alliance_url', edkURI::build(
                    array('a', 'alliance_detail', true),
                    array('all_id', $this->alliance->getID(), true)));
        }
        $smarty->assign('alliance_name', $this->alliance->getName());

        $smarty->assign('kill_count', $this->kill_summary->getTotalKills());
        $smarty->assign('loss_count', $this->kill_summary->getTotalLosses());
        $smarty->assign('damage_done', number_format($this->kill_summary->getTotalKillISK()/1000000000, 2));
        $smarty->assign('damage_received', number_format($this->kill_summary->getTotalLossISK()/1000000000, 2));
        if ($this->kill_summary->getTotalKillISK()) {
            $this->efficiency = number_format(100 * $this->kill_summary->getTotalKillISK() /
                            ($this->kill_summary->getTotalKillISK()
                            + $this->kill_summary->getTotalLossISK()), 2);
        } else {
            $this->efficiency = 0;
        }
                
                $smarty->assign('efficiency', $this->efficiency);

        if ($EsiCorp) 
        {
            $ceoPilotId = $EsiCorp->getCeoId();
            try
            {
                $CeoPilot = new Pilot(0, $ceoPilotId);
                $CeoPilot->fetchPilot();
                $this->corpDetails['pilotIdCeo'] = $ceoPilotId;
                $this->corpDetails['pilotNameCeo'] = $CeoPilot->getName();
            }
            catch (ApiException $e) 
            {
                EDKError::log(ESI::getApiExceptionReason($e) . PHP_EOL . $e->getTraceAsString());
            }
            // FIXME not provided by ESI!
			$this->corpDetails['homeStationId'] = $EsiCorp->getHomeStationId();
			$EdkEsi = new ESI();
			$UniverseApi = new UniverseApi($EdkEsi);
			$UniverseDetails = $UniverseApi->getUniverseStationsStationId($this->corpDetails['homeStationId'], $EdkEsi->getDataSource());
            $this->corpDetails['headQuartersName'] = $UniverseDetails->getName();
            $this->corpDetails['memberCount'] = $EsiCorp->getMemberCount();
            // FIXME not provided by ESI!
            $this->corpDetails['shareCount'] = $EsiCorp->getShares();
            $this->corpDetails['taxRate'] = $EsiCorp->getTaxRate() * 100;
            $this->corpDetails['externalUrl'] = $EsiCorp->getUrl();
            $smarty->assign('ceo_url', edkURI::build(
                    array('a', 'pilot_detail', true),
                    array('plt_ext_id', $this->corpDetails['pilotIdCeo'], true)));
            $smarty->assign('ceo_name', $this->corpDetails['pilotNameCeo']);
            $smarty->assign('HQ_location', $this->corpDetails['headQuartersName']);
            $smarty->assign('member_count', $this->corpDetails['memberCount']);
            $smarty->assign('share_count', $this->corpDetails['shareCount']);
            $smarty->assign('tax_rate', $this->corpDetails['taxRate']);
            $smarty->assign('external_url', $this->corpDetails['externalUrl']);
            $description = $EsiCorp->getDescription();
            $description = preg_replace('/<br>/', '<br />', $description);
            // replace non-html size
            $description = preg_replace('/<font size=\"[1-9]+\"/', '<font', $description);
            //strip out broken cyan color tag
            $description = preg_replace('/color=\"#bfffffff\"/', '', $description);
            // replace character links
            $description = preg_replace_callback('/showinfo:[1-9]+\/\//', array($this, 'parseShowInfoLink'), $description);
            $this->corpDetails['description'] = $description;
            $smarty->assign('corp_description', $this->corpDetails['description']);
        }
        return $smarty->fetch(get_tpl('corp_detail_stats'));
    }
        
        /**
         * callback for showinfo links in corp description;
         * replaces the showinfo-link with a link to the correct entity (corp/ally/pilot)
         * @param array $showInfoLinks
         */
        static function parseShowInfoLink($showInfoLinks)
        {
            // showInfoLinks[0] looks like this: showinfo:1378//
            // make it look like: showinfo:1378
            $showInfoLink = substr($showInfoLinks[0], 0, strlen($showInfoLinks[0])-2);
            // 1378
            $typeID = substr($showInfoLink, strpos($showInfoLink, ':')+1, strlen($showInfoLink)-strpos($showInfoLink, ':'));

            // Alliance
            if($typeID == 16159)
            {
                return KB_HOST.'/?a=alliance_detail&all_ext_id=';
            }
            
            // Corporation
            elseif($typeID == 2)
            {
                return KB_HOST.'/?a=corp_detail&crp_ext_id=';
            }
            
            // Characters of various races
            elseif($typeID >= 1373 && $typeID <= 1386)
            {
                return KB_HOST.'/?a=pilot_detail&plt_ext_id=';
            }
            
            // nothing of the above, don't change anything
            return $showInfoLinks[0];
        }

    /**
     *  Build the killlists that are needed for the options selected.
     */
    function killList()
    {
        global $smarty;
        if(isset($this->viewList[$this->view])) {
            return call_user_func_array($this->viewList[$this->view], array(&$this));
        }
        $args = array();
        if ($this->crp_external_id) {
            $args[] = array('crp_ext_id', $this->crp_external_id, true);
        } else {
            $args[] = array('crp_id', $this->crp_id, true);
        }

        $pyearUrlArgument = array('y', $this->pyear, true);
        $nyearUrlArgument = array('y', $this->nyear, true);
        $pmonthUrlArgument = array('m', $this->pmonth, true);
        $nmonthUrlArgument = array('m', $this->nmonth, true);
        switch ($this->view)
        {
            case "":
                $list = new KillList();
                $list->setOrdered(true);
                $list->setLimit(10);
                $list->addInvolvedCorp($this->crp_id);
                if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
                else $list->setPodsNoobShips(config::get('podnoobs'));
                if (config::get('comments_count')) $list->setCountComments(true);
                if (config::get('killlist_involved')) $list->setCountInvolved(true);

                $ktab = new KillListTable($list);
                $ktab->setLimit(10);
                $ktab->setDayBreak(false);
                $smarty->assign('kills', $ktab->generate());

                $list = new KillList();
                $list->setOrdered(true);
                $list->setLimit(10);
                $list->addVictimCorp($this->crp_id);
                if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
                else $list->setPodsNoobShips(config::get('podnoobs'));
                if (config::get('comments_count')) $list->setCountComments(true);
                if (config::get('killlist_involved')) $list->setCountInvolved(true);

                $ltab = new KillListTable($list);
                $ltab->setLimit(10);
                $ltab->setDayBreak(false);
                $smarty->assign('losses', $ltab->generate());
                return $smarty->fetch(get_tpl('detail_kl_default'));

                break;
            case "kills":
                $list = new KillList();
                $list->setOrdered(true);
                $list->addInvolvedCorp($this->crp_id);
                if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
                else $list->setPodsNoobShips(config::get('podnoobs'));
                $list->setPageSplit(config::get('killcount'));
                $pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));
                $table = new KillListTable($list);
                $table->setDayBreak(false);
                $smarty->assign('splitter',$pagesplitter->generate());
                $smarty->assign('kills', $table->generate());
                return $smarty->fetch(get_tpl('detail_kl_kills'));

                break;
            case "losses":
                $list = new KillList();
                $list->setOrdered(true);
                $list->addVictimCorp($this->crp_id);
                if ($this->scl_id) $list->addVictimShipClass($this->scl_id);
                else $list->setPodsNoobShips(config::get('podnoobs'));
                $list->setPageSplit(config::get('killcount'));
                $pagesplitter = new PageSplitter($list->getCount(), config::get('killcount'));

                $table = new KillListTable($list);
                $table->setDayBreak(false);
                $smarty->assign('splitter',$pagesplitter->generate());
                $smarty->assign('losses', $table->generate());
                return $smarty->fetch(get_tpl('detail_kl_losses'));

                break;
            case "pilot_kills":
                $smarty->assign('title', 'Top Killers');
                $smarty->assign('crp_id', $this->crp_id);
                $smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_kills', true), $pyearUrlArgument, $pmonthUrlArgument));
                $smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_kills', true), $nyearUrlArgument, $nmonthUrlArgument));

                $list = new TopList_Kills();
                $list->addInvolvedCorp($this->crp_id);
                $list->setPodsNoobShips(config::get('podnoobs'));
                $list->setMonth($this->month);
                $list->setYear($this->year);
                $table = new TopTable_Pilot($list, "Kills");
                $smarty->assign('monthly_stats', $table->generate());

                $list = new TopList_Kills();
                $list->addInvolvedCorp($this->crp_id);
                $list->setPodsNoobShips(config::get('podnoobs'));
                $table = new TopTable_Pilot($list, "Kills");
                $smarty->assign('total_stats', $table->generate());

                return $smarty->fetch(get_tpl('detail_kl_monthly'));

                break;
            case "pilot_scores":
                $smarty->assign('title', 'Top Scorers');
                $smarty->assign('crp_id', $this->crp_id);
                $smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_scores', true), $pyearUrlArgument, $pmonthUrlArgument));
                $smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_scores', true), $nyearUrlArgument, $nmonthUrlArgument));

                $list = new TopList_Score();
                $list->addInvolvedCorp($this->crp_id);
                $list->setPodsNoobShips(config::get('podnoobs'));
                $list->setMonth($this->month);
                $list->setYear($this->year);
                $table = new TopTable_Pilot($list, "Points");
                $smarty->assign('monthly_stats', $table->generate());

                $list = new TopList_Score();
                $list->addInvolvedCorp($this->crp_id);
                $list->setPodsNoobShips(config::get('podnoobs'));
                $table = new TopTable_Pilot($list, "Points");
                $smarty->assign('total_stats', $table->generate());

                return $smarty->fetch(get_tpl('detail_kl_monthly'));

                break;
            case "pilot_solo":
                $smarty->assign('title', 'Top Solokillers');
                $smarty->assign('crp_id', $this->crp_id);
                $smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_solo', true), $pyearUrlArgument, $pmonthUrlArgument));
                $smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_solo', true), $nyearUrlArgument, $nmonthUrlArgument));

                $list = new TopList_SoloKiller();
                $list->addInvolvedCorp($this->crp_id);
                $list->setPodsNoobShips(config::get('podnoobs'));
                $list->setMonth($this->month);
                $list->setYear($this->year);
                $table = new TopTable_Pilot($list, "Solokills");
                $smarty->assign('monthly_stats', $table->generate());

                $list = new TopList_SoloKiller();
                $list->addInvolvedCorp($this->crp_id);
                $list->setPodsNoobShips(config::get('podnoobs'));
                $table = new TopTable_Pilot($list, "Solokills");
                $smarty->assign('total_stats', $table->generate());

                return $smarty->fetch(get_tpl('detail_kl_monthly'));

                break;

            case "pilot_damage":
                $smarty->assign('title', 'Top Damagedealers');
                $smarty->assign('crp_id', $this->crp_id);
                $smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_damage', true), $pyearUrlArgument, $pmonthUrlArgument));
                $smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_damage', true), $nyearUrlArgument, $nmonthUrlArgument));

                $list = new TopList_DamageDealer();
                $list->addInvolvedCorp($this->crp_id);
                $list->setPodsNoobShips(config::get('podnoobs'));
                $list->setMonth($this->month);
                $list->setYear($this->year);
                $table = new TopTable_Pilot($list, "Kills");
                $smarty->assign('monthly_stats', $table->generate());

                $list = new TopList_DamageDealer();
                $list->addInvolvedCorp($this->crp_id);
                $list->setPodsNoobShips(config::get('podnoobs'));
                $table = new TopTable_Pilot($list, "Kills");
                $smarty->assign('total_stats', $table->generate());

                return $smarty->fetch(get_tpl('detail_kl_monthly'));

                break;

            case "pilot_griefer":
                $smarty->assign('title', 'Top Griefers');
                $smarty->assign('crp_id', $this->crp_id);
                $smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_griefer', true), $pyearUrlArgument, $pmonthUrlArgument));
                $smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_griefer', true), $nyearUrlArgument, $nmonthUrlArgument));

                $list = new TopList_Kills();
                $list->addVictimShipClass(20); // freighter
                $list->addVictimShipClass(22); // exhumer
                $list->addVictimShipClass(7); // industrial
                $list->addVictimShipClass(12); // barge
                $list->addVictimShipClass(14); // transport

                $list->addInvolvedCorp($this->crp_id);
                $list->setMonth($this->month);
                $list->setYear($this->year);
                $table = new TopTable_Pilot($list, "Kills");
                $smarty->assign('monthly_stats', $table->generate());

                $list = new TopList_Kills();
                $list->addVictimShipClass(20); // freighter
                $list->addVictimShipClass(22); // exhumer
                $list->addVictimShipClass(7); // industrial
                $list->addVictimShipClass(12); // barge
                $list->addVictimShipClass(14); // transport
                $list->addInvolvedCorp($this->crp_id);
                $table = new TopTable_Pilot($list, "Kills");
                $smarty->assign('total_stats', $table->generate());

                return $smarty->fetch(get_tpl('detail_kl_monthly'));

                break;

            case "pilot_losses":
                $smarty->assign('title', 'Top Losers');
                $smarty->assign('crp_id', $this->crp_id);
                $smarty->assign('url_previous', edkURI::build($args, array('view', 'pilot_losses', true), $pyearUrlArgument, $pmonthUrlArgument));
                $smarty->assign('url_next', edkURI::build($args, array('view', 'pilot_losses', true), $nyearUrlArgument, $nmonthUrlArgument));

                $list = new TopList_Losses();
                $list->addVictimCorp($this->crp_id);
                $list->setPodsNoobShips(config::get('podnoobs'));
                $list->setMonth($this->month);
                $list->setYear($this->year);
                $table = new TopTable_Pilot($list, "Losses");
                $smarty->assign('monthly_stats', $table->generate());

                $list = new TopList_Losses();
                $list->addVictimCorp($this->crp_id);
                $list->setPodsNoobShips(config::get('podnoobs'));
                $table = new TopTable_Pilot($list, "Losses");
                $smarty->assign('total_stats', $table->generate());

                return $smarty->fetch(get_tpl('detail_kl_monthly'));

                break;
            case "ships_weapons":
                $shiplist = new TopList_Ship();
                $shiplist->addInvolvedCorp($this->crp_id);
                $shiplisttable = new TopTable_Ship($shiplist);
                $smarty->assign('ships', $shiplisttable->generate());

                $weaponlist = new TopList_Weapon();
                $weaponlist->addInvolvedCorp($this->crp_id);
                $weaponlisttable = new TopTable_Weapon($weaponlist);
                $smarty->assign('weapons', $weaponlisttable->generate());
                return $smarty->fetch(get_tpl('detail_kl_ships_weapons'));

                break;
            case 'violent_systems':
                $smarty->assign('title', 'Most violent systems');
                $smarty->assign('crp_id', $this->crp_id);
                $smarty->assign('url_previous', edkURI::build($args, array('view', 'violent_systems', true), $pyearUrlArgument, $pmonthUrlArgument));
                $smarty->assign('url_next', edkURI::build($args, array('view', 'violent_systems', true), $nyearUrlArgument, $nmonthUrlArgument));

                $startdate = gmdate('Y-m-d H:i:s', makeStartDate(0, $this->year, $this->month));
                $enddate = gmdate('Y-m-d H:i:s', makeEndDate(0, $this->year, $this->month));
                $sql = "select sys.sys_name, sys.sys_sec, sys.sys_id, count(kll.kll_id) as kills
                            from kb3_systems sys, kb3_kills kll, kb3_inv_crp inc
                            where kll.kll_system_id = sys.sys_id
                            and inc.inc_kll_id = kll.kll_id
                            and inc.inc_crp_id = ".$this->crp_id;

                $sql .= "   and kll.kll_timestamp > '$startdate'
                            and kll.kll_timestamp < '$enddate'
                            and inc.inc_timestamp > '$startdate'
                            and inc.inc_timestamp < '$enddate'
                            group by sys.sys_id
                            order by kills desc, sys.sys_name asc
                            limit 25";

                $qry = DBFactory::getDBQuery();
                $qry->execute($sql);
                $odd = false;
                $counter = 1;
                $syslist = array();

                while ($row = $qry->getRow())
                {
                    if (!$odd)
                    {
                        $odd = true;
                        $rowclass = 'kb-table-row-odd';
                    }
                    else
                    {
                        $odd = false;
                        $rowclass = 'kb-table-row-even';
                    }

                    $syslist[] = array(
                        "counter"=>$counter,
                        "url"=>"?a=system_detail&amp;sys_id=".$row['sys_id'],
                        "name"=>$row['sys_name'],
                        "sec"=>roundsec($row['sys_sec']),
                        "kills"=>$row['kills']);
                    $counter++;
                }
                $smarty->assignByRef('syslist', $syslist);
                $smarty->assign('monthly_stats', $smarty->fetch(get_tpl('violent_systems')));

                $sql = "select sys.sys_name, sys.sys_id, sys.sys_sec, count(kll.kll_id) as kills
                            from kb3_systems sys, kb3_kills kll, kb3_inv_crp inc
                            where kll.kll_system_id = sys.sys_id
                            and inc.inc_kll_id = kll.kll_id
                            and inc.inc_crp_id = ".$this->crp_id;

                $sql .= " group by sys.sys_id
                            order by kills desc, sys.sys_name asc
                            limit 25";

                $qry = DBFactory::getDBQuery();
                $qry->execute($sql);
                $odd = false;
                $counter = 1;
                $syslist = array();

                while ($row = $qry->getRow())
                {
                    if (!$odd)
                    {
                        $odd = true;
                        $rowclass = 'kb-table-row-odd';
                    }
                    else
                    {
                        $odd = false;
                        $rowclass = 'kb-table-row-even';
                    }

                    $syslist[] = array(
                        "counter"=>$counter,
                        "url"=>"?a=system_detail&amp;sys_id=".$row['sys_id'],
                        "name"=>$row['sys_name'],
                        "sec"=>roundsec($row['sys_sec']),
                        "kills"=>$row['kills']);
                    $counter++;
                }
                $smarty->assignByRef('syslist', $syslist);
                $smarty->assign('total_stats', $smarty->fetch(get_tpl('violent_systems')));
                return $smarty->fetch(get_tpl('detail_kl_monthly'));
                break;
        }
        return $html;
    }
    /**
     * Set up the menu.
     *
     *  Prepare all the base menu options.
     */
    function menuSetup()
    {
        $args = array();
        if ($this->crp_external_id) {
            $args[] = array('crp_ext_id', $this->crp_external_id, true);
        } else {
            $args[] = array('crp_id', $this->crp_id, true);
        }

        $this->addMenuItem("caption","Kills &amp; losses");
        $this->addMenuItem("link","Recent activity", edkURI::build($args));
        $this->addMenuItem("link","Kills", edkURI::build($args, array('view', 'kills', true)));
        $this->addMenuItem("link","Losses", edkURI::build($args, array('view', 'losses', true)));
        $this->addMenuItem("caption","Pilot statistics");
        $this->addMenuItem("link","Top killers", edkURI::build($args, array('view', 'pilot_kills', true)));

        if (config::get('kill_points'))
            $this->addMenuItem("link","Top scorers", edkURI::build($args, array('view', 'pilot_scores', true)));
        $this->addMenuItem("link","Top solokillers", edkURI::build($args, array('view', 'pilot_solo', true)));
        $this->addMenuItem("link","Top damagedealers", edkURI::build($args, array('view', 'pilot_damage', true)));
        $this->addMenuItem("link","Top griefers", edkURI::build($args, array('view', 'pilot_griefer', true)));
        $this->addMenuItem("link","Top losers", edkURI::build($args, array('view', 'pilot_losses', true)));
        $this->addMenuItem("caption","Global statistics");
        $this->addMenuItem("link","Ships &amp; weapons", edkURI::build($args, array('view', 'ships_weapons', true)));
        $this->addMenuItem("link","Most violent systems", edkURI::build($args, array('view', 'violent_systems', true)));
        return "";
    }
        
    /** 
     * adds meta tags for Twitter Summary Card and OpenGraph tags
     * to the HTML header
     */
    function metaTags()
    {
        // meta tag: title
        $metaTagTitle = $this->corp->getName() . " | Corp Details";
        $this->page->addHeader('<meta name="og:title" content="'.$metaTagTitle.'">');
        $this->page->addHeader('<meta name="twitter:title" content="'.$metaTagTitle.'">');

        // build description
        $metaTagDescription = $this->corp->getName();
        if($this->corpDetails['ticker'])
        {
            $metaTagDescription .= " [" . $this->corpDetails['ticker'] . "] (" . $this->corpDetails['memberCount'] . " pilots";
        }
        if(isset($this->corpDetails['allianceName']))
        {
             $metaTagDescription .= ", member of " . $this->corpDetails['allianceName'];
        }
        $metaTagDescription .= ") has " . $this->kill_summary->getTotalKills() . " kills and " . $this->kill_summary->getTotalLosses() . " losses (Efficiency: ".$this->efficiency."%) at " . config::get('cfg_kbtitle');

        $this->page->addHeader('<meta name="description" content="'.$metaTagDescription.'">');
        $this->page->addHeader('<meta name="og:description" content="'.$metaTagDescription.'">');

        // meta tag: image
        $this->page->addHeader('<meta name="og:image" content="'.$this->corp->getPortraitURL(128).'">');
        $this->page->addHeader('<meta name="twitter:image" content="'.$this->corp->getPortraitURL(128).'">');

        $this->page->addHeader('<meta name="og:site_name" content="EDK - '.config::get('cfg_kbtitle').'">');

        // meta tag: URL
        $this->page->addHeader('<meta name="og:url" content="'.edkURI::build(array('crp_id', $this->crp_id, true)).'">');
        // meta tag: Twitter summary
        $this->page->addHeader('<meta name="twitter:card" content="summary">');
    }
        
    /**
     * Build the menu.
     *
     *  Add all preset options to the menu.
     */
    function menu()
    {
        $menubox = new box("Menu");
        $menubox->setIcon("menu-item.gif");
        foreach($this->menuOptions as $options)
        {
            if(isset($options[2]))
                $menubox->addOption($options[0],$options[1], $options[2]);
            else
                $menubox->addOption($options[0],$options[1]);
        }
        return $menubox->generate();
    }
    /**
     * Add an item to the menu in standard box format.
     *
     *  Only links need all 3 attributes
     * @param string $type Types can be caption, img, link, points.
     * @param string $name The name to display.
     * @param string $url Only needed for URLs.
     */
    function addMenuItem($type, $name, $url = '')
    {
        $this->menuOptions[] = array($type, $name, $url);
    }
    
    /**
    * Removes the menu item with the given name
    * 
    * @param string $name the name of the menu item to remove
    */
   function removeMenuItem($name)
   {
       foreach((array)$this->menuOptions AS $menuItem)
       {
           if(count($menuItem) > 1 && $menuItem[1] == $name)
           {
               unset($this->menuOptions[key($this->menuOptions)]);
           }
       }
   }

    /**

     * Add a type of view to the options.

     *
     * @param string $view The name of the view to recognise.
     * @param mixed $callback The method to call when this view is used.
     */
    function addView($view, $callback)
    {
        $this->viewList[$view] = $callback;
    }
        
        /**
     * Return the set month.
     * @return integer
     */
    function getMonth()
    {
        return $this->month;
    }

    /**
     * Return the set year.
     * @return integer
     */
    function getYear()
    {
        return $this->year;
    }

    /**
     * Return the set view.
     * @return string
     */
    function getView()
    {
        return $this->view;
    }
        
        
    /**
     * Return the corporation
     * @return Corporation
     */
    function getCorp()
    {
        return $this->corp;
    }
    
    function getCorpDetails() 
    {
        return $this->corpDetails;
    }

    function getNextMonth() 
    {
        return $this->nmonth;
    }

    function getNextYear() 
    {
        return $this->nyear;
    }

    function getPreviousMonth() 
    {
        return $this->pmonth;
    }

    function getPreviousYear() 
    {
        return $this->pyear;
    }

    function getKillSummary() 
    {
        return $this->kill_summary;
    }

    function getEfficiency() 
    {
        return $this->efficiency;
    }


}

$corpDetail = new pCorpDetail();
event::call("corpDetail_assembling", $corpDetail);
$html = $corpDetail->assemble();
$corpDetail->page->setContent($html);

$corpDetail->context();
event::call("corpDetail_context_assembling", $corpDetail);
$context = $corpDetail->assemble();
$corpDetail->page->addContext($context);

$corpDetail->page->generate();
