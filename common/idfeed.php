<?php

use EDK\ESI\EsiConfiguration;
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */
/*
 * Create a syndication feed of kills stored on this board.
 *
 * Flags
 * startdate = unix timestamp for start date
 * enddate = unix timestamp for end date
 * lastID = return all kills from lastID on (ordered by external id)
 * lastintID = return all kills from lastintID internal id on (ordered by internal id)
 * range = return all kills between lastID and lastID + range
 *     (limited by $maxkillsreturned)
 * allkills = also return results without an external id set (default = 1)
 * pilot = pilot id to retrieve kills for
 * corp =  corp id to retrieve kills for
 * alliance = alliance id to retrieve kills for
 * pilotname = pilot id to retrieve kills for
 * corpname =  corp name to retrieve kills for
 * alliancename = alliance name to retrieve kills for
 * system = restrict kills to a specific system
 * region = restrict kills to a specific region
 * kll_id = show one kill only
 * kll_ext_id = show one kill only
 * limit = maximum number of kills to return
 * output = [json|xml|edk]; default = xml; changes output, edk means it only
 *          outputs id's and hashes as json; only works with kll_id or kll_ext_id
 *
 */

$starttime = microtime(true);

$maxkillsreturned = 200;
$xml = "<?xml version='1.0' encoding='UTF-8'?>
<eveapi version='2' edkapi='".ID_FEED_VERSION."'>
</eveapi>";
$sxe = new SimpleXMLElement($xml);

$list = new KillList();
if (isset($_GET['kll_id'])) {
    $_GET['lastintID'] = $_GET['kll_id'];
    $_GET['allkills'] = 1;
    $_GET['range'] = 0;
}
if (isset($_GET['kll_ext_id'])) {
    $_GET['lastID'] = $_GET['kll_ext_id'];
    $_GET['allkills'] = 0;
    $_GET['range'] = 0;
}

$list->setOrdered(true);
if (isset($_GET['allkills']) && $_GET['allkills'] == 0 ) {
    $list->setAPIKill();
    $list->setOrderBy(' kll.kll_external_id ASC ');
} else {
    $list->setOrderBy(' kll.kll_id ASC ');
}
if (isset($_GET['limit'])) {
    $list->setLimit(min($maxkillsreturned, (int)$_GET['limit']));
} else {
    $list->setLimit($maxkillsreturned);
}

$qry = DBFactory::getDBQuery();

if (isset($_GET['alliance'])) {
    $arr = explode(',', $_GET['alliance']);
    foreach ($arr as &$val) {
        $val = intval($val);
    }
    $qry->execute("SELECT all_id FROM kb3_alliances WHERE all_external_id IN (".implode(',',
                    $arr).")");
    if (!$qry->recordCount()) {
        show($sxe);
    }
    while ($row = $qry->getRow()) {
        $list->addCombinedAlliance($row['all_id']);
    }
}
if (isset($_GET['corp'])) {
    $arr = explode(',', $_GET['corp']);
    foreach ($arr as &$val) {
        $val = intval($val);
    }
    $qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_external_id IN (".implode(',',
                    $arr).")");
    if (!$qry->recordCount()) {
        show($sxe);
    }
    while ($row = $qry->getRow()) {
        $list->addCombinedCorp($row['crp_id']);
    }
}
if (isset($_GET['pilot'])) {
    $arr = explode(',', $_GET['pilot']);
    $arr_pilots = array();
    foreach ($arr as $val) {
        // Remove 0 external ids since that matches all pilots with no id.
        if ((int) $val) {
            $arr_pilots[] = (int) $val;
        }
    }
    $qry->execute("SELECT plt_id FROM kb3_pilots WHERE plt_externalid IN (".implode(',',
                    $arr_pilots).")");
    if (!$qry->recordCount()) {
        show($sxe);
    }
    while ($row = $qry->getRow()) {
        $list->addCombinedPilot($row['plt_id']);
    }
}
if (isset($_GET['alliancename'])) {
    $_GET['alliancename'] = '"'.str_replace(',', '","',
                    $qry->escape(urldecode($_GET['alliancename']))).'"';
    $qry->execute("SELECT all_id FROM kb3_alliances WHERE all_name IN (".$_GET['alliancename'].")");
    if (!$qry->recordCount()) {
        show($sxe);
    }
    while ($row = $qry->getRow()) {
        $list->addCombinedAlliance($row['all_id']);
    }
}
if (isset($_GET['corpname'])) {
    $_GET['corpname'] = '"'.str_replace(',', '","',
                    $qry->escape(urldecode($_GET['corpname']))).'"';
    $qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_name IN (".$_GET['corpname'].")");
    if (!$qry->recordCount()) {
        show($sxe);
    }
    while ($row = $qry->getRow()) {
        $list->addCombinedCorp($row['crp_id']);
    }
}
if (isset($_GET['pilotname'])) {
    $_GET['corpname'] = '"'.str_replace(',', '","',
                    $qry->escape(urldecode($_GET['pilotname']))).'"';
    $qry->execute("SELECT plt_id FROM kb3_pilots WHERE plt_name IN (".$_GET['corpname'].")");
    if (!$qry->recordCount()) {
        show($sxe);
    }
    while ($row = $qry->getRow()) {
        $list->addCombinedPilot($row['plt_id']);
    }
}

