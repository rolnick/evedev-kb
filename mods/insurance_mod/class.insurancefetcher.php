<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

class InsuranceFetcherCrestException extends Exception {}
/**
 * Fetches average item prices from CREST
 * 
 * @package EDK
 */
class InsuranceFetcherCrest
{
    
    /** CREST url pointing to insurance payouts */
    public static $CREST_INSURANCE_ENDPOINT = '/insuranceprices/';
    
    /** the url to fetch item prices from */
    protected $url;

    /**
     * @param string $url URL for item price xml
     */
    public function __construct($url = null)
    {
        // Check the input
        if ($url != null && $url != "" && (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://')) 
        {
            $this->url = $url;
        }
        
        else
        {
            $this->url = CREST_PUBLIC_URL . self::$CREST_INSURANCE_ENDPOINT;
        }
    }

    /**
     * Fetch item values.
     * 
     * @return int The count of values fetched
     * @throws ValueFetcherCrestException
     */
    public function fetchInsurance($kllid, $shipid, $classid)
    {
        // New query
        $qry = DBFactory::getDBQuery();

        // fetch and decode JSON
        $data = SimpleCrest::getReferenceByUrl($this->url);

        if(!isset($data->items) || count($data->items) < 1)
        {
            return 0;
        }
        // basic insurance for supers
        if($classid == 26 || $classid == 28)
        {
            $insurancelevel = 'Basic';
        }
        else
        {
            $insurancelevel = 'Platinum';
        }

        $net_insurance = 0;
        foreach ($data->items as $item) 
        {
            $typeId = @(int)$item->type->id;
            if ($typeId == @(int)$shipid)
                {
                foreach ($item->insurance as $insurance)
                {
                    // use averagePrice (alternative is adjustedPrice, but it's not public what it's adjusted to)
                    $level = $insurance->level;
                    $payout = @(float)$insurance->payout;
                    $cost = @(float)$insurance->cost;
                    if ($level == $insurancelevel)
                    {
                        if ($insurancelevel == 'Basic')
                        {
                            $net_insurance = $payout;
                        }
                        else
                        {
                            $net_insurance = $payout - $cost;
                        }
                        break;
                    }
                }
                break;
            }
        }

        $querytext = "INSERT INTO kb3_insurances (kll_id, kll_insurance) VALUES ";
        $querytext .= "($kllid,".number_format($net_insurance, 0, '', '').")";
        $querytext .= " ON DUPLICATE KEY UPDATE kll_id=kll_id;";

        $qry->execute($querytext);
    }
}    
