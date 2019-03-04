<?php

function Apps_admin_page() {
    
    if(!empty($_REQUEST['settings-updated'])) {
        echo '<div id="message" class="updated notice is-dismissible fade"><p><strong>设置已保存</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此通知</span></button></div>';
    }
?>

<div class="wrap">
    <h1>功能设置</h1>
    <div style="width: 100%;max-width:720px;margin-top:20px;">
    <form method="post" action="options.php">
        <?php settings_fields('appsDevTools'); ?>
        <?php do_settings_sections('appsDevTools'); ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th scope="col">功能</th>
                    <th scope="col">状态</th>
                </tr>
            </thead>
            <tbody id="the-list">
                <tr class="alternate">
                    <th scope="row" style="padding-left:10px;">小程序助手</th>
                    <td>
                        <input type="checkbox" name="appsDevTools_lite" id="appsDevTools_lite" value="true" <?php echo empty(get_option('appsDevTools_lite')) ? '' : 'checked="checked"'; ?> />
                        <label for="appsDevTools_lite">开启</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="padding-left:10px;">公众号助手</th>
                    <td>
                        <input type="checkbox" name="appsDevTools_wxmp" id="appsDevTools_wxmp" value="true" <?php echo empty(get_option('appsDevTools_wxmp')) ? '' : 'checked="checked"'; ?> />
                        <label for="appsDevTools_wxmp">开启</label>
                    </td>
                </tr>
                <tr class="alternate">
                    <th scope="row" style="padding-left:10px;">其 它 优 化</th>
                    <td>
                        <input type="checkbox" name="appsDevTools_other" id="appsDevTools_other" value="true" <?php echo empty(get_option('appsDevTools_other')) ? '' : 'checked="checked"'; ?> />
                        <label for="appsDevTools_other">开启</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="padding-left:10px;">冗 余 清 理</th>
                    <td>
                        <input type="checkbox" name="appsDevTools_clean" id="appsDevTools_clean" value="true" <?php echo empty(get_option('appsDevTools_clean')) ? '' : 'checked="checked"'; ?> />
                        <label for="appsDevTools_clean">开启</label>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button();?>
    </form>
    </div>
</div>

<?php
}