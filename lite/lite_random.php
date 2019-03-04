<?php

// 随机文章 REST API
add_action('rest_api_init', function () {
    register_rest_route('apps/v1', 'posts/random', array(
      'methods' => 'GET',
      'callback' => 'getRandomPosts'
   ));
});
function getRandomPosts($data) {
    $data = get_random_post_data(10);
    if (empty($data)) {
        return new WP_Error('noposts', 'noposts', array('status' => 404));
    }
    $response = new WP_REST_Response($data);
    $response->set_status(200);
    return $response;
}
function get_random_post_data($limit) {
    global $wpdb, $post;
    $today = date("Y-m-d H:i:s"); // 获取当天日期时间
    $limit_date = date("Y-m-d H:i:s", strtotime("-1 year")); // 获取指定日期时间
    $sql = $wpdb->prepare("SELECT ID, post_title, post_date FROM $wpdb->posts WHERE post_status = 'publish' AND post_title != '' AND post_password = '' AND post_type = 'post' ORDER BY RAND() LIMIT 0 , %d", $limit);
    $randposts = $wpdb->get_results($sql);
    $posts = array();
    foreach ($randposts as $post) {
        $post_id = (int)$post->ID;
        $post_title = stripslashes($post->post_title);
        $post_views = (int)get_post_meta($post_id, 'views', true);
        $post_date = $post->post_date;
        $post_permalink = get_permalink($post->ID);
        $post_thumbnail = get_post_thumbnail($post_id);
        $sql_like = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='like' and post_id = %d", $post_id);
        $post_like = $wpdb->get_var($sql_like);
        $sql_comment = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->comments." where comment_approved = '1' and comment_post_ID = %d", $post_id);
        $post_comment = $wpdb->get_var($sql_comment);
        $category = get_the_category($post_id);
        $categoryId = $category[0]->term_id;
        $_data['category'] = $category[0]->cat_name;
        $_data["id"] = $post_id;
        $_data["title"]["rendered"] = $post_title;
        $_data["date"] = $post_date;
        $_data["link"] = $post_permalink;
        $_data['comments'] = !empty($post_comment->total_comments) ? $post_comment->total_comments : '0';
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
