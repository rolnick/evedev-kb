<?php
/**
 * $Date: 2010-05-30 19:38:00 +1000 (Sun, 30 May 2010) $
 * $Revision: 732 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

# require_once("class.api.php");
 use EDK\ESI\ESI;
 use EsiClient\AllianceApi;
 use EsiClient\CorporationApi;
 use \Swagger\Client\ApiException;

/**
 * Retrieve Alliance list from CCP to find alliance details.
 * @package EDK
 */
class API_Alliance extends API
{
	protected $sxe = null;
	protected $CachedUntil_ = null;
	protected $CurrentTime_ = null;
	protected $data = null;

	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function fetchalliances($overide=false)
	  
	{   
		$EdkEsi = new ESI();
		$AllianceApi = new AllianceApi($EdkEsi);
		$this->data = $AllianceApi->getAlliances();
	}
	function LocateAlliance($name)
	{
		$res = array();
                if(is_null($this->data) || (!is_array($this->data->alliances) && !$this->data->alliances instanceof Traversable))
                {
                    return false;
                }
		foreach( $this->data->alliances as $alliance ) {
			if( $alliance->name != $name ) {
				continue;
			}
			$res['name'] = $alliance->name;
			$res['shortName'] = $alliance->shortName;
			$res['allianceID'] = $alliance->allianceID;
			$res['executorCorpID'] = $alliance->executorCorpID;
			$res['memberCount'] = $alliance->memberCount;
			$res['startDate'] = $alliance->startDate;
			$res['allianceName'] = $alliance->name; // @todo wtf?
			
			$res['memberCorps'] = array();
                            foreach( $alliance->memberCorporations as $corp ) {
                                    $res['memberCorps'][] = array('corporationID'=>$corp->corporationID, 
                                                                                              'startDate'=>$corp->startDate);
                            }
			return $res;
		}
		return false;	
	}

