<?php
$kll_id = (int) edkURI::getArg('kll_id', 1);

$kill = new Kill($kll_id);
$verification = false;
if($kill->getExternalID() != 0)
{
  $verification = true;
  $smarty->assign('api_verified_id', $kill->getExternalID());
}
$time = time();
$offset = 172800;
$timestamp = $kill->getTimeStamp();
if (strtotime($timestamp) < ($time-$offset))
{
	$verification = true;
}
$smarty->assign('kb_host', KB_HOST);
$smarty->assign('api_verified_status', $verification);
?>
