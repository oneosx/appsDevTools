<?php

/*
Plugin Name: AppsDevTools
Plugin URI:  https://appxlite.com
Description: App 开发助手，专门为基于 Wordpress 后端开发的 App 提供个性化定制服务。
Version:     0.0.1
Author:      Kiyo
Author URI:  https://appxlite.com
*/

define('APPSDEVTOOLS_PLUGIN_DIR', plugin_dir_path(__FILE__));

require('admin/admin.php');
add_action('admin_menu', 'appsDevTools_menu');

function appsDevTools_menu() {
    add_menu_page('AppsDevTools Setting','Apps','manage_options','AppsDevTools','Apps_admin_page','dashicons-screenoptions',99);
    add_action('admin_init', 'reg_apps_value');
}

function reg_apps_value() {
    $menu_group = array('appsDevTools_lite', 'appsDevTools_wxmp', 'appsDevTools_clean', 'appsDevTools_other');
    foreach($menu_group as $value) {
        register_setting('appsDevTools', $value);
    }
}

if(get_option('appsDevTools_lite')) {
    include(APPSDEVTOOLS_PLUGIN_DIR.'lite/lite.php');
}

if(get_option('appsDevTools_wxmp')) {
    include(APPSDEVTOOLS_PLUGIN_DIR.'wxmp/wxmp.php');
}

if(get_option('appsDevTools_other')) {
    include(APPSDEVTOOLS_PLUGIN_DIR.'other/other.php');
}

if(get_option('appsDevTools_clean')) {
    include(APPSDEVTOOLS_PLUGIN_DIR.'clean/clean.php');
}