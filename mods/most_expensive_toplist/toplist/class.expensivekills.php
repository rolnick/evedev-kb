<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_ExpensiveKills extends TopList_Base
{
	function generate()
	{
		$sql = "select DISTINCT kll.kll_id, kll_isk_loss as isk, kll_ship_id as ship, kll_victim_id as plt_id
                from kb3_kills kll
	      INNER JOIN kb3_inv_detail ind on ( ind.ind_kll_id = kll.kll_id )";

		$bottomSQL = "order by 2 desc limit ".$this->limit;
                
                // do we need to exclude ship types?
                $excludeShipClasses = array();
                // exclude supercaps?
                if(config::get("mostexptoplist_supercaps") === "0")
                {
                    // Supercarrier
                    $this->excludeVictimShipClass(28);

                    // Titan
                    $this->excludeVictimShipClass(26);
                }

                // exclude regular caps?
                if(config::get("mostexptoplist_caps") === "0")
                {
                    // Dreadnought
                    $this->excludeVictimShipClass(19);

                    // Carrier
                    $this->excludeVictimShipClass(27);

                    // Capital Industrial
                    $this->excludeVictimShipClass(29);
                }

                // exclude NPC kills?
                if(config::get("mostexptoplist_npclosses") === "0")
                {
                    $noNpcWhere = " AND ind_shp_id != 0 ";
                    $bottomSQL = $noNpcWhere.$bottomSQL;
                }

                $this->setSQLTop($sql);

                // we dont want structures
                $this->setNoStructures();
		$this->setSQLBottom($bottomSQL);

                // pods, shuttles, noob ships are intersting, too!
                $this->setPodsNoobShips(TRUE);

	}
}