<?php
$page = new Page('Most Expensive Mod - Settings');

$version = "1.3"; //Version Update for me, do not change!

$versiondb = config::get('most_exp_mod_ver');
if($version != $versiondb) 
{ 
  config::set('most_exp_mod_ver', $version); 
  $html .= "<br /><b>This Mod got updated, have fun with it! New version set!</b><br /><br />";
}

switch($_GET["step"]){
 default:

$zeitanzeige = config::get('most_exp_mod_time');
$anzahlanzeige = config::get('most_exp_mod_count');
$versionanzeige = config::get('most_exp_mod_ver');
$whatanzeige = config::get('most_exp_mod_what');
 
$html .= "
   <form name=\"add\" action=\"?a=settings_most_expensive_mod&amp;step=add\" method=\"post\">
   <table width=\"75%\">
    <tr><td>How many days to count with? &raquo;</td><td><input type=\"text\" name=\"add_zeit\" value=\"".$zeitanzeige."\" /></td><td><small>recommended: <font color=\"red\">7</font></small></td></tr>
    <tr><td>How many kills to show? &raquo;</td><td><input type=\"text\" name=\"add_anzahl\" value=\"".$anzahlanzeige."\" /></td><td><small>recommended: <font color=\"red\">5</font></small></td></tr>
    <tr><td>What to show? &raquo;</td><td><input type=\"radio\" name=\"add_what\" value=\"kills\" ";
    if($whatanzeige == 'kills') { $html .= "checked"; }

$html .="/>Kills only</td><td>&nbsp;</td></tr>
    <tr><td>&nbsp;</td><td><input type=\"radio\" name=\"add_what\" value=\"losses\" ";
    if($whatanzeige == 'losses') { $html .= "checked"; }
    
$html .="/>Losses only</td><td>&nbsp;</td></tr>
    <tr><td>&nbsp;</td><td><input type=\"radio\" name=\"add_what\" value=\"both\" ";
    if($whatanzeige == 'both') { $html .= "checked"; }
    
$html .="/>Kills and losses</td><td>&nbsp;</td></tr>
    <tr><td></td><td><br /><input type=\"submit\" value=\"save\" /></td><td>&nbsp;</td></tr>
   </table>
   </form>
";

$html .= "<br /><br /><hr size=\"1\" /><div align=\"right\"><i><small>Most Expensive Mod (Version $versionanzeige) by <a href=\"http://www.back-to-yarrr.de\" target=\"_blank\">Sir Quentin</a></small></i></div>";

break;

case "add": 
if ($_POST) {
  $exp_time = trim($_POST["add_zeit"]);
  $exp_count = trim($_POST["add_anzahl"]);
  $exp_what = $_POST["add_what"];
  
  config::set('most_exp_mod_time', $exp_time);
  config::set('most_exp_mod_count', $exp_count);
  config::set('most_exp_mod_what', $exp_what);
  
  $html .= "Settings updated!";
}
break;
}

$page->setContent($html);
$page->generate();
?>
