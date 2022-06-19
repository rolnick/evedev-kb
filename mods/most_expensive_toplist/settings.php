<?php
	if(!defined('KB_SITE')) die ("Go Away!");

	require_once("common/admin/admin_menu.php");
	$module = "Most Expensive Toplist";
	$page = new Page("$module");
        $version = "1.01";
	$includeSupercaps = config::get('mostexptoplist_supercaps');
        $includeCaps = config::get('mostexptoplist_caps');
        $includeNpcOnlyLosses = config::get("mostexptoplist_npclosses");
	if ($includeSupercaps !== "0" && $includeSupercaps !== "1")
	{ 
			config::set("mostexptoplist_supercaps", "0");
			config::set("mostexptoplist_caps", "0");
                        config::set("mostexptoplist_npclosses", "0");
			$html .= "<div><strong><em>First run</em>. Loaded default values!</strong></div>";
	}
	if ($_SERVER['REQUEST_METHOD'] == "POST")
	{
			$includeSupercaps	= (isset($_POST["includeSuperCaps"])==true) ? "1" : "0";
			$includeCaps	= (isset($_POST["includeCaps"])==true) ? "1" : "0";
                        $includeNpcOnlyLosses	= (isset($_POST["includeNpcOnlyLosses"])==true) ? "1" : "0";
			config::set("mostexptoplist_supercaps", $includeSupercaps);
			config::set("mostexptoplist_caps", $includeCaps);
                        config::set("mostexptoplist_npclosses", $includeNpcOnlyLosses);
			$html .= "<div><strong>Settings Updated.</strong></div>";
	}
	
	$includeSupercaps = config::get('mostexptoplist_supercaps');
        $includeCaps = config::get('mostexptoplist_caps');
        $includeNpcOnlyLosses = config::get("mostexptoplist_npclosses");
	
	$html .=<<<HTML
	<div class="block-header2">Settings</div>
	<form name="update" id="update" method="post">
	<table class="kb-subtable">
		<tr>
			<td width="160"><strong>Include Super Capitals</strong></td>
			<td><input type="checkbox" name="includeSuperCaps" value="1"
HTML;
	$html .= ($includeSupercaps == "1") ? " checked>" : ">" ;
	$html .=<<<HTML
	</td>
		</tr>
                <tr>
			<td width="160"><strong>Include Capitals</strong></td>
			<td><input type="checkbox" name="includeCaps" value="1"
HTML;
	$html .= ($includeCaps == "1") ? " checked>" : ">" ;
	$html .=<<<HTML
	</td>
		</tr>
                <tr>
			<td width="160"><strong>Include NPC only losses</strong></td>
			<td><input type="checkbox" name="includeNpcOnlyLosses" value="1"
HTML;
	$html .= ($includeNpcOnlyLosses == "1") ? " checked>" : ">" ;
	$html .=<<<HTML
	</td>
		</tr>
	</table>
HTML;

	$html .=<<<HTML
	<div class="block-header2">Save changes</div>
	<table class="kb-subtable">
		<tr>
			<td width="160"></td>
			<td><input type="submit" name="submit" value="Save" /></td>
		</tr>
	</table>
	</form>
HTML;
	$html .= "<div style=\"padding: 5px; margin: 20px 10px 10px; text-align: right; border-top: 1px solid #ccc\">$module $version by <a href=\"http://kb.ev0ke.de/\">Salvoxia</a>.</div>";
	$page->setContent($html);
	$page->addContext($menubox->generate());
	$page->generate();
?>
