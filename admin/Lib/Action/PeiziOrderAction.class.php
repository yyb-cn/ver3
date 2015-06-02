<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require_once APP_ROOT_PATH."/system/libs/peizi.php";
class PeiziOrderAction extends CommonAction{
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
		
	function op_base($map,$where = ''){
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		// $sql_str = "select pc.name as conf_type_name,a.*,AES_DECRYPT(a.stock_pwd_encrypt,'".AES_DECRYPT_KEY."') as stock_pwd, u.user_name, AES_DECRYPT(u.money_encrypt,'".AES_DECRYPT_KEY."') as user_money, m.adm_name, a.cost_money+a.borrow_money as total_money
					 // from ".DB_PREFIX."peizi_order a
					// LEFT JOIN ".DB_PREFIX."user u on u.id = a.user_id
					// LEFT JOIN ".DB_PREFIX."peizi_conf pc on pc.id = a.peizi_conf_id
					// LEFT JOIN ".DB_PREFIX."admin m on m.id = a.admin_id  where 1 = 1 ";
		
		$sql_str = "select pc.name as conf_type_name,a.*,AES_DECRYPT(a.stock_pwd_encrypt,'".AES_DECRYPT_KEY."') as stock_pwd, u.user_name,u.money  as user_money, m.adm_name, a.cost_money+a.borrow_money as total_money
					 from ".DB_PREFIX."peizi_order a
					LEFT JOIN ".DB_PREFIX."user u on u.id = a.user_id
					LEFT JOIN ".DB_PREFIX."peizi_conf pc on pc.id = a.peizi_conf_id
					LEFT JOIN ".DB_PREFIX."admin m on m.id = a.admin_id  where 1 = 1 ";
		
		
		
		

		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> ''){
			$sql_str .= " and a.create_date >= '".$map['start_time']."'";
		}
		
		if( isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and a.create_date <= '".$map['end_time']."'";
		}
		
		if ($map['peizi_conf_id'] != -1){
			$sql_str .= " and a.peizi_conf_id = '".$map['peizi_conf_id']."'";
		}
		
		
		
		//订单状态0:在申请；1:支付成功,等待配资;2:初审通过,股票开户;3:初审不通过,解冻资金;4:复审通过,开始投资股票;5:复审不通过,退回初审; 6:平仓结束,结算资金
		if ($map['status'] != ''){
			$sql_str .= " and a.status in (".$map['status'].")";
		}
		
		/*
		//最后(近)一次扣费日期
		if( isset($map['last_fee_date']) && $map['last_fee_date'] <> ''){
			$sql_str .= " and a.last_fee_date = '".$map['last_fee_date']."'";
		}		
		*/

		
		if( isset($map['fee_start_date']) && $map['fee_start_date'] <> ''){
			$sql_str .= " and a.last_fee_date >= '".$map['fee_start_date']."'";
		}
		
		if( isset($map['fee_end_date']) && $map['fee_end_date'] <> ''){
			$sql_str .= " and a.last_fee_date <= '".$map['fee_end_date']."'";
		}
		
		//预计操作结束时间
		/*
		if( isset($map['end_date']) && $map['end_date'] <> ''){
			$sql_str .= " and a.end_date in (".$map['end_date'].")";
		}*/
		
		if( isset($map['end_start_date']) && $map['end_start_date'] <> ''){
			$sql_str .= " and a.end_date >= '".$map['end_start_date']."'";
		}
		
		if( isset($map['end_end_date']) && $map['end_end_date'] <> ''){
			$sql_str .= " and a.end_date <= '".$map['end_end_date']."'";
		}
						
		//1:自动继费失败  
		if( isset($map['is_arrearage']) && $map['is_arrearage'] <> ''){
			$sql_str .= " and a.is_arrearage = ".$map['is_arrearage']."";
		}

		//亏损警戒线
		if( isset($map['warning_line'])){
			$sql_str .= " and a.stock_money <= a.warning_line and a.stock_money > a.open_line ";
		}
		
