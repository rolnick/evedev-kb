<?php
$kll_id = (int) edkURI::getArg('kll_id', 1);

$qry = new DBQuery(true);
$qry->execute("SELECT kll_insurance FROM kb3_insurances WHERE kll_id=".$kll_id);
$row = $qry->getRow();

$insurance = intval($row['kll_insurance']);

if ($insurance > 0)
    {
    $smarty->assign("insurance", number_format($insurance, 2));
    $smarty->assign("showInsurance", (bool)true);
    }
    else
    {
    $smarty->assign("showInsurance", (bool)false);
    }
?>
