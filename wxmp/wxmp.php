<?php

/*
 * 基于WeixinPress（Version：0.9.0）开发
 * Author    : Will HQ
 * Author URI: http://xsoft.cc
*/

define('WXMP_TOKEN'                   , 'wxmp_token');
define('WXMP_APPID'                   , 'wxmp_appid');
define('WXMP_ENCODINGAESKEY'          , 'wxmp_encodingaeskey');
define('WXMP_WELCOME'                 , 'wxmp_welcome');
define('WXMP_WELCOME_CMD'             , 'wxmp_welcome_cmd');
define('WXMP_HELP'                    , 'wxmp_help');
define('WXMP_HELP_CMD'                , 'wxmp_help_cmd');
define('WXMP_KEYWORD_LENGTH'          , 'wxmp_keyword_length');
define('WXMP_AUTO_REPLY'              , 'wxmp_auto_reply');
define('WXMP_KEYWORD_IN_TITLE'        , 'wxmp_keyword_in_title');
define('WXMP_KEYWORD_IN_CONTENT'      , 'wxmp_keyword_in_content');
define('WXMP_CATEGORY_EXCLUDE'        , 'wxmp_category_exclude');
define('WXMP_KEYWORD_LENGTH_WARNING'  , 'wxmp_keyword_length_warning');
define('WXMP_KEYWORD_ERROR_WARNING'   , 'wxmp_keyword_error_warning');
define('WXMP_DEFAULT_ARTICLE_ACCOUNT' , 'wxmp_default_article_account');
define('WXMP_NEW_ARTICLE_CMD'         , 'wxmp_new_article_cmd');
define('WXMP_RAND_ARTICLE_CMD'        , 'wxmp_rand_article_cmd');
define('WXMP_HOT_ARTICLE_CMD'         , 'wxmp_hot_article_cmd');
define('WXMP_CMD_SEPERATOR'           , 'wxmp_cmd_seperator');
define('WXMP_DEFAULT_THUMB'           , 'wxmp_default_thumb');

define('WXMP_FOLDER'                  , dirname(plugin_basename(__FILE__)));
define('WXMP_URL'			  		  , plugins_url('', __FILE__));
define('WXMP_FILE_PATH'               , dirname(__FILE__));
define('WXMP_DIR_NAME'                , basename(WXMP_FILE_PATH));

// 默认缩略图
$wxmp_thumb = get_option(WXMP_DEFAULT_THUMB);
if(empty($wxmp_thumb)){$wxmp_thumb = WXMP_URL.'/style/img/focus.png';}
define('WXMP_DEFAULT_THUMB_LINK'      , $wxmp_thumb);

function logs($content) {
    file_put_contents(WXMP_FILE_PATH."/logs.txt", date('Y-m-d H:i:s').'#'.var_export($content, true)."\r\n", FILE_APPEND);
}

require_once('wxmp_admin.php');
require_once('wxmp_return.php');
require_once('sdk/wxBizMsgCrypt.php');

if(isset($_GET["signature"])) {
    global $weixinmp;
   	if(!isset($weixinmp)){
    	$weixinmp = new WeixinMp();
    	$weixinmp->valid();
    	exit;
    }
}

class WeixinMp {
    private $items          = '';
    private $articleCount   = 0;
    private $keyword        = '';
    private $arg            = '';
    private $cmd_seperator  = '@';
    private $token          = '';
    private $signature      = '';
    private $timestamp      = '';
    private $nonce          = '';
    private $encodingAesKey = '';
    private $appId          = '';
    private $_receive;

    public function valid() {

        $echoStr = isset($_GET["echostr"]) ? $_GET["echostr"] : '';

        if ($echoStr) {
            if ($this->checkSignature()) {
                die($echoStr);
            } else {
                die('no access');
            }
        } else {
            if ($this->checkSignature()) {
                $this->responseMsg();
            } else {
                die('no access');
            }
        }
        exit;
    }
    