if (isset($_GET['system'])) {
    $qry->execute("SELECT sys_id FROM kb3_systems WHERE sys_id = ".intval($_GET['system'])." LIMIT 1");
    if (!$qry->recordCount()) {
        show($sxe);
    }
    $row = $qry->getRow();
    $list->addSystem($row['sys_id']);
} else if (isset($_GET['region'])) {
    $qry->execute("SELECT reg_id FROM kb3_regions WHERE reg_id = ".intval($_GET['region'])." LIMIT 1");
    if (!$qry->recordCount()) show($sxe);
    $row = $qry->getRow();
    $list->addRegion($row['reg_id']);
}

if (isset($_GET['lastID']) && isset($_GET['allkills'])
        && $_GET['allkills'] == 0) {
    $list->setMinExtID(intval($_GET['lastID']));
    if (isset($_GET['range']))
            $list->setMaxExtID(intval($_GET['lastID'] + $_GET['range']));
} else if (isset($_GET['lastintID'])) {
    $list->setMinKllID(intval($_GET['lastintID']));
    if (isset($_GET['range']))
            $list->setMaxKllID(intval($_GET['lastintID'] + $_GET['range']));
}
if (isset($_GET['startdate']))
        $list->setStartDate(gmdate('Y-m-d H:i:s', intval($_GET['startdate'])));
if (isset($_GET['enddate']))
        $list->setEndDate(gmdate('Y-m-d H:i:s', intval($_GET['enddate'])));


if (isset($_GET['output'])&&($_GET['output']=='json'||$_GET['output']=='edk')){

    header("Access-Control-Allow-Origin: *");//CORS
    header("Content-Type: application/json; charset=UTF-8");

    $extkllid=0;
    $intkllid=0;
    $hash="";
    $pqry = new DBPreparedQuery();

    if(isset($_GET['kll_id'])){
        $pqry->prepare('SELECT kll_external_id, kll_id, kll_crest_hash FROM kb3_mails WHERE kll_id=?');
        $pqry->bind_param('i',$_GET['kll_id']);
    } else if(isset($_GET['kll_ext_id'])) {
        $pqry->prepare('SELECT kll_external_id, kll_id, kll_crest_hash FROM kb3_mails WHERE kll_external_id=?');
        $pqry->bind_param('i',$_GET['kll_ext_id']);
    }

    $pqry->bind_result($extkllid,$intkllid,$hash);
    $pqry->execute();
    if($pqry->recordCount()<=0) {
        print_r(json_encode(array('error'=>'No valid external or internal kill!')));
        die;
    } else {
        $pqry->fetch();
    }

    if ($_GET['output']=='edk') {
        print_r(json_encode(array("external_kill_id"=>$extkllid,"internal_kill_id"=>$intkllid,"hash"=>$hash)));
    } else if ($_GET['output']=='json') {
        $EsiConfig = new EsiConfiguration();
        $json_kill = file_get_contents($EsiConfig->getHost()."/latest/killmails/".$extkllid."/".$hash."/?datasource=".ESI_DATA_SOURCE);

        foreach ($http_response_header as $value) {
            if(preg_match('/HTTP\/[0-9\.]+\s+([0-9]+)/',$value,$retarr)){
                break;
            };
        }

        if($retarr[1]==200){
            print_r($json_kill);
        } else {
            print_r(json_encode(array('error'=>'Error when calling ESI for killmail')));
            die;
        }
    }
} else {
    header("Content-Type: text/xml");
    echo IDFeed::killListToXML($list);
}

//echo "<!-- ".$timing."\n -->";
//echo "<!-- Finished: ".(microtime(true)-$starttime)." -->";


/**
 * Output generated XML and terminate.
 * 
 * @param SimpleXMLElement $sxe
 */
function show($sxe)
{
    header("Content-Type: text/xml");
    echo $sxe->asXML();
    cache::generate(); // We should really be in a class so this isn't needed.
    die;
}