		//亏损平仓线
		if( isset($map['open_line'])){
			$sql_str .= " and a.stock_money <= a.open_line ";
		}
		
		if( isset($map['next_start_date']) && $map['next_start_date'] <> ''){
			$sql_str .= " and a.next_fee_date >= '".$map['next_start_date']."'";
		}
		
		if( isset($map['next_end_date']) && $map['next_end_date'] <> ''){
			$sql_str .= " and a.next_fee_date <= '".$map['next_end_date']."'";
		}
		
		$sql_str .= $where;
		
		$model = D();
		//print_r($map);
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, 'a.create_time', false);
		foreach ($voList as $k => $v) {
			//配资类型 
			$voList[$k] = get_peizi_order_fromat($v);
			if($v["rate_money"] == 0)
			{
				$voList[$k]["last_fee_date"] = $v["next_fee_date"];
			}
		}
		$this->assign('list', $voList);
	}
	
	
	//待初审: 1,5
	public function op0(){
		$map =  $this->com_search();
		$map['status'] = '1,5';
		$this->op_base($map);
		 // var_dump($map);exit;
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);		
				
		$this->assign("main_title","待初审");
		$this->display("index");		
	}
	
	
	//初审失败:3
	public function op1(){
		$map =  $this->com_search();
		$map['status'] = '3';
		$this->op_base($map);
		
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);	
		
		$this->assign("main_title","初审失败");
		$this->display("index");
	}	
	
	//待复审:2
	public function op2(){
		$map =  $this->com_search();
		$map['status'] = '2';
		$this->op_base($map);
		
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);	
		
		$this->assign("main_title","待复审");
		$this->display("index");
	}	
	
	//操盘中: 4
	public function op3(){
		$map =  $this->com_search();
		$map['status'] = '4';
		$this->op_base($map);
		
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);	
		
		$this->assign("main_title","操盘中");
		$this->display("op3");
	}	
	
	//历史实盘: 6
	public function op4(){
		$map =  $this->com_search();
		$map['status'] = '6';
		
		if (!isset($_REQUEST['end_start_date']) || $_REQUEST['end_start_date'] == '') {
			$end_start_date = dec_date(to_date(TIME_UTC, 'Y-m-d'),7);
		}else{
			$end_start_date = $_REQUEST['end_start_date'];
		}
		
		if (!isset($_REQUEST['end_end_date']) || $_REQUEST['end_end_date'] == '') {
			$end_end_date = to_date(TIME_UTC, 'Y-m-d');
		}else{
			$end_end_date = $_REQUEST['end_end_date'];
		}
		
		$map['end_start_date'] = $end_start_date;
		$map['end_end_date'] = $end_end_date;
		
		$this->assign("end_start_date",$end_start_date);
		$this->assign("end_end_date",$end_end_date);
		
		$this->op_base($map);
		
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);	
	
		$this->assign("main_title","历史实盘");
		$this->display("op4");
	}
	
	//今日扣费
	public function fee_date(){
		
		
		$map =  $this->com_search();
		$map['status'] = '4,6';
		//$map['last_fee_date'] = to_date(TIME_UTC,'Y-m-d');
		
		if (!isset($_REQUEST['fee_start_date']) || $_REQUEST['fee_start_date'] == '') {
			$map['fee_start_date'] = to_date(TIME_UTC, 'Y-m-d');
		}else{
			$map['fee_start_date'] = $_REQUEST['fee_start_date'];
		}
		
		if (!isset($_REQUEST['fee_end_date']) || $_REQUEST['fee_end_date'] == '') {
			$map['fee_end_date'] = to_date(TIME_UTC, 'Y-m-d');
		}else{
			$map['fee_end_date'] = $_REQUEST['fee_end_date'];
		}		
		
		$this->assign("fee_start_date",$map['fee_start_date']);
		$this->assign("fee_end_date",$map['fee_end_date']);
		
		$this->op_base($map);
		
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);	
	
		$this->assign("main_title","今日扣费");
		$this->display("fee_date");
	}
	
	//崔单
	public function next_fee_date(){
	
	
		$map =  $this->com_search();
		$map['status'] = '4';
		//$map['last_fee_date'] = to_date(TIME_UTC,'Y-m-d');
	
		if (!isset($_REQUEST['next_start_date']) || $_REQUEST['next_start_date'] == '') {
			$map['next_start_date'] = to_date(TIME_UTC, 'Y-m-d');
		}else{
			$map['next_start_date'] = $_REQUEST['next_start_date'];
		}
	
		if (!isset($_REQUEST['next_end_date']) || $_REQUEST['next_end_date'] == '') {
			$map['next_end_date'] = dec_date(to_date(TIME_UTC, 'Y-m-d'),-3);
		}else{
			$map['next_end_date'] = $_REQUEST['next_end_date'];
		}
	
		$this->assign("next_start_date",$map['next_start_date']);
		$this->assign("next_end_date",$map['next_end_date']);
	
		
		// $this->op_base($map," and a.rate_money > AES_DECRYPT(u.money_encrypt,'".AES_DECRYPT_KEY."') ");
		 $this->op_base($map," and a.rate_money > u.money");
		 // var_dump($map);exit;
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);	
	
		$this->assign("main_title","崔单");
		$this->display("next_fee_date");
	}	 
	
	
	//快到期
	public function next_end_date(){
		$map =  $this->com_search();
		$map['status'] = '4';
		
		if (!isset($_REQUEST['end_start_date']) || $_REQUEST['end_start_date'] == '') {
			$end_start_date = to_date(TIME_UTC, 'Y-m-d');
		}else{
			$end_start_date = $_REQUEST['end_start_date'];
		}
		
		if (!isset($_REQUEST['end_end_date']) || $_REQUEST['end_end_date'] == '') {
			$end_end_date = dec_date(to_date(TIME_UTC, 'Y-m-d'),-3);
		}else{
			$end_end_date = $_REQUEST['end_end_date'];
		}
		
		$map['end_start_date'] = $end_start_date;
		$map['end_end_date'] = $end_end_date;
		
		$this->assign("end_start_date",$end_start_date);
		$this->assign("end_end_date",$end_end_date);
		
		
		//$map['end_date'] = date_in($end_start_date, $end_end_date);
		$this->op_base($map);
		// var_dump($volist);exit;
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);	
	
		$this->assign("main_title","快到期");
		$this->display("next_end_date");
	}
	
	//扣费失败
	public function arrearage(){
		$map =  $this->com_search();
		$map['status'] = '4,6';
		$map['is_arrearage'] = '1';
		
		if (!isset($_REQUEST['next_start_date']) || $_REQUEST['next_start_date'] == '') {
			//$map['fee_start_date'] = dec_date(to_date(TIME_UTC, 'Y-m-d'),7);
		}else{
			$map['next_start_date'] = $_REQUEST['next_start_date'];
			$this->assign("next_start_date",$map['next_start_date']);
		}
		
		if (!isset($_REQUEST['next_end_date']) || $_REQUEST['next_end_date'] == '') {
			//$map['fee_end_date'] = to_date(TIME_UTC, 'Y-m-d');
		}else{
			$map['next_end_date'] = $_REQUEST['next_end_date'];
			$this->assign("next_end_date",$map['next_end_date']);
		}
		
		
		
		$this->op_base($map);
		
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);	
	
		$this->assign("main_title","扣费失败");
		$this->display("arrearage");
	}

	//预警线
	public function warning_line(){
		$map =  $this->com_search();
		$map['status'] = '4';
		$map['warning_line'] = true;
		
		if (!isset($_REQUEST['end_start_date']) || $_REQUEST['end_start_date'] == '') {
			$end_start_date = '';
		}else{
			$end_start_date = $_REQUEST['end_start_date'];
			$map['end_start_date'] = $end_start_date;
		}
		
		if (!isset($_REQUEST['end_end_date']) || $_REQUEST['end_end_date'] == '') {
			$end_end_date = '';
		}else{
			$end_end_date = $_REQUEST['end_end_date'];
			$map['end_end_date'] = $end_end_date;
		}
		
		$this->assign("end_start_date",$end_start_date);
		$this->assign("end_end_date",$end_end_date);
		
		$this->op_base($map);
		
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);	
	
		$this->assign("main_title","预警线");
		$this->display("warning_line");
	}

	//平仓线
	public function open_line(){
		$map =  $this->com_search();
		$map['status'] = '4';
		$map['open_line'] = true;
		
		if (!isset($_REQUEST['end_start_date']) || $_REQUEST['end_start_date'] == '') {
			$end_start_date = '';
		}else{
			$end_start_date = $_REQUEST['end_start_date'];
			$map['end_start_date'] = $end_start_date;
		}
		
		if (!isset($_REQUEST['end_end_date']) || $_REQUEST['end_end_date'] == '') {
			$end_end_date = '';
		}else{
			$end_end_date = $_REQUEST['end_end_date'];
			$map['end_end_date'] = $end_end_date;
		}
		
		
		$this->assign("end_start_date",$end_start_date);
		$this->assign("end_end_date",$end_end_date);
		
		$this->op_base($map);
		
		$type_list = M("PeiziConf")->where('is_effect = 1')->findAll();
		$this->assign("type_list",$type_list);	
	
		$this->assign("main_title","平仓线");
		$this->display("open_line");
	}	
	
	public function op_edits(){
		$id = intval($_REQUEST ['id']);
		$yanzheng = $_REQUEST ['yanzheng'];  //布尔值
		$status = intval($_REQUEST ['status']);
		
		if(!$yanzheng || $id =="")
		{
			$this->error("验证错误");
		}
		
		$condition['id'] = $id;
		//$vo = M("PeiziOrder")->where($condition)->find();
		
		$vo = $GLOBALS['db']->getRow("select *,AES_DECRYPT(stock_pwd_encrypt,'".AES_DECRYPT_KEY."') as stock_pwd FROM ".DB_PREFIX."peizi_order WHERE id=".$id);
		
		$vo = get_peizi_order_fromat($vo);
		
		$vo["conf_type_name"] = $GLOBALS["db"]->getOne("select name from ".DB_PREFIX."peizi_conf where id = ".$vo["peizi_conf_id"]);
		
		if ($vo['status'] == 4){
			
			$this->assign("stock_date",to_date(TIME_UTC,'Y-m-d'));
		}	
			
		if($status==1){
			$this->assign("main_title","初审");
		}
		
		$this->assign ( 'status', $status );
		$this->assign ( 'vo', $vo );
		
		/************************************************************/
		/*操盘列表*/
		$op_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."peizi_order_op where peizi_order_id=".$id." order by id desc ");		

		foreach($op_list as $k => $v)
		{
			//0:追加保证金;1:申请延期;2:申请增资;3:申请减资;4:提取赢余;5:申请结束配资',
			switch($v["op_type"])
			{
				case 0: $op_list[$k]["op_type_format"] = "追加保证金";
					break;
				case 1: $op_list[$k]["op_type_format"] = "申请延期";
					break;
				case 2: $op_list[$k]["op_type_format"] = "申请增资";
					break;
				case 3: $op_list[$k]["op_type_format"] = "申请减资";
					break;
				case 4: $op_list[$k]["op_type_format"] = "提取赢余";
					break;
				case 5: $op_list[$k]["op_type_format"] = "申请结束配资";
					break;
			}
			//0:未审核;1:初审通过;2:初审未通过;3:复审通过;4:复审未通过;5:撤消申请'
			switch($v["op_status"])
			{
				case 0: $op_list[$k]["op_status_format"] = "未审核";
					break;
				case 1: $op_list[$k]["op_status_format"] = "初审通过";
					break;
				case 2: $op_list[$k]["op_status_format"] = "初审未通过";
					break;
				case 3: $op_list[$k]["op_status_format"] = "复审通过";
					break;
				case 4: $op_list[$k]["op_status_format"] = "复审未通过";
					break;
				case 5: $op_list[$k]["op_status_format"] = "撤消申请";
					break;
			}
			if($v["op_status"] == 3 || $v["op_status"] == 4)
			{
				$op_list[$k]["op_date"] = $v["op_date2"];
			}
			else
			{
				$op_list[$k]["op_date"] = $v["op_date1"];
			}
		}
		$this->assign('op_list', $op_list);
		
		/**********************/
		/*资金列表*/
		//1:业务审核费;2:日利息;3:月利息;4:其它费用',
		$fee_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."peizi_order_fee_list where peizi_order_id=".$id." order by id desc");		
		
		foreach($fee_list as $k => $v)
		{
			switch($v["fee_type"])
			{
				case 1: $fee_list[$k]["fee_type_format"] = "业务审核费";
					break;
				case 2: $fee_list[$k]["fee_type_format"] = "日利息";
					break;
				case 3: $fee_list[$k]["fee_type_format"] = "月利息";
					break;
				case 4: $fee_list[$k]["fee_type_format"] = "其它费用";
					break;
			}
		}
		$this->assign('fee_list', $fee_list);
		
		/*历史金额*/
		$history_list = $GLOBALS['db']->getAll("select m.stock_date,m.stock_money from ".DB_PREFIX."peizi_order_stock_money m left join ".DB_PREFIX."peizi_order po on m.peizi_order_id = po.id where peizi_order_id=".$id." order by m.id asc");		
		
		$this->assign('history_list', $history_list);

		$this->display ();
		
	}
	
	public function set_price(){
		$stock_money =  floatval($_REQUEST['price']);
		$order_id = intval($_REQUEST['id']);
		$stock_date = to_date(TIME_UTC,"Y-m-d");
		
		if($user_id = M("PeiziOrder")->where("id=".$order_id)->field("user_id") > 0){
			set_peizi_order_stock_money($order_id,$user_id,$stock_date,$stock_money);
			save_log("编号：".$order_id." 股票总值改为：".format_price($stock_money),1);
			$this->ajaxReturn(format_price($stock_money),"改价成功",1);die();
		}
		else{
			$this->error("改价失败",1);
		}
	}
	
	public function update(){
		$data = M("PeiziOrder")->create ();
		
		//status 订单状态0:在申请；1:支付成功,等待配资;2:初审通过,股票开户;3:初审不通过,解冻资金;4:复审通过,开始投资股票;5:复审不通过,退回初审; 6:平仓结束,结算资金
		$peiziorder = M("PeiziOrder")->where("id =".$data['id'])->find();
		$cost_money = $peiziorder['cost_money'];
		$first_rate_money = $peiziorder['first_rate_money'];
		$user_id = $peiziorder['user_id'];
		$manage_money = $peiziorder['manage_money'];
		$order_id = $data['id'];
		
		// 更新数据
		if($data['status']==2)  //初审通过操作
		{
			if($data['begin_date']==""){
				$this->error(L("请填写开始时间"));
			}
			if($data['end_date']==""){
				$this->error(L("请填写结束时间"));
			}
			if($data['stock_sn']==""){
				$this->error(L("请填写股票账户"));
			}
			if($_REQUEST['stock_pwd']==""){
				$this->error(L("请填写股票密码"));
			}
			//AES_DECRYPT(a.stock_pwd_encrypt,'".AES_DECRYPT_KEY."')
			$data['stock_pwd_encrypt'] = "AES_ENCRYPT('".$_REQUEST['stock_pwd']."','".AES_DECRYPT_KEY."')";
			//lock_money
			//判断当前用户，冻结 资金是否足够
			$sql = "select lock_money from ".DB_PREFIX."user where id = ".$user_id;
			$lock_money = $GLOBALS['db']->getOne($sql);
			$tmoney = $peiziorder['cost_money'] + $peiziorder['first_rate_money'];
			if ($lock_money < $tmoney){
				$this->error(L("冻结的余额不足:".format_price($lock_money).';实际:'.format_price($tmoney)));
			}
			
			
			$this->assign("jumpUrl",u("PeiziOrder/op0"));
		}elseif($data['status']==3 || $data['status']==5)	//审核失败操作
		{
			if($data['op_memo']==""){
				$this->error(L("请填写失败原因"));
			}
		}elseif($data['status']==4)	//复审通过
		{
			//`next_fee_date` date default NULL COMMENT '下次扣费日期',
				//rate_type type=2时，有效;0:按月收取;1:一次性收取
			if ($peiziorder['rate_type'] == 0){
				//第一次 自动扣费时间
				$data['next_fee_date'] = $peiziorder['begin_date'];
			}else{
				//已收利息总额
				$data['total_rate_money'] = $peiziorder['first_rate_money'];
				$data['next_fee_date'] = $peiziorder['begin_date'];
				$data['last_fee_date'] = $peiziorder['begin_date'];
			}
				
			//lock_money
			//判断当前用户，冻结 资金是否足够
			$sql = "select lock_money from ".DB_PREFIX."user where id = ".$user_id;
			$lock_money = $GLOBALS['db']->getOne($sql);
			$tmoney = $peiziorder['cost_money'] + $peiziorder['first_rate_money'];
			if ($lock_money < $tmoney){
				$this->error(L("冻结的余额不足:".format_price($lock_money).';实际:'.format_price($tmoney)));
			}			
			
		}elseif($data['status']==6)	//平仓操作
		{
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
		}
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order",$data,"UPDATE","id = ".$data['id']);
		
		if($GLOBALS['db']->affected_rows()){
			//成功提示
			save_log($data['order_sn'].L("UPDATE_SUCCESS"),1);
			
			require_once APP_ROOT_PATH.'system/libs/user.php';
			
			if($data['status']==3)	////审核失败操作
			{
				
				//30:配资本金(冻结); 31:配资预交款(冻结);32:配资审核费(冻结);33:配资日利息(平台收入);34:配资月利息(平台收入);
					
				//冻结：本金 cost_money array('money'=>-$data['money'],'lock_money'=>$data['money'])
				modify_account(array('money'=>$cost_money,'lock_money'=>-$cost_money), $user_id,'配资申请失败解冻配资本金,配资编号:'.$order_id,30);
				
				//冻结：首次付款  first_rate_money
				modify_account(array('money'=>$first_rate_money,'lock_money'=>-$first_rate_money), $user_id,'配资申请失败解冻预交款,配资编号:'.$order_id,31);
				
				//冻结：业务审核费 (32借款服务费) manage_money
				if ($manage_money > 0)
					modify_account(array('money'=>$manage_money,'lock_money'=>-$manage_money), $user_id,'配资申请失败解冻服务费,配资编号:'.$order_id,32);
				
				$this->assign("jumpUrl",u("PeiziOrder/op0"));
			}elseif($data['status']==4)	//4:复审通过,开始投资股票;
			{
				
				//30:配资本金(冻结); 31:配资预交款(冻结);32:配资审核费(冻结);33:配资日利息(平台收入);34:配资月利息(平台收入);
				
				//解冻并收取：业务审核费 (32借款服务费) manage_money
				if ($manage_money > 0){
					
					modify_account(array('lock_money'=>-$manage_money,'site_money'=>$manage_money), $user_id,'配资申请复审通过,解冻服务费,增加平台收入,配资编号:'.$order_id,32);
					
					$fee_data = array();
					$fee_data['user_id'] = $user_id;
					$fee_data['peizi_order_id'] = $order_id;
					$fee_data['create_date'] = to_date(TIME_UTC);
					$fee_data['fee_date'] = to_date(TIME_UTC);
					$fee_data['fee'] = $manage_money;
					$fee_data['fee_type'] = 1;//费用类型;1:业务审核费;2:日利息;3:月利息;4:其它费用					
					$fee_data['memo'] = '复审通过,收取：业务审核费 ';					
					$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order_fee_list",$fee_data,"INSERT");
					
				}
				
				//初始股票帐户金额
				$total_money = $peiziorder['cost_money'] + $peiziorder['borrow_money'];
				set_peizi_order_stock_money($order_id,$user_id,$peiziorder['begin_date'],$total_money);
				
				
				//type=2时，有效;0:按月收取;1:一次性收取
				if ($peiziorder['rate_type'] == 1){
					//解冻并收取：首次付款  first_rate_money
					modify_account(array('site_money'=>$first_rate_money,'lock_money'=>-$first_rate_money), $user_id,'配资申请复审通过,解冻服务费,增加平台收入,配资编号:'.$order_id,31);	

					$fee_data = array();
					$fee_data['user_id'] = $user_id;
					$fee_data['peizi_order_id'] = $order_id;
					$fee_data['create_date'] = to_date(TIME_UTC);
					$fee_data['fee_date'] = to_date(TIME_UTC);
					$fee_data['fee'] = $first_rate_money;
					$fee_data['fee_type'] = 3;//费用类型;1:业务审核费;2:日利息;3:月利息;4:其它费用
					$fee_data['memo'] = '复审通过,收取：一次性收取 服务费';
					$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order_fee_list",$fee_data,"INSERT");
				}else{
					//冻结：首次付款  first_rate_money
					modify_account(array('money'=>$first_rate_money,'lock_money'=>-$first_rate_money), $user_id,'配资申请失败解冻预交款,配资编号:'.$order_id,31);
					
					//自动收取 第一个月或第一天的 利息
					auto_charging_rate_money(false,true);
				}
				
				
				
				//通知用户复审通过
				if (app_conf("SMS_ON") == 1){
					$sql = "select id,user_name,AES_DECRYPT(mobile_encrypt,'".AES_DECRYPT_KEY."') AS mobile from ".DB_PREFIX."user where id = ".$user_id;
					$user_info = $GLOBALS['db']->getRow($sql);
					
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_PEIZI_SUCCESS_MSG'");
					$tmpl_content = $tmpl['content'];
				
					//$peiziorder
					
					$notice['site_name'] = app_conf("SHOP_TITLE");
					$notice['user_name'] = $user_info["user_name"];
					$notice['order_sn'] = $peiziorder['order_sn'];
					$notice['stock_sn'] = $peiziorder['stock_sn'];
					$notice['stock_pwd'] = $peiziorder['stock_pwd'];
					$notice['begin_date'] = $peiziorder['begin_date'];
				
					$GLOBALS['tmpl']->assign("notice",$notice);
				
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				
					$msg_data['dest'] = $user_info['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['title'] = "配资审核通过通知";
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = TIME_UTC;
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
				}
				
				$this->assign("jumpUrl",u("PeiziOrder/op2"));
			}elseif($data['status']==5)	//5:复审不通过,退回初审
			{
				$this->assign("jumpUrl",u("PeiziOrder/op2"));
			}elseif($data['status']==6)	//平仓操作
			{
				
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
				
				
				if ($data['other_fee'] > 0){
					$fee_data = array();
					$fee_data['user_id'] = $user_id;
					$fee_data['peizi_order_id'] = $order_id;
					$fee_data['create_date'] = to_date(TIME_UTC);
					$fee_data['fee_date'] = to_date(TIME_UTC);
					$fee_data['fee'] = $data['other_fee'];
					$fee_data['fee_type'] = 4;//费用类型;1:业务审核费;2:日利息;3:月利息;4:其它费用
					$fee_data['memo'] = $data['other_memo'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order_fee_list",$fee_data,"INSERT");
				}
				
				$this->assign("jumpUrl",u("PeiziOrder/op3"));
			}	
			
			$this->success(L("UPDATE_SUCCESS"),0);
		} else {
			//错误提示
			save_log($data['order_sn'].L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$data['name'].L("UPDATE_FAILED"));
		}
	}
}
?>