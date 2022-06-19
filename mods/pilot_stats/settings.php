<?php
require_once('common/admin/admin_menu.php');

$page = new Page("Settings - Corp Pilot Statistics Page");

if ($_POST['submit'])
{
	config::set('corppilotstatspage_eff', $_POST['corppilotstatspage_eff']);
	config::set('corppilotstatspage_datefilter', $_POST['corppilotstatspage_datefilter']);
	config::set('corppilotstatspage_order', $_POST['corppilotstatspage_order']);
    config::set('corppilotstatspage_filterkillcnt', $_POST['corppilotstatspage_filterkillcnt']);
    config::set('corppilotstatspage_location', $_POST['corppilotstatspage_location']);
    config::set('corpilotstatspage_reslimit',$_POST['corpilotstatspage_reslimit']);
    config::set('corpilotstatspage_contracts',$_POST['corpilotstatspage_contracts']);
    config::set('corppilotstatspage_showpos',$_POST['corppilotstatspage_showpos']);
	$html .= "Settings Saved";
}

// get the value to populate the page
$corppilotstatspage_eff = config::get('corppilotstatspage_eff');
$corppilotstatspage_datefilter = config::get('corppilotstatspage_datefilter');
$corppilotstatspage_order = config::get('corppilotstatspage_order');
$corppilotstatspage_filterkillcnt = config::get('corppilotstatspage_filterkillcnt');
$corppilotstatspage_location = config::get('corppilotstatspage_location');
$corpilotstatspage_reslimit = config::get('corpilotstatspage_reslimit');
$corpilotstatspage_contracts = config::get('corpilotstatspage_contracts');
$corppilotstatspage_showpos = config::get('corppilotstatspage_showpos');

// start the output display
$html .= "<form id=options name=options method=post action=>";

$html .= "<div class=block-header2>General Options</div>";
$html .= "<table class=kb-subtable width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td width=300><b>Calculate Efficiency by ISK</b></td><td><input type=radio name=corppilotstatspage_eff value=iskeff";
if ($corppilotstatspage_eff == 'iskeff') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Calculate Efficiency by Kills/Losses</b></td><td><input type=radio name=corppilotstatspage_eff value=killlosseff";
if ($corppilotstatspage_eff == 'killlosseff') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Pilot Kill Limit</b><p>(only shows pilots with minimum number of kills. Set number or 0 to disable)</p></td><td><input type=textarea name=corppilotstatspage_filterkillcnt size=\"2\"";
if ($corppilotstatspage_filterkillcnt) $html .= " value=\"$corppilotstatspage_filterkillcnt\"";
$html .= "></td></tr>";
$html .= "</table><br />";

$html .= "<div class=block-header2>Datefilter Options</div>";
$html .= "<table class=kb-subtable width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td width=300><b>Weekly Corp Statistics</b></td><td><input type=radio name=corppilotstatspage_datefilter value=weekly";
if ($corppilotstatspage_datefilter == 'weekly') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Monthly Corp Statistics</b></td><td><input type=radio name=corppilotstatspage_datefilter value=monthly";
if ($corppilotstatspage_datefilter == 'monthly') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Yearly Corp Statistics</b></td><td><input type=radio name=corppilotstatspage_datefilter value=yearly";
if ($corppilotstatspage_datefilter == 'yearly') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>All-Time Corp Statistics</b></td><td><input type=radio name=corppilotstatspage_datefilter value=alltime";
if ($corppilotstatspage_datefilter == 'alltime') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "</table><br />";

$html .= "<div class=block-header2>Order By Options</div>";
$html .= "<table class=kb-subtable width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td width=300></td><td>ASC</td><td>DESC</td></tr>";
$html .= "<tr><td width=300><b>Order by Name</b></td><td><input type=radio name=corppilotstatspage_order value=nameasc";
if ($corppilotstatspage_order == 'nameasc') $html .= " checked=\"checked\"";
$html .= "></td><td><input type=radio name=corppilotstatspage_order value=namedesc";
if ($corppilotstatspage_order == 'namedesc') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Order by Kills</b></td><td><input type=radio name=corppilotstatspage_order value=killsasc";
if ($corppilotstatspage_order == 'killsasc') $html .= " checked=\"checked\"";
$html .= "></td><td><input type=radio name=corppilotstatspage_order value=killsdesc";
if ($corppilotstatspage_order == 'killsdesc') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Order by Kill ISK value</b></td><td><input type=radio name=corppilotstatspage_order value=killiskasc";
if ($corppilotstatspage_order == 'killiskasc') $html .= " checked=\"checked\"";
$html .= "></td><td><input type=radio name=corppilotstatspage_order value=killiskdesc";
if ($corppilotstatspage_order == 'killiskdesc') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Order by Losses</b></td><td><input type=radio name=corppilotstatspage_order value=lossesasc";
if ($corppilotstatspage_order == 'lossesasc') $html .= " checked=\"checked\"";
$html .= "></td><td><input type=radio name=corppilotstatspage_order value=lossesdesc";
if ($corppilotstatspage_order == 'lossesdesc') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Order by Loss ISK value</b></td><td><input type=radio name=corppilotstatspage_order value=lossiskasc";
if ($corppilotstatspage_order == 'lossiskasc') $html .= " checked=\"checked\"";
$html .= "></td><td><input type=radio name=corppilotstatspage_order value=lossiskdesc";
if ($corppilotstatspage_order == 'lossiskdesc') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Order by Efficiency</b></td><td><input type=radio name=corppilotstatspage_order value=effasc";
if ($corppilotstatspage_order == 'effasc') $html .= " checked=\"checked\"";
$html .= "></td><td><input type=radio name=corppilotstatspage_order value=effdesc";
if ($corppilotstatspage_order == 'effdesc') $html .= " checked=\"checked\"";
$html .= "></td></tr>";

$html .= "</table><br />";

$html .= "<div class=block-header2>Display Options</div>";
$html .= "<table class=kb-subtable width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td width=300><b>Display on a new page:</b></td><td><input type=radio name=corppilotstatspage_location value=newpage";
if ($corppilotstatspage_location == 'newpage') $html .= " checked=\"checked\"";
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Show on Corp detail page:</b></td><td><input type=radio name=corppilotstatspage_location value=corppage";
if ($corppilotstatspage_location == 'corppage') $html .= " checked=\"checked\"";
$html .= "></td></tr>";

$html .= "<tr><td width=300><b>Show Campaign links:</b></td><td><input type=checkbox name=corpilotstatspage_contracts value=showcontracts";
if ($corpilotstatspage_contracts == 'showcontracts') $html .= " checked=\"checked\"";
$html .= "></td></tr>";

$html .= "<tr><td width=300><b>Hide POS Modules:</b></td><td><input type=checkbox name=corppilotstatspage_showpos value=hide";
if ($corppilotstatspage_showpos == 'hide') $html .= " checked=\"checked\"";
$html .= "></td></tr>";

$html .= "<tr><td width=300><b>Pilot limit:</b><p>(only shows this number of pilots. Set number or 0 to disable)</p></td><td><input type=text name=corpilotstatspage_reslimit size=2 ";
$html .= " value=\"$corpilotstatspage_reslimit\"";
$html .= "></td></tr>";

$html .= "</table><br />";


$html .= "<table class=kb-subtable style=\"margin-top:10px;\"><tr><td width=120></td><td colspan=3 ><input type=submit name=submit value=\"Save\"></td></tr></table>";

$html .= "</form>";

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>