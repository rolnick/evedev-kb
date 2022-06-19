<?php
function handle_involved($kill, $side, &$pilots, $sideAssignmentMap = array(), $completeInformation = FALSE)
{
    
    // -------------------------------------------------------------------------
    // FIX BATTLE REPORT a little by Evoke. Salvoxia
    // BEGIN
    // -------------------------------------------------------------------------
    // we dont want our corp/alliance to show up on the enemy's side
    if($side == 'e')
    {
        if(config::get('cfg_corpid'))
        {
            $corpId = config::get('cfg_corpid');
            $corpId = $corpId[0];
            $excludeWhere = "AND ind_crp_id != {$corpId}";

        }
        elseif(config::get('cfg_allianceid'))
        {
            $allianceId = config::get('cfg_allianceid');
            $allianceId = $allianceId[0];
            $excludeWhere = "AND ind_all_id != {$allianceId}";
        }
        else
        {
            $excludeWhere = "";
        }
    }
    
    // we need to get all involved pilots, killlists dont supply them
    $qry= DBFactory::getDBQuery();
    $sql="select ind_plt_id AS ind_plt_id, ind_crp_id, ind_all_id, ind_sec_status, ind_shp_id, ind_wep_id, ind_dmgdone,
            typeName, plt_name, crp_name, all_name, scl_points, scl_id, scl_class
            from kb3_inv_detail
            left join kb3_invtypes on ind_wep_id=typeID
            left join kb3_pilots on ind_plt_id=plt_id
            left join kb3_corps on ind_crp_id=crp_id
            left join kb3_alliances on ind_all_id=all_id
            left join kb3_ships on ind_shp_id=shp_id
            left join kb3_ship_classes on shp_class=scl_id
            where ind_kll_id = " . $kill->getID() . " {$excludeWhere}
            order by ind_order";

    $qry->execute($sql);

    while ($row=$qry->getRow())
    {
        if(config::get('fleet_battles_mod_sideassign'))
        {
            // determine whether the pilot is member of an alliance
            if($row["all_name"] == "None")
            {
                $entityType = "corp";
                $entityId = $row["ind_crp_id"];
            }

            else 
            {
                $entityType = "alliance";
                $entityId = $row["ind_all_id"];
            }
           
            if(isset($sideAssignmentMap[$entityType][$entityId]))
            {
                $pilotSide = $sideAssignmentMap[$entityType][$entityId];
            }

            else
            {
                $pilotSide = $side;
            }
        }
        
        else
        {
            $pilotSide = $side;
        }
        
        
        // check for manual side assignment for pilot
        
        $ship           = Cacheable::factory('Ship', $row['ind_shp_id']);
        $shipc          = Cacheable::factory('ShipClass', $row['scl_id']);

        // check for npc names (copied from pilot class)
        $pos = strpos($row['plt_name'], "#");
			if ($pos !== false) {
				$name = explode("#", $row['plt_name']);
				$item = Item::getByID($name[2]);
				$row['plt_name'] = $item->getName();
			}

        // dont set pods as ships for pilots we already have
        if (isset($pilots[$pilotSide][$row['ind_plt_id']]))
            {
            if ($row['scl_id'] == 18 || $row['scl_id'] == 2)
                {
                continue;
                }
            }

        // search for ships with the same id
        if (isset($pilots[$pilotSide][$row['ind_plt_id']]) && is_array($pilots[$pilotSide][$row['ind_plt_id']]))
        {
            foreach ($pilots[$pilotSide][$row['ind_plt_id']] as $id => $_ship)
            {
            if ($row['ind_shp_id'] == $_ship['sid'])
                {
                // we already got that pilot in this ship, continue
                $pilots[$pilotSide][$row['ind_plt_id']][0]["times"]+=1;
                // add up his damage done
                $pilots[$pilotSide][$row['ind_plt_id']][0]["damage"]+=$row['ind_dmgdone'];
                continue 2;
                }
            }
        }
        if($completeInformation)
        {
            $pilots[$pilotSide][$row['ind_plt_id']][]=array
            (
            'name'      => $row['plt_name'],
            'plt_url'   => edkURI::page("pilot_detail", $row["ind_plt_id"], "plt_id"),
            'sid'       => $row['ind_shp_id'],
            'spic'      => imageURL::getURL('Ship', $ship->getID(), 32),
            'aid'       => $row['ind_all_id'],
            'ts'        => strtotime($kill->getTimeStamp()),
            'corp'      => $row['crp_name'],
            'alliance'  => $row['all_name'],
            'alliance_url' => edkURI::page("alliance_detail", $row['ind_all_id'], "all_id"),
            'scl'       => $row['scl_points'],
            'ship'      => $ship->getName(),
            'shipClass' => $row['scl_class'],
            'shipClassObject' => $shipc,
            'weapon'    => $row['itm_name'],
            'cid'       => $row['ind_crp_id'],
            'crp_url'   => edkURI::page("corp_detail", $row['ind_crp_id'], "crp_id"),
            'times'     => 1,
            'damage'    => $row['ind_dmgdone'],
            'color'     => getColorClassByClass($shipc)
            );

        }
        
        else
        {
            $pilots[$pilotSide][$row['ind_plt_id']] = 1;
        }
    }
}

    /**
     *
     * @global array $destroyed
     * @global <type> $pilots
     * @global array $pods
     * @param Kill $kill
     * @param <type> $side
     * @return <type>
     */
