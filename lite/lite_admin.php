<?php

add_action('admin_menu', 'lite_admin_menu');
function lite_admin_menu() {
	add_submenu_page(
		'AppsDevTools',
		'小程序设置',
		'小程序助手',
		'manage_options',
		'lite',
		'lite_admin_page'
    );
    add_action('admin_init', 'reg_lite_value');
}

function reg_lite_value() {
    $group = array('appid', 'appsecretkey', 'swipe', 'focus', 'not_cate', 'meta', 'donate', 'donate_qrcode', 'comments', 'check_comments', 'ad_home', 'ad_home_code', 'ad_list', 'ad_list_code', 'ad_detail', 'ad_detail_code', 'video', 'user');
    foreach($group as $value) {
        register_setting('lite', 'lite_' . $value);
    }
}

function lite_admin_page() {
    if(!empty($_REQUEST['settings-updated'])) {
        echo '<div id="message" class="updated notice is-dismissible fade"><p><strong>设置已保存</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此通知</span></button></div>';
    }
?>

<div class="wrap">
	<h1>小程序设置</h1>
    <div style="width: 100%;max-width:720px;margin-top:20px;">
        <style>.tabs,.items{margin:0;}.tabs{overflow:hidden;padding:0 10px;height:34px;line-height:34px;background:#fff;border:solid 1px #e5e5e5;border-bottom:none;}.tab{padding: 0 5px;border-radius: 2px;cursor:pointer;float:left;height:24px;line-height:24px;margin:5px 10px 5px 0;color:#999;}.on{background: #e5e5e5;color:#006799;}.hide{display:none;}</style>
        <ul id="tabs" class="tabs">
            <li class="tab on">基本设置</li>
            <li class="tab">使用说明</li>
        </ul>
        <ul id="items" class="items">
            <li>
                <form method="post" action="options.php">
                    <?php settings_fields('lite'); ?>
                    <?php do_settings_sections('lite'); ?>
                    <table class="widefat">
                        <tbody id="the-list">
                            <tr class="alternate">
                                <th scope="row" style="padding-left:10px;">AppId</th>
                                <td>
                                    <input type="text" name="lite_appid" id="lite_appid" value="<?php echo get_option('lite_appid'); ?>" class="regular-text" placeholder="" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;">AppSecret</th>
                                <td>
                                    <input type="text" name="lite_appsecretkey" id="lite_appsecretkey" value="<?php echo get_option('lite_appsecretkey'); ?>" class="regular-text" placeholder="" />
                                </td>
                            </tr>
                            <tr class="alternate">
                                <th scope="row" style="padding-left:10px;">是否开启评论</th>
                                <td>
                                    <input type="checkbox" id="lite_comments" name="lite_comments" value="1" <?php echo get_option('lite_comments') ? 'checked' : ' '; ?>/>
                                    <label for="lite_comments">开启</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;">评论启用审核</th>
                                <td>
                                    <input type="checkbox" id="lite_check_comments" name="lite_check_comments" value="1" <?php echo get_option('lite_check_comments') ? 'checked' : ' '; ?>/>
                                    <label for="lite_check_comments">启用</label>
                                </td>
                            </tr>
                            <tr class="alternate">
                                <th scope="row" style="padding-left:10px;">腾讯视频解析</th>
                                <td>
                                    <input type="checkbox" id="lite_video" name="lite_video" value="1" <?php echo get_option('lite_video') ? 'checked' : ' '; ?>/>
                                    <label for="lite_video">开启</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;">禁用用户列表</th>
                                <td>
                                    <input type="checkbox" id="lite_user" name="lite_user" value="1" <?php echo get_option('lite_user') ? 'checked' : ' '; ?>/>
                                    <label for="lite_user">禁用</label>
                                </td>
                            </tr>
                            <tr class="alternate">
                                <th scope="row" style="padding-left:10px;">排除分类</th>
                                <td>
                                    <input type="text" name="lite_not_cate" id="lite_not_cate" value="<?php echo get_option('lite_not_cate'); ?>" class="regular-text" placeholder="请输入分类ID" />
                                </td>
                            </tr>
                            <tr class="alternate">
                                <th scope="row" style="padding-left:10px;"></th>
                                <td style="padding-top:0;">多个分类请用英文逗号(,)隔开，将过滤这些分类的所有结果。</td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;">轮播图片</th>
                                <td>
                                    <input type="text" name="lite_swipe" id="lite_swipe" value="<?php echo get_option('lite_swipe'); ?>" class="regular-text" placeholder="请输入文章ID" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;"></th>
                                <td style="padding-top:0;">多篇文章请用英文逗号(,)隔开，展示图片为文章焦点图；留空则不显示轮播模块。</td>
                            </tr>
                            <tr class="alternate">
                                <th scope="row" style="padding-left:10px;">默认缩略图</th>
                                <td>
                                    <input type="text" name="lite_focus" id="lite_focus" value="<?php echo get_option('lite_focus'); ?>" class="regular-text" placeholder="" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;">自定义字段</th>
                                <td>
                                    <input type="text" name="lite_meta" id="lite_meta" value="<?php echo get_option('lite_meta'); ?>" class="regular-text" placeholder="" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;"></th>
                                <td style="padding-top:0;">文章自定义字段输出，多个自定义字段请用英文逗号(,)隔开；若无需输出则留空。</td>
                            </tr>
                            <tr class="alternate">
                                <th scope="row" style="padding-left:10px;">开启赞赏码</th>
                                <td>
                                    <input type="checkbox" id="lite_donate" name="lite_donate" value="1" <?php echo get_option('lite_donate') ? 'checked' : ' '; ?>/>
                                    <label for="lite_donate">开启</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;">赞赏二维码</th>
                                <td>
                                    <input type="text" name="lite_donate_qrcode" id="lite_donate_qrcode" value="<?php echo get_option('lite_donate_qrcode'); ?>" class="regular-text" placeholder="请输入二维码地址" />
                                </td>
                            </tr>
                            <tr class="alternate">
                                <th scope="row" style="padding-left:10px;">首页广告</th>
                                <td>
                                    <input type="checkbox" id="lite_ad_home" name="lite_ad_home" value="1" <?php echo get_option('lite_ad_home') ? 'checked' : ' '; ?>/>
                                    <label for="lite_ad_home">开启</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;">首页广告ID</th>
                                <td>
                                    <input type="text" name="lite_ad_home_code" id="lite_ad_home_code" value="<?php echo get_option('lite_ad_home_code'); ?>" class="regular-text" placeholder="" />
                                </td>
                            </tr>
                            <tr class="alternate">
                                <th scope="row" style="padding-left:10px;">列表页广告</th>
                                <td>
                                    <input type="checkbox" id="lite_ad_list" name="lite_ad_list" value="1" <?php echo get_option('lite_ad_list') ? 'checked' : ' '; ?>/>
                                    <label for="lite_ad_list">开启</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;">列表页广告ID</th>
                                <td>
                                    <input type="text" name="lite_ad_list_code" id="lite_ad_list_code" value="<?php echo get_option('lite_ad_list_code'); ?>" class="regular-text" placeholder="" />
                                </td>
                            </tr>
                            <tr class="alternate">
                                <th scope="row" style="padding-left:10px;">详情页广告</th>
                                <td>
                                    <input type="checkbox" id="lite_ad_detail" name="lite_ad_detail" value="1" <?php echo get_option('lite_ad_detail') ? 'checked' : ' '; ?>/>
                                    <label for="lite_ad_detail">开启</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;">详情页广告ID</th>
                                <td>
                                    <input type="text" name="lite_ad_detail_code" id="lite_ad_detail_code" value="<?php echo get_option('lite_ad_detail_code'); ?>" class="regular-text" placeholder="" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row" style="padding-left:10px;"></th>
                                <td style="padding-top:0;">说明：只需填写小程序广告单元ID即可。<br>比如：&lt;ad unit-id="adunit-<span style="color:red;">a32c58049cc42bb3</span>"&gt;&lt;/ad&gt;只需填写红色部分。</td>
                            </tr>
                        </tbody>
                    </table>
                    <?php submit_button();?>
                </form>
            </li>
            <li class="hide">
                <table class="widefat">
                    <tbody id="the-list">
                        
                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"><strong>评论接口</strong></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/comment/config</td>
                            <td>评论开关</td>
                        </tr>
                        
                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/comment/hot</td>
                            <td>热门评论</td>
                        </tr>
                        
                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/comment/new</td>
                            <td>最新评论</td>
                        </tr>
                        
                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[P] <?php echo site_url();?>/wp-json/apps/v1/comment/add</td>
                            <td>创建评论</td>
                        </tr>
                        
                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/comment/get</td>
                            <td>我的评论</td>
                        </tr>
                        
                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/comment/list</td>
                            <td>评论列表</td>
                        </tr>
                        
                        <tr>
                            <th scope="row" style="padding-left:10px;"><strong>文章接口</strong></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/posts/random</td>
                            <td>随机文章</td>
                        </tr>
                        
                        <tr>
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/posts/swipe</td>
                            <td>轮播文章</td>
                        </tr>
                        
                        <tr>
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/posts/views/{postID}</td>
                            <td>阅读统计</td>
                        </tr>
                        
                        <tr>
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/posts/hot</td>
                            <td>热门文章</td>
                        </tr>

                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"><strong>点赞接口</strong></th>
                            <td>[P] <?php echo site_url();?>/wp-json/apps/v1/like/up</td>
                            <td>点赞统计</td>
                        </tr>

                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[P] <?php echo site_url();?>/wp-json/apps/v1/like/get</td>
                            <td>是否点赞</td>
                        </tr>

                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/like/user</td>
                            <td>我的点赞</td>
                        </tr>

                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/like/hot</td>
                            <td>热门点赞</td>
                        </tr>
                        
                        <tr>
                            <th scope="row" style="padding-left:10px;"><strong>用户接口</strong></th>
                            <td>[P] <?php echo site_url();?>/wp-json/apps/v1/user/openid</td>
                            <td>获取openid</td>
                        </tr>
                        
                        <tr>
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/user/get</td>
                            <td>获取UserId</td>
                        </tr>
                        
                        <tr>
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[P] <?php echo site_url();?>/wp-json/apps/v1/user/subscribe/sub</td>
                            <td>订阅栏目</td>
                        </tr>
                        
                        <tr>
                            <th scope="row" style="padding-left:10px;"></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/user/subscribe/get</td>
                            <td>我的订阅</td>
                        </tr>
                        
                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"><strong>消息接口</strong></th>
                            <td>[P] <?php echo site_url();?>/wp-json/apps/v1/message/send</td>
                            <td>发送消息</td>
                        </tr>

                        <tr>
                            <th scope="row" style="padding-left:10px;"><strong>广告接口</strong></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/ad/config</td>
                            <td>广告配置</td>
                        </tr>

                        <tr class="alternate">
                            <th scope="row" style="padding-left:10px;"><strong>赞助接口</strong></th>
                            <td>[G] <?php echo site_url();?>/wp-json/apps/v1/donate/config</td>
                            <td>赞助配置</td>
                        </tr>
                        
                        <tr>
                            <th scope="row" style="padding-left:10px;"><strong>海报接口</strong></th>
                            <td>[P] <?php echo site_url();?>/wp-json/apps/v1/qrcode/creat</td>
                            <td>生成海报</td>
                        </tr>

                    </tbody>
                </table>
            </li>
        </ul>
        <script>
        (function() {
            var _tabs = document.getElementById("tabs");
            var _li = _tabs.getElementsByTagName("li");
            var _items = document.getElementById("items");
            var _it = _items.getElementsByTagName("li");
            for(var i = 0; i < _li.length; i++) {
                _li[i].index = i;
                _li[i].onclick = function() {
                    for(var x = 0; x < _li.length; x++) {
                        _li[x].className = 'tab';
                    }
                    this.className = 'tab on';
                    for(var x = 0; x < _it.length; x++) {
                        _it[x].className = 'hide';
                    }
                    _it[this.index].className = '';
                }
            }
        })();
        </script>
    </div>
</div>

<?php
}