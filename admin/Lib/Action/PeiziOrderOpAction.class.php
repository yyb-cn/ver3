<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require_once APP_ROOT_PATH."/system/libs/peizi.php";
class PeiziOrderOpAction extends CommonAction{
	public function com_search(){
		$map = array ();
	
	
		if (isset($_REQUEST['end_time']) && $_REQUEST['end_time'] != '') {
			$map['end_time'] = trim($_REQUEST['end_time']);			
			$this->assign("end_time",$map['end_time']);
		}
		
		
		if (isset($_REQUEST['start_time']) && $_REQUEST['start_time'] != '') {
			$map['start_time'] = trim($_REQUEST['start_time']);
			$this->assign("start_time",$map['start_time']);
		}
		
		//0:追加保证金;1:申请延期;2:申请增资;3:申请减资;4:提取赢余;5:申请结束配资
		$op_type = -1;
		if (isset($_REQUEST['op_type']) && $_REQUEST['op_type'] != '') {
			$op_type = intval($_REQUEST['op_type']);
		}
		
		$map['op_type'] = $op_type;
		//配资类型;0:天;1周；2月
		$peizi_conf_id = -1;
		if (isset($_REQUEST['peizi_conf_id']) && $_REQUEST['peizi_conf_id'] != '') {
			$peizi_conf_id = intval($_REQUEST['peizi_conf_id']);
		}
		
		
		$map['peizi_conf_id'] = $peizi_conf_id;	

		
		
		$this->assign("op_type",$op_type);
		$this->assign("peizi_conf_id",$peizi_conf_id);
		
		return $map;
	}
		
	function op_base($map){
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select a.*,u.user_name, b.order_sn,b.type,b.cost_money,b.borrow_money,
					b.lever,b.begin_date,b.time_limit_num, b.cost_money+b.borrow_money as total_money
					 from ".DB_PREFIX."peizi_order_op a
					LEFT JOIN ".DB_PREFIX."peizi_order b on b.id = a.peizi_order_id
					LEFT JOIN ".DB_PREFIX."user u on u.id = a.user_id  where 1 = 1 ";
		
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> ''){
			$sql_str .= " and a.create_date <= '".$map['start_time']."'";
		}
		
		if( isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and a.create_date >= '".$map['end_time']."'";
		}
		
		if ($map['op_type'] != -1){
			$sql_str .= " and a.op_type = '".$map['op_type']."'";
		}
		
		if ($map['peizi_conf_id'] != -1){
			$sql_str .= " and b.peizi_conf_id = '".$map['peizi_conf_id']."'";
		}
		