	function LocateAllianceID($id)
	{	$EdkEsi = new ESI();
		$AllianceApi = new AllianceApi($EdkEsi);
		$CorporationApi = new CorporationApi($EdkEsi);
		$res = array();
                if(is_null($this->data) || (!is_array($this->data) && !$this->data instanceof Traversable))
                {
                    return false;
                }
		foreach( $this->data as $alliance ) {
			if( $alliance != $id ) {
				continue;
			}
			$AllianceDetails = $AllianceApi->getAlliancesAllianceId($id, $EdkEsi->getDataSource());
			$allianceCorps = $AllianceApi->getAlliancesAllianceIdCorporations($id, $EdkEsi->getDataSource());

			$res['name'] = $AllianceDetails->getName();
			$res['shortName'] = $AllianceDetails->getTicker();
			$res['allianceID'] = $id;
			$res['executorCorpID'] = $AllianceDetails->getExecutorCorporationId();
			$res['memberCount'] = 0;
			$res['startDate'] = $AllianceDetails->getDateFounded();
			$res['allianceName'] = $AllianceDetails->getName();
			
			$res['tmpMemberCorps'] = array();
			foreach ($allianceCorps as $allianceCorpId) {
				$CorporationDetails = $CorporationApi->getCorporationsCorporationId($allianceCorpId, $EdkEsi->getDataSource());
				if ($AllianceDetails->getExecutorCorporationId() == $allianceCorpId) {
					$res["executorCorpName"] = $CorporationDetails->getName();
					$membercorp["members"] = $CorporationDetails->getMemberCount();
					$res["memberCount"] += $membercorp["members"];
				}
				array_push($res["tmpMemberCorps"], $allianceCorpId);
			}		
			foreach( $res["tmpMemberCorps"] as $corp ) {
				$res['memberCorps'][] = array('corporationID'=>$corp,
										  'startDate'=>$CorporationDetails->getDateFounded());
			}
			return $res;
		}
		return false;
	}
    function stats()
    {
        global $smarty;
        $tempMyCorp = new Corporation();
	$this->all_id = (int) edkURI::getArg('all_id');
        $this->all_external_id = (int) edkURI::getArg('all_ext_id');
	print $this->all_external_id;
        $this->alliance = new Alliance($this->all_external_id, true);

        // Use alliance ID if we have it
        if (!$this->alliance->getExternalID())
        {
            $allianceID = ESI_Helpers::getExternalIdForEntity($this->alliance->getName(), 'alliance');
            if(isset($allianceID))
            {
                $this->alliance->setExternalID($allianceID);
            }
        }
        if ($this->alliance->getExternalID())
        {
            if ($this->alliance->isFaction())
            {

                $Faction = Faction::getByID($this->alliance->getExternalID());
                $FactionCorp = new Corporation($Faction->getCorporationID(), true);
                $EsiFactionCorp = $FactionCorp->fetchCorp();
                $myAlliance = array(
                    "shortName" => $EsiFactionCorp->getTicker(),
                    "memberCount" => $EsiFactionCorp->getMemberCount(),
                    "executorCorpID" => $FactionCorp->getExternalID(),
                    "executorCorpName" => $FactionCorp->getName(),
                    "startDate" => null
                );

                $this->page->setTitle(Language::get('page_faction_det').' - '
                        .$this->alliance->getName()." [".$myAlliance["shortName"]
                        ."]");
            }

            else
            {

                $EdkEsi = new ESI();
                $AllianceApi = new AllianceApi($EdkEsi);
                $AllianceDetails = $AllianceApi->getAlliancesAllianceId($this->alliance->getExternalID(), $EdkEsi->getDataSource());
                // initialize array holding the alliance details
                $myAlliance = array(
                    "shortName" => $AllianceDetails->getTicker(),
                    "memberCount" => 0,
                    "executorCorpID" => null,
                    "executorCorpName" => null,
                    "startDate" => ESI_Helpers::formatDateTime($AllianceDetails->getDateFounded())
                );

                $this->page->setTitle(Language::get('page_all_det').' - '
                        .$this->alliance->getName()." [".$myAlliance["shortName"]
                        ."]");

                // fetch the alliance's corps
                $allianceCorps = $AllianceApi->getAlliancesAllianceIdCorporations($this->alliance->getExternalID(), $EdkEsi->getDataSource());

                $CorporationApi = new CorporationApi($EdkEsi);
                // fetch details for each member corp
                foreach ($allianceCorps as $allianceCorpId)
                {
                    try
                    {
                        $CorporationDetails = $CorporationApi->getCorporationsCorporationId($allianceCorpId, $EdkEsi->getDataSource());
                    }

                    catch(ApiException $e)
                    {
                        EDKError::log(ESI::getApiExceptionReason($e) . PHP_EOL . $e->getTraceAsString());
                        continue;
                    }

                    if ($AllianceDetails->getExecutorCorporationId() == $allianceCorpId)
                    {
                        $myAlliance["executorCorpName"] = $CorporationDetails->getName();
                        $myAlliance["executorCorpID"] = $AllianceDetails->getExecutorCorporationId();
                    }
                    // Build Data array
                    $membercorp["corpExternalID"] = $allianceCorpId;
                    $membercorp["corpName"] = $CorporationDetails->getName();
                    $membercorp["ticker"] = $CorporationDetails->getTicker();
                    $membercorp["members"] = $CorporationDetails->getMemberCount();
                    $myAlliance["memberCount"] += $membercorp["members"];

                    $this->allianceCorps[] = $membercorp;

                    // Check if corp is known to EDK DB, if not, add it.
                    $tempMyCorp = Corporation::getByExternalID($allianceCorpId);
                    if (!$tempMyCorp) {
                        $tempMyCorp = Corporation::add($membercorp["corpName"], $this->alliance,
                                $membercorp["joinDate"], $allianceCorpId);
                    }

                    $membercorp = array();
                    unset($membercorp);
                }
            }
	 }
    }
}

