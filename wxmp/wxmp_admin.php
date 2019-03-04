<?php

// 获取设置信息
function get_weixinmp_option() {

    $array_weixinmp_option                               = array();
    $array_weixinmp_option[WXMP_TOKEN]                   = stripslashes(get_option(WXMP_TOKEN));
    $array_weixinmp_option[WXMP_APPID]                   = stripslashes(get_option(WXMP_APPID));
    $array_weixinmp_option[WXMP_ENCODINGAESKEY]          = stripslashes(get_option(WXMP_ENCODINGAESKEY));
    $array_weixinmp_option[WXMP_WELCOME]                 = stripslashes(get_option(WXMP_WELCOME));
    $array_weixinmp_option[WXMP_WELCOME_CMD]             = stripslashes(get_option(WXMP_WELCOME_CMD));
    $array_weixinmp_option[WXMP_HELP]                    = stripslashes(get_option(WXMP_HELP));
    $array_weixinmp_option[WXMP_HELP_CMD]                = stripslashes(get_option(WXMP_HELP_CMD));
    $array_weixinmp_option[WXMP_KEYWORD_LENGTH]          = get_option(WXMP_KEYWORD_LENGTH);
    $array_weixinmp_option[WXMP_AUTO_REPLY]              = get_option(WXMP_AUTO_REPLY);
    $array_weixinmp_option[WXMP_KEYWORD_IN_TITLE]        = get_option(WXMP_KEYWORD_IN_TITLE);
    $array_weixinmp_option[WXMP_KEYWORD_IN_CONTENT]      = get_option(WXMP_KEYWORD_IN_CONTENT);
    $array_weixinmp_option[WXMP_CATEGORY_EXCLUDE]        = get_option(WXMP_CATEGORY_EXCLUDE);
    $array_weixinmp_option[WXMP_KEYWORD_LENGTH_WARNING]  = stripslashes(get_option(WXMP_KEYWORD_LENGTH_WARNING));
    $array_weixinmp_option[WXMP_KEYWORD_ERROR_WARNING]   = stripslashes(get_option(WXMP_KEYWORD_ERROR_WARNING));
    $array_weixinmp_option[WXMP_DEFAULT_ARTICLE_ACCOUNT] = get_option(WXMP_DEFAULT_ARTICLE_ACCOUNT);
    $array_weixinmp_option[WXMP_NEW_ARTICLE_CMD]         = stripslashes(get_option(WXMP_NEW_ARTICLE_CMD));
    $array_weixinmp_option[WXMP_RAND_ARTICLE_CMD]        = stripslashes(get_option(WXMP_RAND_ARTICLE_CMD));
    $array_weixinmp_option[WXMP_HOT_ARTICLE_CMD]         = stripslashes(get_option(WXMP_HOT_ARTICLE_CMD));
    $array_weixinmp_option[WXMP_CMD_SEPERATOR]           = stripslashes(get_option(WXMP_CMD_SEPERATOR));
    $array_weixinmp_option[WXMP_DEFAULT_THUMB]           = stripslashes(get_option(WXMP_DEFAULT_THUMB));

    return $array_weixinmp_option;

}

// 更新设置信息
function update_weixinmp_option() {

    if($_POST['action'] == '保存设置') {

        update_option(WXMP_TOKEN, $_POST['wxmp-token']);
        update_option(WXMP_APPID, $_POST['wxmp-appid']);
        update_option(WXMP_ENCODINGAESKEY, $_POST['wxmp-encodingaeskey']);
        update_option(WXMP_WELCOME, $_POST['wxmp-welcome']);
        update_option(WXMP_WELCOME_CMD, $_POST['wxmp-welcome-cmd']);
        update_option(WXMP_HELP, $_POST['wxmp-help']);
        update_option(WXMP_HELP_CMD, $_POST['wxmp-help-cmd']);
        update_option(WXMP_KEYWORD_LENGTH, $_POST['wxmp-keyword-length']);

        $auto_reply = isset($_POST['wxmp-auto-reply']) ? $_POST['wxmp-auto-reply'] : 0;
        if($auto_reply != 1 ) {$auto_reply = 0;}
        update_option(WXMP_AUTO_REPLY, $auto_reply);

        $keyword_in_title = isset($_POST['wxmp-keyword-in-title']) ? $_POST['wxmp-keyword-in-title'] : 0;
        if($keyword_in_title != 1){$keyword_in_title = 0;}
        update_option(WXMP_KEYWORD_IN_TITLE, $keyword_in_title);

        $keyword_in_content = isset($_POST['wxmp-keyword-in-content']) ? $_POST['wxmp-keyword-in-content'] : 0;
        if($keyword_in_content != 1){$keyword_in_content = 0;}
        update_option(WXMP_KEYWORD_IN_CONTENT, $keyword_in_content);

        update_option(WXMP_CATEGORY_EXCLUDE, $_POST['wxmp-category-exclude']);
        update_option(WXMP_KEYWORD_LENGTH_WARNING, $_POST['wxmp-keyword-length-warning']);
        update_option(WXMP_KEYWORD_ERROR_WARNING, $_POST['wxmp-keyword-error-warning']);

        $default_article_account = isset($_POST['wxmp-default-article-account']) ? $_POST['wxmp-default-article-account'] : 6;
        update_option(WXMP_DEFAULT_ARTICLE_ACCOUNT, $default_article_account);
        
        update_option(WXMP_NEW_ARTICLE_CMD, $_POST['wxmp-new-article-cmd']);
        update_option(WXMP_RAND_ARTICLE_CMD, $_POST['wxmp-rand-article-cmd']);
        update_option(WXMP_HOT_ARTICLE_CMD, $_POST['wxmp-hot-article-cmd']);
        update_option(WXMP_CMD_SEPERATOR, $_POST['wxmp-cmd-seperator']);
        update_option(WXMP_DEFAULT_THUMB, $_POST['wxmp-default-thumb']);

    }

    weixinmp_topbarmessage('配置已更新');

}

