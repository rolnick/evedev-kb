<?php
$modInfo['insurance_mod']['name'] = "Insurance payout Mod";
$modInfo['insurance_mod']['abstract'] = "Show how much was covered by a platinum insurance";
$modInfo['insurance_mod']['about'] = "by Snitch Ashor";

event::register("killDetail_assembling", "insurance::add");
event::register("killmail_added", "insurance::fetch");
event::register("killmail_delete", "insurance::delinsurance");

class insurance {
	static function  add($page)
	{
		$page->addBehind("itemsLost", "insurance::show");
	}
  
  static function show(){
      global $smarty;
 	  include_once('mods/insurance_mod/insurance.php');
      $html .= $smarty->fetch("../../../mods/insurance_mod/insurance.tpl");
      return $html;
  }
public static function fetch($km){
      $kllid = $km->getId();
      $shipid = $km->getVictimShipExternalID();
      $classid = $km->getVictimShip()->getClass()->getID();
      include_once('mods/insurance_mod/class.insurancefetcher.php');
      $fetch = New InsuranceFetcherCrest;
      $fetch->fetchInsurance($kllid, $shipid, $classid);
  }
  function delinsurance($km){
      $kllid = $km->getId();
      $qry = DBFactory::getDBQuery();
      $querytext = "DELETE FROM kb3_insurances WHERE kll_id=".$kllid.";";
      $qry->execute($querytext);
  }
}
?>
