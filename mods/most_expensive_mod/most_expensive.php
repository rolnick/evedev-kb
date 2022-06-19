<?php
$zeitanzeige = config::get('most_exp_mod_time');
$anzahlanzeige = config::get('most_exp_mod_count');
$versionanzeige = config::get('most_exp_mod_ver');
$whatanzeige = config::get('most_exp_mod_what');
$allianzid = config::get('cfg_allianceid');
$corpid = config::get('cfg_corpid');

if($allianzid == '0') { $use = 'corp'; }
if($allianzid != '0') { $use = 'alli'; }


$until = time() - (86400 * $zeitanzeige);
$until = date("Y-m-d H:i:s",$until);


$qry = new DBQuery();
if($whatanzeige == 'both')
{
  $query = "SELECT * FROM kb3_kills WHERE kll_timestamp > '$until' ORDER BY kll_isk_loss DESC LIMIT $anzahlanzeige";
}

if($whatanzeige == 'kills')
{
  if($use == 'corp')
  {
    $query = "SELECT * FROM kb3_kills,kb3_inv_crp WHERE kb3_kills.kll_timestamp > '$until' AND kb3_kills.kll_id = kb3_inv_crp.inc_kll_id AND kb3_inv_crp.inc_crp_id = '$corpid' ORDER BY kll_isk_loss DESC LIMIT $anzahlanzeige";
  }
  else
  {
    $query = "SELECT * FROM kb3_kills,kb3_inv_all WHERE kb3_kills.kll_timestamp > '$until' AND kb3_kills.kll_id = kb3_inv_all.ina_kll_id AND kb3_inv_all.ina_all_id = '$allianzid' ORDER BY kll_isk_loss DESC LIMIT $anzahlanzeige";
  }
}

if($whatanzeige == 'losses')
{
  if($use == 'corp')
  {
    $query = "SELECT * FROM kb3_kills WHERE kll_timestamp > '$until' AND kll_crp_id = '$corpid' ORDER BY kll_isk_loss DESC LIMIT $anzahlanzeige";
  }
  else
  {
    $query = "SELECT * FROM kb3_kills WHERE kll_timestamp > '$until' AND kll_all_id = '$allianzid' ORDER BY kll_isk_loss DESC LIMIT $anzahlanzeige";
  }
}

$qry->execute($query);


function check_tv_pilot($id)
{
    $qry = new DBQuery();
    $qry->execute("SELECT plt_name FROM kb3_pilots WHERE plt_id = '$id'");
    $row = $qry->getRow();
    return $row[plt_name];
}

function check_tv_ship($id,$what)
{
    $qry = new DBQuery();
    $qry->execute("SELECT shp_name,shp_externalid FROM kb3_ships WHERE shp_id = '$id'");
    $row = $qry->getRow();
    if($what == 'id')
    {
        return $row[shp_externalid];
    }
    else
    {
        return $row[shp_name];
    }
}

$html .= "<br />
<table width=\"100%\">
<tr><td colspan=\"".$anzahlanzeige."\"><img src=\"img/items/24_24/icon25_13.png\" alt=\"Most Expensive Mod by Sir Quentin\" /><b>Most expensive";

if($whatanzeige == 'kills') { $html .= " kills "; }
if($whatanzeige == 'losses') { $html .= " losses "; }
if($whatanzeige == 'both') { $html .= " kills and losses "; }

$html .= "for the last ".$zeitanzeige." Days</b><hr size=\"1\" /></td></tr>
<tr>";

while ($kills = $qry->getRow()) 
{
  $html .= "
  <td align=\"center\">
  <a class=\"kb-shipclass\" href=\"?a=pilot_detail&amp;plt_id=".$kills[kll_victim_id]."\">".check_tv_pilot($kills[kll_victim_id])."</a><br /><br />
  <a class=\"kb-shipclass\" href=\"?a=kill_detail&amp;kll_id=".$kills[kll_id]."\"><img src=\"img/ships/64_64/".check_tv_ship($kills[kll_ship_id],id).".png\" alt=\"".check_tv_ship($kills[kll_ship_id],name)."\" border=\"0\" /></a><br /><br />
  <a class=\"kb-shipclass\" href=\"?a=invtype&amp;id=".check_tv_ship($kills[kll_ship_id],id)."\">".check_tv_ship($kills[kll_ship_id],name)."</a><br /><b>".number_format($kills[kll_isk_loss],0,'.','.')."</b> ISK
  </td>";
}
$html .= "
</tr>
<tr><td colspan=\"".$anzahlanzeige."\"><hr size=\"1\" /><div align=\"right\"><i><small>Most Expensive Mod (Version $versionanzeige) by <a href=\"http://www.back-to-yarrr.de\" target=\"_blank\">Sir Quentin</a></small></i></div></td></tr>
</table>";

?>