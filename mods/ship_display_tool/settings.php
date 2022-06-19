<?php
//require_once('common/includes/class.http.php');
require_once('common/includes/class.httprequest.php');
require_once('common/admin/admin_menu.php');

$version = "2.8";

/*$html .= "
<script src='http://code.jquery.com/jquery.min.js' type='text/javascript'></script>
<script type='text/javascript'>
$(document).ready(function(){
	$.getJSON('http://www.elementstudio.co.uk/downloads/v.json', function(data) {

	alert('here');

	})
.success(function() { alert('second success'); })
.error(function() { alert('error'); })
.complete(function() { alert('complete'); });
});
</script>";*/

if ($_POST) {
  $tool_back = $_POST["sel_back"];
  $tool_pods = ($_POST["fit_pods"]) ? 1 : 0;


  config::set('ship_display_back', $tool_back);
  config::set('ship_display_pods', $tool_pods);
//  Header("Location: ?a=settings_ship_display_tool");
}


$page = new Page('Ship Display tool - Settings');

$html .= "Ship Display Tool Admin page.<br />Created by Spark's.<br />Enjoy.";


$backgroundimg = config::get('ship_display_back');
if($backgroundimg == "") {
	$backgroundimg = "#222222";
}
$html .= "<br />
<form name=options id=options method=post action=><br /><br />
	<div style='float:left; width:100%;'>Select your mod background colour in hash, Example: #ffffff: <input type='text' name='sel_back' value='".$backgroundimg."' /></div>";
$html .= "<div style='float:left; width:100%;'>Fit Implants to Pods:<input type=checkbox name='fit_pods' id='fit_pods'";
if (config::get('ship_display_pods'))
{
    $html .= " checked=\"checked\"";
}

$html .= "	/><br /><br /></div>
<div style='float:left; width:100%;'><input type=\"submit\" value=\"Save\" /></div>
</form>
";




$html .= "<br /><br />Remember to report bugs to this post: <a href='http://eve-id.net/forum/viewtopic.php?f=505&t=17295'>http://eve-id.net/forum/viewtopic.php?f=505&t=17295</a>.<br /><br />Thanks";


$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
