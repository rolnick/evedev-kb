<?php
if(!defined('KB_SITE')) die ("Go Away!");

$modInfo['statsmod']['name'] = "Statsmod v0.52";
$modInfo['statsmod']['abstract'] = "Provide an SSO login, various CREST features and some extended Stats";
$modInfo['statsmod']['about'] = "by Snitch Ashor";

$includeDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR;
	
require_once("mods/statsmod/class.statsmod.php");
require_once('xajax_ssocomments.php');

event::register("home_context_assembling", "init_statsmod::home");
event::register('get_tpl', 'init_statsmod::get_template');
event::register("killDetail_context_assembling", "init_statsmod::kill");
event::register("pilotDetail_context_assembling", "init_statsmod::pilot");
event::register("pilotDetail_assembling", "init_statsmod::show_pilotstats");


if(isset($_SESSION['sso_char']) || isset($_SESSION['sso_error']))
{
	session::forceNoCaching(true);
}
else
{
        session::forceNoCaching(false);
}

class init_statsmod
	{
            static function get_template(&$template_name)
            {
                if (config::get('statsmod_sso_comments') && ( $template_name == "block_comments" || $template_name == "comments_comments"))
                    {
                        global $smarty;
                        $_SESSION['sso_kill_id'] = edkURI::getArg('kll_id', 1);
                        $smarty->assign('ssocommentformURL', edkURI::page('sso_comment'));
                        $smarty->assign('sso_kill_id', edkURI::getArg('kll_id', 1));
                        if(isset($_SESSION['sso_char']))
                        {
                    	if (in_array($_SESSION['sso_char']->getCorp()->getAlliance()->getID(), config::get('cfg_allianceid'))){
                            $smarty->assign('sso_pilot', $_SESSION['sso_char']->getName());
                            if(config::get('statsmod_comments_ownersonly'))
                            {
                                $sso_char = $_SESSION['sso_char'];
                                $corp = $sso_char->getCorp();

                                $ok = false;
                                if (count(config::get('cfg_pilotid')) > 0)
                                {
                                    if (in_array($sso_char->getID(), config::get('cfg_pilotid')))
                                    $ok = true;
                                }
                                if ($corp && count(config::get('cfg_corpid')) > 0)
                                {
                                    if (in_array($corp->getID(), config::get('cfg_corpid')))
                                    $ok = true;
                                }
                                if ($corp && count(config::get('cfg_allianceid')) > 0)
                                {
                                    $alliance = $corp->getAlliance();
                                    if ($alliance && in_array($alliance->getID(), config::get('cfg_allianceid')))
                                        $ok = true;
                                }
                                if ($ok)
                                {
                                    $smarty->assign('comment_allowed', true);
                                }
                                else
                                {
                                    $smarty->assign('comment_allowed', false);
                                    $smarty->assign('comment_disallowed_reason', "<b>Only Board owners are allowed to comment.</b>");
                                }
                            }
                            else
                            {
                                $smarty->assign('comment_allowed', true); 
                            }
                        }
                        else {
                    	    $smarty->assign('comment_allowed', false);
                                    $smarty->assign('comment_disallowed_reason', "<b>Only Alliance members are allowed to comment.</b>");
                        }
                        }
                        else
                        {
                            $smarty->assign('comment_allowed', false);
                            $reason = "SSO Log in required to comment";
                            if(config::get("cfg_pathinfo") == '1') {
                                $reason .= "<form method=post action=".edkURI::page('sso_login/?method=login&page='.rawurlencode(getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'], '/')))."><div style='padding-top: 5px;'><input type='image' src='".config::get("cfg_kbhost")."/mods/statsmod/img/logineve.png' style='width: 100%; max-width: 160px; margin: 0px; padding: 0px; border: none'></div></form>";
                            } else {
                                                                $reason .= "<form method=post action=".edkURI::page('sso_login&method=login&page='.rawurlencode(getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'], '/')))."><div style='padding-top: 5px;'><input type='image' src='".config::get("cfg_kbhost")."/mods/statsmod/img/logineve.png' style='width: 100%; max-width: 160px; margin: 0px; padding: 0px; border: none'></div></form>";
                            }
                            $smarty->assign('comment_disallowed_reason', "$reason");
                        }

                    if ($template_name == "block_comments")
                        $template_name = "../../../mods/statsmod/sso_comments.tpl";
                    if ($template_name == "comments_comments")
                        $template_name = "../../../mods/statsmod/sso_comments_only.tpl";

                    return $template_name;
                }
                else
                {
                    return $template_name;
                }
            }
	    static function home($page)
	    {
			$page->addBehind("menu", "statsmod::display");
	    }
            static function kill($page)
            {
                        $page->addBehind("points", "statsmod::display");
                        $page->addBehind("menuSetup", 'init_statsmod::show_savefitting'); 
            }
            static function pilot($page)
            {
                        $page->addBehind("menu", "statsmod::display");
            }
            static function show_pilotstats($page)
            {
                    if (config::get('statsmod_pilotstats'))
                    {
                        include_once('class.ssopilotstats.php');
                        if (config::get('statsmod_pilotstats_public')){
                            $page->addBehind("summaryTable", "ssopilotstats::displaypersonal");
                        } elseif (isset($_SESSION['sso_char'])) {
                            if ($_SESSION['sso_char']->getID() == edkURI::getArg('plt_id', 1)) 
                            {
                                $page->addBehind("summaryTable", "ssopilotstats::displaypersonal");
                            }
                        }
                    }
                    if (config::get('statsmod_pilotstats_versus'))
                    {
                        if (isset($_SESSION['sso_char'])) {
                            if ($_SESSION['sso_char']->getID() != edkURI::getArg('plt_id', 1))
                            {
                                include_once('class.ssopilotstats.php');
                                $page->addBehind("summaryTable", "ssopilotstats::displayversus");
                            }
                        }
                    }
            }
            static function show_savefitting($page)
            {
                    if ($page->kll_id) {
                        $km = Cacheable::factory('Kill', $page->kll_id);
			$id = $page->kll_id;
		    } else {
			$km = new Kill($page->kll_external_id, true);
			$id = $page->kill->getID();
		    }  

                    if ($km->exists()) {
                        $page->addMenuItem("caption", "Fitting options");
                        if(config::get("cfg_pathinfo") == '1') {
                            $page->addMenuItem("link", "Save to EVE character", edkURI::page('sso_login/?method=savefit&fit='.$id.'&page='.rawurlencode(getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'],'/'))));
                        } else {
                            $page->addMenuItem("link", "Save to EVE character", edkURI::page('sso_login&method=savefit&fit='.$id.'&page='.rawurlencode(getRequestScheme().$_SERVER['HTTP_HOST'].rtrim($_SERVER['REQUEST_URI'],'/'))));
                        }
                    }
            }
	}
?>
