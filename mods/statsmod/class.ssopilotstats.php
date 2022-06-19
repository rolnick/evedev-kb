<?php
        if(!defined("KB_SITE")) die ("Go Away!");

        class ssopilotstats
        {
                private static function formatisk($isk) {
                    if ($isk > 1000000000) {
                        return round($isk/1000000000, 1)." B";
                    } elseif ($isk > 1000000) {
                        return round($isk/1000000, 1)." M";
                    } elseif ($isk > 1000) {
                        return round($isk/1000, 0)." K";
                    } else {
                        return (string)round($isk, 0);
                    }
                }
                static function displaypersonal()
                {
                    global $pilotDetail;
                    $plt_id = $pilotDetail->plt_id;
                    $plt = Pilot::getByID($plt_id);
                    if (isset($_SESSION['sso_char']) && $plt_id == $_SESSION['sso_char']->getID() ) {
                        $personal = true;
                    } else {
                        $personal = false;
                    }
                    $sql = "SELECT ind_shp_id as id, kb3_invtypes.typeName as name, COUNT(*) as kills, kb3_ship_classes.scl_class as class 
                            FROM kb3_inv_detail LEFT JOIN kb3_kills ON kb3_inv_detail.ind_kll_id = kb3_kills.kll_id 
                            LEFT JOIN kb3_ships AS victim_ship ON kb3_kills.kll_ship_id = victim_ship.shp_id 
                            LEFT JOIN kb3_invtypes ON kb3_invtypes.typeID = kb3_inv_detail.ind_shp_id 
                            LEFT JOIN kb3_ship_classes AS victim_ship_class ON victim_ship_class.scl_id = victim_ship.shp_class 
                            LEFT JOIN kb3_ships ON kb3_inv_detail.ind_shp_id = kb3_ships.shp_id 
                            LEFT JOIN kb3_ship_classes ON kb3_ship_classes.scl_id = kb3_ships.shp_class
                            WHERE ind_plt_id = ".$plt_id."
                            AND kb3_kills.kll_timestamp > NOW() - INTERVAL 30 DAY ";
                    if (config::get('statsmod_pilotstats_nopods'))
                    {
                        $sql .= "AND NOT (kb3_ships.shp_class = 2 OR kb3_ships.shp_class = 3 OR kb3_ships.shp_class = 11 OR kb3_inv_detail.ind_shp_id = 0) ";
                    } else {
                        $sql .= "AND NOT (kb3_inv_detail.ind_shp_id = 0) ";
                    }
                    $sql .= "GROUP BY ind_shp_id ORDER BY kills DESC LIMIT 10";
                    global $cssimported;
                    if (!$cssimportd) {
                        $html = "<link rel='stylesheet' href='".config::get('cfg_kbhost')."/mods/statsmod/style.css' type='text/css'>";
                    } else {
                        $html = "";
                    }
                    if ($personal) {
                        $html .= "<div class='kb-date-header'>Your personal stats</div>";
                    } else {
                        $html .= "<div class='kb-date-header'>Stats for ".$plt->getName()."</div>";
                    }
                    if (config::get('statsmod_pilotstats_nopods'))
                    {
                        $html .= "<span style='font-style: italic;'>Ignoring Pods, Shuttles and Rookie ships.</span>";
                    }
		    $qry = DBFactory::getDBQuery();
		    $qry->execute($sql);
                    $html .= "<div class='statscontainer'>";
                    $html .= "<div class='statstable leftstats'>";
                    //$html .= "Favorite Ships in the last 30 days<br/>";
                    $html .= "<table class='kb-table kb-stats-table kb-kl-table kb-table-rows'><tr><th class='kb-table-header' colspan='2'>Favorite ships (30 days)</th><th class='kb-table-header n_kills' colspan='1'># of kills</th></tr>";
		    while ($row = $qry->getRow())
		    {
		        $html .= "<tr><td class='kb-table-imgcell'><img src='https://imageserver.eveonline.com/Type/".$row['id']."_32.png'></td>";
                        $html .= "<td><a href=".edkURI::page('invtype',$row['id']).">".$row['name']."</a><br/>".$row['class']."</td>";
                        $html .= "<td class='n_kills'>".$row['kills']."</td></tr>";
                    }
		    $html .= "</table></div>";

                    $sql = "SELECT ind_shp_id as id, kb3_invtypes.typeName as name, COUNT(*) as kills, kb3_ship_classes.scl_class as class
                            FROM kb3_inv_detail LEFT JOIN kb3_kills ON kb3_inv_detail.ind_kll_id = kb3_kills.kll_id
                            LEFT JOIN kb3_ships AS victim_ship ON kb3_kills.kll_ship_id = victim_ship.shp_id
                            LEFT JOIN kb3_invtypes ON kb3_invtypes.typeID = kb3_inv_detail.ind_shp_id
                            LEFT JOIN kb3_ship_classes AS victim_ship_class ON victim_ship_class.scl_id = victim_ship.shp_class
                            LEFT JOIN kb3_ships ON kb3_inv_detail.ind_shp_id = kb3_ships.shp_id
                            LEFT JOIN kb3_ship_classes ON kb3_ship_classes.scl_id = kb3_ships.shp_class
                            WHERE ind_plt_id = ".$plt_id." ";
                    if (config::get('statsmod_pilotstats_nopods'))
                    {
                        $sql .= "AND NOT (kb3_ships.shp_class = 2 OR kb3_ships.shp_class = 3 OR kb3_ships.shp_class = 11 OR kb3_inv_detail.ind_shp_id = 0) ";
                    } else {
                        $sql .= "AND NOT (kb3_inv_detail.ind_shp_id = 0) ";
                    }
                    $sql .= "GROUP BY ind_shp_id ORDER BY kills DESC LIMIT 10";

                    $qry = DBFactory::getDBQuery();
                    $qry->execute($sql);
                    if ($qry->recordCount() == 0) return '';
                    $html .= "<div class='statstable middlestats'>";
                    //$html .= "Favorite Ships all time<br/>";
                    $html .= "<table class='kb-table kb-stats-table kb-kl-table kb-table-rows'><tr><th class='kb-table-header' colspan='2'>Favorite ships (all time)</th><th class='kb-table-header n_kills' colspan='1'># of kills</th></tr>";
                    while ($row = $qry->getRow())
                    {
                        $html .= "<tr><td class='kb-table-imgcell'><img src='https://imageserver.eveonline.com/Type/".$row['id']."_32.png'></td>";
                        $html .= "<td><a href=".edkURI::page('invtype',$row['id']).">".$row['name']."</a><br/>".$row['class']."</td>";
                        $html .= "<td class='n_kills'>".$row['kills']."</td></tr>";
                    }
                    $html .= "</table></div>";

                    $sql = "SELECT kb3_kills.kll_system_id as id, kb3_systems.sys_name as name, kb3_systems.sys_sec as sec, COUNT(*) as kills, kb3_mapdenormalize.typeID as sunID
                            FROM kb3_inv_detail LEFT JOIN kb3_kills ON kb3_inv_detail.ind_kll_id = kb3_kills.kll_id 
                            LEFT JOIN kb3_ships ON kb3_kills.kll_ship_id = kb3_ships.shp_id
                            LEFT JOIN kb3_systems ON kb3_systems.sys_id = kb3_kills.kll_system_id 
                            LEFT JOIN kb3_mapdenormalize ON (kb3_mapdenormalize.solarSystemID = kb3_kills.kll_system_id AND x = 0 AND y = 0 AND z = 0)
                            WHERE ind_plt_id = ".$plt_id." ";

                    if (config::get('statsmod_pilotstats_nopods'))
                    {
                        $sql .= "AND NOT (kb3_ships.shp_class = 2 OR kb3_ships.shp_class = 3 OR kb3_ships.shp_class = 11 OR kb3_inv_detail.ind_shp_id = 0) ";
                    }
                    $sql .= "GROUP BY kb3_kills.kll_system_id ORDER BY kills DESC LIMIT 10";
                    $qry = DBFactory::getDBQuery();
                    $qry->execute($sql);
                    $html .= "<div class='statstable rightstats'>";
                    //$html .= "Favorite Systems<br/>";
                    $html .= "<table class='kb-table kb-stats-table kb-kl-table kb-table-rows'><tr><th class='kb-table-header' colspan='2'>Favorite Systems</th><th class='kb-table-header n_kills' colspan='1'># of kills</th></tr>";
                    while ($row = $qry->getRow())
                    {
                        $html .= "<tr><td class='kb-table-imgcell'><img src='https://imageserver.eveonline.com/Type/".$row['sunID']."_32.png'></td>";
                        $html .= "<td><a href=".edkURI::page('system_detail',$row['id'],'sys_id').">".$row['name']."</a>";
                        $sec = round($row["sec"], 1);
                        if ($sec < 0) {
                            $sec = 0.0;
                        }
                        $html.= " (<span class=s".str_replace(".", "", $sec).">".$sec."</span>)</td>";
                        $html .= "<td class='n_kills'>".$row['kills']."</td></tr>";
                    }
                    $html .= "</table></div>";
                    $html .= "</div>";
                    return $html;
                }

                static function displayversus()
                {

                    if (isset($_SESSION['sso_char'])) {
                        $plt_id = $_SESSION['sso_char']->getID();
                        $plt = $_SESSION['sso_char'];
                        global $pilotDetail;
                        $plt_id2 = $pilotDetail->plt_id;
                        $plt2 = Pilot::getByID($plt_id2);
                    } else {
                        return '';
                    }

                    global $cssimported;
                    if (!$cssimportd) {
                        $html = "<link rel='stylesheet' href='".config::get('cfg_kbhost')."/mods/statsmod/style.css' type='text/css'>";
                    } else {
                        $html = "";
                    }
                    $html .= "<div class='kb-date-header'>Stats for ".$plt->getName()." versus ".$plt2->getName()."</div>";
                    $sql = "SELECT ind_plt_id as id, COUNT(*) as kills, SUM(kll_isk_loss) as isk
                            FROM kb3_inv_detail LEFT JOIN kb3_kills ON kb3_inv_detail.ind_kll_id = kb3_kills.kll_id LEFT JOIN kb3_ships ON kb3_kills.kll_ship_id = kb3_ships.shp_id LEFT JOIN kb3_invtypes ON kb3_invtypes.typeID = kb3_inv_detail.ind_shp_id LEFT JOIN kb3_ship_classes ON kb3_ship_classes.scl_id = kb3_ships.shp_class
                            WHERE (ind_plt_id = ".$plt_id." and kll_victim_id = ".$plt_id2.") OR (ind_plt_id = ".$plt_id2." and kll_victim_id = ".$plt_id.")";
                    if (config::get('statsmod_pilotstats_nopods'))
                    {
                        $sql .= "AND NOT (kb3_ships.shp_class = 2 OR kb3_ships.shp_class = 3 OR kb3_ships.shp_class = 11 OR kb3_inv_detail.ind_shp_id = 0)";
                    }
                    $sql .= "GROUP BY ind_plt_id";
                    $qry = DBFactory::getDBQuery();
                    $qry->execute($sql);
                    $kills = 0;
                    $losses = 0;
                    $iskdestroyed = 0;
                    $isklost = 0;
                    while ($row = $qry->getRow())
                    {
                        if ($row['id'] == $plt_id) {
                            $kills = $row['kills'];
                            $iskdestroyed = $row['isk'];
                        } elseif ($row['id'] == $plt_id2) {
                            $losses = $row['kills'];
                            $isklost = $row['isk'];
                        }
                    }
                    if ($kills == 0 && $losses == 0) {
                        return '';
                    }
                    if ($iskdestroyed == 0 && $isklost == 0) {
                        $eff1 = 0;
                        $eff2 = 0;
                    } else {
                        $eff1 = (float)round($iskdestroyed*100/($iskdestroyed+$isklost), 1);
                        $eff2 = (float)round($isklost*100/($iskdestroyed+$isklost), 1);
                    }
                    if (null !== (config::get('statsmod_killcolor'))) {
                        $green = '#'.config::get('statsmod_killcolor');
                    } else {
                        $green = 'green';
                    }
                    if (null !== (config::get('statsmod_losscolor'))) {
                        $red = '#'.config::get('statsmod_losscolor');
                    } else {
                        $red = 'red';
                    }
                    $html .= "<style>#vs-kills {display: inline-block; min-height: 8px; background: ".($kills > $losses ? $green : $red)."; width: ".round($kills*120/($kills+$losses)+2)."px;}";
                    $html .= "#vs-losses {display: inline-block; min-height: 8px; background: ".($losses > $kills ? $green : $red)."; width: ".round($losses*120/($kills+$losses)+2)."px;}";
                    $html .= "#vs-iskdest {display: inline-block; min-height: 8px; background: ".($eff1 > $eff2 ? $green : $red)."; width: ".round($eff1*1.2+2)."px;}";
                    $html .= "#vs-isklost {display: inline-block; min-height: 8px; background: ".($eff2 > $eff1 ? $green : $red)."; width: ".round($eff2*1.2+2)."px;}</style>";
                    $html .= "<table id='versusstats'><tr colspan=3><th class='versus-left'><a href=".edkURI::page('pilot_detail', $plt_id)."><img src=https://imageserver.eveonline.com/Character/".$plt->getExternalID()."_64.jpg title='".$plt->getName()."'></a></th><th class='versus-middle'><h1 style='margin: 0'> vs. </h1></th><th class='versus-right'><a href=".edkURI::page('pilot_detail', $plt_id2)."><img src=https://imageserver.eveonline.com/Character/".$plt2->getExternalID()."_64.jpg title='".$plt2->getName()."'></a></th><tr>";
                    $html .= "<tr><td class='versus-left'>".$kills." <div id='vs-kills'></div></td><td class='versus-middle'>Kills</td><td class='versus-right'><div id='vs-losses'></div> ".$losses."</td></tr>";
                    $html .= "<tr><td class='versus-left'>".self::formatisk($iskdestroyed)." (".$eff1."%) <div id='vs-iskdest'></div></td><td class='versus-middle'>ISK destroyed</td><td class='versus-right'><div id='vs-isklost'></div> ".self::formatisk($isklost)." (".$eff2."%) </td></tr>";
		    $html .= "</table>";
                     
                    return $html;
                }

        }
?>
