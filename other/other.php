<?php

require_once('other_admin.php');

if(get_option('other_privacy')) {
	add_action('admin_menu', function() {
		global $menu, $submenu;
		unset($submenu['options-general.php'][45]);
		remove_action('admin_menu', '_wp_privacy_hook_requests_page');
		remove_filter('wp_privacy_personal_data_erasure_page', 'wp_privacy_process_personal_data_erasure_page', 10, 5);
		remove_filter('wp_privacy_personal_data_export_page', 'wp_privacy_process_personal_data_export_page', 10, 7);
		remove_filter('wp_privacy_personal_data_export_file', 'wp_privacy_generate_personal_data_export_file', 10);
		remove_filter('wp_privacy_personal_data_erased', '_wp_privacy_send_erasure_fulfillment_notification', 10);
		remove_action('admin_init', array('WP_Privacy_Policy_Content', 'text_change_check'), 100);
		remove_action('edit_form_after_title', array('WP_Privacy_Policy_Content', 'notice'));
		remove_action('admin_init', array('WP_Privacy_Policy_Content', 'add_suggested_content'), 1);
		remove_action('post_updated', array('WP_Privacy_Policy_Content', '_policy_page_updated'));
	}, 9);
}

if(get_option('other_fonts')) {
	function remove_open_sans() {
		wp_deregister_style('open-sans');
		wp_register_style('open-sans', false);
		wp_enqueue_style('open-sans', '');
	}
	add_action('init', 'remove_open_sans');
}