<?php 


// 自定义全部查询结果页面

add_action('parse_request', 'wxmp_query_return', 4);

function wxmp_query_return($wp_query) {

    $request_url = $_SERVER['REQUEST_URI'];
 
    if(strpos($request_url, '/wechat/mp?') !== false) {

        $code   = isset($_GET["code"]) ? $_GET["code"] : '';
        $postId = isset($_GET["id"]) ? $_GET["id"] : '';
        $pageId = 606;

        if(strlen($code) == 4) {
            $slug = $code;
            $firm = '';
            $area = '';
        } elseif(strlen($code) == 15) {
            $firm = substr($code, 0, 3);
            $area = substr($code, 3, 4);
            $slug = substr($code, 7, 4);
        } else {
            $firm = '';
            $area = '';
            $slug = '';
        }

        if(!empty($code)) {

            echo wxmp_return_page($code, $postId, $pageId, $slug, $firm, $area);
            exit;
        
        } else {
            // 返回默认信息
            exit;
        }
    }
}

// 更多信息页面
function wxmp_return_page($code, $postId, $pageId, $slug, $firm, $area) {

    $wxmp_url = WXMP_URL;

    $payCompany = json_decode(get_post_meta($pageId, 'paycompany', true), true);
    $areacode = json_decode(get_post_meta($pageId, 'areacode', true), true);

    $payCompanyCode = array_column($payCompany, 'code');
    $payCompanyName = array_column($payCompany, 'name');
    $payName = empty(array_search($firm, $payCompanyCode)) ? '收单机构未知' : $payCompanyName[array_search($firm, $payCompanyCode)];

    $areaxcode = array_column($areacode, 'areaCode');
    $areaxname = array_column($areacode, 'areaName');
    $areastr = empty(array_search($area, $areaxcode)) ? '未知刷卡地点' : $areaxname[array_search($area, $areaxcode)];

    // 招行天书
    if(strlen($code) == 15) {
        
        function test($date, $code) {
            $path = $_SERVER['DOCUMENT_ROOT'];
            $file = $path . '/data/' . $date . '.txt';
            $exis = is_file($file);
            if($exis) {
                $contents = file_get_contents($file);
                $pattern = preg_quote($code, '/');
                $pattern = "/^.*$pattern.*\$/m";
                if(preg_match_all($pattern, $contents, $matches)) {
                    $data = 'true';
                    return $data;
                } else {
                    $data = 'false';
                    return $data;
                }
            } else {
                $data = 'update';
                return $data;
            }
        }

        $Y = date("Y");
        $M = date("m");
        $now = date("Ym");
        $pre = ($M - 1 != 0 ? $Y : $Y - 1).($M - 1 != 0 ? sprintf('%02s', $M - 1) : 12);
        $nxt = ($M - 2 != 0 ? $Y : $Y - 1).($M - 2 != 0 ? sprintf('%02s', $M - 2) : 12);
        $ts1 = test($now, $code);
        $ts2 = test($pre, $code);
        $ts3 = test($nxt, $code);

    } else {
        $ts1 = 'null';
        $ts2 = 'null';
        $ts3 = 'null';
    }

    // 其他零星规则
    $testData = json_decode(get_post_meta($pageId, 'all-test', true), true);
    for($i = 0; count($testData) > $i; $i++) {
        if($testData[$i]['name'] == 'other') {
            for($x = 0; count($testData[$i]['data']) > $x; $x++) {
                if ($testData[$i]['data'][$x]['code'] == $slug) {
                  $test[] = $testData[$i]['data'][$x]['data'];
                }
            }
        } else {
            if(in_array($slug, $testData[$i]['data'])) {
                $test[] = $testData[$i]['name'];
            }
        }
    }

    // 所有银行
    $total = json_decode(get_post_meta($pageId, 'zone-bank', true), true);
    for ($i = 0; count($total) > $i; $i++) {
        $theOnbp = []; $theInfo = [];
        $theOnbp = json_decode(get_post_meta($pageId, 'all-bank-'.$i, true), true);
        $theInfo = $theOnbp[0]['data'];
        if(in_array($slug, $theInfo)) {
            $allBankOnResult[] = $total[$i];
        } else {
            $allBankNoResult[] = $total[$i];
        }
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo strlen($slug) == 4 ? '['.$slug.']信息详情' : ''; ?></title>
    <meta name="keywords" content="<?php echo $slug; ?>积分">
    <meta name="description" content="<?php echo $slug; ?>积分详情">
    <style>
        html,body,ul,ol,form,p{margin: 0;padding: 0;}
        html{height: 100%;font-size: 12px;background: #f2f2f2;}
        body{height: 100%;}
		a{text-decoration:none;}
        li{list-style:none;}
        #app{position: relative;min-height: 100%;overflow:hidden;}
        #form,.nav{position: relative;height: 40px;margin: 15px auto;float:left;box-sizing:border-box;}
        #form{width: 96%;max-width: 640px;}
        #code,#submit{height: 40px;line-height: 40px;box-sizing: border-box;float: left;margin: 0;padding: 0;outline: none;border: none;}
        #code{width: 80%;padding: 0 5px;border-top-left-radius: 2px;border-bottom-left-radius: 2px;border: solid 1px #ddd;border-right: none;}
        #submit{width: 20%;background: #09bb07;color: #fff;border-top-right-radius: 2px;border-bottom-right-radius: 2px;cursor: pointer;}
        .wrap,.box{position: relative;width: 1200px;max-width: 96%;margin: 0 auto;box-sizing:border-box;overflow:hidden;}
        .box{background: #fff;padding: 30px;border-radius: 2px;border:solid 1px #eee;box-shadow:0 0 5px rgba(0,0,0,0.05);margin-top:15px;}
        .info{overflow:hidden;position:relative;}
        .section{line-height:1.8em;color:#333;overflow:hidden;}
        .section ul{overflow:hidden;}
        .mg{margin-bottom:5px;}
        .h{position:relative;margin:15px auto;z-index:2;overflow:hidden;}
        .h::before{content: "";position: absolute;top: 0;bottom: 0;left: 0;right: 0;margin: auto;height: 0;width: 100%;height: 1px;background: #eee;z-index:0;}
        .h>h5{padding:0 5px 0 0;float:left;z-index:1;background:#fff;margin:0;position:relative;}
        .ex{color:#999;}
        .warning{color:#00f;}
        .nobp{color:red;}
        .onbp{color:#09bb07;}
        .onbp li,.nobp li{width:10%;float:left;padding-left:16px;background-repeat:no-repeat;background-position:left center;background-size:12px 12px;box-sizing:border-box;}
        .update{position: relative;width: 1200px; max-width: 90%; margin: 15px auto;color:#999;text-align: center;}
		.update a{color:#999;}
		.update a:hover{color:red;}
        .red{color:red;}
        .nav{width:560px;}
        .home,.wxmp,.lite,.dome{width:40px;height:40px;float:right;margin-left:20px;background-repeat:no-repeat;background-position:center center;background-size:24px 24px;opacity:0.5;}
        .home:hover,.wxmp:hover,.lite:hover,.dome:hover{opacity:1;}
        #popu{position:absolute;top:0;right:0;width:80px;height:80px;}
        #popu img{width:100%;opacity:0.7;}
        .onbp li{background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyB0PSIxNTMyMzQ4OTE5NDk4IiBjbGFzcz0iaWNvbiIgc3R5bGU9IiIgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHAtaWQ9IjE5MzIiIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTYiIGhlaWdodD0iMTYiPjxkZWZzPjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+PC9zdHlsZT48L2RlZnM+PHBhdGggZD0iTTk4My43NzA5ODkgMzEyLjcxMDIxMWE1MTAuMjQzOTY0IDUxMC4yNDM5NjQgMCAwIDAtMTA5LjcxNDE1NS0xNjIuNzQyNjY0QTUxMC4yOTI3MjYgNTEwLjI5MjcyNiAwIDAgMCA1MTIuMDAwMTIyIDAuMDAwNDg4IDUxMC4yOTI3MjYgNTEwLjI5MjcyNiAwIDAgMCAxNDkuOTQzNDEgMTQ5Ljk0MzE2NiA1MTAuMTQ2NDQgNTEwLjE0NjQ0IDAgMCAwIDAuMDAwNzMxIDUxMS45OTk4NzhhNTEwLjIxOTU4MyA1MTAuMjE5NTgzIDAgMCAwIDE0OS45NDI2NzkgMzYyLjA1NjcxMkE1MTAuMjE5NTgzIDUxMC4yMTk1ODMgMCAwIDAgNTEyLjAwMDEyMiAxMDIzLjk5OTI2OWE1MTAuMTcwODIxIDUxMC4xNzA4MjEgMCAwIDAgMzYyLjA1NjcxMi0xNDkuOTQyNjc5QTUxMC40MTQ2MyA1MTAuNDE0NjMgMCAwIDAgMTAyMy45OTk1MTIgNTExLjk5OTg3OGE1MDguODI5ODcgNTA4LjgyOTg3IDAgMCAwLTQwLjIyODUyMy0xOTkuMjg5NjY3eiBtLTIwMC44OTg4MDkgNjEuOTc2MzA3TDQ3Mi4yODM1OTggNjk1LjI0Njg5OGEzMS42MjIwNTggMzEuNjIyMDU4IDAgMCAxLTQ0LjI1MTM3NiAxLjI2NzgwOGwtMTg3Ljk1MjUzOC0xNzIuNzYzMjIzYTMxLjc2ODM0MyAzMS43NjgzNDMgMCAwIDEgMjEuNDc5NTkzLTU1LjA3NjUwNmM3Ljk3MjU2MiAwIDE1LjYwMzc5MSAyLjk3NDQ3MyAyMS40NTUyMTMgOC4zNjI2NTdsMTY1LjIwNTEzNiAxNTEuODY4NzcyIDI4OS4wODQ2MDktMjk4LjM3Mzc0YzYuMDQ2NDY5LTYuMjE3MTM1IDE0LjE0MDkzNi05LjY1NDg0NiAyMi43OTYxNjMtOS42NTQ4NDZhMzEuNzY4MzQzIDMxLjc2ODM0MyAwIDAgMSAyMi43NzE3ODIgNTMuODA4Njk4eiIgZmlsbD0iIzJBQkI1MCIgcC1pZD0iMTkzMyI+PC9wYXRoPjwvc3ZnPg==);}
        .nobp li{background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyB0PSIxNTMyMzQ4OTkwOTAzIiBjbGFzcz0iaWNvbiIgc3R5bGU9IiIgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHAtaWQ9IjQxMTciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTYiIGhlaWdodD0iMTYiPjxkZWZzPjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+PC9zdHlsZT48L2RlZnM+PHBhdGggZD0iTTUxNS41NjIxMjkgOTQ1LjI0MDIxNSIgcC1pZD0iNDExOCIgZmlsbD0iI2ZmNWY1ZiI+PC9wYXRoPjxwYXRoIGQ9Ik01MTUuNTYyMTI5IDk1OS45MDExMjNNNTE1LjU2MjEyOSA5NTIuNTY5MTM0IiBwLWlkPSI0MTE5IiBmaWxsPSIjZmY1ZjVmIj48L3BhdGg+PHBhdGggZD0iTTUxNS41NjIxMjkgOTUyLjU2OTEzNE01MTUuNTYyMTI5IDk0NS4yNDAyMTUiIHAtaWQ9IjQxMjAiIGZpbGw9IiNmZjVmNWYiPjwvcGF0aD48cGF0aCBkPSJNNTEwLjU0NTg4MiA2My44MTU0MjFjLTI0Ni43MzI0MjcgMC00NDYuNzA4OTcxIDE5OS45NzY1NDQtNDQ2LjcwODk3MSA0NDYuNzA4OTcxUzI2My44MTM0NTQgOTU3LjIzNDM4NyA1MTAuNTQ1ODgyIDk1Ny4yMzQzODcgOTU3LjI1NTg3NyA3NTcuMjU3ODQzIDk1Ny4yNTU4NzcgNTEwLjUyNTQxNiA3NTcuMjc4MzA5IDYzLjgxNTQyMSA1MTAuNTQ1ODgyIDYzLjgxNTQyMXpNNzIxLjg0MDE5MiA3MjMuMzA3NjEzYy05LjgyNzgzMyA5LjgyNzgzMy0yNS43NjA3MSA5LjgyNzgzMy0zNS41ODc1MiAwTDUwOS4wNTY5NzEgNTQ2LjExMjkzNSAzMzIuNDU3ODU4IDcyMi43MTIwNDljLTkuODI3ODMzIDkuODI3ODMzLTI1Ljc2MDcxIDkuODI3ODMzLTM1LjU4NzUyIDAtOS44Mjc4MzMtOS44Mjc4MzMtOS44Mjc4MzMtMjUuNzYwNzEgMC0zNS41ODc1MmwxNzYuNTk5MTE0LTE3Ni41OTkxMTRMMjk5LjEwNDIxNSAzMzYuMTU5MTU2Yy05LjgyNzgzMy05LjgyNzgzMy05LjgyNzgzMy0yNS43NjA3MSAwLTM1LjU4NzUyIDkuODI3ODMzLTkuODI3ODMzIDI1Ljc2MDcxLTkuODI3ODMzIDM1LjU4NzUyIDBsMTc0LjM2NTIzNiAxNzQuMzY1MjM2IDE3My4wMjU3MjktMTc3LjkzOTY0NWM5LjgyNzgzMy05LjgyNzgzMyAyNS43NjA3MS05LjgyNzgzMyAzNS41ODc1MiAwIDkuODI3ODMzIDkuODI3ODMzIDkuODI3ODMzIDI1Ljc2MDcxIDAgMzUuNTg3NTJMNTQ0LjY0NTUxNCA1MTAuNTI1NDE2bDE3Ny4xOTQ2NzggMTc3LjE5NDY3OEM3MzEuNjY3MDAyIDY5Ny41NDY5MDMgNzMxLjY2NzAwMiA3MTMuNDc5NzgxIDcyMS44NDAxOTIgNzIzLjMwNzYxM3oiIHAtaWQ9IjQxMjEiIGZpbGw9IiNmZjVmNWYiPjwvcGF0aD48L3N2Zz4=);}
        .home{background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyB0PSIxNTMyMzUzNzEyMDkwIiBjbGFzcz0iaWNvbiIgc3R5bGU9IiIgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHAtaWQ9IjE0NzYyIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgd2lkdGg9IjQ4IiBoZWlnaHQ9IjQ4Ij48ZGVmcz48c3R5bGUgdHlwZT0idGV4dC9jc3MiPjwvc3R5bGU+PC9kZWZzPjxwYXRoIGQ9Ik05NTQuNTIxNiAzMjUuMDUzNDRjLTI0LjE5Mi01Ny4xOTU1Mi01OC44MTg1Ni0xMDguNTU0MjQtMTAyLjkyMjI0LTE1Mi42NTI4LTQ0LjA5ODU2LTQ0LjA5ODU2LTk1LjQ2MjQtNzguNzI1MTItMTUyLjY1MjgtMTAyLjkxNzEyQzYzOS43MTg0IDQ0LjQzMTM2IDU3Ni44MjQzMiAzMS43Mjg2NCA1MTIgMzEuNzI4NjRzLTEyNy43MjM1MiAxMi42OTc2LTE4Ni45NDY1NiAzNy43NTQ4OGMtNTcuMTkwNCAyNC4xOTItMTA4LjU1NDI0IDU4LjgxODU2LTE1Mi42NTI4IDEwMi45MTcxMi00NC4xMDM2OCA0NC4wOTg1Ni03OC43MjUxMiA5NS40NjI0LTEwMi45MjIyNCAxNTIuNjUyOEM0NC40MzEzNiAzODQuMjc2NDggMzEuNzI4NjQgNDQ3LjE3NTY4IDMxLjcyODY0IDUxMnMxMi42OTc2IDEyNy43MTg0IDM3Ljc0OTc2IDE4Ni45NTE2OGMyNC4xOTIgNTcuMTkwNCA1OC44MTg1NiAxMDguNTQ5MTIgMTAyLjkyMjI0IDE1Mi42NDc2OCA0NC4wOTg1NiA0NC4wOTg1NiA5NS40NjI0IDc4LjczMDI0IDE1Mi42NTI4IDEwMi45MjIyNCA1OS4yMjMwNCAyNS4wNTIxNiAxMjIuMTIyMjQgMzcuNzQ5NzYgMTg2Ljk0NjU2IDM3Ljc0OTc2czEyNy43MTg0LTEyLjY5NzYgMTg2Ljk0NjU2LTM3Ljc0OTc2YzU3LjE5NTUyLTI0LjE5MiAxMDguNTU0MjQtNTguODE4NTYgMTUyLjY1MjgtMTAyLjkyMjI0czc4LjcyNTEyLTk1LjQ2MjQgMTAyLjkyMjI0LTE1Mi42NDc2OGMyNS4wNTIxNi01OS4yMjMwNCAzNy43NDk3Ni0xMjIuMTIyMjQgMzcuNzQ5NzYtMTg2Ljk1MTY4IDAtNjQuODI0MzItMTIuNzAyNzItMTI3LjcxODQtMzcuNzQ5NzYtMTg2Ljk0NjU2eiBtLTE4Mi4zMTI5Ni01OC4xNjMybC0xOTcuMDE3NiAzMDUuMDQ5NmExMC45MzYzMiAxMC45MzYzMiAwIDAgMS0zLjI1MTIgMy4yNTEybC0zMDUuMDU5ODQgMTk2Ljk5NzEyYTEwLjkyNjA4IDEwLjkyNjA4IDAgMCAxLTEzLjYzOTY4LTEuNDQ4OTYgMTAuOTE1ODQgMTAuOTE1ODQgMCAwIDEtMS40NDg5Ni0xMy42Mzk2OGwxOTcuMDA3MzYtMzA1LjA0OTZjMC44Mzk2OC0xLjMwMDQ4IDEuOTQ1Ni0yLjQwNjQgMy4yNDYwOC0zLjI1MTJsMzA1LjA3NTItMTk2Ljk5NzEyYTEwLjkxMDcyIDEwLjkxMDcyIDAgMCAxIDE1LjA4ODY0IDE1LjA4ODY0eiIgcC1pZD0iMTQ3NjMiIGZpbGw9IiMxMjk2ZGIiPjwvcGF0aD48cGF0aCBkPSJNNTEyIDQ2Ni4yOTM3NmMtMjUuMjA1NzYgMC00NS43MTEzNiAyMC41MDU2LTQ1LjcxMTM2IDQ1LjcwNjI0czIwLjUwNTYgNDUuNzExMzYgNDUuNzExMzYgNDUuNzExMzZjMjUuMjAwNjQgMCA0NS43MTEzNi0yMC41MTA3MiA0NS43MTEzNi00NS43MTEzNiAwLTI1LjIwMDY0LTIwLjUxMDcyLTQ1LjcwNjI0LTQ1LjcxMTM2LTQ1LjcwNjI0eiIgcC1pZD0iMTQ3NjQiIGZpbGw9IiMxMjk2ZGIiPjwvcGF0aD48L3N2Zz4=);}
        .wxmp{background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyB0PSIxNTMyMzUzMTg0NTU5IiBjbGFzcz0iaWNvbiIgc3R5bGU9IiIgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHAtaWQ9IjQ0NjYiIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iNDgiIGhlaWdodD0iNDgiPjxkZWZzPjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+PC9zdHlsZT48L2RlZnM+PHBhdGggZD0iTTY5My4yNDggMzQ3LjIxNzkyYzExLjYzMjY0IDAgMjMuMjY1MjggMC44NjAxNiAzNC45Mzg4OCAyLjA0OEM2OTYuODUyNDggMjAzLjEyMDY0IDU0MC42NzIgOTQuNzQwNDggMzYyLjQ5NiA5NC43NDA0OCAxNjMuMzQ4NDggOTQuNzQwNDggMCAyMzAuNCAwIDQwMy4wMDU0NGMwIDk5LjU3Mzc2IDU0LjE5MDA4IDE4MS40NTI4IDE0NC45MTY0OCAyNDQuODk5ODRsLTM2LjEyNjcyIDEwOS4xNTg0IDEyNi44NTMxMi02My44MTU2OGM0NS4zNDI3MiA4Ljg0NzM2IDgxLjg3OTA0IDE4LjAyMjQgMTI2Ljg1MzEyIDE4LjAyMjQgMTEuMjIzMDQgMCAyMi40ODcwNC0wLjM2ODY0IDMzLjcxMDA4LTEuNTk3NDQtNy4yNDk5Mi0yNC4wODQ0OC0xMS4yMjMwNC00OS43NjY0LTExLjIyMzA0LTc1Ljg1NzkyQzM4NC45NDIwOCA0NzUuNjY4NDggNTIxLjA1MjE2IDM0Ny4yMTc5MiA2OTMuMjQ4IDM0Ny4yMTc5Mkw2OTMuMjQ4IDM0Ny4yMTc5MnpNNDk4LjE1NTUyIDI0OC44NzI5NmMyNy4yNzkzNiAwIDQ1LjM0MjcyIDE4LjA2MzM2IDQ1LjM0MjcyIDQ1LjM0Mjcycy0xOC4wNjMzNiA0NS4zNDI3Mi00NS4zNDI3MiA0NS4zNDI3MmMtMjcuMzIwMzIgMC01NC41OTk2OC0xOC4wNjMzNi01NC41OTk2OC00NS4zNDI3MkM0NDMuOTY1NDQgMjY2Ljk3NzI4IDQ3MS4yNDQ4IDI0OC44NzI5NiA0OTguMTU1NTIgMjQ4Ljg3Mjk2TDQ5OC4xNTU1MiAyNDguODcyOTZ6TTI0NC40OTAyNCAzMzkuNTk5MzZjLTI3LjMyMDMyIDAtNTQuNTk5NjgtMTguMDYzMzYtNTQuNTk5NjgtNDUuMzQyNzJzMjcuMjc5MzYtNDUuMzQyNzIgNTQuNTk5NjgtNDUuMzQyNzJjMjcuMjc5MzYgMCA0NS4zMDE3NiAxOC4wNjMzNiA0NS4zMDE3NiA0NS4zNDI3MkMyODkuNzkyIDMyMS4xMjY0IDI3MS43Njk2IDMzOS41OTkzNiAyNDQuNDkwMjQgMzM5LjU5OTM2TDI0NC40OTAyNCAzMzkuNTk5MzZ6TTEwMjQgNjI5LjgwMDk2YzAtMTQ0Ljg3NTUyLTE0NC45MTY0OC0yNjIuOTIyMjQtMzA3Ljg5NjMyLTI2Mi45MjIyNC0xNzIuNjA1NDQgMC0zMDguMjY0OTYgMTE4LjA0NjcyLTMwOC4yNjQ5NiAyNjIuOTIyMjQgMCAxNDUuMzI2MDggMTM1LjcwMDQ4IDI2Mi45NjMyIDMwOC4yNjQ5NiAyNjIuOTYzMiAzNi4xMjY3MiAwIDcyLjYyMjA4LTkuMjU2OTYgMTA4Ljc0ODgtMTguMDYzMzZsOTkuNTczNzYgNTQuNTk5NjgtMjcuMjc5MzYtOTAuNzI2NEM5NjkuODA5OTIgNzgzLjk3NDQgMTAyNCA3MTEuMjcwNCAxMDI0IDYyOS44MDA5NkwxMDI0IDYyOS44MDA5NnpNNjE2LjE2MTI4IDU4NC40NTgyNGMtMTguMDIyNCAwLTM2LjEyNjcyLTE4LjAyMjQtMzYuMTI2NzItMzYuMTI2NzIgMC0xOC4wMjI0IDE4LjA2MzM2LTM2LjEyNjcyIDM2LjEyNjcyLTM2LjEyNjcyIDI3LjMyMDMyIDAgNDUuMzQyNzIgMTguMDYzMzYgNDUuMzQyNzIgMzYuMTI2NzJDNjYxLjUwNCA1NjYuMzk0ODggNjQzLjQ4MTYgNTg0LjQ1ODI0IDYxNi4xNjEyOCA1ODQuNDU4MjRMNjE2LjE2MTI4IDU4NC40NTgyNHpNODE1LjY3NzQ0IDU4NC40NTgyNGMtMTguMDYzMzYgMC0zNi4xMjY3Mi0xOC4wMjI0LTM2LjEyNjcyLTM2LjEyNjcyIDAtMTguMDIyNCAxOC4wMjI0LTM2LjEyNjcyIDM2LjEyNjcyLTM2LjEyNjcyIDI3LjI3OTM2IDAgNDUuMzQyNzIgMTguMDYzMzYgNDUuMzQyNzIgMzYuMTI2NzJDODYxLjAyMDE2IDU2Ni4zOTQ4OCA4NDIuNTQ3MiA1ODQuNDU4MjQgODE1LjY3NzQ0IDU4NC40NTgyNEw4MTUuNjc3NDQgNTg0LjQ1ODI0ek04MTUuNjc3NDQgNTg0LjQ1ODI0IiBmaWxsPSIjMDBDNzAwIiBwLWlkPSI0NDY3Ij48L3BhdGg+PC9zdmc+);}
        .lite{background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyB0PSIxNTMyMzUzMjM1NDYyIiBjbGFzcz0iaWNvbiIgc3R5bGU9IiIgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHAtaWQ9IjU4MzkiIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iNDgiIGhlaWdodD0iNDgiPjxkZWZzPjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+PC9zdHlsZT48L2RlZnM+PHBhdGggZD0iTTUxMiA1MTJtLTUxMiAwYTUxMiA1MTIgMCAxIDAgMTAyNCAwIDUxMiA1MTIgMCAxIDAtMTAyNCAwWiIgZmlsbD0iI0ZGRkZGRiIgcC1pZD0iNTg0MCI+PC9wYXRoPjxwYXRoIGQ9Ik01MTIgMzkuMzg0NjE1YTQ3Mi42MTUzODUgNDcyLjYxNTM4NSAwIDEgMCA0NzIuNjE1Mzg1IDQ3Mi42MTUzODUgNDcyLjYxNTM4NSA0NzIuNjE1Mzg1IDAgMCAwLTQ3Mi42MTUzODUtNDcyLjYxNTM4NXogbTE1Ny41Mzg0NjIgNTA3LjI3Mzg0N2EzOS4zODQ2MTUgMzkuMzg0NjE1IDAgMCAxLTQ4LjA0OTIzMS0xOS42OTIzMDhjLTguNjY0NjE1LTE4LjkwNDYxNSAwLTMyLjI5NTM4NSAxNy4zMjkyMzEtNDMuMzIzMDc3QTEyMC41MTY5MjMgMTIwLjUxNjkyMyAwIDAgMSA2NTkuMjk4NDYyIDQ3Mi42MTUzODVjMzEuNTA3NjkyLTE4LjExNjkyMyA1Ny41MDE1MzgtNDEuNzQ3NjkyIDQxLjc0NzY5Mi03OC43NjkyMzFhNzEuNjggNzEuNjggMCAwIDAtODQuMjgzMDc3LTQxLjc0NzY5MiA2Ni45NTM4NDYgNjYuOTUzODQ2IDAgMCAwLTY1LjM3ODQ2MiA2OC41MjkyM3YxOTYuMTM1Mzg1YTE1Ny41Mzg0NjIgMTU3LjUzODQ2MiAwIDAgMS0xNTcuNTM4NDYxIDEzNi4yNzA3NjkgMTU3LjUzODQ2MiAxNTcuNTM4NDYyIDAgMCAxLTE0NS43MjMwNzctMTY2LjIwMzA3N2MwLTQ1LjY4NjE1NCA0Mi41MzUzODUtNzguNzY5MjMxIDk4LjQ2MTUzOC0xMDcuMTI2MTU0IDIzLjYzMDc2OS03LjA4OTIzMSA0Ny4yNjE1MzgtNy4wODkyMzEgNTkuODY0NjE2IDE2LjU0MTUzOXMtMTEuMDI3NjkyIDQwLjk2LTMxLjUwNzY5MyA1MC40MTIzMDhjLTQxLjc0NzY5MiAxOC4xMTY5MjMtNjIuMjI3NjkyIDQ3LjI2MTUzOC00Mi41MzUzODQgODkuNzk2OTIzYTczLjI1NTM4NSA3My4yNTUzODUgMCAwIDAgOTguNDYxNTM4IDM0LjY1ODQ2MSA2OS4zMTY5MjMgNjkuMzE2OTIzIDAgMCAwIDQ2LjQ3Mzg0Ni03NC44MzA3NjlWNDE4LjI2NDYxNWExMzcuMDU4NDYyIDEzNy4wNTg0NjIgMCAwIDEgOTkuMjQ5MjMxLTEzOS40MjE1MzggMTYzLjg0IDE2My44NCAwIDAgMSAxNzMuMjkyMzA4IDQ0Ljg5ODQ2MSAxNDQuMTQ3NjkyIDE0NC4xNDc2OTIgMCAwIDEtNzguNzY5MjMxIDIyMi45MTY5MjR6IiBmaWxsPSIjNzc4OURCIiBwLWlkPSI1ODQxIj48L3BhdGg+PC9zdmc+);}
        .dome{background-image:url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyB0PSIxNTMyMzUzNDEzNTEyIiBjbGFzcz0iaWNvbiIgc3R5bGU9IiIgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHAtaWQ9IjEyMDMxIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgd2lkdGg9IjQ4IiBoZWlnaHQ9IjQ4Ij48ZGVmcz48c3R5bGUgdHlwZT0idGV4dC9jc3MiPjwvc3R5bGU+PC9kZWZzPjxwYXRoIGQ9Ik01MTEuNjE3MjgzIDMwMC41NDUwMyA5MTYuNTIzMTczIDgwLjQ4NzEyNmMtMTQuOTM3MjAxLTkuODkzMzI0LTMyLjgzMjc3OS0xNS42NjY4MTgtNTIuMTAwNjEyLTE1LjY2NjgxOEwxNTkuNjE1MzAxIDY0LjgyMDMwOGMtMTIuNTMyNDMxIDAtMjQuNDA5OTQ2IDIuNTcwNTQ1LTM1LjMxMjI1IDcuMDc3MTg2TDUxMS42MTcyODMgMzAwLjU0NTAzIDUxMS42MTcyODMgMzAwLjU0NTAzek01MTEuNjE3MjgzIDMwMC41NDUwMyIgcC1pZD0iMTIwMzIiIGZpbGw9IiNmZjVmNWYiPjwvcGF0aD48cGF0aCBkPSJNOTU5LjA5NTc4MSAxNTkuNDk0NTUxYzAtOC45NjUxODYtMS42NDEzODMtMTcuNDYyNzItMy45NzI0NzUtMjUuNjg3MDMybC0zLjIyMDM0NS0xMC4zNzczNDhjLTIuMjUxMjc0LTUuNDU1MjQ1LTQuOTk2ODA0LTEwLjY0NTQ1NC04LjE3MTEtMTUuNTI0NTc4TDUxMS42MTcyODMgMzQ1LjQ5Mzc1NSA4OC4xNDI0NzkgOTcuNDc3MDc5Yy0xNC40NDcwMzcgMTYuNjAxMDk2LTIzLjIwNjUzOCAzOC4yNzQ3MjItMjMuMjA2NTM4IDYyLjAxNzQ3MmwwIDcwNC44MTg1MTdjMCA1Mi4yODI3NiA0Mi4zOTA0NTkgOTQuNjY3MDggOTQuNjc5MzU5IDk0LjY2NzA4bDcwNC44MTIzNzcgMGM1Mi4yODM3ODQgMCA5NC42NjcwOC00Mi4zODQzMTkgOTQuNjY3MDgtOTQuNjY3MDhsMC0yNzYuNzYzMzk0IDAuMDA2MTQtNDI4LjA1NTEyM0w5NTkuMDk1NzgxIDE1OS40OTQ1NTEgOTU5LjA5NTc4MSAxNTkuNDk0NTUxek02MzQuNDgzNjEyIDY3NC43MjgxOThjMTEuODQyNzIzIDAgMjEuNDM5Mjg4IDkuNTk2NTY2IDIxLjQzOTI4OCAyMS40NTY2ODQgMCAxMS44MTkxODctOS41OTY1NjYgMjEuNDY4OTY0LTIxLjQzOTI4OCAyMS40Njg5NjRsMCAwLjE3MDg5Mi05OS4wOTQ5MjYtMC4wMTEyNTYgMCA1MC40ODI3NjUtMC4xMDIzMzEgMGMwIDExLjgxMzA0Ny05LjYwMjcwNSAyMS40MTU3NTItMjEuNDQ1NDI4IDIxLjQxNTc1Mi0xMS44NDM3NDYgMC0yMS40NTI1OTEtOS42MDI3MDUtMjEuNDUyNTkxLTIxLjQxNTc1MmwtMC4xMDIzMzEgMCAwLTUwLjQ4Mjc2NS0xMDIuMTQ0Mzc4LTAuMDc0NzAxIDAtMC4wODQ5MzRjLTExLjgzNjU4MyAwLTIxLjQzMzE0OC05LjYwMjcwNS0yMS40MzMxNDgtMjEuNDYyODI0IDAtMTEuODIwMjEgOS41OTY1NjYtMjEuNDYyODI0IDIxLjQzMzE0OC0yMS40NjI4MjRsMC0wLjA4NTk1OCAxMDIuMTQ0Mzc4IDAuMDc0NzAxIDAtNDMuNTIzMjU5LTEwMi4xMjY5ODItMC4wNzk4MTggMC0wLjEwNzQ0N2MtMTEuODQyNzIzIDAtMjEuNDM5Mjg4LTkuNTk2NTY2LTIxLjQzOTI4OC0yMS40MzkyODhzOS41OTY1NjYtMjEuNDYyODI0IDIxLjQzOTI4OC0yMS40NjI4MjRsMC0wLjA4NTk1OCAxMDIuMTI2OTgyIDAuMDc5ODE4IDAtMS4wMjAyMzYtMTAxLjYxOTQyMi0xMTcuMDI0Mjc0IDAuMTM2MS0wLjE1NDUxOWMtOC44Njc5NzItNy44NDE1OTUtOS42NDI2MTQtMjEuNDE1NzUyLTEuNzk5OTk2LTMwLjI2MTIxMSA3Ljg3MTI3MS04Ljg4NTM2OCAyMS40MjI5MTUtOS42ODc2NCAzMC4yNzk2My0xLjgxNzM5Mmw5My4wMzc5NzYgMTEwLjQxOTg1NSA5MC45MTY2NjMtMTA1Ljc5MjQ2NWM4Ljg1NTY5Mi03Ljg2NTEzMSAyMi40MDgzNTktNy4wNjE4MzYgMzAuMjg0NzQ3IDEuODIzNTMyIDcuODQxNTk1IDguODQwMzQyIDcuMDY1OTI5IDIyLjQxOTYxNi0xLjgwMjA0MiAzMC4yNjIyMzRsMC4xMzA5ODMgMC4xNTQ1MTlMNTM1LjM4MjU0NyA1ODguMDk3MTQybDk5LjExMzM0NSAwLjAxMTI1NmMxMS44NDI3MjMgMCAyMS40MzkyODggOS41OTY1NjYgMjEuNDM5Mjg4IDIxLjQzMzE0OCAwIDExLjg0NzgzOS05LjU5NjU2NiAyMS40Njg5NjQtMjEuNDM5Mjg4IDIxLjQ2ODk2NGwwIDAuMTk0NDI4LTk5LjEwNzIwNS0wLjAxMTI1NiAwIDQzLjUyOTM5OUw2MzQuNDgzNjEyIDY3NC43MjgxOTggNjM0LjQ4MzYxMiA2NzQuNzI4MTk4ek02MzQuNDgzNjEyIDY3NC43MjgxOTgiIHAtaWQ9IjEyMDMzIiBmaWxsPSIjZmY1ZjVmIj48L3BhdGg+PC9zdmc+);}
        @media screen and (min-width: 320px) and (max-width: 799px) {
            .wrap{display:none;}
            .box{padding:15px;margin-top:2%;}
            .onbp li,.nobp li{width:25%;}
        }
    </style>
</head>
<body>
<div id="app">
    <div class="box">
        <div class="info">
            <div id="popu"><!--img src="" alt="二维码"--></div>
            <div class="section">
                <div class="col">商户代码：<?php echo $slug; ?></div>
                <div class="col">商户类别：<?php echo get_post_meta($postId, 'mcc-type', true); ?></div>
                <div class="col">商户费率：<?php echo get_post_meta($postId, 'mcc-rate', true); ?></div>
                <div class="col">代码名称：<?php echo get_post_meta($postId, 'mcc-name', true); ?></div>
            </div>
            <div class="h"><h5>收单信息</h5></div>
            <div class="section">
                <div class="col">收单机构：<?php echo $payName; ?></div>
                <div class="col">刷卡地点：<?php echo $areastr; ?></div>
                <div class="col ex">1) 输入11位/15位商户编码才能显示这些信息。 2) 如果收单机构与你使用的机子品牌不符，可能是非一清机子。 3) 如果刷卡地点不符，可能是跳外地了，因为这个不太规范，仅供参考，具体以银行/银联为准。</div>
            </div>
            <div class="h"><h5>累计积分</h5></div>
            <div class="section">
                <ul class="col onbp">
                    <?php 
                        if(empty($allBankOnResult)) {
                            echo '无';
                        } else {
                            foreach($allBankOnResult as $v) {
                                echo '<li>' . $v . '</li>';
                            }
                        };
                    ?>
                </ul>
            </div>
            <div class="h"><h5>不计积分</h5></div>
            <div class="section">
                <ul class="col nobp">
                    <?php 
                        if(empty($allBankNoResult)) {
                            echo '无';
                        } else {
                            foreach($allBankNoResult as $v) {
                                echo '<li>' . $v . '</li>';
                            }
                        };
                    ?>
                </ul>
            </div>
            <?php if(!empty($test)) : ?>
            <div class="h"><h5>特别注意</h5></div>
            <div class="section">
                <div class="col warning">
                    <?php 
                        foreach($test as $value) {
                            echo $value . '<br>';
                        }
                    ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="h"><h5>招行天书</h5></div>
            <div class="section">
                <div class="col">商户编码：<?php echo $code; ?></div>
                <?php 
                    if ($ts1 != 'null' || $ts2 != 'null' || $ts3 != 'null') {

                        if ($ts1 == 'false') {
                            echo '<div class="col">' . $now . '期：<span class="onbp">恭喜，不在招行黑名单里。</span></div>';
                        } elseif ($ts1 == 'true') {
                            echo '<div class="col">' . $now . '期：<span class="nobp">我去，这货在招行黑名单。</span></div>';
                        } elseif ($ts1 == 'update') {
                            echo '<div class="col">' . $now . '期：<span class="nobp">抱歉，本期数据还未更新。</span></div>';
                        }

                        if ($ts2 == 'false') {
                            echo '<div class="col">' . $pre . '期：<span class="onbp">恭喜，不在招行黑名单里。</span></div>';
                        } elseif ($ts2 == 'true') {
                            echo '<div class="col">' . $pre . '期：<span class="nobp">我去，这货在招行黑名单。</span></div>';
                        } elseif ($ts2 == 'update') {
                            echo '<div class="col">' . $pre . '期：<span class="nobp">抱歉，本期数据还未更新。</span></div>';
                        }

                        if ($ts3 == 'false') {
                            echo '<div class="col">' . $nxt . '期：<span class="onbp">恭喜，不在招行黑名单里。</span></div>';
                        } elseif ($ts3 == 'true') {
                            echo '<div class="col">' . $nxt . '期：<span class="nobp">我去，这货在招行黑名单。</span></div>';
                        } elseif ($ts3 == 'update') {
                            echo '<div class="col">' . $nxt . '期：<span class="nobp">抱歉，本期数据还未更新。</span></div>';
                        }

                    }
                ?>
                <div class="col ex">1) 输入完整15位商户编码才能判断是否在招行天书中。2) 如果商户在黑名单里，招商银行是没有积分的。3) 天书数据每月自动同步招行官网数据。</div>
            </div>
            <div class="h"><h5>商户描述</h5></div>
            <div class="section">
                <div class="col note"><p class="mg"><?php echo get_post_meta($postId, 'mcc-note', true); ?></p><?php echo strlen(get_post_meta($postId, 'mcc-rule', true)) > 1 ? '<p>'.get_post_meta($postId, 'mcc-rule', true).'</p>' : ''; ?></div>
            </div>
        </div>
    </div>
    <div class="update">©<a href="javascript:;">MCC信息查询</a>版权所有 | 最后更新:<?php echo date('Y年m月'); ?></div>
</div>
</body>
</html>

<?php 
}