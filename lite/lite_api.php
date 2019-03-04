<?php 
add_action('rest_api_init', function () {
    register_rest_route('apps/v1', 'api/get', array(
        'methods' => 'GET',
        'callback' => 'getDiyData'
    ));
});
function getDiyData($request) {
    global $wpdb;
	$cate = $request['cate']; // * cate=bank 银行MCC汇总, cate=tianshu 招行天书, cate=zone 银行收录, cate=all 所有银行mcc汇总, cate=test 注意事项
	$code = $request['code']; // * 0-22 代表银行, code=15位编码 招行天书, code=100 网络支付, code=101 地区编码, code=102 支付平台
	$data = getDiyInfo($cate, $code);
	return $data;
}
function getDiyInfo($cate, $code) {
	global $wpdb;
	$page = 606;
	$bank = json_decode(get_post_meta($page, 'zone-bank', true), true); // 所有收录银行
	if($code == 100) { // 网络支付
		$result["code"] = "success";
        $result["message"] = "get online payment success";
        $result["status"] = "200";
        $result["data"] = json_decode(get_post_meta($page, 'online-payment', true));
        return $result;
	} elseif ($code == 101) { // 地区编码
		$result["code"] = "success";
        $result["message"] = "get area code success";
        $result["status"] = "200";
        $result["data"] = json_decode(get_post_meta($page, 'areacode', true));
        return $result;
	} elseif ($code == 102) { // 支付平台
		$result["code"] = "success";
        $result["message"] = "get payments company success";
        $result["status"] = "200";
        $result["data"] = json_decode(get_post_meta($page, 'paycompany', true));
        return $result;
	} elseif ($cate == 'zone') { // 银行收录
		$result["code"] = "success";
        $result["message"] = "get all bank success";
        $result["status"] = "200";
        $result["data"] = json_decode(get_post_meta($page, 'zone-bank', true));
        return $result;
	} elseif ($cate == 'bank') {
		$result["code"] = "success";
        $result["message"] = "get bank success";
        $result["status"] = "200";
        $result["data"] = json_decode(get_post_meta($page, 'all-bank-'.$code, true));
        return $result;
	} elseif($cate == 'find') { // 根据MCC查找各银行积分情况
		for ($i = 0; count($bank) > $i; $i++) {
			$theOnbp = []; $theInfo = [];
			$theOnbp = json_decode(get_post_meta($page, 'all-bank-'.$i, true), true);
			$theInfo = $theOnbp[0]['data'];
			if(in_array($code, $theInfo)) {
				$allBankResult[] = true;
			} else {
				$allBankResult[] = false;
			}
		}
		$data = json_encode($allBankResult);
		$result["code"] = "success";
        $result["message"] = "get mcc of bank success";
        $result["status"] = "200";
        $result["data"] = json_decode($data);
        return $result;
	} elseif($cate == 'test') { // 注意事项
		$testData = json_decode(get_post_meta($page, 'all-test', true), true);
		for($i = 0; count($testData) > $i; $i++) {
			if($testData[$i]['name'] == 'other') {
				for($x = 0; count($testData[$i]['data']) > $x; $x++) {
					if ($testData[$i]['data'][$x]['code'] == $code) {
					  $test[] = $testData[$i]['data'][$x]['data'];
					}
				}
			} else {
				if(in_array($code, $testData[$i]['data'])) {
					$test[] = $testData[$i]['name'];
				}
			}
		}
		$data = json_encode(!empty($test) ? $test : '');
		$result["code"] = "success";
        $result["message"] = "get test data success";
        $result["status"] = "200";
        $result["data"] = json_decode($data);
        return $result;
	} elseif ($cate == 'tianshu') { // 招行天书
		if($code == 'md5') {
			$path = $_SERVER['DOCUMENT_ROOT'];
			$o = $path . '/data/JFmch_nbr.rar';
			$n = 'http://market.cmbchina.com/ccard/jf/JFmch_nbr.rar';
			$data = '{"N":"' . md5_file($n) . '","O":"' . md5_file($o) . '"}';
		} elseif($code == 'all') {

			function GetHttpStatusCode($url){ 
				$curl = curl_init();
				curl_setopt($curl,CURLOPT_URL,$url);//获取内容url
				curl_setopt($curl,CURLOPT_HEADER,1);//获取http头信息
				curl_setopt($curl,CURLOPT_NOBODY,1);//不返回html的body信息
				curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//返回数据流，不直接输出
				curl_setopt($curl,CURLOPT_TIMEOUT,30); //超时时长，单位秒
				curl_exec($curl);
				$rtn = curl_getinfo($curl,CURLINFO_HTTP_CODE);
				curl_close($curl);
				return  $rtn;
			}
			$url = site_url() . '/tianshu/' . date("Ym");
			$StatusCode = GetHttpStatusCode($url);

			if ($StatusCode == 404) {

				$file = $_SERVER['DOCUMENT_ROOT'] . '/data/' . date("Ym") . '.txt';
				$exis = is_file($file);

				if ($exis) {
					$str = file_get_contents($file);//将整个文件内容读入到一个字符串中
					$str_encoding = mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');//转换字符集（编码）
					$arr = explode("\r\n", $str_encoding);//转换成数组

					//取前15位字符串
					foreach ($arr as &$row) {
						$row = substr($row , 0, 15);
					}
					unset($row);
					//得到后的数组
					//$data = json_encode($arr);//15位完整的商户编码
					$arr1 = $arr;
					$arr2 = $arr;
					$count  = count($arr);

					//取前3位字符串
					foreach ($arr1 as &$val) {
						$val = substr($val , 0, 3);
					}
					unset($val);
					$arr1 = array_count_values($arr1);//统计数组中所有的值出现的次数
					arsort($arr1);//倒序排序
					$i = 1;
					foreach($arr1 as $key=>$value) {
						if($i<=10) {
							$iarr[] = $key;
							$iarr[] = $value;
							$paycompanyCount[] = $iarr; 
						} else {
							break;
						}
						$iarr = [];
						$i++;
					}
					$total[] = $paycompanyCount;
					unset($arr1);

					//取前4-7位字符串
					foreach ($arr2 as &$val) {
						$val = substr($val , 3, 4);
					}
					unset($val);
					$arr2 = array_count_values($arr2);//统计数组中所有的值出现的次数
					arsort($arr2);//倒序排序
					$i = 1;
					foreach($arr2 as $key=>$value) {
						if($i<=10) {
							$iarr[] = $key;
							$iarr[] = $value;
							$areaCount[] = $iarr; 
						} else {
							break;
						}
						$iarr = [];
						$i++;
					}
					$total[] = $areaCount;
					unset($arr2);

					//取前8-11位字符串
					foreach ($arr as &$val) {
						$val = substr($val , 7, 4);
					}
					unset($val);
					$arr = array_count_values($arr);//统计数组中所有的值出现的次数
					arsort($arr);//倒序排序
					$i = 1;
					foreach($arr as $key=>$value) {
						if($i<=10) {
							$iarr[] = $key;
							$iarr[] = $value;
							$mccCount[] = $iarr; 
						} else {
							break;
						}
						$iarr = [];
						$i++;
					}
					$total[] = $mccCount;
					unset($arr);

					$data = '{"date":"' . date("Ym") . '","count":' . $count . ',"data":' . json_encode($total) .'}';

					$content = array(
						'post_title' 	=> date("Ym"),
						'post_content' 	=> '',
						'post_status' 	=> 'publish',
						'post_author' 	=> 1,
						'post_category' => array(6),
						'post_name' 	=> date("Ym")
					);

					$pid = wp_insert_post($content);
					update_post_meta($pid, 'summary', $data);
					
				} else {
					$data = '{"date":"' . date("Ym") . '","count":0,"data":[]}';
				}
			} elseif ($StatusCode == 200) {
				$args = array(
					'name'        => date("Ym"),
					'post_type'   => 'post',
					'post_status' => 'publish',
					'numberposts' => 1
					);
				$the_posts = get_posts($args);
				if($the_posts) {
					$pid = $the_posts[0] -> ID;
				} else {
					$pid = '';
				}
				$data = get_post_meta($pid, 'summary', true);
			} else {
				$data = '{"date":"' . date("Ym") . '","count":0,"data":[]}';
			}

		} else {
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
					$data = '本期数据还没有更新';
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
			$data = '[{"date":'.$now.',"result":"'.$ts1.'"},{"date":'.$pre.',"result":"'.$ts2.'"},{"date":'.$nxt.',"result":"'.$ts3.'"}]';
		}

		$result["code"] = "success";
        $result["message"] = "get tianshu success";
        $result["status"] = "200";
		$result["data"] = json_decode($data);
		return $result;
		
	} elseif($cate == 'all') { // 所有银行mcc汇总

		$zone_mcc = json_decode(get_post_meta($page, 'zone-mcc', true), true);
		$bank_mcc = json_decode(get_post_meta($page, 'all-bank-'.$code, true), true);

		for($i = 0; count($bank_mcc[0]['data']) > $i; $i++) {

			if(in_array($bank_mcc[0]['data'][$i], $zone_mcc[0]['data'][0]['code'])) {//餐娱类

				$onbp00[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[0]['data'][1]['code'])) {

				$onbp01[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[0]['data'][2]['code'])) {

				$onbp02[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[0]['data'][3]['code'])) {

				$onbp03[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[0]['data'][4]['code'])) {

				$onbp04[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][0]['code'])) {//一般类

				$onbp10[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][1]['code'])) {

				$onbp11[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][2]['code'])) {

				$onbp12[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][3]['code'])) {

				$onbp13[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][4]['code'])) {

				$onbp14[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][5]['code'])) {

				$onbp15[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][6]['code'])) {

				$onbp16[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][7]['code'])) {

				$onbp17[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][8]['code'])) {

				$onbp18[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][9]['code'])) {

				$onbp19[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][10]['code'])) {

				$onbp110[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[1]['data'][11]['code'])) {

				$onbp111[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[2]['data'][0]['code'])) {//民生类

				$onbp20[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[2]['data'][1]['code'])) {

				$onbp21[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[2]['data'][2]['code'])) {

				$onbp22[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[2]['data'][3]['code'])) {

				$onbp23[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[2]['data'][4]['code'])) {

				$onbp24[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[3]['data'])) {//公益类

				$onbp30[] = $bank_mcc[0]['data'][$i];

			} elseif(in_array($bank_mcc[0]['data'][$i], $zone_mcc[4]['data'])) {//县乡优惠

				$onbp40[] = $bank_mcc[0]['data'][$i];

			} else {//特殊类

				$onbp50[] = $bank_mcc[0]['data'][$i];

			}
		}

		for($i = 0; count($bank_mcc[1]['data']) > $i; $i++) {

			if(in_array($bank_mcc[1]['data'][$i], $zone_mcc[0]['data'][0]['code'])) {//餐娱类

				$nobp00[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[0]['data'][1]['code'])) {

				$nobp01[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[0]['data'][2]['code'])) {

				$nobp02[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[0]['data'][3]['code'])) {

				$nobp03[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[0]['data'][4]['code'])) {

				$nobp04[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][0]['code'])) {//一般类

				$nobp10[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][1]['code'])) {

				$nobp11[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][2]['code'])) {

				$nobp12[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][3]['code'])) {

				$nobp13[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][4]['code'])) {

				$nobp14[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][5]['code'])) {

				$nobp15[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][6]['code'])) {

				$nobp16[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][7]['code'])) {

				$nobp17[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][8]['code'])) {

				$nobp18[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][9]['code'])) {

				$nobp19[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][10]['code'])) {

				$nobp110[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[1]['data'][11]['code'])) {

				$nobp111[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[2]['data'][0]['code'])) {//民生类

				$nobp20[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[2]['data'][1]['code'])) {

				$nobp21[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[2]['data'][2]['code'])) {

				$nobp22[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[2]['data'][3]['code'])) {

				$nobp23[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[2]['data'][4]['code'])) {

				$nobp24[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[3]['data'])) {//公益类

				$nobp30[] = $bank_mcc[1]['data'][$i];

			} elseif(in_array($bank_mcc[1]['data'][$i], $zone_mcc[4]['data'])) {//县乡优惠

				$nobp40[] = $bank_mcc[1]['data'][$i];

			} else {//特殊类

				$nobp50[] = $bank_mcc[1]['data'][$i];

			}
		}

		$data = '[ [ { "type": "餐娱类", "data": [ { "name": "宾馆/餐饮类", "code": ' . json_encode(empty($onbp00) ? "" : $onbp00) . '}, { "name": "珠宝/工艺类", "code": ' . json_encode(empty($onbp01) ? "" : $onbp01) . '}, { "name": "娱乐类", "code": ' . json_encode(empty($onbp02) ? "" : $onbp02) . '}, { "name": "房产类", "code": ' . json_encode(empty($onbp03) ? "" : $onbp03) . '}, { "name": "汽车销售类", "code": ' . json_encode(empty($onbp04) ? "" : $onbp04) . '} ] }, { "type": "一般类", "data": [ { "name": "批发类", "code": ' . json_encode(empty($onbp10) ? "" : $onbp10) . '}, { "name": "百货/其他零售类", "code": ' . json_encode(empty($onbp11) ? "" : $onbp11) . '}, { "name": "运输服务类", "code": ' . json_encode(empty($onbp12) ? "" : $onbp12) . '}, { "name": "通讯服务类", "code": ' . json_encode(empty($onbp13) ? "" : $onbp13) . '}, { "name": "金融业服务类", "code": ' . json_encode(empty($onbp14) ? "" : $onbp14) . '}, { "name": "个人服务类", "code": ' . json_encode(empty($onbp15) ? "" : $onbp15) . '}, { "name": "商业服务类", "code": ' . json_encode(empty($onbp16) ? "" : $onbp16) . '}, { "name": "维修服务类", "code": ' . json_encode(empty($onbp17) ? "" : $onbp17) . '}, { "name": "娱乐和游艺服务类", "code": ' . json_encode(empty($onbp18) ? "" : $onbp18) . '}, { "name": "专业服务/成员服务类", "code": ' . json_encode(empty($onbp19) ? "" : $onbp19) . '}, { "name": "其他类型服务类", "code": ' . json_encode(empty($onbp110) ? "" : $onbp110) . '}, { "name": "旅行社/景区门票类", "code": ' . json_encode(empty($onbp111) ? "" : $onbp111) . '} ] }, { "type": "民生类", "data": [ { "name": "民生类", "code": ' . json_encode(empty($onbp20) ? "" : $onbp20) . '}, { "name": "交通运输类", "code": ' . json_encode(empty($onbp21) ? "" : $onbp21) . '}, { "name": "水电气缴费类", "code": ' . json_encode(empty($onbp22) ? "" : $onbp22) . '}, { "name": "政府类", "code": ' . json_encode(empty($onbp23) ? "" : $onbp23) . '}, { "name": "便民类", "code": ' . json_encode(empty($onbp24) ? "" : $onbp24) . '} ] }, { "type": "公益类", "data": ' . json_encode(empty($onbp30) ? "" : $onbp30) . '}, { "type": "县乡优惠", "data": ' . json_encode(empty($onbp40) ? "" : $onbp40) . '}, { "type": "特殊类", "data": ' . json_encode(empty($onbp50) ? "" : $onbp50) . '} ], [ { "type": "餐娱类", "data": [ { "name": "宾馆/餐饮类", "code": ' . json_encode(empty($nobp00) ? "" : $nobp00) . '}, { "name": "珠宝/工艺类", "code": ' . json_encode(empty($nobp01) ? "" : $nobp01) . '}, { "name": "娱乐类", "code": ' . json_encode(empty($nobp02) ? "" : $nobp02) . '}, { "name": "房产类", "code": ' . json_encode(empty($nobp03) ? "" : $nobp03) . '}, { "name": "汽车销售类", "code": ' . json_encode(empty($nobp04) ? "" : $nobp04) . '} ] }, { "type": "一般类", "data": [ { "name": "批发类", "code": ' . json_encode(empty($nobp10) ? "" : $nobp10) . '}, { "name": "百货/其他零售类", "code": ' . json_encode(empty($nobp11) ? "" : $nobp11) . '}, { "name": "运输服务类", "code": ' . json_encode(empty($nobp12) ? "" : $nobp12) . '}, { "name": "通讯服务类", "code": ' . json_encode(empty($nobp13) ? "" : $nobp13) . '}, { "name": "金融业服务类", "code": ' . json_encode(empty($nobp14) ? "" : $nobp14) . '}, { "name": "个人服务类", "code": ' . json_encode(empty($nobp15) ? "" : $nobp15) . '}, { "name": "商业服务类", "code": ' . json_encode(empty($nobp16) ? "" : $nobp16) . '}, { "name": "维修服务类", "code": ' . json_encode(empty($nobp17) ? "" : $nobp17) . '}, { "name": "娱乐和游艺服务类", "code": ' . json_encode(empty($nobp18) ? "" : $nobp18) . '}, { "name": "专业服务/成员服务类", "code": ' . json_encode(empty($nobp19) ? "" : $nobp19) . '}, { "name": "其他类型服务类", "code": ' . json_encode(empty($nobp110) ? "" : $nobp110) . '}, { "name": "旅行社/景区门票类", "code": ' . json_encode(empty($nobp111) ? "" : $nobp111) . '} ] }, { "type": "民生类", "data": [ { "name": "民生类", "code": ' . json_encode(empty($nobp20) ? "" : $nobp20) . '}, { "name": "交通运输类", "code": ' . json_encode(empty($nobp21) ? "" : $nobp21) . '}, { "name": "水电气缴费类", "code": ' . json_encode(empty($nobp22) ? "" : $nobp22) . '}, { "name": "政府类", "code": ' . json_encode(empty($nobp23) ? "" : $nobp23) . '}, { "name": "便民类", "code": ' . json_encode(empty($nobp24) ? "" : $nobp24) . '} ] }, { "type": "公益类", "data": ' . json_encode(empty($nobp30) ? "" : $nobp30) . '}, { "type": "县乡优惠", "data": ' . json_encode(empty($nobp40) ? "" : $nobp40) . '}, { "type": "特殊类", "data": ' . json_encode(empty($nobp50) ? "" : $nobp50) . '} ] ]';
		
		$result["code"] = "success";
        $result["message"] = "get all mcc success";
        $result["status"] = "200";
        $result["data"] = json_decode($data);
        return $result;
	} else {
        $result["code"] = "success";
        $result["message"] = "get data fail";
        $result["status"] = "501";
        $result["data"] = [];
        return $result;
	}
}