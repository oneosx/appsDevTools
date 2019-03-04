<?php

// 点赞 REST API
add_action('rest_api_init', function () {
	register_rest_route('apps/v1', 'like/up', array(
		'methods' => 'POST',
		'callback' => 'post_like_up'
	));
});
function post_like_up($request) {
    $openid = $request['openid'];
    $postid = $request['postid'];
    if(empty($openid) || empty($postid)) {
		return new WP_Error('error', 'openid or postid is empty', array('status' => 500));
    } elseif(get_post($postid) == null) {
        return new WP_Error('error', 'post id is error', array('status' => 500));
    } else { 
        if(!username_exists($openid)) {
            return new WP_Error('error', 'not allowed to submit', array('status' => 500));
        } else if(is_wp_error(get_post($postid))) {
            return new WP_Error('error', 'post id is error', array('status' => 500));
        } else {
            $data = post_like_up_data($openid,$postid); 
            if (empty($data)) {
                return new WP_Error('error', 'post like up error', array('status' => 404));
            }
            $response = new WP_REST_Response($data);
            $response->set_status(200); 
            return $response;
        }
    }
}
function post_like_up_data($openid, $postid) { 
    $openid = "_" . $openid;
    $postmeta = get_post_meta($postid, $openid, true);
    if (empty($postmeta)) {
        if(add_post_meta($postid, $openid, 'like', true)) {
            $result["code"] = "success";
            $result["message"] = "post like up success";
            $result["status"] = "200";
            return $result;
        } else {
            $result["code"] = "success";
            $result["message"] = "post like up error";
            $result["status"] = "500";
            return $result;
        }
    } else {
            $result["code"] = "success";
            $result["message"] = "you have liked up post";
            $result["status"] = "501";
            return $result;
    } 
} 
 
// 是否点赞 REST API
add_action('rest_api_init', function() {
	register_rest_route('apps/v1', 'like/get', array(
		'methods' => 'POST',
		'callback' => 'get_liked_post'
	));
});
function get_liked_post($request) {
    $openid = $request['openid'];
    $postid = $request['postid'];
    if(empty($openid) || empty($postid)) {
        return new WP_Error('error', 'openid or postid is empty', array('status' => 500));
    } elseif(get_post($postid) == null) {
         return new WP_Error('error', 'post id is error', array('status' => 500));
    } else { 
        if(!username_exists($openid)) {
            return new WP_Error('error', 'not allowed to submit', array('status' => 500));
        } else if(is_wp_error(get_post($postid))) {
            return new WP_Error('error', 'post id is error', array('status' => 500));
        } else {
            $data = post_liked_up_data($openid, $postid); 
            if (empty($data)) {
                return new WP_Error('error', 'post liked up error', array('status' => 404));
            }
            $response = new WP_REST_Response($data);
            $response->set_status(200); 
            return $response;
        }
    }
}
function post_liked_up_data($openid,$postid) {
    $openid = "_" . $openid; 
    $postmeta = get_post_meta($postid, $openid, true);
    if (!empty($postmeta)) {
        $result["code"]="success";
        $result["message"] = "you have liked up post";
        $result["status"] = "200";
        return $result;
    } else {
        $result["code"] = "success";
        $result["message"] = "you have not liked up post";
        $result["status"] = "501";
        return $result;
    }
}

