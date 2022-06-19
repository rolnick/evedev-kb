<?php

require_once( "common/admin/admin_menu.php" );

$config = new Config(KB_SITE);

function ab_setConfiguration($post, $key) {
	config::set($key, $post[$key]);
}

if ($_POST) {
	ab_setConfiguration($_POST, 'augmented_banner_numDays');
	ab_setConfiguration($_POST, 'augmented_banner_maxDisplayed');
	ab_setConfiguration($_POST, 'augmented_banner_displayCorps');
	ab_setConfiguration($_POST, 'augmented_banner_displayPilots');
	ab_setConfiguration($_POST, 'augmented_banner_displayType');
}

function setDefaultConfig_ab2($key, $value) {
  if (config::get($key) == null || config::get($key) == "" ) config::set($key, $value);
}

setDefaultConfig_ab2('augmented_banner_numDays', 7);
setDefaultConfig_ab2('augmented_banner_maxDisplayed', 27);
setDefaultConfig_ab2('augmented_banner_displayCorps', 'true');
setDefaultConfig_ab2('augmented_banner_displayPilots', 'true');
setDefaultConfig_ab2('augmented_banner_displayType', 'mixed');


$numDays = config::get('augmented_banner_numDays');
$maxDisplayed = config::get('augmented_banner_maxDisplayed');
$displayCorps = config::get('augmented_banner_displayCorps');
$displayPilots = config::get('augmented_banner_displayPilots');
$displayType = config::get('augmented_banner_displayType');
$alliID = (int) config::get('cfg_allianceid');

$html = "
<form method='post'>
<table>
	<tr><td>Number of Days to Count:</td><td><input size='3' name='augmented_banner_numDays' type='text' value='$numDays' /> </td></tr>
	<tr><td>Maximum Images to Display:</td><td><input size='3' name='augmented_banner_maxDisplayed' type='text' value='$maxDisplayed' /> </td></tr>
";

if ($alliID != 0 ) {
$selected1 = $displayCorps == 'true' ? "selected='yes'" : "";
$selected2 = $displayCorps == 'false' ? "selected='yes'" : ""; 
$html .= "
	<tr>
		<td>Display Corps?</td>
		<td>
			<select name='augmented_banner_displayCorps'>
				<option $selected1 value='true' >Yes</option>
				<option $selected2 value='false'>No</option>
			</select>
		</td>
	</tr>";
}

$selected1 = $displayPilots == 'true' ? "selected='yes'" : "";
$selected2 = $displayPilots == 'false' ? "selected='yes'" : ""; 
$html .= "
	<tr>
		<td>Display Pilots:</td>
		<td>
			<select name='augmented_banner_displayPilots'>
				<option value='true' $selected1 >Yes</option>
				<option value='false' $selected2 >No</option>
			</select>
		</td>
	</tr>
";

$selected1 = $displayType == 'mixed' ? "selected='yes'" : "";
$selected2 = $displayType == 'straight' ? "selected='yes'" : ""; 
$html .= "
	<tr>
		<td>Display Type:</td>
		<td>
			<select name='augmented_banner_displayType'>
				<option $selected1 value='mixed'>Mixed Corps/Pilots</option>
				<option $selected2 value='straight'>Corps then Pilots</option>
		</select>
		</td>
	</tr>
";

$html .= "
	<tr>
		<td></td>
		<td><input type='Submit' value='Save' /></td>
	</tr>
";

$html .= "</table></form>";

$html .= "

<br/><br/><hr><br/><br/>

If you have enabled this mod and don't see corps or pilots beneath your banner, then<br/>
you have probably not added this line to your active template's index.tpl file:<br/>
<br/>" .
'{$augmented_banner}<br/>' . "
<br/>
Look for the first table with the class navigation and add that line prior to the table line.<br/>
<br/>
<hr/>
<br/>
<i>-- Squizz Caphinator</i><br/>
<a href='http://eve-id.net/forum/viewtopic.php?f=505&t=17311'>EVE ID Forum Posting</a>
";

$page = new Page( "Augmented Banner" );
$page->setAdmin();
$page->setContent($html);
$page->addContext( $menubox->generate() );
$page->generate();

?>