		if ($map['op_status'] != ''){
			$sql_str .= " and a.op_status in (".$map['op_status'].")";
		}
		
		
		$model = D();
		//print_r($map);
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, 'a.create_date', false);
		foreach ($voList as $k => $v) {
			//配资类型 
			$voList[$k]['type_format'] = get_peizi_type($v['type']);
			
			//申请类型 
			$voList[$k]['op_type_format'] = get_peizi_op_type($v['op_type']);
			
			//审核状态
			$voList[$k]['op_status_format'] = get_peizi_op_status($v['op_status']);
			
			$voList[$k]['time_limit_num_format'] = $v['time_limit_num'].$voList[$k]['type_format'];
			
			//描述
			$op_val_info = get_peizi_op_val_info($v,$v['type_format']);
			
			$voList[$k]['op_val_info'] = $op_val_info;
		}
		$type_list = M("PeiziConf")->where('is_effect = 0')->findAll();
		$this->assign("type_list",$type_list);	
		
		$this->assign('list', $voList);
	}
	
	
	//待初审: 0,4
	public function op0(){
		$map =  $this->com_search();
		$map['op_status'] = '0,4';
		$this->op_base($map);
				
		$this->assign("main_title","待初审");
		$this->display("index");		
	}
	
	
	//初审失败:2
	public function op1(){
		$map =  $this->com_search();
		$map['op_status'] = '2';
		$this->op_base($map);
		
		$this->assign("main_title","初审失败");
		$this->display("index");
	}	
	
	//待复审:1
	public function op2(){
		$map =  $this->com_search();
		$map['op_status'] = '1';
		$this->op_base($map);
		
		$this->assign("main_title","待复审");
		$this->display("index");
	}	
	
	//操作结束: 3
	public function op3(){
		$map =  $this->com_search();
		$map['op_status'] = '3';
		$this->op_base($map);
		
		$this->assign("main_title","操作结束");
		$this->display("index");
	}	
	
	public function op_edits(){
		
		$id = intval($_REQUEST ['id']);
		$yanzheng = $_REQUEST ['yanzheng'];  //布尔值
		$status = intval($_REQUEST ['status']);
		$type = strim($_REQUEST["from"]);
	
		if(!$yanzheng || $id =="")
		{
			$this->error("验证错误".$dbErr);
		}
		
		$sql_str = "select a.*,u.user_name, b.order_sn,b.type,b.cost_money,b.borrow_money,
					b.lever,b.begin_date,b.time_limit_num, b.cost_money+b.borrow_money as total_money,
					b.warning_line,b.open_line,b.rate_money,b.end_date,b.stock_money,b.stock_date,b.other_fee,b.other_memo
					 from ".DB_PREFIX."peizi_order_op a
					LEFT JOIN ".DB_PREFIX."peizi_order b on b.id = a.peizi_order_id
					LEFT JOIN ".DB_PREFIX."user u on u.id = a.user_id where a.id =".$id;
		$list = $GLOBALS['db']->getRow($sql_str);
	
		$list["total_money_format"] = format_price($list["total_money"]);
		$list["cost_money_format"] = format_price($list["cost_money"]);
		$list["warning_line_format"] = format_price($list["warning_line"]);
		$list["open_line_format"] = format_price($list["open_line"]);
		$list["rate_money_format"] = format_price($list["rate_money"]);
		//配资类型
		$list['type_format'] = get_peizi_type($list['type']);
			
		//申请类型
		$list['op_type_format'] = get_peizi_op_type($list['op_type']);
			
		//审核状态
		$list['op_status_format'] = get_peizi_op_status($list['op_status']);
			
		$list['time_limit_num_format'] = $list['time_limit_num'].$list['type_format'];
			
		//描述
		$op_val_info = get_peizi_op_val_info($list,$list['type_format']);
		
		if ($list['op_type'] == 0){
			$label = "预计保证金";
			$label_val = format_price($list['cost_money']+$list['op_val']);
		}else if ($list['op_type'] == 1){
			$label = "预计操作结束时间";
			$label_val = to_date(to_timespan($list['end_date'])+$list['op_val']*3600*24);
		}else if ($list['op_type'] == 2){
			$label = "预计借款金额";
			$label_val = format_price($list['borrow_money']+($list['op_val'] - $list['lever']) * $list['cost_money']);
		}else if ($list['op_type'] == 3){
			$label = "预计借款金额";
			$label_val = format_price($list['borrow_money']+($list['lever'] - $list['op_val']) * $list['cost_money']);
		}
			
		$list['op_val_info'] = $op_val_info;
		
		if($list['op_status'] == 3)
		{
			$this->assign("main_title","详细   <a href='".u(MODULE_NAME."/op3")."' class='back_list'>返回列表</a>");
		}
		elseif($type == "review")
		{
			$this->assign("review",true);
			$this->assign("main_title","复审操作   <a href='".u(MODULE_NAME."/op2")."' class='back_list'>返回列表</a>");
		}
		else
		{
			$this->assign("main_title","初审操作   <a href='".u(MODULE_NAME."/op0")."' class='back_list'>返回列表</a>");
		}
			
		$this->assign ( 'label', $label );
		$this->assign ( 'label_val', $label_val );
		$this->assign ( 'status', $status );
		$this->assign ( 'list', $list );
		$this->display ();
	}
	public function update_first()
	{
		$id = intval($_REQUEST ['id']);
		$status = intval($_REQUEST ['status']);
		$data_memo = strim($_REQUEST["memo"]);
		
		$data =  array();
		$data["op_status"] = $status;
		$data["op_memo"] = $data_memo;
		$data["id"] = $id;
		$data["op_date1"] = to_date(TIME_UTC);
		$data["change_memo"] = strim($_REQUEST['change_memo']);
		
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		
		$this->assign("jumpUrl",u(MODULE_NAME."/op0"));
		
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	public function update_review()
	{
		$id = intval($_REQUEST ['id']);
		$status = intval($_REQUEST ['status']);
		$data_memo = strim($_REQUEST["memo"]);
		
		$op_info = $GLOBALS["db"]->getRow("select * from ".DB_PREFIX."peizi_order_op op left join ".DB_PREFIX."peizi_order od on op.peizi_order_id = od.id where op.op_status = 1 and op.id =".$id );
		/********审核同步操作*******/
		if($op_info && $status == 3)
		{
			if ($op_info['op_type'] == 0){
				//追加保证金
				
				$user_info = $GLOBALS["db"]->getRow("select *,AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."') as user_money from ".DB_PREFIX."user where id = ".$op_info["user_id"]);

				if($user_info["user_money"]>=$op_info["op_val"])
				{
					
					require_once APP_ROOT_PATH.'system/libs/user.php';
					modify_account(array('money'=>-$op_info["op_val"],'lock_money'=>$op_info["op_val"]), $op_info["user_id"],'冻结追加的保证金,配资编号:'.$op_info["peizi_order_id"],30);
					
					$op_data = array();
					$op_data["cost_money"] = $op_info["cost_money"] + $op_info["op_val"];
					$op_data["id"] = $op_info["peizi_order_id"];
					$result=M("PeiziOrder")->save ($op_data);
					if(!$result)
					{
						$log_info = "保证追加失败";
						save_log($log_info.L("UPDATE_FAILED"),0);
						$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
					}
				}
				else
				{
					$log_info = "余额不足以支付保证金";
					save_log($log_info.L("UPDATE_FAILED"),0);
					$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
				}
			}else if ($op_info['op_type'] == 1){
				//延期
				
				$user_info = $GLOBALS["db"]->getRow("select *,AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."') as user_money from ".DB_PREFIX."user where id = ".$op_info["user_id"]);
				$money = 0;
				if ($op_info["type"] == 2 && $op_info["rate_type"] == 1){
					$money = $op_info["rate_money"] * $op_info["op_val"];
				}
				if($user_info["user_money"] >= $money)
				{
					//1、调整结束时间
					$op_data = array();
					$op_data["end_date"] = get_peizi_end_date($op_info["end_date"],$op_info["op_val"],0);
					$op_data["id"] = $op_info["peizi_order_id"];
					//2、调整time_limit_num值
					$op_data["time_limit_num"] = $op_info["time_limit_num"] + $op_info["op_val"];
					if ($op_info["type"] == 2 && $op_info["rate_type"] == 1){
						$op_data["total_rate_money"] = $op_data["total_rate_money"] + $money;
					}
					$result=M("PeiziOrder")->save ($op_data);
					
					//3‘一次性收取 月利息
					if($result)
					{
						if ($op_info["type"] == 2 && $op_info["rate_type"] == 1){
							require_once APP_ROOT_PATH.'system/libs/user.php';
							modify_account(array("money"=>-$money), $op_info["user_id"],'延期的月利息,配资编号:'.$op_info["peizi_order_id"],34);
							//modify_account(array('money'=>-$money,'lock_money'=>$money), $op_info["user_id"],'冻结延期的月利息,配资编号:'.$op_info["peizi_order_id"],34);
							$op_fee_data = array();
							$op_fee_data["user_id"] = $op_info["user_id"];
							$op_fee_data["peizi_order_id"] = $op_info["peizi_order_id"];
							$op_fee_data["create_date"] = to_date(TIME_UTC);
							$op_fee_data["fee_date"] = to_date(TIME_UTC,"Y-m-d");
							$op_fee_data["fee"] = $money;
							$op_fee_data["fee_type"] = 3;
							$op_fee_data["memo"] = "延期的月利息,配资编号:".$op_info["peizi_order_id"];
							$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order_fee_list",$op_fee_data,"INSERT");
						}
					}
				}
				else
				{
					$log_info = "余额不足以支付月利息";
					save_log($log_info.L("UPDATE_FAILED"),0);
					$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
				}
			}else if ($op_info['op_type'] == 2){
				//增资
				$def_num = $op_info["op_val"] - $op_info["lever"];
				$def_money = $op_info["cost_money"] * $def_num;

				$op_data = array();
				$op_data["borrow_money"] = $op_info["borrow_money"] + $def_money ;
				$op_data["lever"] = $op_info["op_val"];
				$parma = get_peizi_conf($op_info['peizi_conf_id'],$op_data["borrow_money"],$op_data["lever"],0,0);
				$op_data["rate"] = $parma['rate'];
				$op_data["rate_money"] = $parma['rate_money'];
				
				$op_data["warning_line"] = $parma['warning_line'];
				$op_data["open_line"] = $parma['open_line'];

				if($op_info["type"]==2)
				{
					$add_fee = ($def_money * $op_data["rate"]) *((to_timespan($op_info["next_fee_date"]) - TIME_UTC)/3600/24/30);
					require_once APP_ROOT_PATH.'system/libs/user.php';
					modify_account(array("money"=>-$add_fee), $op_info["user_id"],'增资的月利息差额,配资编号:'.$op_info["peizi_order_id"],34);
					$op_data["total_rate_money"] = $op_data["total_rate_money"] + $add_fee;
				}
				$op_data["id"] = $op_info["peizi_order_id"];
				
				$result=M("PeiziOrder")->save ($op_data);

			}else if ($op_info['op_type'] == 3){
				//减资
				$def_num = $op_info["lever"] - $op_info["op_val"] ;
				$def_money = $op_info["cost_money"] * $def_num;
				$op_data = array();
				$op_data["borrow_money"] = $op_info["borrow_money"] - $def_money ;
				$op_data["lever"] = $op_info["op_val"];
				$parma = get_peizi_conf($op_info['peizi_conf_id'],$op_data["borrow_money"],$op_data["lever"],0,0);
				$op_data["rate"] = $parma['rate'];
				$op_data["rate_money"] = $parma['rate_money'];
				
				$op_data["warning_line"] = $parma['warning_line'];
				$op_data["open_line"] = $parma['open_line'];

				if($op_info["type"]==2)
				{
					$add_fee = ($def_money * $op_data["rate"]) *((to_timespan($op_info["next_fee_date"]) - TIME_UTC)/3600/24/30);
					require_once APP_ROOT_PATH.'system/libs/user.php';
					modify_account(array("money"=>$add_fee), $op_info["user_id"],'减资的月利息差额,配资编号:'.$op_info["peizi_order_id"],34);
					$op_data["total_rate_money"] = $op_data["total_rate_money"] + $add_fee;
				}
				$op_data["id"] = $op_info["peizi_order_id"];
				
				$result=M("PeiziOrder")->save ($op_data);
			}else if ($op_info['op_type'] == 4){
				if($op_info["op_val"]>0)
				{
					require_once APP_ROOT_PATH.'system/libs/user.php';
					modify_account(array("money"=>$op_info["op_val"]), $op_info["user_id"],'提取赢余,配资编号:'.$op_info["peizi_order_id"],5);
				};
			}else if ($op_info['op_type'] == 5){
				require_once APP_ROOT_PATH.'system/libs/user.php';
				//平仓
				$data = M("PeiziOrder")->create ();
				$data["id"] = $op_info["peizi_order_id"];
				
				//status 订单状态0:在申请；1:支付成功,等待配资;2:初审通过,股票开户;3:初审不通过,解冻资金;4:复审通过,开始投资股票;5:复审不通过,退回初审; 6:平仓结束,结算资金
				$peiziorder = M("PeiziOrder")->where("id =".$data["id"])->find();
				$cost_money = $peiziorder['cost_money'];
				$first_rate_money = $peiziorder['first_rate_money'];
				$user_id = $peiziorder['user_id'];
				$manage_money = $peiziorder['manage_money'];
				$order_id = $data['id'];

				if (empty($data['stock_date']))
					$data['stock_date'] = to_date(TIME_UTC,'Y-m-d');
				$data['other_fee'] = floatval($data['other_fee']);
				$data['stock_money'] = floatval($data['stock_money']);
				$data['end_date'] = to_date(TIME_UTC,'Y-m-d');
				$total_payoff_fee = $data['stock_money'] - ($peiziorder['borrow_money'] + $cost_money + $data['other_fee']);
				//盈亏
				$data['total_payoff_fee'] = $total_payoff_fee;
				if($total_payoff_fee >0)
				{
					//实际盈利了
					$data['re_cost_money'] = $cost_money; //返还保证金
					$data['user_payoff_fee'] = $total_payoff_fee * $peiziorder['payoff_rate']; //用户获利
					$data['site_payoff_fee'] = $total_payoff_fee - $data['user_payoff_fee']; //平台获得
				}else {
					//亏本
					
					$data['re_cost_money'] = $cost_money + $total_payoff_fee; //返还保证金
					$data['user_payoff_fee'] = $total_payoff_fee; //用户获利（亏损)
					$data['site_payoff_fee'] = 0; //平台获得
					
					if ($data['re_cost_money'] < 0)
						$data['re_cost_money'] = 0;
					
				}
				$data["status"] = 6;
				$result=M("PeiziOrder")->save ($data);
				
				if (false !== $result) {
					set_peizi_order_stock_money($order_id,$user_id,$data['stock_date'],$data['stock_money']);
					//冻结：本金 cost_money array('money'=>-$data['money'],'lock_money'=>$data['money'])
					$msg = '配资平仓解冻配资本金,配资编号:'.$order_id;
					if ($data['user_payoff_fee'] < 0){
						$msg = '平仓解冻配资本金:'.format_price($cost_money).';亏损:'.format_price($data['user_payoff_fee']).';剩:'.format_price($data['re_cost_money']).',配资编号:'.$order_id;
					}
					
					modify_account(array('money'=>$data['re_cost_money'],'lock_money'=>-$data['re_cost_money']), $user_id,$msg,30);

					//配资平仓,用户收益			
					if ($data['user_payoff_fee'] > 0)	
						modify_account(array('money'=>$data['user_payoff_fee']), $user_id,'配资平仓,用户收益,配资编号:'.$order_id,35);
					
					//配资平仓,平台收益
					if ($data['site_payoff_fee'] > 0)
						modify_account(array('site_money'=>$data['site_payoff_fee']), $user_id,'配资平仓,平台收益,配资编号:'.$order_id,35);				
					
					$this->assign("jumpUrl",u("PeiziOrderOp/op2"));
				}
			}
			
		}
		/*************************/
		
		$data =  array();
		$data["op_status"] = $status;
		$data["op_memo"] = $data_memo;
		$data["id"] = $id;
		$data["op_date2"] = to_date(TIME_UTC);
		$data["change_memo"] = strim($_REQUEST['change_memo']);
		
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		
		$this->assign("jumpUrl",u(MODULE_NAME."/op2"));
		
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
}
?>