<?php
require_once( "common/admin/admin_menu.php" );

function generateRandomString($length = 24) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

if(isset($_POST['submit']))
{
  config::set('statsmod_sso_client_id',$_POST['client_id']);
  config::set('statsmod_sso_secret',$_POST['secret']);
  config::set('statsmod_sso_comments',$_POST['sso_comments']);
  config::set('statsmod_comments_ownersonly',$_POST['comments_ownersonly']);
  config::set('statsmod_pilotstats',$_POST['pilotstats']);
  config::set('statsmod_pilotstats_public',$_POST['pilotstats_public']);
  config::set('statsmod_pilotstats_nopods',$_POST['pilotstats_nopods']);
  config::set('statsmod_pilotstats_versus',$_POST['pilotstats_versus']);
  config::set('statsmod_killcolor', $_POST["killcolor"]);
  config::set('statsmod_losscolor', $_POST["losscolor"]);
  $confirm = "<span style='color:green'>Settings Saved</span><br/>";
}

$killcolor = config::get('statsmod_killcolor');
if($killcolor == "") {
        $killcolor = "00AA00";
}
$losscolor = config::get('statsmod_losscolor');
if($losscolor == "") {
        $losscolor = "F90000";
}


$page = new Page( "Settings - Statsmod" );
$html .= $confirm;

$html .='<form action="" method="post">';
$html .= "<table class=kb-subtable>";
$html .= "<tr><td colspan=\"4\"><div class=block-header2>SSO options</div></td></tr>";
$html .= "<tr><td><b>EVE SSO Client id:</b></td><td><input type='text' size='45' name='client_id' value='".config::get('statsmod_sso_client_id')."'/></td></tr>";
$html .= "<tr><td><b>EVE SSO Client secret:</b></td><td><input type='text' size='45' name='secret' value='".config::get('statsmod_sso_secret')."'/></td></tr>";
$html .= "<tr><td colspan=\"4\">Your EVE SSO callback URL should be set to ".edkURI::page('sso_login')."</td></tr>";
$html .= "<tr><td colspan=\"4\"><br/>Register and application here: <a href=https://developers.eveonline.com/>https://developers.eveonline.com</a> (valid subscription needed)<br/>Select CREST access and the following scopes: publicData, characterKillsRead, corporationKillsRead, characterFittingsWrite </td></tr>";
$html .= "<tr><td colspan=\"4\"><div class=block-header2>Comment options</div></td></tr>";
$html .= "<tr><td><b>Require SSO login for comments: </b></td><td><input type=checkbox name='sso_comments' id='sso_comments'";
if (config::get('statsmod_sso_comments'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";
$html .= "<tr><td><b>Allow only owners comments: </b></td><td><input type=checkbox name='comments_ownersonly' id='comments_ownersonly'";
if (config::get('statsmod_comments_ownersonly'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";
$html .= "<tr><td colspan=\"4\"><div class=block-header2>Stats options</div></td></tr>";
$html .= "<tr><td><b>Show Pilot stats: </b></td><td><input type=checkbox name='pilotstats' id='pilotstats'";
if (config::get('statsmod_pilotstats'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";
$html .= "<tr><td><b>Pilot stats are public: </b></td><td><input type=checkbox name='pilotstats_public' id='pilotstats_public'";
if (config::get('statsmod_pilotstats_public'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";
$html .= "<tr><td><b>Ignore Pods, Shuttles and noob ships: </b></td><td><input type=checkbox name='pilotstats_nopods' id='pilotstats_nopods'";
if (config::get('statsmod_pilotstats_nopods'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";
$html .= "<tr><td><b>Show stats versus other pilots: </b></td><td><input type=checkbox name='pilotstats_versus' id='pilotstats_versus'";
if (config::get('statsmod_pilotstats_versus'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";
$html .= "<tr><td>Color for Kills: </td><td>#<input type='text' name='killcolor' value='".$killcolor."' /></td></tr>";
$html .= "<tr><td>Color for Losses: </td><td>#<input type='text' name='losscolor' value='".$losscolor."' /></td></tr>";
$html .= "<tr><td colspan=\"4\">&nbsp;</td></tr>";
$html .= "<tr></tr><tr><td></td><td colspan=3 ><input type=submit name=submit value=\"Save\"></td></tr>";
$html .= "<tr><td colspan=\"4\">&nbsp;</td></tr>";
$html .= "</table>";
$html .= "</form><br/>";


$page->setContent( $html );
$page->addContext( $menubox->generate() );
$page->generate();