    public function responseMsg() {
    	// 获取后台的各种配置
        $array_weixinmp_option      = get_weixinmp_option();
        // 获取欢迎命令的配置
        $array_weixinmp_welcome_cmd = explode(' ', $array_weixinmp_option[WXMP_WELCOME_CMD]);
        // 获取帮助命令的配置
        $array_weixinmp_help_cmd    = explode(' ', $array_weixinmp_option[WXMP_HELP_CMD]);
        // 获取新文章命令的配置
        $array_weixinmp_new_cmd     = explode(' ', $array_weixinmp_option[WXMP_NEW_ARTICLE_CMD]);
        // 获取随机文章命令的配置
        $array_weixinmp_rand_cmd    = explode(' ', $array_weixinmp_option[WXMP_RAND_ARTICLE_CMD]);
        // 获取热门文章明林的配置
        $array_weixinmp_hot_cmd     = explode(' ', $array_weixinmp_option[WXMP_HOT_ARTICLE_CMD]);
        // 获取关键词长度限制
        $wxmp_keyword_length            = $array_weixinmp_option[WXMP_KEYWORD_LENGTH];
        // 获取是否自动回复的设置
        $wxmp_auto_reply                = $array_weixinmp_option[WXMP_AUTO_REPLY];
        // 获取关键词长度提醒信息
        $wxmp_keyword_length_warning    = $array_weixinmp_option[WXMP_KEYWORD_LENGTH_WARNING];
        // 获取关键词错误提醒信息
        $wxmp_keyword_error_warning     = $array_weixinmp_option[WXMP_KEYWORD_ERROR_WARNING];
        // 获取关键词和命令之间分隔符设置
        $wxmp_cmd_seperator             = $array_weixinmp_option[WXMP_CMD_SEPERATOR];
        $this->cmd_seperator           = $wxmp_cmd_seperator;
       
        // 获取从微信端POST过来的信息
        // 另外一种获取POST数据的方式是：
        // $postStrs = $GLOBALS['HTTP_RAW_POST_DATA'];

        $postStr = file_get_contents("php://input");

        // 提取解析微信端POST的数据信息
        if (!empty($postStr)) {

            $postObj = $postStr;
            // appId或encodingAesKey，有一个为空的话，则采用明文的方式
            // 否则的话，则采用加密的方式
            // 后面用一个开关变量控制是加密还是非加密
            if(!empty($this->appId) && !empty($this->encodingAesKey)) {
 
                // 加密的模式
                $pc = new wxmpWXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);
                
                // 目前在wxBizMsgCrypt.php中没有验证signature
                $msg_signature = $_GET["msg_signature"];
                
                $errCode = $pc->decryptMsg($msg_signature, $timeStamp, $nonce, $postStr, $postObj);

                if($errCode != 0) {
                    die('decrypt msg error');
                    exit;
                }

            }

            $postObj = simplexml_load_string($postObj, 'SimpleXMLElement', LIBXML_NOCDATA);
            
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $msgType = strtolower(trim($postObj->MsgType));
            if($msgType == 'event') {
                $keywords = strtolower(trim($postObj->Event));
            } else {
                $keywords = strtolower(trim($postObj->Content));
            }

            // 处理关键词之后是否带命令参数arg，比如@2，表示返回2篇相关文章
            $keywordArray = explode($wxmp_cmd_seperator, $keywords, 2);
            if(is_array($keywordArray)) {
                $this->keyword = $keywordArray[0];
                $this->arg = $keywordArray[1];
            } else {
                $this->keyword = $keywordArray;
            }

            $time = time();
            $textTpl = '<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%d</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        </xml>';
            $picTpl = ' <xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%d</CreateTime>
                        <MsgType><![CDATA[news]]></MsgType>
                        <Content><![CDATA[]]></Content>
                        <ArticleCount>%d</ArticleCount>
                        <Articles>
                        %s
                        </Articles>
                        </xml>';
            
            if(strpos($this->keyword, '/:') === false) {
                
                if((count($array_weixinmp_welcome_cmd) > 0) && (in_array($this->keyword, $array_weixinmp_welcome_cmd) || $this->keyword == 'subscribe' )) {
                    // 订阅欢迎信息
                    $weixin_welcome = $array_weixinmp_option[WXMP_WELCOME];
                    $weixin_welcome = apply_filters('weixin_welcome',$weixin_welcome);
					$replyMsg = sprintf($textTpl, $fromUsername, $toUsername, $time, $weixin_welcome);
					
                } elseif((count($array_weixinmp_welcome_cmd) > 0) && in_array($this->keyword, $array_weixinmp_help_cmd)) {
                    // 获取帮助信息
                    $weixin_help = $array_weixinmp_option[WXMP_HELP];
                    $weixin_help = apply_filters('weixin_help',$weixin_help);
					$replyMsg = sprintf($textTpl, $fromUsername, $toUsername, $time, $weixin_help);
					
                } elseif((count($array_weixinmp_new_cmd) > 0) && in_array($this->keyword, $array_weixinmp_new_cmd)) {
                	// 获取最新文章
                    $this->query('new');
                    if($this->articleCount == 0) {
    					$weixin_not_found = "抱歉，最新文章显示错误，请重试一下。";
    					$replyMsg = sprintf($textTpl, $fromUsername, $toUsername, $time, $weixin_not_found);
    				} else {
    					$replyMsg = sprintf($picTpl, $fromUsername, $toUsername, $time, $this->articleCount,$this->items);
					}
					
                } elseif((count($array_weixinmp_rand_cmd) > 0) && in_array($this->keyword, $array_weixinmp_rand_cmd)) {
					// 随机文章获取
                    $this->query('rand');
                    if($this->articleCount == 0) {
    					$weixin_not_found = "抱歉，随机文章显示错误，请重试一下。";
    					$replyMsg = sprintf($textTpl, $fromUsername, $toUsername, $time, $weixin_not_found);
    				} else {
    					$replyMsg = sprintf($picTpl, $fromUsername, $toUsername, $time, $this->articleCount,$this->items);
					}
					
                } elseif((count($array_weixinmp_hot_cmd) > 0) && in_array($this->keyword, $array_weixinmp_hot_cmd)) {
					// 热门文章获取
                    $this->query('hot');
                    if($this->articleCount == 0) {
    					$weixin_not_found = "抱歉，热门文章显示错误，请重试一下。";
    					$replyMsg = sprintf($textTpl, $fromUsername, $toUsername, $time, $weixin_not_found);
                    }else{
    					$replyMsg = sprintf($picTpl, $fromUsername, $toUsername, $time, $this->articleCount,$this->items);
					}
					
                } else {

                    $keyword_length = mb_strwidth(preg_replace('/[\x00-\x7F]/','',$this->keyword),'utf-8')+str_word_count($this->keyword)*2;
                    $weixin_keyword_allow_length = $wxmp_keyword_length;
            
                    if($keyword_length > $weixin_keyword_allow_length) {
                        if($wxmp_auto_reply) {
                            $weixin_keyword_too_long = $wxmp_keyword_length_warning;
                            $replyMsg = sprintf($textTpl, $fromUsername, $toUsername, $time, $weixin_keyword_too_long);
                        }
                    } elseif(!empty($this->keyword)) {
                        $this->query();
                        if($this->articleCount == 0) {
                            $weixin_not_found = str_replace('{keyword}', $this->keyword, $wxmp_keyword_error_warning);
                            $replyMsg = sprintf($textTpl, $fromUsername, $toUsername, $time, $weixin_not_found);
                        } else {
                            $replyMsg = sprintf($picTpl, $fromUsername, $toUsername, $time, $this->articleCount,$this->items);
                        }
                    }
                }

                if(!empty($this->appId) && !empty($this->encodingAesKey)) {// 加密回复
                    if($errCode == 0) {
                        $errCode = $pc->encryptMsg($replyMsg, $this->timeStamp, $this->nonce, $encryptReplyMsg);
                        echo $encryptReplyMsg;
                    } else {
                        die('Encrypt msg error');
                        exit;
                    }
                } else {// 明文回复
                    echo $replyMsg;
                }
			}
			
        } else {
            echo "";
            exit;
        }
    }

    private function query($query_arg = NULL) {

        global $wpdb;

        $query_keyword = $this->keyword;
        $wxmp_article_count = get_option(WXMP_DEFAULT_ARTICLE_ACCOUNT);
        // 微信公众号最多只能返回8篇文章，超过8篇文章报错，因此数量设置超过8时，默认为8
        $wxmp_article_count = $wxmp_article_count > 7 ? 7 : $wxmp_article_count;
        
        // @ 处理关键词部分，应该放在前面去处理，而不是在这里处理
        if(!empty($this->arg)) { 
        	// 判断关键词后的命令参数，是否是数字；另外一个判断方式是使用函数is_numeric($this->arg)
        	// if the arg is a number or not；another way to do this is to use function is_numeric($this-arg)
            if (preg_match("/^\d*$/",$this->arg)) {
                $wxmp_article_count = $this->arg;
                // 微信公众号最多只能返回8篇文章，超过8篇文章报错，因此数量设置超过8时，默认为8
        		$wxmp_article_count = $wxmp_article_count > 8 ? 8 : $wxmp_article_count;
            } else { 
            	// 如果关键词后面的命令参数不是数字，那么把XXX@YYY当成一个关键词，使用“XXX YYY”来代替“XXX@YYY”来查询信息
            	// if the arg is not a number, so we consier XXX@YYY the whole as one keyword, and we use "XXX YYY" instead of "XXX@YYY" to query information.
                $query_keyword = $this->keyword.' '.$this->arg;
                // 这个地方需要修改下，如果考量启用自定义命令分隔符，也就说，实际使用中，可能不用@符号，而用其他的符号
                $this->keyword = $this->keyword.$this->cmd_seperator.$this->arg;
            }
        } 

        /**
         * @todo 还需要好好的理解apply_filters()这个函数
         */
        $wxmp_article_count = apply_filters('wxmp_article_count', $wxmp_article_count);
        $category_exclude = trim(get_option(WXMP_CATEGORY_EXCLUDE));
        // $category_exclude_array = explode(',', $category_exclude);

        $select         =   "
                            SELECT DISTINCT ID, post_title, post_content, post_excerpt 
                            ";
        $from_posts     =   "
                            FROM $wpdb->posts 
                            "; 
        $from_exclude   =   "
                            FROM $wpdb->posts,$wpdb->term_relationships, $wpdb->term_taxonomy 
                            "; 

        $where_normal   =   "
                            WHERE $wpdb->posts.post_status = 'publish' 
                            AND $wpdb->posts.post_type='post' 
                            ";
        
        $where_exclude  =   "
                            WHERE $wpdb->posts.post_status = 'publish' 
                            AND $wpdb->posts.post_type = 'post' 
                            AND $wpdb->posts.ID=$wpdb->term_relationships.object_id 
                            AND $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id 
                            AND $wpdb->term_taxonomy.taxonomy = 'category' 
                            AND $wpdb->term_relationships.term_taxonomy_id not in ($category_exclude)  
                            ";

        $orderby        =   "
                            ORDER BY $wpdb->posts.post_date DESC 
                            ";

        $limit          =   "
                            LIMIT 0, $wxmp_article_count 
                            ";

        switch ($query_arg) {
            case 'new':
                if(empty($category_exclude)) {
                    $wxmp_query_array = $select . $from_posts . $where_normal . $orderby . $limit;
                } else {
                    $wxmp_query_array = $select . $from_exclude . $where_exclude . $orderby . $limit;
                }
                break;
            case 'rand':
                $where_normal   =   "
                                    WHERE $wpdb->posts.post_status = 'publish' 
                                    AND $wpdb->posts.post_type = 'post' 
                                    AND $wpdb->posts.post_title<>'' 
                                    ";
                $orderby        =   "
                                    ORDER BY RAND() 
                                    ";
                if(empty($category_exclude)) {
                    $wxmp_query_array = $select . $from_posts . $where_normal . $orderby . $limit;
                } else {
                    $wxmp_query_array = $select . $from_exclude . $where_exclude . $orderby . $limit;
                }
                break;
             case 'hot':
                $from_postmeta          =   "
                                            FROM $wpdb->posts,$wpdb->postmeta 
                                            "; 
                $from_postmeta_exclude  =   "
                                            FROM $wpdb->posts,$wpdb->postmeta,$wpdb->term_relationships, $wpdb->term_taxonomy 
                                            "; 
                $where_postmeta         =   "
                                            WHERE $wpdb->posts.post_status = 'publish' 
                                            AND $wpdb->posts.post_type = 'post' 
                                            AND $wpdb->posts.ID=$wpdb->postmeta.post_id 
                                            AND $wpdb->postmeta.meta_key = 'views' 
                                            ";
                $where_postmeta_exclude =   "
                                            WHERE $wpdb->posts.post_status='publish' 
                                            AND $wpdb->posts.post_type='post' 
                                            AND $wpdb->posts.ID=$wpdb->postmeta.post_id 
                                            AND $wpdb->postmeta.meta_key = 'views' 
                                            AND $wpdb->posts.ID=$wpdb->term_relationships.object_id 
                                            AND $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id 
                                            AND $wpdb->term_taxonomy.taxonomy = 'category' 
                                            AND $wpdb->term_relationships.term_taxonomy_id not in ($category_exclude) 
                                            ";
                $orderby                =   "
                                            ORDER BY CAST($wpdb->postmeta.meta_value AS INT) DESC 
                                            ";
                if(empty($category_exclude)) {
                    $wxmp_query_array = $select . $from_postmeta . $where_postmeta . $orderby . $limit;
                } else {
                    $wxmp_query_array = $select . $from_postmeta_exclude . $where_postmeta_exclude . $orderby . $limit;
                }
                break;
            default:
				$query_keyword = strlen($query_keyword) == 15 && ctype_alnum($query_keyword) ? substr($query_keyword, 7, 4) : $query_keyword;
                $wxmp_query_array = array('s' => $query_keyword, 'posts_per_page' => $wxmp_article_count , 'post_status' => 'publish' );
                /*$where_normal   =   "
                                    WHERE $wpdb->posts.post_status = 'publish' 
                                    AND $wpdb->posts.post_type = 'post' 
                                    AND ($wpdb->posts.post_title LIKE '%$query_keyword%' OR $wpdb->posts.post_content LIKE '%$query_keyword%') 
                                    ";
                
                $where_exclude  =   "
                                    WHERE $wpdb->posts.post_status = 'publish' 
                                    AND $wpdb->posts.post_type = 'post' 
                                    AND ($wpdb->posts.post_title LIKE '%$query_keyword%' OR $wpdb->posts.post_content LIKE '%$query_keyword%') 
                                    AND $wpdb->posts.ID=$wpdb->term_relationships.object_id 
                                    AND $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id 
                                    AND $wpdb->term_taxonomy.taxonomy = 'category' 
                                    AND $wpdb->term_relationships.term_taxonomy_id not in ($category_exclude) 
                                    ";*/
				$where_normal   =   "
                                    WHERE $wpdb->posts.post_status = 'publish' 
                                    AND $wpdb->posts.post_type = 'post' 
                                    AND ($wpdb->posts.post_title LIKE '%$query_keyword%') 
                                    ";
                
                $where_exclude  =   "
                                    WHERE $wpdb->posts.post_status = 'publish' 
                                    AND $wpdb->posts.post_type = 'post' 
                                    AND ($wpdb->posts.post_title LIKE '%$query_keyword%') 
                                    AND $wpdb->posts.ID=$wpdb->term_relationships.object_id 
                                    AND $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id 
                                    AND $wpdb->term_taxonomy.taxonomy = 'category' 
                                    AND $wpdb->term_relationships.term_taxonomy_id not in ($category_exclude) 
                                    ";
                if(empty($category_exclude)) {
                    $wxmp_query_array = $select . $from_posts . $where_normal . $orderby . $limit;
                } else {
                    $wxmp_query_array = $select . $from_exclude . $where_exclude . $orderby . $limit;
                }
                break;
        }

        logs($wxmp_query_array);

        $wxmp_query_array = apply_filters('wxmp_query_array', $wxmp_query_array); 

        $posts = $wpdb->get_results($wxmp_query_array);

        if(is_array($posts) && count($posts, 0) > 0) {

            foreach ($posts as $post) {

                $title = $post->post_title; 
                $content = $post->post_content;
                $excerpt = strip_tags($post->post_excerpt);

                if(!isset($excerpt)) {
                    $except = $this->get_post_excerpt($content);
                }
                
                $thumbnail_id = get_post_thumbnail_id($post->ID);
                
                if($thumbnail_id) {
                    //$thumb = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
                    //$thumb = $thumb[0];
					$thumb = WXMP_DEFAULT_THUMB_LINK;
                } else {
                    $thumb = $this->get_post_first_image($content);
                }

                if(!$thumb && WXMP_DEFAULT_THUMB_LINK) {
                    $thumb = WXMP_DEFAULT_THUMB_LINK;
                }

                //$link = get_permalink($post->ID);
				$link = $this->get_site_url() . '/wechat/mp?id=' . $post->ID . '&code=' . $this->keyword;
                
                $items = $items . $this->get_item($title, $excerpt, $thumb, $link);
            }
            if(empty($query_arg)) {
                $query_arg = 's&k=' . $query_keyword;
            }
            //$items = $items . $this->get_item('获取更多文章...', '获取更多文章...', WXMP_URL.'/style/img/logo.png', $this->get_site_url() . '/wxmp?c='. $query_arg);
			$items = $items . $this->get_item(使用帮助, 使用帮助, WXMP_URL.'/style/img/logo.png', 'https://mp.weixin.qq.com/s?__biz=MzAxOTI5MTYyMQ==&mid=100000003&idx=1&sn=23c775110767c01aaebedd7ea41e0852');
            $this->items = $items;
            $this->articleCount = count($posts, 0) > $wxmp_article_count ? $wxmp_article_count:count($posts, 0);
            $this->articleCount = $this->articleCount + 1;
        }
    }

    public function get_item($title, $description, $picUrl, $url) {
        if(!$description) $description = $title;
        return
        '
        <item>
            <Title><![CDATA['.$title.']]></Title>
            <PicUrl><![CDATA['.$picUrl.']]></PicUrl>
            <Url><![CDATA['.$url.']]></Url>
        </item>
        ';
    }

    public function get_site_url() {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $page_url = $http_type . $_SERVER['HTTP_HOST'];
        return $page_url;
    }

    // 截取文章部分内容作为简介
    public function get_post_excerpt($post_content) {
        $post_excerpt = mb_substr(trim(strip_tags($content)), 0, 120);
        return $post_excerpt;
    }

    // 获取文章的第一张图片
    public function get_post_first_image($post_content) {
        preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', $post_content, $matches);
        if($matches) {
            return $matches[1][0];
        } else {
            return false;
        }
    }
    
    private function checkSignature() {

        $this->signature = $_GET["signature"];
        $this->timestamp = $_GET["timestamp"];
        $this->nonce = $_GET["nonce"];
        //定义微信 Token/appId/encodingAesKey
        $this->token            = get_option(WXMP_TOKEN             , 'weixin');
        $this->appId            = get_option(WXMP_APPID             , '');
        $this->encodingAesKey   = get_option(WXMP_ENCODINGAESKEY    , '');
                
        $wxmp_token = apply_filters('wxmp_token', $this->token);
        
        if(empty($wxmp_token)) {
            return false;
        }

        if(isset($_GET['debug'])) {
            echo "\n".'WEIXIN_TOKEN：'.$wxmp_token;
        }

        $tmpArr = array($wxmp_token, $this->timestamp, $this->nonce);
        sort($tmpArr, SORT_STRING);// 解决微信有时无法响应的bug
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        
        if($tmpStr == $this->signature) {
            return true;
        } else {
            return false;
        }
    }
}