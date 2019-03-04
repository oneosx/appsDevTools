<?php

/*
 * 基于WP-CLEAN-UP（Version：1.2.3）开发
 * Author    : BoLiQuan
 * Author URI: http://boliquan.com
*/

function clean_lang() {
	$currentLocale = get_locale();
	if(!empty($currentLocale)) {
		$moFile = dirname(__FILE__) . "/lang/wp-clean-up-" . $currentLocale . ".mo";
		if(@file_exists($moFile) && is_readable($moFile)) load_textdomain('WP-Clean-Up',$moFile);
	}
}
add_filter('init','clean_lang');

if(is_admin()){require_once('clean_admin.php');}

?>