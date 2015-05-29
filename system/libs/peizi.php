<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

/**
 * 每天自动运行，扣除利息
 */
function auto_charging_rate_money($allow_arrearage = true,$allow_send_msg = true){

	$cur_date = to_date(TIME_UTC,"Y-m-d");

	$sql = "select id,user_id,order_sn, stock_sn, rate_money,begin_date,last_fee_date,next_fee_date,rate_money,type,is_holiday_fee from ".DB_PREFIX."peizi_order where rate_money > 0 and `status` = 4 and rate_type = 0 and next_fee_date <= '".$cur_date."'";

	if ($allow_arrearage == false){
		$sql .= " and is_arrearage = 0 ";
	}
	$sql .= " order by next_fee_date asc";

	$peizi_order_list = $GLOBALS['db']->getAll($sql);


	require_once APP_ROOT_PATH.'system/libs/user.php';


	foreach ($peizi_order_list as $k => $v) {

		
		
		$user_id = intval($v['user_id']);
		$order_id = intval($v['id']);
		$sql = "select id,AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."') AS money,user_name,AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') AS mobile from ".DB_PREFIX."user where id = ".$user_id;
		$user_info = $GLOBALS['db']->getRow($sql);
		$money = floatval($user_info['money']);

		if ($v['rate_money'] >= money){
			//扣费失败，余额不足
			$sql = "update ".DB_PREFIX."peizi_order set is_arrearage = 1 where id = ".$order_id;
			$GLOBALS['db']->query($sql);
				
			if ($allow_send_msg && app_conf("SMS_ON") == 1){
				//通知用户扣费失败
				$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_CHARGING_FAILED_MSG'");
				$tmpl_content = $tmpl['content'];
					
				$notice['site_name'] = app_conf("SHOP_TITLE");
				$notice['user_name'] = $user_info["user_name"];
				$notice['order_sn'] = $v['order_sn'];
				$notice['fee_date'] = $v['next_fee_date'];
				$notice['rate_money'] = format_price($v['rate_money']);
					
				$GLOBALS['tmpl']->assign("notice",$notice);
					
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					
				$msg_data['dest'] = $user_info['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['title'] = "配资自动扣费失败通知";
				$msg_data['content'] = addslashes($msg);;
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = TIME_UTC;
				$msg_data['user_id'] = $user_info['id'];
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			}
		}else{
			//自动继费到下期
			if ($v['type'] == 2){
				//type 配资类型;0:天;1周；2月

				$y1 = intval(to_date(to_timespan($v['begin_date']),'Y'));
				$m1 = intval(to_date(to_timespan($v['begin_date']),'m'));
					
				$y2 = intval(to_date(to_timespan($v['next_fee_date']),'Y'));
				$m2 = intval(to_date(to_timespan($v['next_fee_date']),'m'));
					
				$month = ($y2 - $y1) * 12 + ($m2 - $m1) + 1;

				//按自然月计算，如使用1个月，1月8日到2月8日，当月日期没有,则该按月的最后一天计算，包含各类节假日
				$next_fee_date = add_month($v['begin_date'], $month);
			}else{
				$next_fee_date = get_peizi_end_date($v['next_fee_date'], 1,$v['type'],$v['is_holiday_fee']);
			}

				
				
			$sql = "update ".DB_PREFIX."peizi_order set total_rate_money = total_rate_money + rate_money, is_arrearage = 0, last_fee_date = '".$v['next_fee_date']."', next_fee_date = '".$next_fee_date."' where  next_fee_date = '".$v['next_fee_date']."' and id = ".$order_id;
			//echo $sql; exit;
			$GLOBALS['db']->query($sql);
				
			if($GLOBALS['db']->affected_rows()){
					
				$fee_data = array();
				$fee_data['user_id'] = $user_id;
				$fee_data['peizi_order_id'] = $order_id;
				$fee_data['create_date'] = to_date(TIME_UTC);
				$fee_data['fee_date'] = $v['next_fee_date'];
				$fee_data['fee'] = $v['rate_money'];
				if ($v['type'] == 2){
					$fee_data['fee_type'] = 3;//费用类型;1:业务审核费;2:日利息;3:月利息;4:其它费用
					$fee_data['memo'] = '后台自动扣月管理费';
				}else{
					$fee_data['fee_type'] = 2;//费用类型;1:业务审核费;2:日利息;3:月利息;4:其它费用
					$fee_data['memo'] = '后台自动扣日管理费';
				}

				

				$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order_fee_list",$fee_data,"INSERT");

				//30:配资本金; 31:配资预交款;32:配资审核费;33:配资日利息;34:配资月利息
				if ($v['type'] == 2){
					modify_account(array("money"=>-$v['rate_money'],'site_money'=>$v['rate_money']), $user_id,'自动扣费,配资编号:'.$order_id,34);
				}else{
					modify_account(array("money"=>-$v['rate_money'],'site_money'=>$v['rate_money']), $user_id,'自动扣费,配资编号:'.$order_id,33);
				}

				//通知用户扣费成功
				if ($allow_send_msg && app_conf("SMS_ON") == 1){
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_CHARGING_SUCCESS_MSG'");
					$tmpl_content = $tmpl['content'];

					$notice['site_name'] = app_conf("SHOP_TITLE");
					$notice['user_name'] = $user_info["user_name"];
					$notice['order_sn'] = $v['order_sn'];
					$notice['fee_date'] = $v['next_fee_date'];
					$notice['next_fee_date'] = $next_fee_date;
					$notice['rate_money'] = format_price($v['rate_money']);

					$GLOBALS['tmpl']->assign("notice",$notice);

					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

					$msg_data['dest'] = $user_info['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['title'] = "配资自动扣费成功通知";
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = TIME_UTC;
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
			}
		}
	}

	//查询，是否还有未扣费的
	$sql = "select count(*) from ".DB_PREFIX."peizi_order where is_arrearage = 0 and rate_money > 0 and `status` = 4 and rate_type = 0 and next_fee_date <= '".$cur_date."'";

	$num = intval($GLOBALS['db']->getOne($sql));
	if ($num > 0){
		//return $num;
		auto_charging_rate_money(false,$allow_send_msg);
	}else{
		return 1;//to_date(TIME_UTC);
	}


}


/**
 * 返回： 开始交易时间 是否显示：今天 
 * @return number 1:显示；0：不显示
 */
function get_peizi_show_today(){
	//开始交易时间，是否显示：今天(节假日，周末及下午1:30后，也不显示）
	$i_time = intval(to_date(TIME_UTC,'Hi'));
	if ($i_time>1430){ //超过下午2点半后，今天不显示
		$is_show_today = 0;
	}else{
		if (get_peizi_is_holiday(to_date(TIME_UTC,'Y-m-d'))){
			$is_show_today = 0;//节假日今天也不显示
		}else{
			$is_show_today = 1;			
		}
	}
	
	return $is_show_today;
}
/**
 * 更新帐户金额
 * @param unknown_type $order_id
 * @param unknown_type $user_id
 * @param unknown_type $stock_date
 * @param unknown_type $stock_money
 */
function set_peizi_order_stock_money($order_id,$user_id,$stock_date,$stock_money){
	
	$sql = "update ".DB_PREFIX."peizi_order set stock_money = '$stock_money', stock_date = '$stock_date' where id = ".$order_id;
	$GLOBALS['db']->query($sql);
	
	$sql = "delete from ".DB_PREFIX."peizi_order_stock_money where peizi_order_id = ".$order_id." and stock_date = '".$stock_date."'";
	$GLOBALS['db']->query($sql);
	
	$stock = array();
	//$stock['user_id'] = $user_id;
	$stock['peizi_order_id'] = $order_id;
	$stock['stock_date'] = $stock_date;
	$stock['stock_money'] = $stock_money;
	$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order_stock_money",$stock); //插入
}

/**
 * 格式化  peizi_order 展示数据
 * @param unknown_type $vo
 * @return unknown
 */
function get_peizi_order_fromat($vo){
	
	
	$vo['total_money'] = $vo['cost_money']+$vo['borrow_money'];
	//配资类型
	$vo['type_format'] = get_peizi_type($vo['type']);

	$vo['status_format'] = get_peizi_status($vo['status']);
	
	$vo['user_name'] = get_user_name($vo['user_id']);

	//$vo['time_limit_num_format'] = $vo['time_limit_num'].$vo['type_format'];
	$vo['time_limit_num_format'] = $vo['time_limit_num'];
	
	$vo['is_today_format'] = get_peizi_is_today($vo['is_today']);
	
	$vo['rate_format'] = getPeiziRateFormat($vo['rate'],$vo['type']);
	//盈亏金额
	$vo['loss_money'] = $vo["stock_money"] - ($vo["cost_money"] + $vo["borrow_money"]);
	
	$vo['loss_money_format'] = format_price($vo['loss_money']);
	
	$vo['loss_rate'] = $vo['loss_money']/($vo["cost_money"] + $vo["borrow_money"]);
	
	$vo['loss_rate_format'] = number_format($vo['loss_rate'] * 100, 2, '.', '') ."%";
	
	$vo['total_money_format'] = format_price($vo['total_money']);//总操盘资金
	
	$vo['stock_money_format'] = format_price($vo['stock_money']);//股票总值
	
	$vo['payoff_rate_format'] =  ($vo['payoff_rate'] * 100).'%';
	
	$vo['cost_money_format'] = format_price($vo['cost_money']);//保证金
	$vo['re_cost_money_format'] = format_price($vo['re_cost_money']);//返还保证金
	$vo['user_payoff_fee_format'] = format_price($vo['user_payoff_fee']);//用户盈利
	$vo['site_payoff_fee_format'] = format_price($vo['site_payoff_fee']);//平台盈利
	$vo['other_fee_format'] = format_price($vo['other_fee']);//其它费用
	$vo['manage_money_format'] = format_price($vo['manage_money']);//业务审核费

	$vo['warning_line_format'] = format_price($vo['warning_line']);//亏损警戒线
	$vo['open_line_format'] = format_price($vo['open_line']);//亏损平仓线
	
	$vo['rate_money_format'] = format_price($vo['rate_money']);//每日或每月利息费用
	
	$vo['first_rate_money_format'] = format_price($vo['first_rate_money']);//首次收取的利息费用(或预存款)
	$vo['borrow_money_format'] = format_price($vo['borrow_money']);//借款金额
	$vo['total_rate_money_format'] = format_price($vo['total_rate_money']);//已收利息总额
	
	//交易开始时间(0:下一交易日;1:今天)	
	if ($vo['is_today'] == 1){
		$vo['is_today_format'] = '今天';
	}else{
		$vo['is_today_format'] = '下一交易日';
	}
	
	if (isset($vo['user_money']))
	$vo['user_money_format'] = format_price($vo['user_money']);//用户帐户余额
	
	//type=2时，有效;0:按月收取;1:一次性收取
	if ($vo['type'] == 2){
		if ($vo['rate_type'] == 1){
			$vo['rate_type_format'] = '一次性收取';
		}else{
			$vo['rate_type_format'] = '按月收取';
		}
	}else if ($vo['type'] == 1){
		$vo['rate_type_format'] = '按收益比收取';
	}else if ($vo['type'] == 0){
		if ($vo['rate_type'] == 1){
			$vo['rate_type_format'] = '一次性收取';
		}else{
			$vo['rate_type_format'] = '按日收取';
		}
	}
	
	if ($vo['is_holiday_fee'] == 1){
		$vo['is_holiday_fee_format'] = '是';
	}else{
		$vo['is_holiday_fee_format'] = '否';
	}
	
	return $vo;
}

function get_peizi_conf($conf_id,$borrow_money,$lever,$month,$rate_id){
	//$peizi_conf = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."peizi_conf where type = ".$type." limit 1");

	$peizi_conf = load_auto_cache("peizi_conf",array('id'=>$conf_id));

	$sql = "select lm.* from ".DB_PREFIX."peizi_conf_lever_coefficient_list lm where lm.pid = ".intval($peizi_conf['id'])." and lm.lever = ".$lever;
	$lm = $GLOBALS['db']->getRow($sql);
	
	if ($peizi_conf['type']  == 2){
		$sql = "select * from ".DB_PREFIX."peizi_conf_rate_list rl where pid = ".intval($peizi_conf['id'])." and min_lever <= ".$lever." and ".$lever." <= max_lever and min_month <= ".$month." and ".$month." <= max_month and min_money <= ".$borrow_money." and ".$borrow_money." <= max_money";		
	}else{
		$sql = "select * from ".DB_PREFIX."peizi_conf_rate_list rl where pid = ".intval($peizi_conf['id'])." and min_lever <= ".$lever." and ".$lever." <= max_lever and min_money <= ".$borrow_money." and ".$borrow_money." <= max_money";		
	}
	
	$rate_row = $GLOBALS['db']->getRow($sql);	
		//print_r($sql);exit;
	//风险保证金(本金)
	//实盘资金
	//总操盘资金
	//亏损警戒线
	//亏损平仓线
	//账户管理费
	//
	/*
	* total_money:总操盘资金
	* warning_line:亏损警戒线
	* open_line:亏损平仓线
	* rate_id: 利率ID
	* rate: 利率
	* rate_format: 利率格式化
	* rate_money:账户管理费
	* rate_money_format: 账户管理费格式化后
	* limit_info: 仓位限制消息
	* payoff_rate: 盈利比如：0.7则，实际盈利的70%归操盘者；30%归平台
	* payoff_rate_format
	*/
	$parma = array();
	$parma['peizi_conf'] = $peizi_conf;

	$parma['borrow_money'] = $borrow_money;
	$parma['cost_money'] = floor($borrow_money / $lever);
	$parma['total_money'] = $borrow_money + $parma['cost_money'];



	$parma['warning_line'] = floor($borrow_money + $borrow_money * $lm['warning_coefficient']);
	$parma['open_line'] = floor($borrow_money + $borrow_money * $lm['open_coefficient']);



	$parma['borrow_money_format'] = getPeiziMoneyFormat($parma['borrow_money']);
	$parma['cost_money_format'] = getPeiziMoneyFormat($parma['cost_money']);
	$parma['total_money_format'] = getPeiziMoneyFormat($parma['total_money']);
	$parma['warning_line_format'] = getPeiziMoneyFormat($parma['warning_line']);
	$parma['open_line_format'] = getPeiziMoneyFormat($parma['open_line']);


	if ($rate_id <= 0 || $rate_id > 4){
		$rate_id = 1;
	}

	$parma['rate_id'] = $rate_id;
	$parma['rate'] = $rate_row['rate'.$rate_id];
	$parma['rate_format'] = getPeiziRateFormat($parma['rate']);

	$parma['rate_money'] = $borrow_money * $parma['rate'];
	$parma['rate_money_format'] = getPeiziMoneyFormat($parma['rate_money']);
	
	$parma['payoff_rate'] = $lm['payoff_rate'];
	if ($parma['payoff_rate'] == 0) $parma['payoff_rate'] = 1;
	
	$parma['payoff_rate_format'] =  ($parma['payoff_rate'] * 100).'%';
	
	$parma['limit_info'] = $rate_row['limit_info'];

	$parma['is_show_today'] = $rate_row['is_show_today'];
	
	return $parma;

}


function getPeiziMoneyFormat($money) {
	if ($money == 0)
		return '免费';
	else
		return number_format($money,2);
}


	//利率格式化
function getPeiziRateFormat($rate,$type) {
	$rate_format = rate;
	
	if ($rate == 0){
		$rate_format = '免';
	}else{
		if ($type == 2){
			$rate_format = ($rate * 100).'分 / 每月';
		}else{
		$rate_format = ($rate * 1000).'分 / 每日';
			}
	}

	return $rate_format;
}
	
 /**
  * 返回配资类型
  * @param unknown_type $type
  * @return string 配资类型;0:天;1周；2月
  */
 function get_peizi_type($type){
	if ($type == 0){
 		return '天';
 	}else if ($type == 1){
 		return '周';
 	}else if ($type == 2){
 		return '月';
 	}else{
 		return '未知';
 	}
 }
 
 
/**
 * 开始交易时间
 * @param unknown_type $is_today
 * @return string
 */
 function get_peizi_is_today($is_today){
 	if ($is_today == 0){
 		return '今天';
 	}else{
 		return '下个交易日';
 	}
 }
 
 
 /**
  * 订单状态0:在申请；1:支付成功,等待配资;2:初审通过,股票开户;3:初审不通过,解冻资金;4:复审通过,开始投资股票;5:复审不通过,退回初审; 6:平仓结束,结算资金
  * @param unknown_type $status
  * @return string
  */
 function get_peizi_status($status){
 	if ($status == 0){
 		return '在申请';
 	}else if ($status == 1){
 		return '支付成功';
 	}else if ($status == 2){
 		return '初审通过';
 	}else if ($status == 3){
 		return '初审不通过';
 	}else if ($status == 4){
 		return '复审通过,开始投资';
 	}else if ($status == 5){
 		return '复审不通过';
 	}else if ($status == 6){
 		return '配资结束';
 	}else{
 		return '未知';
 	}
 } 

/**
 * 配资费用类型
 * @param unknown_type $type
 * @return string
 */
 function get_peizi_fee_type($type){
 	//费用类型;1:业务审核费;2:日利息;3:月利息
 	if ($type == 1){
 		return '业务审核费';
 	}else if ($type == 1){
 		return '日利息';
 	}else if ($type == 2){
 		return '月利息';
 	}else{
 		return '未知';
 	}
 }
 
 /**
  * 返回配资操作类型
  * @param unknown_type $type
  * @return string 0:追加保证金;1:申请延期;2:申请增资;3:申请减资;4:提取赢余;5:申请结束配资
  */
 function get_peizi_op_type($type){
 	if ($type == 0){
 		return '追加保证金';
 	}else if ($type == 1){
 		return '申请延期';
 	}else if ($type == 2){
 		return '申请增资';
 	}else if ($type == 3){
 		return '申请减资';
 	}else if ($type == 4){
 		return '提取赢余';
 	}else if ($type == 5){
 		return '申请结束配资';
 	}else{
 		return '未知';
 	}
 }
 
 /**
  * 配资申请操作，审核状态 审核状态;0:未审核;1:初审通过;2:初审未通过;3:复审通过;4:复审未通过;5:撤消申请
  * @param unknown_type $status
  * @return string
  */
 function get_peizi_op_status($status){
 	if ($status == 0){
 		return '未审核';
 	}else if ($status == 1){
 		return '初审通过';
 	}else if ($status == 2){
 		return '初审未通过';
 	}else if ($status == 3){
 		return '复审通过';
 	}else if ($status == 4){
 		return '复审未通过';
 	}else if ($status == 5){
 		return '撤消申请';
 	}else{
 		return '未知';
 	}
 }
 
 /**
  * 配资申请操作描述
  * @param unknown_type $v fanwe_peizi_order_op
  * @param unknown_type $type_format (fanwe_peizi_order.type 日，周，月)
  * @return string
  */
 function get_peizi_op_val_info($v,$type_format){
 	//描述
 	$op_val_info = $v['op_val'];
 	//0:追加保证金;1:申请延期;2:申请增资;3:申请减资;4:提取赢余;5:申请结束配资
 	if ($v['op_type'] == 0){
 		$op_val_info = '追加:'.format_price($op_val_info);
 	}else if ($v['op_type'] == 1){
 		$op_val_info = '延期:'. $op_val_info.$type_format;
 	}else if ($v['op_type'] == 2){
 		$op_val_info = '倍率旧:'.$v['lever'] .';新倍率:'. $op_val_info.';增资:'.format_price(($v['op_val'] - $v['lever']) * $v['cost_money']);
 	}else if ($v['op_type'] == 3){
 		$op_val_info = '倍率旧:'.$v['lever'] .';新倍率:'. $op_val_info.';减资:'.format_price(($v['lever'] - $v['op_val']) * $v['cost_money']);
 	}else if ($v['op_type'] == 4){
 		$op_val_info = '提取赢余:'. $op_val_info;
 	}else if ($v['op_type'] == 5){
 		$op_val_info = '预计剩总值:'.$op_val_info;
 	}
 	
 	return $op_val_info;
 }
 
 
 
 /**
  * 下一交易日
  */
 function get_peizi_next_date(){
 	
 	$date = to_date(TIME_UTC);
 	for($i = 1; $i < 30; $i ++){
 		$cur_date = dec_date($date, -$i);
 		
 		if (get_peizi_is_holiday($cur_date) == false){
 			return $cur_date;
 		} 		
 	}	
 	
 	return null;
 }
 
 /**
  * 判断是否交易日
  * @param unknown_type $date
  */
 function get_peizi_is_holiday($date){
 	//判断是否是：周末 	
 	$w = to_date(TIME_UTC,'w');
 	//echo $w;exit;
 	if ($w == 0 || $w == 6){
 		return true;
 	} 
 	
 	//判断是否为节假日
 	$sql = "select id from where ".DB_PREFIX."peizi_holiday where holiday = '".$date."'";
 	if (intval($GLOBALS['db']->getOne($sql)) > 0){
 		return true;
 	}else{
 		return false;
 	} 
 }
 
 
 /**
  * 按自然月计算，如使用1个月，1月8日到2月8日，当月日期没有,则按该月的最后一天计算
  * @param unknown_type $begin_date
  * @param unknown_type $num
  */
 function add_month($begin_date,$num){
 	$y = to_date(to_timespan($begin_date),'Y');//当前年份
 	$m = to_date(to_timespan($begin_date),'m');//当前月份
 	$d = to_date(to_timespan($begin_date),'d');//当前几号
 	
 	$new_y = $y +  intval(($m + $num) / 12);
 	
 	$new_m = ($m + $num) % 12;
 	if ($new_m == 0) {
 		$new_m = 12;
 		$new_y = $new_y - 1;
 	}
 	
 	$t = to_date(to_timespan($new_y.'-'.$new_m.'-01','Y-m-d'),'t');//本月共有
 	
 	if ($t <= $d){
 		$new_d = $t;
 	}else{
 		$new_d = $d;
 	}
 	
 	return to_date(to_timespan($new_y.'-'.$new_m.'-'.$new_d,'Y-m-d'),'Y-m-d');
 	
 }
 
 /**
  * 预计配资结束时间
  * @param date $begin_date 开始时间
  * @param int $num 时长(天,月)
  * @param int $type 类型; 配资类型;0:天;1周；2月(按自然月计算，如使用1个月，1月8日到2月8日，当月日期没有,则该按月的最后一天计算，包含各类节假日)
  * @param int $is_holiday_fee 周末节假日免费;type=0时有效;0:不免费;1:免费
  */
 function get_peizi_end_date($begin_date,$num,$type = 0,$is_holiday_fee = 0){
 	
 	if ($type == 2){
 		//如果日期不存在，则取当月最大一天
 		//exit; 		
		return	add_month($begin_date,$num);
 	}else{
 		
 		if ($is_holiday_fee == 1){
 			//周末节假日免费		
 			$sql = "select holiday from ".DB_PREFIX."peizi_holiday where `year` = ".to_date(TIME_UTC,"Y"). " or `year` = ".(to_date(TIME_UTC,"Y") + 1) ." order by holiday";
 			$holiday_list = $GLOBALS['db']->getAll($sql);
 			//echo $sql;exit;
 			$max_num = count($holiday_list) + $num;
 			//echo 'max_num:'.$max_num."<br>";
 			
 			$day_num = $num;
 			for($i = 1; $i <= $max_num; $i ++){
 				$cur_date = dec_date($begin_date, -$i); 				
 				//echo $cur_date."<br>";
 				
 				$is_holiday = false;
 				//判断是否是：周末
 				$w = date("w",strtotime($cur_date));//date('w',$cur_date);
 				//echo 'w:'.$w."<br>";
 				if ($w == 0 || $w == 6) $is_holiday = true;
 				
 				if ($is_holiday == false){
	 				foreach ($holiday_list as $k => $v) {
	 					if ($v['holiday'] == $cur_date){
	 						$is_holiday = true;
	 					}
	 				}		
 				}
 				
 				//echo 'is_holiday:'.$is_holiday.'<br>';
 				if ($is_holiday == false){
 					$day_num = $day_num - 1;
 				}
 				
 				if ($day_num == 0){
 					break;
 				} 				
 			}
 			
 			
 			return $cur_date; 			
 			
 		}else{
 			return dec_date($begin_date, -$num);
 		}
 	}
 }
 
?>