// 信息提示状态
function weixinmp_topbarmessage($msg) {
     echo '<div id="message" class="updated notice is-dismissible fade"><p><strong>'.$msg.'</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此通知</p></button></div>';
}

// 插件设置页面
add_action('admin_menu', 'wxmp_admin_menu');
function wxmp_admin_menu() {
	add_submenu_page(
		'AppsDevTools',
		'公众号设置',
		'公众号助手',
		'manage_options',
		'wxmp',
		'wxmp_admin_page'
	);
}

function wxmp_admin_page() {

?>

<div class="wrap">

    <h1>公众号设置</h1>

    <?php 
        if(isset($_POST['action'])) {
			if($_POST['action'] == '保存设置') {
				update_weixinmp_option();
			}
		}
        $array_weixinmp_option = get_weixinmp_option();
    ?>

    <div style="width: 100%;max-width:720px;margin-top:15px;">

        <form name="wxmp-options" method="post" action="">

            <table class="widefat">
				<thead>
					<tr>
						<th scope="col">类型</th>
						<th scope="col">值</th>
					</tr>
				</thead>
                <tbody id="the-list">

                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;">接口Token</th>
                        <td>
                            <input type="text" name="wxmp-token" value="<?php echo $array_weixinmp_option[WXMP_TOKEN]; ?>" class="regular-text" placeholder="如：weixin" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" style="padding-left:10px;">微信AppId</th>
                        <td>
                            <input type="text" name="wxmp-appid" value="<?php echo $array_weixinmp_option[WXMP_APPID]; ?>" class="regular-text" />
                        </td>
                    </tr>

                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;">EncodingAesKey</th>
                        <td>
                            <input type="text" name="wxmp-encodingaeskey" value="<?php echo $array_weixinmp_option[WXMP_ENCODINGAESKEY]; ?>" class="regular-text" />
                        </td>
                    </tr>

					<tr class="alternate">
						<th scope="row" style="padding-left:10px;"></th>
						<td style="padding-top:0;">注：AppId和EncodingAesKey任一为空时，不可用信息加密。</td>
					</tr>

                    <tr>
                        <th scope="row" style="padding-left:10px;">欢迎信息</th>
                        <td>
                            <textarea name="wxmp-welcome" class="regular-text" placeholder="填写用于用户订阅时发送的欢迎信息"><?php echo $array_weixinmp_option[WXMP_WELCOME]; ?></textarea>
                        </td>
                    </tr>

                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;">欢迎命令</th>
                        <td>
                            <input type="text" name="wxmp-welcome-cmd" value="<?php echo $array_weixinmp_option[WXMP_WELCOME_CMD]; ?>" class="regular-text" placeholder="如：welcome" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" style="padding-left:10px;">帮助信息</th>
                        <td>
                            <textarea name="wxmp-help" class="regular-text" placeholder="填写用于用户寻求帮助时的帮助信息"><?php echo $array_weixinmp_option[WXMP_HELP]; ?></textarea>
                        </td>
                    </tr>

                     <tr class="alternate">
                        <th scope="row" style="padding-left:10px;">帮助命令</th>
                        <td>
                            <input type="text" name="wxmp-help-cmd" value="<?php echo $array_weixinmp_option[WXMP_HELP_CMD]; ?>" class="regular-text" placeholder="如：help" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" style="padding-left:10px;">关键字长度</th>
                        <td>
                            <input type="text" name="wxmp-keyword-length" value="<?php echo $array_weixinmp_option[WXMP_KEYWORD_LENGTH]; ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;">是否自动回复</th>
                        <td>
                            <input type="checkbox" id="wxmp-auto-reply" name="wxmp-auto-reply" value="1" <?php echo $array_weixinmp_option[WXMP_AUTO_REPLY] ? 'checked' : ' '; ?>/>
							<label for="wxmp-auto-reply">开启</label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" style="padding-left:10px;">关键字长度提醒</th>
                        <td>
                            <textarea name="wxmp-keyword-length-warning" class="regular-text"><?php echo $array_weixinmp_option[WXMP_KEYWORD_LENGTH_WARNING]; ?></textarea>
                        </td>
                    </tr>

					<tr>
						<th scope="row" style="padding-left:10px;"></th>
						<td style="padding-top:0;">当关键字长度超出限制时，回复用户错误信息，结合上面“是否自动回复”使用。</td>
					</tr>

                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;">关键字查询范围</th>
                        <td>
                            <input type="checkbox" id="wxmp-keyword-in-title" name="wxmp-keyword-in-title" value="1" <?php echo $array_weixinmp_option[WXMP_KEYWORD_IN_TITLE] ? 'checked' : ' '; ?>/>
							<label for="wxmp-keyword-in-title" style="margin-right:30px;">标题</label>
                            <input type="checkbox" id="wxmp-keyword-in-content" name="wxmp-keyword-in-content" value="1" <?php echo $array_weixinmp_option[WXMP_KEYWORD_IN_CONTENT] ? 'checked' : ' ' ?>/>
							<label for="wxmp-keyword-in-content">内容</label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" style="padding-left:10px;">排除分类</th>
                        <td>
                            <input type="text" name="wxmp-category-exclude" value="<?php echo $array_weixinmp_option[WXMP_CATEGORY_EXCLUDE]; ?>" class="regular-text" />
                        </td>
                    </tr>

					<tr>
						<th scope="row" style="padding-left:10px;"></th>
						<td style="padding-top:0;">排除某分类内容，多个分类请用英文逗号(,)隔开，留空表示查询所有分类内容。</td>
					</tr>

                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;">关键字错误提醒</th>
                        <td>
                            <textarea name="wxmp-keyword-error-warning" class="regular-text"><?php echo $array_weixinmp_option[WXMP_KEYWORD_ERROR_WARNING]; ?></textarea>
                        </td>
                    </tr>

                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;"></th>
                        <td style="padding-top:0;">关键词不存在时，回复错误信息，信息中用户输入的关键词用“{keyword}”表示。</td>
                    </tr>

                    <tr>
                        <th scope="row" style="padding-left:10px;">默认文章数</th>
                        <td>
                            <input type="text" name="wxmp-default-article-account" value="<?php echo $array_weixinmp_option[WXMP_DEFAULT_ARTICLE_ACCOUNT]; ?>" class="regular-text" />
                        </td>
                    </tr>

					<tr>
						<th scope="row" style="padding-left:10px;"></th>
						<td style="padding-top:0;">文章返回数量，在不用命令分隔符指定返回数量时返回的文章数量，最多8篇。</td>
					</tr>

                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;">最新文章命令</th>
                        <td>
                            <input type="text" name="wxmp-new-article-cmd" value="<?php echo $array_weixinmp_option[WXMP_NEW_ARTICLE_CMD]; ?>" class="regular-text" placeholder="如：new" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" style="padding-left:10px;">随机文章命令</th>
                        <td>
                            <input type="text" name="wxmp-rand-article-cmd" value="<?php echo $array_weixinmp_option[WXMP_RAND_ARTICLE_CMD]; ?>" class="regular-text" placeholder="如：random" />
                        </td>
                    </tr>

                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;">热门文章命令</th>
                        <td>
                            <input type="text" name="wxmp-hot-article-cmd" value="<?php echo $array_weixinmp_option[WXMP_HOT_ARTICLE_CMD]; ?>" class="regular-text" placeholder="如：hot" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" style="padding-left:10px;">命令分隔符</th>
                        <td>
                            <input type="text" name="wxmp-cmd-seperator" value="<?php echo $array_weixinmp_option[WXMP_CMD_SEPERATOR]; ?>" class="regular-text" />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" style="padding-left:10px;"></th>
                        <td style="padding-top:0;">支持“关键词@6”命令，其中“@”为命令分隔符，后面数字指文章数，最多8篇。</td>
                    </tr>

                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;">默认缩略图</th>
                        <td>
                            <input type="text" name="wxmp-default-thumb" value="<?php echo $array_weixinmp_option[WXMP_DEFAULT_THUMB]; ?>" class="regular-text" />
                        </td>
                    </tr>

                    <tr class="alternate">
                        <th scope="row" style="padding-left:10px;"></th>
                        <td style="padding-top:0;">默认缩略图地址，当文章中没有图片时，则使用该缩略图代替。</td>
                    </tr>

                </tbody>
            </table>
			<p class="submit wxmp-center wxmp-btn-box"><input type="submit" name="action" id="submit" class="button button-primary wxmp-submit-btn" value="保存设置"></p>
        </form>
    </div>
</div>

<?php 
}