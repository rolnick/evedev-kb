<?php
        if(!defined("KB_SITE")) die ("Go Away!");

        class statsmod
        {
                static function display()
                {
                        $loginBox = new box("SSO login");
                        if (isset($_SESSION['sso_error'])) {
                            $loginBox->addOption("caption", "<center>".$_SESSION['sso_error']."</center>");
                            if(config::get("cfg_pathinfo") == '1') {
                                $loginBox->addOption("caption", "<form method=post action=".edkURI::page('sso_login/?method=login&page='.getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'], '/'))."><div style='padding-top: 5px;'><center><input type='image' src='".config::get("cfg_kbhost")."/mods/statsmod/img/logineve.png' style='width: 100%; max-width: 160px; margin: 0px; padding: 0px; border: none'></center></div></form>");
                            } else {
                                $loginBox->addOption("caption", "<form method=post action=".edkURI::page('sso_login&method=login&page='.getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'], '/'))."><div style='padding-top: 5px;'><center><input type='image' src='".config::get("cfg_kbhost")."/mods/statsmod/img/logineve.png' style='width: 100%; max-width: 160px; margin: 0px; padding: 0px; border: none'></center></div></form>");
                            }
                        } elseif (isset($_SESSION['sso_char'])) {
                            $owner = false;
                            if (count(config::get('cfg_pilotid')) > 0)
                            {
                                if (in_array($_SESSION['sso_char']->getID(), config::get('cfg_pilotid')))
                                $owner = true;
                            }
                            if (count(config::get('cfg_corpid')) > 0)
                            {
                                if (in_array($_SESSION['sso_char']->getCorp()->getID(), config::get('cfg_corpid')))
                                $owner = true;
                            }
                            if (count(config::get('cfg_allianceid')) > 0)
                            {
                                if (in_array($_SESSION['sso_char']->getCorp()->getAlliance()->getID(), config::get('cfg_allianceid')))
                                    $owner = true;
                            }
                            $loginBox->addOption("caption", "<div style='float: left; height: 80px; padding-right: 5px'><img height='42' src='".$_SESSION['sso_char']->getPortraitURL(64)."'></div>");
                            $loginBox->addOption("caption", "<div style='margin-left: 46px'>Hello ".$_SESSION['sso_char']->getName().", good to see you!</div>");
                            $loginBox->addOption("link", "Pilot details", edkURI::page('pilot_detail&plt_id='.$_SESSION['sso_char']->getID()));
                            if ($owner) {
                            //    if(config::get("cfg_pathinfo") == '1') {
                            //        $loginBox->addOption("link", "Post my kills", edkURI::page('sso_login/?method=postkills&page='.rawurlencode(getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'], '/'))));
                            //    } else {
                            //        $loginBox->addOption("link", "Post my kills", edkURI::page('sso_login&method=postkills&page='.rawurlencode(getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'], '/'))));
                            //    }
                                //$loginBox->addOption("caption", "<div style='background: rgba(0,0,0,0.95); padding: 10px';><section style='height: 150px; width: 400px'><p>testdiv</p></section></div>");
                            }
                            if(config::get("cfg_pathinfo") == '1') {
                                $loginBox->addOption("caption", "<div style='clear: both; padding-top: 5px;'><center><a href='".edkURI::page('sso_login/?method=logout&page='.rawurlencode(getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'], '/')))."'><img src='".config::get("cfg_kbhost")."/mods/statsmod/img/logout.png' style='width: 100px;'></a></center></div>");
                            } else {
                                $loginBox->addOption("caption", "<div style='clear: both; padding-top: 5px;'><center><a href='".edkURI::page('sso_login&method=logout&page='.rawurlencode(getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'], '/')))."'><img src='".config::get("cfg_kbhost")."/mods/statsmod/img/logout.png' style='width: 100px;'></a></center></div>");
                            }
                        } else {
                            $loginBox->addOption("caption", "<center>Not logged in.<br /></center>");
                            if(config::get("cfg_pathinfo") == '1') {
	                        $loginBox->addOption("caption", "<form method=post action=".edkURI::page('sso_login/?method=login&page='.getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'], '/'))."><div style='padding-top: 5px;'><center><input type='image' src='".config::get("cfg_kbhost")."/mods/statsmod/img/logineve.png' style='width: 100%; max-width: 160px; margin: 0px; padding: 0px; border: none'></center></div></form>");
                            } else {
	                        $loginBox->addOption("caption", "<form method=post action=".edkURI::page('sso_login&method=login&page='.getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'], '/'))."><div style='padding-top: 5px;'><center><input type='image' src='".config::get("cfg_kbhost")."/mods/statsmod/img/logineve.png' style='width: 100%; max-width: 160px; margin: 0px; padding: 0px; border: none'></center></div></form>");
                            }
                        }
                        return $loginBox->generate();
                }
        }
?>
