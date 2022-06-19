<?php

require_once('common/xajax/xajax.php');
require_once('class.ssocomments.php');
global $xajax;
if (isset($xajax))
{
    if (get_class($xajax) == 'xajax')
    {
        $xajax->register(XAJAX_FUNCTION, $b = "getSSOComments");
    }
}

function getSSOComments($kll_id, $message = '')
{
        file_put_contents('/tmp/xajaxtest.txt', 'testing');
	if (config::get('comments'))
	{
		$kll_id = intval($kll_id);
		$comments = new SSOComments($kll_id);
		global $smarty;
		$config = new Config();
		if(!$smarty)
		{
			$smarty = new Smarty();
			$themename = config::get('theme_name');
			if(is_dir('./themes/'.$themename.'/templates'))
				$smarty->template_dir = './themes/'.$themename.'/templates';
			else $smarty->template_dir = './themes/default/templates';
			if(!is_dir(KB_CACHEDIR.'/templates_c/'.$themename))
				mkdir(KB_CACHEDIR.'/templates_c/'.$themename);
			$smarty->compile_dir = KB_CACHEDIR.'/templates_c/'.$themename;
			$smarty->cache_dir = KB_CACHEDIR.'/data';
			$smarty->assign('theme_url', THEME_URL);
			$smarty->assign('style', $stylename);
			$smarty->assign('img_url', config::get('cfg_img'));
			$smarty->assign('img_host', IMG_HOST);
			$smarty->assign('kb_host', KB_HOST);
			$smarty->assignByRef('config', $config);
			$smarty->assign('is_IGB', IS_IGB);
			$smarty->assign('kll_id', $kll_id);
		}
		$smarty->assignByRef('page', new Page("Comments"));
		$message = $message.$comments->getSSOComments(true);
	}
	else $message = 'comments disabled';
	$objResponse = new xajaxResponse();
	$objResponse->assign('kl-detail-ssocomment-list', "innerHTML", $message);
	return $objResponse;
}
?>
