<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

class TopTable_ExpensiveKill
{
	function __construct($toplist, $entity)
	{
		$this->toplist = $toplist;
		$this->entity_ = $entity;
	}

	function generate()
	{
		global $smarty;
		$this->toplist->generate();

		$i = 1;
		$rows = array();
		while ($row = $this->toplist->getRow())
		{
			$pilot = Pilot::getByID($row['plt_id']);
                        $uri = edkURI::build(array('a', 'kill_detail', true), array('kll_id', $row['kll_id'], true));
			if($row['plt_externalid']) {
				$img = imageURL::getURL('Pilot', $row['plt_externalid'], 32);
			} else {
				$img = $pilot->getPortraitURL(32);
			}
                        $ship = Ship::getByID($row['ship']);
                        $shipUri = edkURI::build(array('a', 'invtype', true), array('id', $row['ship'], true));

                        if((int) number_format($row["isk"], 0, "","")>1000000000)
				{
					$isk = number_format($row["isk"]/1000000000, 2, ".","") . " b";
				} elseif((int) number_format($row["isk"], 0, "","")>1000000)
				{
					$isk = number_format($row["isk"]/1000000, 2, ".","") . " M";
				} else
				{
					$isk = number_format($row["isk"], 0, ".",",");
				}

			$rows[] = array(
				'rank' => $i,
				'name' => $pilot->getName(),
				'uri' => $uri,
				'portrait' => $img,
                                'shipImage' => $ship->getImage(32),
                                'shipName' => $ship->getName(),
                                'shipId'    => $row['ship'],
                                'shipURI'   => $shipUri,
				'isk' => $isk);
			$i++;
		}

		$smarty->assign('tl_name', 'Pilot');
		$smarty->assign('tl_type', $this->entity_);
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(getcwd() . '/mods/most_expensive_toplist/templates/toplisttable_expensive.tpl');
	}
}
