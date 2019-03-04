<?php

add_action('admin_menu', 'other_admin_menu');
function other_admin_menu() {
	add_submenu_page(
		'AppsDevTools',
		'其它优化设置',
		'其它优化',
		'manage_options',
		'other',
		'other_admin_page'
    );
    add_action('admin_init', 'reg_other_value');
}

function reg_other_value() {
    $group = array('privacy', 'fonts');
    foreach($group as $value) {
        register_setting('other', 'other_' . $value);
    }
}

function other_admin_page() {
    if(!empty($_REQUEST['settings-updated'])) {
        echo '<div id="message" class="updated notice is-dismissible fade"><p><strong>设置已保存</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此通知</span></button></div>';
    }
?>

<div class="wrap">
    <h1>其它优化设置</h1>
    <div style="width: 100%;max-width:720px;margin-top:20px;">
    <form method="post" action="options.php">
        <?php settings_fields('other'); ?>
        <?php do_settings_sections('other'); ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th scope="col">类型</th>
                    <th scope="col">值</th>
                </tr>
            </thead>
            <tbody id="the-list">
                <tr class="alternate">
                    <th scope="row" style="padding-left:10px;">隐私菜单</th>
                    <td>
                        <input type="checkbox" name="other_privacy" id="other_privacy" value="true" <?php echo empty(get_option('other_privacy')) ? '' : 'checked="checked"'; ?> />
                        <label for="other_privacy">移除</label>
                    </td>
                </tr>
                <tr class="alternate">
                    <th scope="row" style="padding-left:10px;"></th>
                    <td style="padding-top:0;">移除后台隐私相关的页面。</td>
                </tr>
                <tr>
                    <th scope="row" style="padding-left:10px;">Google Fonts</th>
                    <td>
                        <input type="checkbox" name="other_fonts" id="other_fonts" value="true" <?php echo empty(get_option('other_fonts')) ? '' : 'checked="checked"'; ?> />
                        <label for="other_fonts">移除</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="padding-left:10px;"></th>
                    <td style="padding-top:0;">移除后台Google字体链接。</td>
                </tr>
            </tbody>
        </table>
        <?php submit_button();?>
    </form>
    </div>
</div>

<?php
}