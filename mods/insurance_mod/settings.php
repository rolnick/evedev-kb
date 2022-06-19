<?php
require_once('common/includes/class.httprequest.php');
require_once('common/admin/admin_menu.php');

$version = "0.11";

$page = new Page('Insurance Mod - DB setup');

$html .= "<div class='block-header2'>Insurance mod Admin page.</div><br />Created by Snitch Ashor.<br />Enjoy.<br />";

if ($_POST['createdb']) {
  $dbq = DBFactory::getDBQuery();
  $create_sql = "CREATE TABLE IF NOT EXISTS `kb3_insurances` ( `kll_id` INT NOT NULL PRIMARY KEY, `kll_insurance` BIGINT NOT NULL DEFAULT '0' )";
  $dbq->execute($create_sql);
  $html .= "Created Insurance Tables.<br /><br />";
}

if ($_POST['dropdb'])
{
        $dbq = DBFactory::getDBQuery();
        $drop_sql1 = "DROP TABLE IF EXISTS `kb3_insurances`";
        $dbq->execute($drop_sql1);
	$html .= "Insurance tables dropped.";
}

$html .= "<br /><div class='block-header2'>Database Operations</div>
<form name=options id=options method=post action=>
<div style='float:left; width:100%;'><input type=submit name=createdb value=\"Create DB\" /> Required for this mod to work.</div>
<div style='float:left; width:100%;'><input type=submit name=dropdb value=\"Drop Tables again\" /> Remove the Tables required. This will remove Insurance payouts for all current kills.</div>
</form>
";

$html .= "<br /><br />Please send Issues to prometh on the EDk forum or Snitch Ashor ingame.
<br /><br />Thanks.";

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
