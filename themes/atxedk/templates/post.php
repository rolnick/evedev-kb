<?php
require_once ('common/includes/class.parser.php');
require_once ('common/includes/class.phpmailer.php');
require_once ('common/includes/class.kill.php');
require_once ('common/includes/class.logger.php');
require_once ('mods/tsm/tsm.php');
$page = new Page('Post killmail');
global $smarty;
if (config::get('tsm_killmail') && is_file(config::get('tsm_smfrelative') . "/index.php") && is_file(config::get('tsm_smfrelative') . "/SSI.php")) {
    if (!$user_info['is_guest']) {
        require (config::get('tsm_smfrelative') . "/SSI.php");
        $kgroups = explode(',', config::get('tsm_kgroups'));
        $kgroups = array_flip($kgroups);
        foreach($user_info['groups'] as $g) {
            if (isset($kgroups[$g])) {
                $canpost = TRUE;
                Break;
            }
        }
        //    echo "<pre>";var_dump($user_info);die;
        
    }
    $logwho = 'SMF: ' . $context['user']['id'] . ' (' . $user_info['name'] . ')';
} else {
    $usepw = TRUE;
    if (isset($_POST['killmail'])) {
        if ($_POST['password'] == config::get('post_password') || $page->isAdmin()) $canpost = TRUE;
    } else $canpost = TRUE;
    $logwho = $_SERVER['REMOTE_ADDR'];
}
if (isset($_POST['killmail'])) {
    if ($canpost || $page->isAdmin()) {
        $parser = new Parser($_POST['killmail']);
        // Filtering
        if (config::get('filter_apply')) {
            $filterdate = config::get('filter_date');
            $year = substr($_POST['killmail'], 0, 4);
            $month = substr($_POST['killmail'], 5, 2);
            $day = substr($_POST['killmail'], 8, 2);
            $killstamp = mktime(0, 0, 0, $month, $day, $year);
            if ($killstamp < $filterdate) {
                $killid = - 3;
            } else {
                $killid = $parser->parse(true, null, false);
            }
        } else {
            $killid = $parser->parse(true, null, false);
        }
        if ($killid <= 0) {
            if ($killid == 0) {
                $html = "Killmail is malformed.<br/>";
                if ($errors = $parser->getError()) {
                    foreach($errors as $error) {
                        $html.= 'Error: ' . $error[0];
                        if ($error[1]) {
                            $html.= ' The text leading to this error was: "' . $error[1] . '"';
                        }
                        $html.= '<br/>';
                    }
                }
            } elseif ($killid == - 1) {
                $html = "That killmail has already been posted <a href=\"?a=kill_detail&kll_id=" . $parser->getDupeID() . "\">here</a>.";
            } elseif ($killid == - 2) {
                $html = "You are not authorized to post this killmail.";
            } elseif ($killid == - 3) {
                $filterdate = kbdate("j F Y", config::get("filter_date"));
                $html = "You are not allowed to post killmails older than $filterdate.";
            } elseif ($killid == - 4) {
                $html = "That mail has been deleted. Kill id was " . $parser->getDupeID();
                if ($page->isAdmin()) $html.= '<br />
<form id="postform" name="postform" class="f_killmail" method="post" action="?a=post">
    <input type="hidden" name="killmail" id="killmail" value = "' . htmlentities($_POST['killmail']) . '"/>
    <input type="hidden" name="kll_id" id="kill_id" value = "' . $parser->getDupeID() . '"/>
    <input type="hidden" name="undelete" id="undelete" value = "1"/>
<input id="submit" name="submit" type="submit" value="Undelete" />
</form>';
            }
        } else {
            if (config::get('post_mailto') != "") {
                $mailer = new PHPMailer();
                $kill = new Kill($killid);
                if (!$server = config::get('post_mailserver')) {
                    $server = 'localhost';
                }
                $mailer->From = "mailer@" . config::get('post_mailhost');
                $mailer->FromName = config::get('post_mailhost');
                $mailer->Subject = "Killmail #" . $killid;
                $mailer->Host = $server;
                $mailer->Port = 25;
                $mailer->Helo = $server;
                $mailer->Mailer = "smtp";
                $mailer->AddReplyTo("no_reply@" . config::get('post_mailhost'), "No-Reply");
                $mailer->Sender = "mailer@" . config::get('post_mailhost');
                $mailer->Body = $_POST['killmail'];
                $mailer->AddAddress(config::get('post_mailhost'));
                $mailer->Send();
            }
            $qry = new DBQuery();
            $qry->execute("insert into kb3_log (log_kll_id, log_site, log_ip_address, log_timestamp) values(" . $killid . ",'" . KB_SITE . "','" . $logwho . "', now())");
            header("Location: ?a=kill_detail&kll_id=" . $killid);
            exit;
        }
    } else {
        if ($usepw) $html = "Invalid password.";
//        elseif ($user_info['is_guest']) $html = "You need to be Logged in on the Forum to Post Killmails.";
        else $html = "You do not have Access to post Killmails.";
    }
} elseif (!config::get('post_forbid') && !config::get('post_oog_forbid')) {
    if ($canpost || $page->isAdmin()) {
        $html.= "Paste the killmail from your EVEMail inbox into the box below. Make sure you post the <b>ENTIRE</b> mail.<br>Posting fake or otherwise edited mails is not allowed. All posts are logged.";
        $html.= "<br><br>Remember to post your losses as well.<br><br>";
        $html.= "<b>Killmail:</b><br>";
        $html.= "<form id=postform name=postform class=f_killmail method=post action=\"?a=post\">";
        $html.= "<textarea name=killmail id=killmail class=f_killmail cols=\"70\" rows=\"24\"></textarea>";
        if ($usepw && !$page->isAdmin()) {
            $html.= "<br><br><b>Password:</b><br><input id=password name=password type=password></input>";
        }
        $html.= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id=submit name=submit type=submit value=\"Process !\"></input>";
        $html.= "</form>";
    } else {
        if ($user_info['is_guest']) $html = "You need to be Logged in on the Forum to Post Killmails.<br><br>";
        else $html = "You do not have Access to post Killmails.<br><br>";
    }
} else {
    if (config::get('post_oog_forbid')) {
        $html.= 'Out of game posting is disabled, please use the ingame browser.<br/>';
    } else {
        $html.= 'Posting killmails is disabled<br/>';
    }
}
$html.= '<span class="killcount">TSM ' . TSM_VERSION . '</span>';
$page->setContent($html);
$page->generate();
?>
