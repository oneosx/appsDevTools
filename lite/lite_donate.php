<?php

// 是否开启广告 REST API
add_action('rest_api_init', function () {
    register_rest_route('apps/v1', 'donate/config', array(
      'methods' => 'GET',
      'callback' => 'getDonate'
   ));
});
function getDonate($data) {
    $data = getDonateOptions();
    if (empty($data)) {
        return new WP_Error('no options', 'no options', array('status' => 404));
    }
    $response = new WP_REST_Response($data);
    $response->set_status(200);
    return $response;
}
function getDonateOptions() {

    $data['enable'] = get_option('lite_donate');
    $data['qrcode'] = get_option('lite_donate_qrcode');

    $result["code"] = "success";
    $result["message"] = "get enable donate success";
    $result["status"] = "200";
    $result["data"] = $data;
    return $result;
    
}