function handle_destroyed($kill, $side, &$destroyed, &$pilots, $sideAssignmentMap = array(), $completeInformation = FALSE)
    {
         
    
    // -------------------------------------------------------------------------
    // FIX BATTLE REPORT a little by Evoke. Salvoxia
    // BEGIN
    // -------------------------------------------------------------------------
    // we don't want losses of our own corp/ally as losses on the enemy's side
    if($side == 'e')
    {
        if(config::get('cfg_corpid'))
        {
            $corpId = config::get('cfg_corpid');
            $corpId = $corpId[0];
            if($kill->getVictimCorpID() == $corpId)
            {
                return;
            }

        }
        elseif(config::get('cfg_allianceid'))
        {
            $allianceId = config::get('cfg_allianceid');
            $allianceId = $allianceId[0];
            if($kill->getVictimAllianceID() == $allianceId)
            {
                return;
            }
        }
    }
    
    // -------------------------------------------------------------------------
    // FIX BATTLE REPORT a little by Evoke. Salvoxia
    // END
    // -------------------------------------------------------------------------
    if($completeInformation && !is_null($destroyed) && is_array($destroyed))
    {
        $destroyed[$kill->getID()]=$kill->getVictimID();
    }
    
    if(config::get('fleet_battles_mod_sideassign'))
    {
    // determine whether the pilot is member of an alliance
        if($kill->getVictimAllianceName() == "None")
        {
            $entityType = "corp";
            $entityId = $kill->getVictimCorpID();
        }
        
        else 
        {
            $entityType = "alliance";
            $entityId = $kill->getVictimAllianceID();
        }
        
        if(isset($sideAssignmentMap[$entityType][$entityId]))
        {
            $pilotSide = $sideAssignmentMap[$entityType][$entityId];
        }

        else
        {
            $pilotSide = $side;
        }
    }
    
    else
    {
        $pilotSide = $side;
    }

    $ship                     = Ship::lookup($kill->getVictimShipName());
    $shipc=$ship->getClass();

    $ts   =strtotime($kill->getTimeStamp());
    
    // mark the pilot as podded
    if ($shipc->getID() == 18 || $shipc->getID() == 2)
    {
        // increase the timestamp of a podkill by 1 so its after the shipkill
        $ts++;
    }

    // search for ships with the same id
    if (isset($pilots[$pilotSide][$kill->getVictimId()]) && is_array($pilots[$pilotSide][$kill->getVictimId()]))
        {
        foreach ($pilots[$pilotSide][$kill->getVictimId()] as $id => $_ship)
            {
            if ($ship->getID() == $_ship['sid'])
                {
                $pilots[$pilotSide][$kill->getVictimId()][$id]['destroyed']=true;

                if (!isset($pilots[$pilotSide][$kill->getVictimId()][$id]['kll_id']))
                    {
                    $pilots[$pilotSide][$kill->getVictimId()][$id]['kll_id']=$kill->getID();
                    $pilots[$pilotSide][$kill->getVictimId()][$id]['kll_url']=edkURI::page('kill_detail', $kill->getID(), "kll_id");
                    }

                //$pilots[$side][$kill->getVictimId()][0]["times"] +=1;
                return;
                }
            }
        }
    
    if($completeInformation)
    {
        
        $pilots[$pilotSide][$kill->getVictimId()][]=array
        (
        'name'      => $kill->getVictimName(),
        'plt_url'   => edkURI::page("pilot_detail", $kill->getVictimID(), "plt_id"),
        'kll_id'    => $kill->getID(),
        'kll_url'   => edkURI::page('kill_detail', $kill->getID(), "kll_id"), 
        'spic'      => imageURL::getURL('Ship', $ship->getID(), 32),
        'scl'       => $shipc->getPoints(),
        'destroyed' => true,
        'corp'      => $kill->getVictimCorpName(),
        'alliance'  => $kill->getVictimAllianceName(),
        'aid'       => $kill->getVictimAllianceID(),
        'alliance_url' => edkURI::page("alliance_detail", $kill->getVictimAllianceID(), "all_id"),
        'ship'      => $kill->getVictimShipname(),
        'shipClass' => $shipc->getName(),
        'shipClassObject' => $shipc,
        'sid'       => $ship->getID(),
        'cid'       => $kill->getVictimCorpID(),
        'crp_url'   => edkURI::page("corp_detail", $kill->getVictimCorpID(), "crp_id"),
        'ts'        => $ts,
        'times'     => 0,
        'color'     => getColorClassByClass($shipc)
        );
        
    }
    
    else 
    {
        $pilots[$pilotSide][$kill->getVictimId()] = 1;
    }
    
    }
    
