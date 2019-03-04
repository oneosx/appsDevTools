<?php

// 增加卡片
add_action('rest_api_init', function () {
    register_rest_route('apps/v1', 'user/card/add', array(
      'methods' => 'POST',
      'callback' => 'userCardInfo'
    ));
});
function userCardInfo($request) {
    global $wpdb;
    $openid = $request['openid'];
    $cardid = $request['cardid'];
    $action = $request['action']; //add, update
    $oldval = $request['oldval'];
    if(empty($openid) || empty($cardid)) {
        return new WP_Error('error', 'openid or card is empty', array('status' => 500));
    } else { 
        if(!username_exists($openid)) {
            return new WP_Error('error', 'not allowed to submit', array('status' => 500));
        } else {
            $user_id = 0;
            $sql = $wpdb->prepare("SELECT ID FROM " . $wpdb->users . " WHERE user_login=%s", $openid);
            $users = $wpdb->get_results($sql);
            foreach ($users as $user) {
                $user_id = (int) $user->ID;
            }
            if($user_id != 0) {
                $data = userCardData($user_id, $cardid, $action, $oldval); 
                if (empty($data)) {
                    return new WP_Error('error', 'post card error', array('status' => 404));
                }
                $response = new WP_REST_Response($data);
                $response->set_status(200); 
                return $response;
            } else {
                return new WP_Error('error', 'userid id is error', array('status' => 500));
            }  
        }
    }
}
function userCardData($user_id, $cardid, $action, $oldval) {
    global $wpdb;
    if ($action === 'add') {
        if(add_user_meta($user_id, "creditCard", $cardid, false)) {
            $result["code"] = "success";
            $result["message"] = "add card success";
            $result["status"] = "200";
            return $result;
        } else {
            $result["code"] = "success";
            $result["message"] = "add card error";
            $result["status"] = "500";
            return $result;
        }
    } else if ($action === 'update') {
        if (update_user_meta($user_id, 'creditCard', $cardid, $oldval)) {
            $result["code"] = "success";
            $result["message"] = "update card success";
            $result["status"] = "201";
            return $result;
        } else {
            $result["code"] = "success";
            $result["message"] = "update card fail";
            $result["status"] = "501";
            return $result;
        }
    } else {
        if (delete_user_meta($user_id, 'creditCard', $cardid)) {
            $result["code"] = "success";
            $result["message"] = "delete card success";
            $result["status"] = "201";
            return $result;
        } else {
            $result["code"] = "success";
            $result["message"] = "delete card fail";
            $result["status"] = "501";
            return $result;
        }
    }
}

// 获取卡片
add_action('rest_api_init', function () {
    register_rest_route('apps/v1', 'user/card/get', array(
        'methods' => 'GET',
        'callback' => 'getCardInfo'
    ));
});
function getCardInfo($request) {
    global $wpdb;
    $openid = $request['openid'];
    if(empty($openid)) {
        return new WP_Error('error', 'openid is empty', array('status' => 500));
    } else { 
        if(!username_exists($openid)) {
            return new WP_Error('error', 'not allowed to submit', array('status' => 500));
        } else {
            $user_id =0;
            $sql = $wpdb->prepare("SELECT ID FROM " . $wpdb->users . " WHERE user_login = %s", $openid);
            $users = $wpdb->get_results($sql);
            foreach ($users as $user) {
                $user_id = (int) $user->ID ;
            }
            if($user_id != 0) {
                $data = getCardData($user_id);
                if (empty($data)) {
                    return new WP_Error('error', 'post card error', array('status' => 404));
                }
                $response = new WP_REST_Response($data);
                $response->set_status(200); 
                return $response;
            } else {
                return new WP_Error('error', 'userid id is error', array('status' => 500));
            }  
        }
    }
}
function getCardData($user_id) {
    global $wpdb;
    $usermeta = get_user_meta($user_id);
    if (!empty($usermeta)) {
        $result["code"] = "success";
        $result["message"] = "get card success";
        $result["status"] = "200";
        $result["data"] = empty($usermeta['creditCard']) ? [] : $usermeta['creditCard'];
        return $result;
    } else {
        $result["code"] = "success";
        $result["message"] = "you have not card";
        $result["status"] = "501";
        $result["data"] = [];
        return $result;
    }   
}