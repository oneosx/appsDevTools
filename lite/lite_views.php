<?php

// 阅读统计 REST API
add_action('rest_api_init', function () {
	register_rest_route('apps/v1', 'posts/views/(?P<id>\d+)', array(
	  'methods' => 'GET',
	  'callback' => 'updateViews'
	));
});
function updateViews($data) {
	$post_id = $data['id'];
	if(!is_numeric($post_id)) {
		return new WP_Error('error', 'id is not numeric', array('status' => 500));
	} else if(get_post($post_id)==null){
		return new WP_Error('error', 'post id is error', array('status' => 500));
	} else {
		$data=post_update_views($post_id); 
		if (empty($data)) {
			return new WP_Error('error', 'no find post', array('status' => 404));
		}
		$response = new WP_REST_Response($data);
		$response->set_status(200);
		return $response;
	}
}
function post_update_views($post_id) {
	$posts = get_post($post_id);
	if (empty($posts)) {
		return null;
	} else {
		$post_views = (int)get_post_meta($post_id, 'views', true);
		if(!update_post_meta($post_id, 'views', ($post_views + 1))) {
			add_post_meta($post_id, 'views', 1, true);
		} 
		$result = array();
		$result["code"] = "success";
		$result["message"] = "update posts views success";
		$result["status"] = "200";
		return $result;
	}
}

// 热门阅读 REST API
add_action('rest_api_init', function () {
	register_rest_route('apps/v1', 'posts/hot', array(
		'methods' => 'GET',
		'callback' => 'getHotViewsPosts'
	));
});
function getHotViewsPosts($data) {
	$data = get_hot_views_post_data(10); 
	if (empty($data)) {
		return new WP_Error('no posts', 'no posts', array('status' => 404));
	}
	$response = new WP_REST_Response($data);
	$response->set_status(200);
	return $response;
}
function get_hot_views_post_data($limit) {
	global $wpdb, $post;
	$today = date("Y-m-d H:i:s"); // 获取当天日期时间
	$limit_date = date("Y-m-d H:i:s", strtotime("-1 year")); // 获取指定日期时间
	$sql = $wpdb->prepare("SELECT ".$wpdb->posts.".ID as ID, post_title, post_name, post_content, post_date, CONVERT(".$wpdb->postmeta.".meta_value, SIGNED) AS 'views_total' FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE ".$wpdb->postmeta.".meta_key = 'views' AND post_date BETWEEN '".$limit_date."' AND '".$today."' AND post_status = 'publish' AND post_password = '' ORDER BY views_total DESC LIMIT %d",$limit);
	$hotviews = $wpdb->get_results($sql);
	$posts = array();
	foreach ($hotviews as $post) {
		$post_id = (int) $post->ID;
		$post_title = stripslashes($post->post_title);
		$post_views = (int)$post->views_total;
		$post_date = $post->post_date;
		$post_permalink = get_permalink($post->ID);
		$post_thumbnail = get_post_thumbnail($post_id);
		$sql_like = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='like' and post_id = %d", $post_id);
		$post_like = $wpdb->get_var($sql_like);
		$sql_comment = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->comments." where comment_approved = '1' and comment_post_ID = %d", $post_id);
		$post_comment = $wpdb->get_var($sql_comment);
		$_data["id"] = $post_id;
		$_data["title"]["rendered"] = $post_title;
		$_data["date"] = $post_date;
		$_data["link"] = $post_permalink;
		$_data['comments'] = $post_comment;
		$_data['like'] = $post_like;
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