/**
 * checks whether a pilots entity (which is not the owner) on the "allied" side is also on the enemy's side (not as "destroyed) e.g. involved in a kill on allied side
 * if so, move the pilot to enemy side
 * @param type $pilots 
 */    
function cleanPilots(&$pilots)
{
    // loop through all allied pilots
    foreach($pilots["a"] AS $pilotId => $pilot)
    {
        // check for owner
        if(in_array($pilot[1]["aid"], config::get('cfg_allianceid')) || in_array($pilot[1]["cid"], config::get('cfg_corpid')))
        {
            continue;
        }
        
        $pilotCount = $pilot[0]["times"];
        // pilot is not member of the owner entity
        // pilot is member of an alliance
        if(strcasecmp($pilot[1]["alliance"], "None"))
        {
            
            // count how often the pilot got killed
            
            
            
            // look for same alliance on the enemy side
            foreach($pilots["e"] AS $enemy)
            {
                // compare alliances
                if(!strcasecmp($enemy["alliance"], $pilot[1]["alliance"]))
                {
                    // move the pilot to the enemy side
                    $pilots["e"][] = $pilot;
                    unset($pilots["a"][$pilotId]);
                }
            }
            
        }
        
        
    }
    
    
}
    
/**
 * gets the class for decorating ship classes in balance of power
 * @param ShipClass $shipClass
 * @return string 
 */
function getColorClassByClass($shipClass)
{
    
    $shipClassId = $shipClass->getID();
    
    switch($shipClassId)
    {
        // Battleship
        case 1:
            return "bopBattleship";
        // Capsule
        case 2:
            return "bopCapsule";
        // Noobship
        case 3:
            return "bopNoobship";
        // Frigate
        case 4:
            return "bopFrigate";
        // Interceptor
        case 5:
            return "bopInterceptor";
        // Assault frigate
        case 6:
            return "bopAssaultFrigate";
        // Industrial
        case 7:
            return "bopIndustrial";
        // Cruiser
        case 8:
            return "bopCruiser";
        // Heavy assault
        case 9:
            return "bopHeavyAssault";
         // Battlecruiser
        case 10:
            return "bopBattlecruiser";
         // Shuttle
        case 11:
            return "bopShuttle";
        // Mining barge
        case 12:
            return "bopMiningBarge";
        // Logistics
        case 13:
            return "bopLogistics";
        // Transport
        case 14:
            return "bopTransport";
        // Destroyer
        case 15:
            return "bopDestroyer";
        // Covert ops
        case 16:
            return "bopCovertOps";
        // Drone
        case 17:
            return "bopDrone";
        // Unknown
        case 18:
            return "bopUnknown";
        // Dreadnought
        case 19:
            return "bopDreadnought";
        // Freighter
        case 20:
            return "bopFreighter";
        // Command ship
        case 21:
            return "bopCommandShip";
         // Exhumer
        case 22:
            return "bopExhumer";
         // Interdictor
        case 23:
            return "bopInterdictor";
        // Recon ship
        case 24:
            return "bopReconShip";
        // Titan
        case 26:
            return "bopTitan";
        // Carrier
        case 27:
            return "bopCarrier";
        // Supercarrier
        case 28:
            return "bopSupercarrier";
        // Supercarrier
        case 29:
            return "bopCapitalIndustrial";
        // Electronic Attack Ship
        case 30:
            return "bopElectronicAttackShip";
        // Heavy Interdictor
        case 31:
            return "bopHeavyInterdictor";
        // Black Ops
        case 32:
            return "bopBlackOps";
        // Marauder
        case 33:
            return "bopMarauder";
         // Jump Freighter
        case 34:
            return "bopJumpFreighter";
         // POS Small
        case 35:
            return "bopPosSmall";
        // POS Medium
        case 36:
            return "bopPosMedium";
        // POS Large
        case 37:
            return "bopPosLarge";
        // POS Modules
        case 38:
            return "bopPosModules";
        // Indu Command
        case 39:
            return "bopIndustrialCommand";
        // Strategic Cruiser
        case 40:
            return "bopStrategicCruiser";
        // Infrastructure Modules
        case 41:
            return "bopInfrastructureModules";
        // Territory Modules
        case 42:
            return "bopTerritoryModules";
        // Prototype Exploration Ship
        case 43:
            return "bopPrototypeExplorationShip";
        // Customs Offices
        case 44:
            return "bopCustomsOffices";
        // Unknown
        default:
            return "bopUnknown";
            
    }
   
}

