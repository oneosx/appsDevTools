<?php

// 是否开启广告 REST API
add_action('rest_api_init', function () {
    register_rest_route('apps/v1', 'ad/config', array(
      'methods' => 'GET',
      'callback' => 'getEnableAd'
   ));
});
function getEnableAd($data) {
    $data = getEnableAdConfig();
    if (empty($data)) {
        return new WP_Error('no options', 'no options', array('status' => 404));
    }
    $response = new WP_REST_Response($data);
    $response->set_status(200);
    return $response;
}
function getEnableAdConfig() {

    $data['home']['enable'] = get_option('lite_ad_home');
    $data['home']['code'] = get_option('lite_ad_home_code');
    $data['list']['enable'] = get_option('lite_ad_list');
    $data['list']['code'] = get_option('lite_ad_list_code');
    $data['detail']['enable'] = get_option('lite_ad_detail');
    $data['detail']['code'] = get_option('lite_ad_detail_code');

    $result["code"] = "success";
    $result["message"] = "get config success";
    $result["status"] = "200";
    $result["data"] = $data;
    return $result;
    
}