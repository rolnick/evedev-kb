<?php
event::register("page_assembleheader", "augmented_banner::add");

class augmented_banner {
	function add($home){
		global $smarty;
    		include_once('mods/augmented_banner/augmented_banner.php');
		$smarty->assign("augmented_banner", $html);
	}
}
