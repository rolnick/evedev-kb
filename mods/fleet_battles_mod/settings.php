<?php
require_once("common/admin/admin_menu.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'class.battles.php');

if ($_POST['submit'])
{
                $error = 0;
                // trying to activate caching
                if($_POST['fleet_battles_mod_cache'] && config::get("fleet_battles_mod_cache") != "1")
                {
                    // first check if the caching table is there!
                    $dbq = DBFactory::getDBQuery();
                    
                    $check_sql = "SHOW TABLES LIKE 'kb3_battles_cache'";
                    $dbq->execute($check_sql);
                    if(!$dbq->getRow())
                    {
                        $html .= "You need to create the cache table before activating caching.";
                        $error = 1;
                    }
                    
                }
                
                
                 // trying to activate manual side assignment
                if($_POST['fleet_battles_mod_sideassign'] && config::get("fleet_battles_mod_sideassign") != "1")
                {
                    if($error)
                    {
                        $html .= "<br/>";
                    }
                    // first check if the caching table is there!
                    $dbq = DBFactory::getDBQuery();
                    
                    $check_sql = "SHOW TABLES LIKE 'kb3_side_assignment'";
                    $dbq->execute($check_sql);
                    if(!$dbq->getRow())
                    {
                        $html .= "You need to create the side assignment table before activating manual side assignment.";
                        $error = 1;
                    }
                    
                }
                
                
                if(!$error) 
                {
                    
                    $val = ($_POST['fleet_battles_mod_cache']) ? 1 : 0;
                    config::set('fleet_battles_mod_cache', $val);
         
                    $val = ($_POST['fleet_battles_mod_displaymetrics']) ? 1 : 0;
                    config::set('fleet_battles_mod_displaymetrics', $val);
         
                    $val = ($_POST['fleet_battles_mod_showtimeline']) ? 1 : 0;
                    config::set('fleet_battles_mod_showtimeline', $val);
         
                    $val = ($_POST['fleet_battles_mod_showlossvalues']) ? 1 : 0;
                    config::set('fleet_battles_mod_showlossvalues', $val);
                    
                    $val = ($_POST['fleet_battles_mod_damagelists']) ? 1 : 0;
                    config::set('fleet_battles_mod_damagelists', $val);
    
                    $val = ($_POST['fleet_battles_mod_showkilllists']) ? 1 : 0;
                    config::set('fleet_battles_mod_showkilllists', $val);
   
                    $val = ($_POST['fleet_battles_mod_showloot']) ? 1 : 0;
                    config::set('fleet_battles_mod_showloot', $val);
                    
                    $val = ($_POST['fleet_battles_mod_sideassign']) ? 1 : 0;
                    config::set('fleet_battles_mod_sideassign', $val);
                    
 

                    if($_POST['fleet_battles_mod_minkills']) {config::set('fleet_battles_mod_minkills', $_POST['fleet_battles_mod_minkills']);}
                    if($_POST['fleet_battles_mod_minisk']) {config::set('fleet_battles_mod_minisk', $_POST['fleet_battles_mod_minisk']);}	
                    if($_POST['fleet_battles_mod_maxtime']) {config::set('fleet_battles_mod_maxtime', $_POST['fleet_battles_mod_maxtime']);}

                    $html .= "Setting Saved";
                }
}

if ($_POST['empty_table'])
{
	$dbq = DBFactory::getDBQuery();
	$empty_sql = 'TRUNCATE TABLE kb3_battles_cache;';
	$dbq->execute($empty_sql);
        
        $empty_sql = 'TRUNCATE TABLE kb3_battles_owner_pilots;';
	$dbq->execute($empty_sql);
	$html .= "Cache table empty.";
}

if ($_POST['create_table'])
{
        if (config::get('fleet_battles_mod_cache'))
                $html .= "You cannot do this while caching is enabled.";
	else {
		$dbq = DBFactory::getDBQuery();
		$drop_sql = "DROP TABLE IF EXISTS kb3_battles_cache";
		$dbq->execute($drop_sql);

		$create_sql = "CREATE TABLE `kb3_battles_cache` (
  `battle_id` int(11) NOT NULL auto_increment,
  `kll_id` int(11) NOT NULL,
  `killisk` bigint(20) NOT NULL,
  `lossisk` bigint(20) NOT NULL,
  `efficiency` float NOT NULL,
  `bar` tinyblob NOT NULL,
  `kills` int(11) NOT NULL,
  `losses` int(11) NOT NULL,
  `involved` int(11) NOT NULL,
  `system` varchar(100) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `ownersInvolved` int(10) NOT NULL,
  PRIMARY KEY  (`battle_id`),
  KEY `start_end` (`end`,`start`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
		$dbq->execute($create_sql);
                
                $drop_sql = "DROP TABLE IF EXISTS kb3_battles_owner_pilots";
		$dbq->execute($drop_sql);
                
                $create_sql = "CREATE TABLE `kb3_battles_owner_pilots` (
                    `battle_id` int unsigned NOT NULL,
                    `plt_id` int unsigned NOT NULL,
                    PRIMARY KEY (`battle_id`, `plt_id`)
                    )
                    ENGINE=InnoDB";
                $dbq->execute($create_sql);
		$html .= "Cache table created, you can safely enable caching.";
	}
}

if ($_POST['drop_table'])
{
        if (config::get('fleet_battles_mod_cache'))
                $html .= "You cannot do this while caching is enabled.";
	else {
	        $dbq = DBFactory::getDBQuery();
	        $drop_sql = "DROP TABLE IF EXISTS kb3_battles_cache";
	        $dbq->execute($drop_sql);
                
                $drop_sql = "DROP TABLE IF EXISTS kb3_battles_owner_pilots";
		$dbq->execute($drop_sql);
		$html .= "Cache table dropped.";
	}
}

if ($_POST['build_battles'])
{
	if (config::get('fleet_battles_mod_cache')) {
            
            if(function_exists("set_time_limit"))
                @set_time_limit(0);
            
	    $dbq = DBFactory::getDBQuery();
	    $system_sql = "select count(*) as cnt, kll_system_id from kb3_kills
			   group by kll_system_id
			   having cnt > ".config::get('fleet_battles_mod_minkills')." order by cnt";
	    $dbq->execute($system_sql);
            
	    while($system = $dbq->getRow()) {
             
	        $battlelist = new BattleList((int)$system['kll_system_id']);
		$battlelist->execQuery();
               
                $table = new BattleListTable($battlelist);
                 
	        $table->getTableStats();

		unset($battlelist);
		unset($table);
	    } 
	    $html .= "Built battle cache.<br/>";
	}
	else
	    $html .= "Caching must be enabled to build the battle cache.";
}



// ------------------------------------------------------------------------
// side assignment table actions
// ------------------------------------------------------------------------
if ($_POST['side_assignment_empty_table'])
{
	$dbq = DBFactory::getDBQuery();
	$empty_sql = 'TRUNCATE TABLE kb3_side_assignment;';
	$dbq->execute($empty_sql);
	$html .= "Side assignment table empty.";
}

if ($_POST['side_assignment_create_table'])
{
        if (config::get('fleet_battles_mod_sideassign'))
                $html .= "You cannot do this while manual side assignment is enabled.";
	else {
		$dbq = DBFactory::getDBQuery();
		$drop_sql = "DROP TABLE IF EXISTS kb3_side_assignment";
		$dbq->execute($drop_sql);

		$create_sql = "CREATE TABLE `kb3_side_assignment` (
  `system_id` int(11) NOT NULL,
  `timestamp_start` datetime NOT NULL,
  `timestamp_end` datetime NOT NULL,
  `entity_id` bigint(20) NOT NULL,
  `entity_type` ENUM('corp', 'alliance') NOT NULL,
  `side` ENUM('a', 'e') NOT NULL,
  PRIMARY KEY  (`system_id`, `timestamp_start`, `timestamp_end`, `entity_id`, `entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
		$dbq->execute($create_sql);
		$html .= "Side assignment table created, you can safely enable manual side assignment.";
	}
}

if ($_POST['side_assignment_drop_table'])
{
        if (config::get('fleet_battles_mod_sideassign'))
                $html .= "You cannot do this while manual side assignment is enabled.";
	else {
	        $dbq = DBFactory::getDBQuery();
	        $drop_sql = "DROP TABLE IF EXISTS kb3_side_assignment";
	        $dbq->execute($drop_sql);
		$html .= "Side assignment table dropped.";
	}
}
$page = new Page("Settings - Fleet Battles Mod");


$html .= "<form id=options name=options method=post action=>";
$html .= "<table class=kb-subtable>";
$html .= "<tr><td colspan=\"4\"><div class=block-header2>Global options</div></td></tr>";
// Diplay Metrics
$html .= "<tr><td><b>Display battle metrics:</b></td><td><input type=checkbox name=fleet_battles_mod_displaymetrics id=fleet_battles_mod_displaymetrics";
if (config::get('fleet_battles_mod_displaymetrics'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";

//Caching
$html .= "<tr><td><b>Enable caching:</b></td><td><input type=checkbox name=fleet_battles_mod_cache id=fleet_battles_mod_cache";
if (config::get('fleet_battles_mod_cache'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";

// side assignment
$html .= "<tr><td><b>Use manual side assignment feature:</b></td><td><input type=checkbox name=fleet_battles_mod_sideassign id=fleet_battles_mod_sideassign";
if (config::get('fleet_battles_mod_sideassign'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";

// Kills + Losses Config
$html .= "<tr><td><b>Kills+losses for battles:</b></td><td><input type=input name=fleet_battles_mod_minkills id=fleet_battles_mod_minkills";
if (config::get('fleet_battles_mod_minkills'))
{
    $html .= " value=".config::get('fleet_battles_mod_minkills');
}
$html .= "></td></tr>";

// ISK Config
$html .= "<tr><td><b>Kill ISK+Loss ISK (M) for battles:</b></td><td><input type=input name=fleet_battles_mod_minisk id=fleet_battles_mod_minisk";
if (config::get('fleet_battles_mod_minisk'))
{
    $html .= " value=".config::get('fleet_battles_mod_minisk');
}
$html .= "></td></tr>";

// Time Config
$html .= "<tr><td><b>Sliding time in hours:</b></td><td><input type=input name=fleet_battles_mod_maxtime id=fleet_battles_mod_maxtime";
if (config::get('fleet_battles_mod_maxtime'))
{
    $html .= " value=".config::get('fleet_battles_mod_maxtime');
}
$html .= "></td></tr>";
$html .= "<tr><td colspan=\"4\">&nbsp;</td></tr>";
$html .= "<tr><td colspan=4 ><div class=block-header2>Related Kill Options</div></td></tr>";
// Diplay timeline
$html .= "<tr><td><b>Display timeline:</b></td><td><input type=checkbox name=fleet_battles_mod_showtimeline id=fleet_battles_mod_showtimeline";
if (config::get('fleet_battles_mod_showtimeline'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";

// Diplay loss value lists
$html .= "<tr><td><b>Display loss value lists:</b></td><td><input type=checkbox name=fleet_battles_mod_showlossvalues id=fleet_battles_mod_showlossvalues";
if (config::get('fleet_battles_mod_showlossvalues'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";

// Diplay damage overview
$html .= "<tr><td><b>Display damage overview:</b></td><td><input type=checkbox name=fleet_battles_mod_damagelists id=fleet_battles_mod_damagelists";
if (config::get('fleet_battles_mod_damagelists'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";

// Diplay kill lists
$html .= "<tr><td><b>Display kill lists:</b></td><td><input type=checkbox name=fleet_battles_mod_showkilllists id=fleet_battles_mod_showkilllists";
if (config::get('fleet_battles_mod_showkilllists'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";

// Diplay loot overview
$html .= "<tr><td><b>Display loot overview:</b></td><td><input type=checkbox name=fleet_battles_mod_showloot id=fleet_battles_mod_showloot";
if (config::get('fleet_battles_mod_showloot'))
{
    $html .= " checked=\"checked\"";
}
$html .= "</td></tr>";


// submit button
$html .= "<tr><td colspan=\"4\">&nbsp;</td></tr>";
$html .= "<tr></tr><tr><td></td><td colspan=3 ><input type=submit name=submit value=\"Save\"></td></tr>";
$html .= "<tr><td colspan=\"4\">&nbsp;</td></tr>";
$html .= "</table>";



$html .= "</form>";

$html .= "<div class=block-header2>Cache table operations</div>";
$html .= "<form id=options name=options method=post action=>";
$html .= "<table class=kb-subtable><tr>";
$html .= "<td><input type=submit name=create_table value=\"Create Table\"></td>";
$html .= "<td><input type=submit name=drop_table value=\"Drop Table\"></td>";
$html .= "<td><input type=submit name=empty_table value=\"Empty Table\"></td>";
$html .= "<td><input type=submit name=build_battles value=\"Build Cache\"></td>";
$html .= "</tr></table>";
$html .= "</form>";

$html .= "<hr/>";

$html .= "<div class=block-header2>Side assignment table operations</div>";
$html .= "<form id=options name=options method=post action=>";
$html .= "<table class=kb-subtable><tr>";
$html .= "<td><input type=submit name=side_assignment_create_table value=\"Create Table\"></td>";
$html .= "<td><input type=submit name=side_assignment_drop_table value=\"Drop Table\"></td>";
$html .= "<td><input type=submit name=side_assignment_empty_table value=\"Empty Table\"></td>";
$html .= "</tr></table>";
$html .= "</form>";

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>
