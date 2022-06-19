<?php

require_once('common/includes/class.toplist.php');

$config = new Config(KB_SITE);

function setDefaultConfig($key, $value) {
		if (config::get($key) == null ) config::set($key, $value);
}

setDefaultConfig('augmented_banner_numDays', 7);
setDefaultConfig('augmented_banner_maxDisplayed', 27);
setDefaultConfig('augmented_banner_displayCorps', 'true');
setDefaultConfig('augmented_banner_displayPilots', 'true');
setDefaultConfig('augmented_banner_displayType', 'mixed');

$numDays = (int) config::get('augmented_banner_numDays');
$maxDisplayed = (int) config::get('augmented_banner_maxDisplayed');
$displayCorps = 'true' == config::get('augmented_banner_displayCorps');
$displayPilots = 'true' == config::get('augmented_banner_displayPilots');
$displayType = config::get('augmented_banner_displayType');

$alliID = (int) implode(",", config::get('cfg_allianceid'));
$corpID = (int) implode(",", config::get('cfg_corpid'));

$corpPilotArray = array();
$mixedArray = array();


if ($alliID != 0 && $displayCorps ) {
		$corpList = new TopCorpKillsList();
		$corpList->addInvolvedAlliance($alliID);
		$corpList->setLimit($maxDisplayed);
		$corpList->setPodsNoobShips(config::get('podnoobs'));
		$corpList->setStartDate(date('Y-m-d H:i',strtotime("- $numDays days")));
		$corpList->generate();

		while ($row = $corpList->getRow() ) {
				if ($numListed >= $maxDisplayed) continue;
				$id=$row['crp_id'];
				$corp = new Corporation($row['crp_id']);
				$url=$corp->getPortraitURL(32);
				$name=$corp->getName();
				$count=$row['cnt'];
				$html = "
						<td><a href='?a=corp_detail&crp_id=$id'>
						<img style='border: none;' src='$url' title=\"$name - $count kills over the last $numDays days\" width='32' height='32'/>
						</a></td>\n";
				$mixedArray[$html] = $count;
				$corpPilotArray[] = $html;
		}
}

if ($displayPilots) {
		$list = new TopKillsList();
		$list->setLimit($maxDisplayed);
		if ($alliID != 0 ) $list->addInvolvedAlliance($alliID);
		else $list->addInvolvedCorp($corpID);
		$list->setPodsNoobShips(config::get('podnoobs'));
		$list->setStartDate(date('Y-m-d H:i',strtotime("- $numDays days")));
		$list->generate();

		while ($row = $list->getRow() ) {
				if ($numListed >= $maxDisplayed) continue;
				$id=$row['plt_id'];
				$pilot = new Pilot($row['plt_id']);
				$url=$pilot->getPortraitURL(32);
				$name=$pilot->getName();
				$count=$row['cnt'];
				$html = "
						<td><a href='?a=pilot_detail&plt_id=$id'>
						<img style='border: none; padding-right: 1px;' src='$url' title=\"$name - $count kills over the last $numDays days\" width='32' height='32'/>
						</a></td>\n";
				$mixedArray[$html] = $count;
				$corpPilotArray[] = $html;
		}
}

function banner_sort($a, $b) {
		if ($a == $b) {
				return 0;
		}
		return ($a > $b) ? -1 : 1;
		return ($a < $b) ? -1 : 1;
}

$numListed = 0;
$finalHtml = "<table class='kb-table'><tr>\n";
if ($displayType == 'mixed' ) { 
		uasort($mixedArray, "banner_sort");
		foreach ($mixedArray as $html=>$count) {
				if ($numListed < $maxDisplayed) {
						$finalHtml .= $html;
						$numListed++;
				}
		}
} else {
		foreach ($corpPilotArray as $html) {
				if ($numListed < $maxDisplayed) {
						$finalHtml .= $html;
						$numListed++;
				}
		}
}
$finalHtml .= "</tr></table>\n";

$html = $finalHtml;
?>
