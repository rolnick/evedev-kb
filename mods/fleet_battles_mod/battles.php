<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'class.battles.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'class.awardboxlong.php');

$page = new Page('Battles');

/**
*
* @return string HTML string for toplists
*/
function topLists($filterArguments = array())
{
    if(empty($filterArguments))
    {
        $whereSql = "";
    }
    
    else
    {
        $whereSql = "WHERE ".implode(" AND ", $filterArguments);
    }

        $sql = "SELECT COUNT( bop.battle_id ) AS cnt, bop.plt_id
                        FROM kb3_battles_owner_pilots bop
                        INNER JOIN kb3_pilots plt ON ( plt.plt_id = bop.plt_id ) 
                        INNER JOIN kb3_battles_cache bc ON bc.battle_id = bop.battle_id
                        INNER JOIN kb3_systems sys ON sys.sys_name = bc.system
                        INNER JOIN kb3_constellations con ON con.con_id = sys.sys_con_id
                        INNER JOIN kb3_regions reg ON reg.reg_id = con.con_reg_id
                        {$whereSql}
                        GROUP BY bop.plt_id ORDER BY 1 DESC LIMIT 15";
        $query = DBFactory::getDBQuery();
        $query->execute($sql);

        $tkbox = new AwardBoxLong($query, "Top Fleet Attendees", "Fleet attendances", "fleets", "eagle", $query->recordCount());
        return $html = $tkbox->generate();
}

// add filter toggling script
$jsDir = config::get("cfg_kbhost") . '/mods/' . basename(dirname(__FILE__)) . '/js/';
$page->addHeader("<script type=\"text/javascript\" src=\"".$jsDir."toggleFilter.js\"></script>");

switch ($_GET['view'])
{
    case '':
	echo "<!-- MOD VERSION -->\n";
        $battlelist = new BattleList();
        $page->setTitle('Fleet Battles');
	
        $table = new BattleListTable($battlelist);
        

        // pagination only available for cached battles and non-filtered results
        if (config::get('fleet_battles_mod_cache') && !isset($_POST["filter"]))
        {
            $table->setPageSplit(config::get('killcount')*2);
            $pagesplitter = new PageSplitter($table->getCount(),
                            config::get('killcount')*2);
            $pagesplit = $pagesplitter->generate();
            
            $html .= $pagesplit.$table->generate().$pagesplit.$table->getStatsHtml();
        }
        
        else
        {
            $html .= $table->generate().$table->getStatsHtml();  
        }       
        break;
}

$menubox = new box('Menu');
$menubox->setIcon('menu-item.gif');
$menubox->addOption('link', 'Fleet Battles', edkURI::page('battles'));

$page->addContext($menubox->generate());
if(config::get('fleet_battles_mod_cache'))
{
    $page->addContext(toplists($table->getFilterArgumentsWhereSql()));
}

$page->setContent($html);
$page->generate();
?>
