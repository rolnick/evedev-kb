<?php
require_once("common/admin/admin_menu.php");
$page = new Page('Api Verified Mod - Settings');

$version = "1.21"; //Version Update for me, do not change!

$versiondb = config::get('api_verified_mod_ver');
if($version != $versiondb) 
{ 
  config::set('api_verified_mod_ver', $version); 
  $html .= "<br /><b>This Mod got updated, have fun with it! New version set!</b><br /><br />";
}

switch($_GET["step"]){
 default:
 
$versionanzeige = config::get('api_verified_mod_ver');

$html .= "This mod was modified from Sir Quentin's mod.<br />Greetings, Khi3l";

$html .= "<br /><br /><hr size=\"1\" /><div align=\"right\"><i><small>Api Verified Mod (Version $versionanzeige) by <a href=\"http://babylonknights.com\" target=\"_blank\">Khi3l</a></small></i></div>";

break;
}
$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>
