<?php

/*
 * 基于WP-REST-API（Version：3.0）开发
 * Author    : jianbo + 艾码汇
 * Author URI: https://github.com/dchijack/WP-REST-API-PRO
*/

function get_post_thumbnail($post_id) {
    $post = get_post($post_id);
	$thumbnails = get_post_meta($post_id, 'thumbnail', true);
    if(has_post_thumbnail()) {
        $post_thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
        return $post_thumbnail[0];
    } elseif (!empty($thumbnails)) {
		$post_thumbnail = $thumbnails;
		return $post_thumbnail;
	} else { 
		$post_thumbnail = '';
		ob_start();
		ob_end_clean();
		$post_images = preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
		if(!empty($matches[1])) {
			$path_parts = pathinfo($post_img_src);
			$first_img_name = $path_parts["basename"];
			$expired = 604800;
			$post_thumbnail = $post_img_src;
		} else {
			$post_thumbnail = get_option('lite_focus');
		}
		return $post_thumbnail;
    }
}

function time_tran($the_time) {
    $now_time = date("Y-m-d H:i:s", time() + 8 * 60 * 60); 
    $now_time = strtotime($now_time);
    $show_time = strtotime($the_time);
    $dur = $now_time - $show_time;
    if ($dur < 0) {
        return $the_time; 
    } else {
        if ($dur < 60) {
            return $dur.'秒前'; 
        } else {
            if ($dur < 3600) {
				return floor($dur/60).'分钟前'; 
			} else {
				if ($dur < 86400) {
					return floor($dur/3600).'小时前';
				} else {
					if ($dur < 259200) {//3天内
						return floor($dur/86400).'天前';
					} else {
						return date("Y-m-d",$show_time); 
					}
				}
			}
		}
	}
}

// 获取文章数据
function get_content_post($url, $post_data = array(), $header = array()) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    $content = curl_exec($ch);
    $info = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($code == "200") {
        return $content;
    } else {
        return "error";
    }
}
// 发起 HTTPS 请求
function https_request($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl,  CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);  
    $data = curl_exec($curl);
    if (curl_errno($curl)){
        return 'ERROR';
    }
    curl_close($curl);
    return $data;
}

require_once('lite_admin.php');

$incFiles = array('advertisement', 'comment', 'message', 'openid', 'posts', 'qrcode', 'random', 'subscribe', 'swipe', 'like', 'user', 'views', 'donate', 'card', 'api');
foreach ($incFiles as $value) {
    include(APPSDEVTOOLS_PLUGIN_DIR . "lite/lite_$value.php");
}