//用户点赞的文章 REST API
add_action('rest_api_init', function () {
	register_rest_route('apps/v1', 'like/user', array(
		'methods' => 'GET',
		'callback' => 'getMyLikeUp'
	));
});
function getMyLikeUp($request) {
    $openid = $request['openid'];
    if(empty($openid)) {
        return new WP_Error('error', 'openid is empty', array('status' => 500));
    } else {
        if(!username_exists($openid)) {
            return new WP_Error('error', 'not allowed to submit', array('status' => 500));
        } else {
            $data=post_my_like_up_data($openid); 
            if (empty($data)) {
                return new WP_Error('error', 'post like up error', array('status' => 404));
            }
            $response = new WP_REST_Response($data);
            $response->set_status(200); 
            return $response;
        }
    }
}
function post_my_like_up_data($openid) {
    global $wpdb;
    $sql = "SELECT * from ".$wpdb->posts." where ID in (SELECT post_id from ".$wpdb->postmeta." where meta_value='like' and meta_key='_".$openid."') ORDER BY post_date desc LIMIT 20"; 
    $_posts = $wpdb->get_results($sql);
    $posts = array();
    foreach ($_posts as $post) {
        $_data["id"] = $post->ID;
        $_data["title"]["rendered"] = $post->post_title;
		if (empty(get_option('lite_meta'))) {
			$_data["thumbnail"] = get_post_thumbnail($post->ID);
			$_data["views"] = (int)get_post_meta($post->ID, 'views', true);
		} else {
			$_data["meta"]["thumbnail"] = get_post_thumbnail($post->ID);
			$_data['meta']["views"] = (int)get_post_meta($post->ID, 'views', true);
            $metaArr = explode(',', get_option('lite_meta'));
            foreach ($metaArr as $value) {
                $_data["meta"][$value] = get_post_meta($post->ID, $value , true);
            }
		}
        $posts[] = $_data;
    }
    $result["code"] = "success";
    $result["message"] = "get my like up post success";
    $result["status"] = "200";
    $result["data"] = $posts;
    return $result;
}

// 热门点赞 REST API
add_action('rest_api_init', function () {
  register_rest_route('apps/v1', 'like/hot', array(
    'methods' => 'GET',
    'callback' => 'getHotLikeUpPost'
 ));
});
function getHotLikeUpPost($data) {
	$data = get_hot_like_post_data(10); 
	if (empty($data)) {
		return new WP_Error('no posts', 'no posts', array('status' => 404));
	} 
	// Create the response object
	$response = new WP_REST_Response($data); 
	// Add a custom status code
	$response->set_status(200);
	return $response;
}
// Get Top Like Up this year 
function get_hot_like_post_data($limit) {
	global $wpdb, $post;
    $today = date("Y-m-d H:i:s"); // 获取今天日期时间
    $limit_date = date("Y-m-d H:i:s", strtotime("-1 year"));
	$sql = $wpdb->prepare("SELECT ".$wpdb->posts.".ID as ID, post_title, post_name, post_content, post_date, COUNT(".$wpdb->postmeta.".post_id) AS 'like_total' FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE ".$wpdb->postmeta.".meta_value='like' AND post_date BETWEEN '".$limit_date."'AND'".$today."'AND post_status ='publish' AND post_password = '' GROUP BY ".$wpdb->postmeta.".post_id ORDER BY like_total DESC LIMIT %d", $limit);
    $mostlike = $wpdb->get_results($sql);
    $posts = array();
    foreach ($mostlike as $post) {
		$post_id = (int) $post->ID;
        $post_title = stripslashes($post->post_title);
        $post_views = (int)get_post_meta($post_id, 'views', true);
		$sql_like = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='like' and post_id=%d",$post_id);
		$post_like = $wpdb->get_var($sql_like);
		$sql_comment = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->comments." where comment_approved = '1' and comment_post_ID = %d",$post_id);
		$post_comment = $wpdb->get_var($sql_comment);
		$post_date = $post->post_date;
        $post_permalink = get_permalink($post->ID);
		$post_thumbnail = get_post_thumbnail($post_id);
		$_data["id"] = $post_id;
        $_data["title"]["rendered"] = $post_title;
        $_data["like"] = $post_like;
		$_data['comments'] = $post_comment;
        $_data["date"] = $post_date; 
        $_data["link"] = $post_permalink;
		if (empty(get_option('lite_meta'))) {
			$_data["thumbnail"] = $post_thumbnail;
			$_data["views"] = $post_views;
		} else {
			$_data["meta"]["thumbnail"] = $post_thumbnail;
			$_data['meta']["views"] = $post_views;
            $metaArr = explode(',', get_option('lite_meta'));
            foreach ($metaArr as $value) {
                $_data["meta"][$value] = get_post_meta($post_id, $value, true);
            }
		}
        $posts[] = $_data;
    } 
	return $posts;
}