/**
 * gets the sorting weight for a ship class
 * @param ShipClass $shipClass
 * @return int
 */
function getShipClassSortWeight($shipClass)
{
    $shipClassId = $shipClass->getID();
    
    switch($shipClassId)
    {
        // Battleship
        case 1:
            return 60;
        // Capsule
        case 2:
            return 0;
        // Noobship
        case 3:
            return 5;
        // Frigate
        case 4:
            return 10;
        // Interceptor
        case 5:
            return 15;
        // Assault frigate
        case 6:
            return 12;
        // Industrial
        case 7:
            return 7;
        // Cruiser
        case 8:
            return 20;
        // Heavy assault
        case 9:
            return 25;
         // Battlecruiser
        case 10:
            return 35;
         // Shuttle
        case 11:
            return 1;
        // Mining barge
        case 12:
            return 7;
        // Logistics
        case 13:
            return 23;
        // Transport
        case 14:
            return 9;
        // Destroyer
        case 15:
            return 17;
        // Covert ops
        case 16:
            return 16;
        // Drone
        case 17:
            return 6;
        // Unknown
        case 18:
            return -1;
        // Dreadnought
        case 19:
            return 500;
        // Freighter
        case 20:
            return 125;
        // Command ship
        case 21:
            return 40;
         // Exhumer
        case 22:
            return 8;
         // Interdictor
        case 23:
            return 18;
        // Recon ship
        case 24:
            return 21;
        // Titan
        case 26:
            return 1000;
        // Carrier
        case 27:
            return 250;
        // Supercarrier
        case 28:
            return 750;
        // Capital Industrial
        case 29:
            return 450;
        // Electronic Attack Ship
        case 30:
            return 13;
        // Heavy Interdictor
        case 31:
            return 30;
        // Black Ops
        case 32:
            return 150;
        // Marauder
        case 33:
            return 125;
         // Jump Freighter
        case 34:
            return 175;
         // POS Small
        case 35:
            return -20;
        // POS Medium
        case 36:
            return -15;
        // POS Large
        case 37:
            return -10;
        // POS Modules
        case 38:
            return -25;
        // Indu Command
        case 39:
            return 155;
        // Strategic Cruiser
        case 40:
            return 33;
        // Infrastructure Modules
        case 41:
            return -5;
        // Territory Modules
        case 42:
            return -4;
        // Prototype Exploration Ship
        case 43:
            return 2;
        // Customs Offices
        case 44:
            return -12;
        // Unknown
        default:
            return -50;
            
    }
}

    
    
    
/**
* get the side assignment for a given system and time interval
* @param long $systemId
* @param String $timestampStart
* @param String $timestampEnd
* @return array 
*/
function getSideAssignments($systemId, $timestampStart, $timestampEnd)
{
    $qry = DBFactory::getDBQuery();
    $sql = "SELECT
                    entity_id, entity_type, side
                FROM kb3_side_assignment
                WHERE system_id = {$systemId}
                    AND 
                        ((
                            timestamp_start <= '{$timestampStart}'
                                AND timestamp_end >= '{$timestampEnd}'
                        )
                        OR
                        (
                                timestamp_start <= '{$timestampStart}'
                                    AND timestamp_end >= '{$timestampStart}'
                        )
                        OR
                        (
                                timestamp_start <= '{$timestampEnd}'
                                    AND timestamp_end >= '{$timestampEnd}'
                        ))
    ";

    $qry->execute($sql);
    $assignment = array();

    while($row = $qry->getRow())
    {
        $assignment[] = $row;
    }

    return $assignment;
}
?>
