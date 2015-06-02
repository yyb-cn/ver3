<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';

class peiziModule extends SiteBaseModule
{
	
	//首页
	public function peizi_index()
	{
		require_once APP_ROOT_PATH.'system/libs/peizi.php';
		
		$GLOBALS['tmpl']->caching = false;
		

		$cache_id  = md5(MODULE_NAME.ACTION_NAME);
		if (!$GLOBALS['tmpl']->is_cached('peizi/index.html', $cache_id)||true)
		{
			$sql = "select id,name,type from ".DB_PREFIX."peizi_conf where is_effect = 1 order by sort";
			
			$peizi_list = array();
			$conf_list = $GLOBALS['db']->getAll($sql);
//                        var_dump($conf_list);exit;
			foreach($conf_list as $k=>$v){
				$peizi_conf = load_auto_cache("peizi_conf",array('id'=>$v['id']));
				//$html = '1111'.$v['type'];
				//0:天天;1:周;2:月
				if ($v['type'] == 0){
					$file_name = $GLOBALS['tmpl']->template_dir. '/peizi/everwin_inc.html';
				}else if ($v['type'] == 1){
					$file_name = $GLOBALS['tmpl']->template_dir. '/peizi/weekwin_inc.html';
				}else if ($v['type'] == 2){
					$file_name = $GLOBALS['tmpl']->template_dir. '/peizi/scheme_inc.html';
				}
				//
				$html = file_get_contents($file_name);
				$html = str_replace('_prefix', $v['id'], $html);
				
				
				
				$GLOBALS['tmpl']->assign("peizi_conf_json", json_encode($peizi_conf));
				$GLOBALS['tmpl']->assign("peizi_conf",$peizi_conf);
				$GLOBALS['tmpl']->assign("conf_id",$v['id']);
				
				$GLOBALS['tmpl']->assign("is_holiday_fee",$peizi_conf['is_holiday_fee']);//按天收取，周末节假日免费				
				$GLOBALS['tmpl']->assign("is_show_today",get_peizi_show_today()); //开始交易时间，是否显示：今天
				$GLOBALS['tmpl']->assign("SHOP_TEL",app_conf('SHOP_TEL'));//客服电话
				$GLOBALS['tmpl']->assign("contract_title",$peizi_conf['contract_title']);//我已阅读并同意 《操盘协议》
				
				
				$html = $GLOBALS['tmpl']->fetch("str:".$html);
				//_prefix
				
				//echo $html; exit;
				
				$peizi_list[] = array('id'=>$v['id'],'name'=>$v['name'],'html_inc'=>$html);

				//$GLOBALS['tmpl']->assign("weekwin_inc",$html);
			}
			
			$GLOBALS['tmpl']->assign("peizi_list",$peizi_list);
			
			$show_sql = "select a.*,b.type,b.name as conf_name from ".DB_PREFIX."peizi_indexshow a left join ".DB_PREFIX."peizi_conf b on b.id = a.peizi_conf_id order by money desc";
			
			$show_list = $GLOBALS['db']->getAll($show_sql);
//                        var_dump($show_list);exit;
			foreach($show_list as $k => $v)
			{
				if($v["type"] == 0)
				{
					$show_list[$k]["url"] = url("index","peizi#everwin",array('id'=>$v['peizi_conf_id']));
 				}
				elseif($v["type"] == 1)
				{
					$show_list[$k]["url"] = url("index","peizi#weekwin",array('id'=>$v['peizi_conf_id']));
				}
				else
				{
					$show_list[$k]["url"] = url("index","peizi#scheme",array('id'=>$v['peizi_conf_id']));
				}							
			}
			//print_r($peizi_list);exit;
			$GLOBALS['tmpl']->assign("indexshow_list",$show_list);
			
			//print_r($peizi_conf);
			
			$sql = "select * from ".DB_PREFIX."peizi_conf where is_effect = 1 order by sort desc";			
			$conf_list = $GLOBALS['db']->getAll($sql);
			$GLOBALS['tmpl']->assign("conf_list",$conf_list);
			
			
			//$GLOBALS['tmpl']->assign("is_show_today",get_peizi_show_today()); //开始交易时间，是否显示：今天
			//$GLOBALS['tmpl']->assign("SHOP_TEL",app_conf('SHOP_TEL'));//客服电话
			
		}
                
		$GLOBALS['tmpl']->display("peizi/peizi_index.html");
		//$GLOBALS['tmpl']->display("peizi/index.html",$cache_id);
	}
	public function get_showindex_by_sort()
	{
		$by_sort = intval($_REQUEST["by_sort"]);
		$by_time = intval($_REQUEST["by_time"]);
		$order = "";
		$where = "";
		if($by_sort == 0)
		{
			$order = " order by a.money desc ";
		}
		elseif($by_sort == 1)
		{
			$order = " order by a.rate desc ";
		}
		
		if ($by_time > 0){
			$where = " where a.peizi_conf_id = ".$by_time;
		}
			
		
		//$show_sql = "select * from ".DB_PREFIX."peizi_indexshow ".$where.$order;
			
		$show_sql = "select a.*,b.type,b.name as conf_name from ".DB_PREFIX."peizi_indexshow a left join ".DB_PREFIX."peizi_conf b on b.id = a.peizi_conf_id ".$where.$order;
			
			$show_list = $GLOBALS['db']->getAll($show_sql);
			foreach($show_list as $k => $v)
			{
				if($v["type"] == 0)
				{
					$show_list[$k]["url"] = url("index","peizi#everwin",array('id'=>$v['peizi_conf_id']));
 				}
				elseif($v["type"] == 1)
				{
					$show_list[$k]["url"] = url("index","peizi#weekwin",array('id'=>$v['peizi_conf_id']));
				}
				else
				{
					$show_list[$k]["url"] = url("index","peizi#scheme",array('id'=>$v['peizi_conf_id']));
				}							
			}
		
		//print_r($peizi_list);exit;
		$GLOBALS['tmpl']->assign("indexshow_list",$show_list);
		
		$GLOBALS['tmpl']->assign("by_sort",$by_sort);
		
		$GLOBALS['tmpl']->assign("by_time",$by_time);
		
		$GLOBALS['tmpl']->display("peizi/indexshow_inc.html");
	}
	
	public function everwin()
	{
		
		require_once APP_ROOT_PATH.'system/libs/peizi.php';
	/*	
		//echo date("w",strtotime("2015-05-01"));exit;
		for($i=0; $i < 24; $i++){
			//echo add_month("2015-03-31",$i)."<br>";
			$date = add_month("2015-03-11",$i);
			$y1 = intval(to_date(to_timespan("2015-03-11"),'Y'));
			$m1 = intval(to_date(to_timespan("2015-03-11"),'m'));
			
			$y2 = intval(to_date(to_timespan($date),'Y'));
			$m2 = intval(to_date(to_timespan($date),'m'));
			
			$month = ($y2 - $y1) * 12 + ($m2 - $m1) + 1;
			//按自然月计算，如使用1个月，1月8日到2月8日，当月日期没有,则该按月的最后一天计算，包含各类节假日
			$next_fee_date = get_peizi_end_date("2015-03-11", $month,2,0);
			echo $date.';'.$month.';'. $next_fee_date."<br>";
		}

		echo get_peizi_end_date("2015-03-31",2,3,1);
		exit;
		
		
		$y1 = intval(to_date(to_timespan("2015-03-31"),'Ym'));
		$y2 = intval(to_date(to_timespan("2015-08-31"),'Ym'));
		
		$month = $y2 - $y1 + 1;
		//按自然月计算，如使用1个月，1月8日到2月8日，当月日期没有,则该按月的最后一天计算，包含各类节假日
		$next_fee_date = get_peizi_end_date("2015-09-30", $month,2,0);
		echo $next_fee_date; exit;
		*/
		//require_once APP_ROOT_PATH.'system/libs/peizi.php';
		//echo get_peizi_end_date('2015-04-30',1,0,1);exit;
		
		$GLOBALS['tmpl']->caching = true;
		
		$conf_id = intval($_REQUEST['id']);
		if ($conf_id == 0){
			$sql = "select id from ".DB_PREFIX."peizi_conf where type = 0 limit 1";
			$conf_id = intval($GLOBALS['db']->getOne($sql));
		}
		
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$conf_id);			
		
		
		
		$peizi_conf = load_auto_cache("peizi_conf",array('id'=>$conf_id));
		
		//print_r($peizi_conf);
			
		$GLOBALS['tmpl']->assign("conf_id",$conf_id);
		
		$GLOBALS['tmpl']->assign("peizi_conf_json", json_encode($peizi_conf));
		$GLOBALS['tmpl']->assign("peizi_conf",$peizi_conf);
		
		$GLOBALS['tmpl']->assign("is_holiday_fee",$peizi_conf['is_holiday_fee']);//按天收取，周末节假日免费
				
		$GLOBALS['tmpl']->assign("is_show_today",get_peizi_show_today()); //开始交易时间，是否显示：今天
		$GLOBALS['tmpl']->assign("SHOP_TEL",app_conf('SHOP_TEL'));//客服电话		
		$GLOBALS['tmpl']->assign("contract_title",$peizi_conf['contract_title']);//我已阅读并同意 《操盘协议》
		
		
		
		$GLOBALS['tmpl']->display("peizi/everwin.html");
		
	}
	
	public function everwin_confirm()
	{
		
		require_once APP_ROOT_PATH.'system/libs/peizi.php';
		
		//申请资金
		//风险保证金 倍率
		//开始交易时间
		$borrow_money = intval($_POST['borrow_money']);
		$is_today = intval($_POST['is_today']);
		$lever = intval($_POST['lever']);
		$rate_id = intval($_POST['rate_id']);
		$conf_id = intval($_POST['conf_id']);
		
	//print_r($_POST);
		
		$parma = get_peizi_conf($conf_id,$borrow_money,$lever,0,$rate_id);
		$GLOBALS['tmpl']->assign("parma",$parma);
		//print_r($parma);
		
		$GLOBALS['tmpl']->assign("cost_money",$parma['cost_money']);
		$GLOBALS['tmpl']->assign("cost_money_format",$parma['cost_money_format']);
		
		$GLOBALS['tmpl']->assign("rate_money",$parma['rate_money']);
		
		
		
		$GLOBALS['tmpl']->assign("borrow_money",$borrow_money);
		$GLOBALS['tmpl']->assign("lever",$lever);
		$GLOBALS['tmpl']->assign("rate_id",$rate_id);
		$GLOBALS['tmpl']->assign("is_today",$is_today);
		$GLOBALS['tmpl']->assign("conf_id",$conf_id);
		
		$peizi_conf = $parma['peizi_conf'];// load_auto_cache("peizi_conf",array('type'=>0));

//print_r($peizi_conf);

		$day_list = $peizi_conf['day_list'];
		
		$GLOBALS['tmpl']->assign("name",$peizi_conf['name']);//名称
		$GLOBALS['tmpl']->assign("brief",$peizi_conf['brief']);//简介
		
		
		$GLOBALS['tmpl']->assign("is_holiday_fee",$peizi_conf['is_holiday_fee']);//按天收取，周末节假日免费
		
		$GLOBALS['tmpl']->assign("manage_money",$peizi_conf['manage_money']);//一次性业务审核费
		$GLOBALS['tmpl']->assign("day_list",$day_list);//预存管理费天数
		
		
		//print_r($day_list);
		
		$GLOBALS['tmpl']->display("peizi/everwin_confirm.html");
	}
	

	public function weekwin()
	{
	
		require_once APP_ROOT_PATH.'system/libs/peizi.php';
		
		$GLOBALS['tmpl']->caching = true;
		$conf_id = intval($_REQUEST['id']);
		if ($conf_id == 0){
			$sql = "select id from ".DB_PREFIX."peizi_conf where type = 1 limit 1";
			$conf_id = intval($GLOBALS['db']->getOne($sql));
		}
	
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$conf_id);
	
	
		$peizi_conf = load_auto_cache("peizi_conf",array('id'=>$conf_id));
		
		//print_r($peizi_conf);
			
		$GLOBALS['tmpl']->assign("peizi_conf_json", json_encode($peizi_conf));
		$GLOBALS['tmpl']->assign("peizi_conf",$peizi_conf);
				
		$GLOBALS['tmpl']->assign("conf_id",$conf_id);
		
		$GLOBALS['tmpl']->assign("is_show_today",get_peizi_show_today()); //开始交易时间，是否显示：今天
		$GLOBALS['tmpl']->assign("SHOP_TEL",app_conf('SHOP_TEL'));//客服电话		
		$GLOBALS['tmpl']->assign("contract_title",$peizi_conf['contract_title']);//我已阅读并同意 《操盘协议》
	
		$GLOBALS['tmpl']->display("peizi/weekwin.html");
	}
	
	public function weekwin_confirm()
	{
	
		require_once APP_ROOT_PATH.'system/libs/peizi.php';
	
		//申请资金
		//风险保证金 倍率
		//开始交易时间
		$borrow_money = intval($_POST['borrow_money']);
		$is_today = intval($_POST['is_today']);
		$lever = intval($_POST['lever']);
		$rate_id = intval($_POST['rate_id']);
		$conf_id = intval($_POST['conf_id']);
		//print_r($_POST);
		
	
		$parma = get_peizi_conf($conf_id,$borrow_money,$lever,0,$rate_id);
		$GLOBALS['tmpl']->assign("parma",$parma);
		//print_r($parma);
	
		$GLOBALS['tmpl']->assign("cost_money",$parma['cost_money']);
		$GLOBALS['tmpl']->assign("cost_money_format",$parma['cost_money_format']);
	
		$GLOBALS['tmpl']->assign("rate_money",$parma['rate_money']);
	
		$GLOBALS['tmpl']->assign("borrow_money",$borrow_money);
		$GLOBALS['tmpl']->assign("lever",$lever);
		$GLOBALS['tmpl']->assign("rate_id",$rate_id);
		$GLOBALS['tmpl']->assign("is_today",$is_today);
		$GLOBALS['tmpl']->assign("conf_id",$conf_id);
		
		$peizi_conf = $parma['peizi_conf'];// load_auto_cache("peizi_conf",array('type'=>0));
	
		//print_r($peizi_conf);
	
		$GLOBALS['tmpl']->assign("name",$peizi_conf['name']);//名称
		$GLOBALS['tmpl']->assign("brief",$peizi_conf['brief']);//简介		
		$GLOBALS['tmpl']->assign("manage_money",$peizi_conf['manage_money']);//一次性业务审核费
	
	
		//print_r($day_list);
	
		$GLOBALS['tmpl']->display("peizi/weekwin_confirm.html");
	}
	
	
	public function scheme()
	{
		
		require_once APP_ROOT_PATH.'system/libs/peizi.php';
		
		$GLOBALS['tmpl']->caching = true;
		$conf_id = intval($_REQUEST['id']);
		if ($conf_id == 0){
			$sql = "select id from ".DB_PREFIX."peizi_conf where type = 2 limit 1";
			$conf_id = intval($GLOBALS['db']->getOne($sql));
		}
	
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$conf_id);
	
		$peizi_conf = load_auto_cache("peizi_conf",array('id'=>$conf_id));
	
		$GLOBALS['tmpl']->assign("peizi_conf",$peizi_conf);
	
		$GLOBALS['tmpl']->assign("peizi_conf_json", json_encode($peizi_conf));
	
		//print_r($peizi_conf);
	
		$month_list = $peizi_conf['month_list'];
		$GLOBALS['tmpl']->assign("month_list",$month_list);//预存管理费天数
	
		$GLOBALS['tmpl']->assign("conf_id",$conf_id);
		
		$GLOBALS['tmpl']->assign("is_show_today",get_peizi_show_today()); //开始交易时间，是否显示：今天
		$GLOBALS['tmpl']->assign("SHOP_TEL",app_conf('SHOP_TEL'));//客服电话
		$GLOBALS['tmpl']->assign("contract_title",$peizi_conf['contract_title']);//我已阅读并同意 《操盘协议》
	
		$GLOBALS['tmpl']->display("peizi/scheme.html");
	}
	
	public function scheme_confirm()
	{
	
		require_once APP_ROOT_PATH.'system/libs/peizi.php';
	
		//申请资金
		//风险保证金 倍率
		//开始交易时间
		$borrow_money = intval($_POST['borrow_money']);
		$is_today = intval($_POST['is_today']);
		$lever = intval($_POST['lever']);
		$rate_id = intval($_POST['rate_id']);
		$conf_id = intval($_POST['conf_id']);
		$time_limit_num = intval($_POST['time_limit_num']);//资金使用期限
		//print_r($_POST);
		//print_r($_POST);
		$parma = get_peizi_conf($conf_id,$borrow_money,$lever,$time_limit_num,$rate_id);
		$GLOBALS['tmpl']->assign("parma",$parma);
		$peizi_conf = $parma['peizi_conf'];
		//print_r($parma);
	
		$GLOBALS['tmpl']->assign("cost_money",$parma['cost_money']);
		$GLOBALS['tmpl']->assign("cost_money_format",$parma['cost_money_format']);
	
		$rate_type = intval($peizi_conf['rate_type']);
		
		$rate_money = $parma['rate_money'];
		
		
		
		if ($rate_type == 1){		
			$rate_money = $rate_money * $time_limit_num;
		}
		
		$rate_money_fromat = format_price($rate_money);
		$GLOBALS['tmpl']->assign("rate_money",$rate_money);
		$GLOBALS['tmpl']->assign("rate_money_fromat",$rate_money_fromat);
		
		$GLOBALS['tmpl']->assign("rate_type",$rate_type);
		
		$GLOBALS['tmpl']->assign("borrow_money",$borrow_money);
		$GLOBALS['tmpl']->assign("lever",$lever);
		$GLOBALS['tmpl']->assign("rate_id",$rate_id);
		$GLOBALS['tmpl']->assign("is_today",$is_today);
		$GLOBALS['tmpl']->assign("conf_id",$conf_id);
		$GLOBALS['tmpl']->assign("time_limit_num",$time_limit_num);
		
		$peizi_conf = $parma['peizi_conf'];// load_auto_cache("peizi_conf",array('type'=>0));
	
		//print_r($peizi_conf);
	
		$GLOBALS['tmpl']->assign("name",$peizi_conf['name']);//名称
		$GLOBALS['tmpl']->assign("brief",$peizi_conf['brief']);//简介	
		$GLOBALS['tmpl']->assign("manage_money",$peizi_conf['manage_money']);//一次性业务审核费
	
	
		//print_r($day_list);
	
		$GLOBALS['tmpl']->display("peizi/scheme_confirm.html");
	}
		
	public function order_confirm()
	{
		
		require_once APP_ROOT_PATH.'system/libs/peizi.php';
		
		$root = array();
		$root["money"] = 0;//status=2;余额不足，请充值，充值金额
		$root["status"] = 0;//0:出错;1:正确;2:余额不足，请充值;3:请先登陆
		$root["jump"] = url('index');
		
		//判断用户是否登陆
		$user_id = intval($GLOBALS['user_info']['id']);
		
		
		if($user_id == 0){
			$root["jump"] = url("index","user#login");
			$root["status"] = 3;
			$root["info"] = "请先登陆";
			echo json_encode($root);
			exit;
		}
		
		
		$conf_id = intval($_POST['conf_id']);
		$is_today = intval($_POST['is_today']);
		$borrow_money = intval($_POST['borrow_money']);//需要借款金额
		$lever = intval($_POST['lever']);//	
		$rate_id = intval($_POST['rate_id']);//
		
		$sql = "select type from ".DB_PREFIX."peizi_conf where id = ".$conf_id." limit 1";
		$type = intval($GLOBALS['db']->getOne($sql));//配资类型;0:天;1周；2月
		
		$month = 0;
		if ($type == 1){
			$time_limit_num = 5;//周周
		}else{
			$time_limit_num = intval($_POST['time_limit_num']);//资金使用期限			
			$month = $time_limit_num;					
		}
		

		if ($type == 2){
			$parma = get_peizi_conf($conf_id,$borrow_money,$lever,$month,$rate_id);
		}else{
			$parma = get_peizi_conf($conf_id,$borrow_money,$lever,0,$rate_id);
			
			if ($time_limit_num == 0){
				$time_limit_num = $parma['peizi_conf']['min_day'];
			}
		}
		
				
		$peizi_conf = $parma['peizi_conf'];
		
		
		
		//0;type=0时有效;1周末节假日免费
		$is_holiday_fee = intval($peizi_conf['is_holiday_fee']);
		
		
		//一次性业务审核费
		$manage_money = intval($peizi_conf['manage_money']);
		$rate_type = intval($peizi_conf['rate_type']);
		
		//成本;
		$cost_money = intval($parma['cost_money']);
		//日,月 利息
		$rate_money = floatval($parma['rate_money']);
		
		//type=2时，有效;0:按月收取;1:一次性收取
		if ($type == 2){
			if ($rate_type == 1){
				$first_rate_money = $rate_money * $time_limit_num;
			}else{
				$first_rate_money = $rate_money;
			}
		}else{
			$first_rate_money = $rate_money * $time_limit_num;
		}
		
		
		$total_money = $manage_money + $cost_money + $first_rate_money;
		//当前用户余额
		//  1      $sql = "select AES_DECRYPT(money_encrypt,'".AES_DECRYPT_KEY."') AS money from ".DB_PREFIX."user where id = ".$user_id;	
       $sql = "select `money` AS money from ".DB_PREFIX."user where id = ".$user_id;			
		$user_total_money = floatval($GLOBALS['db']->getOne($sql));
		
		if($user_total_money< $total_money){
			$root["money"] = $total_money;
			$root["status"] = 2;
			$root["info"] = "余额不足，请充值:".format_price($total_money);// ';manage_money:' .$manage_money.';cost_money:' .$cost_money . ';first_rate_money:' . $first_rate_money;
			$root["jump"] = SITE_DOMAIN.url("index","uc_money#incharge",array('money'=>$total_money));//member.php?ctl=uc_money&act=incharge
			echo json_encode($root);
			exit;
		}
		
		
		
		$order = array();		
		$order['type'] = $type;
		$order['peizi_conf_id'] = $conf_id;
		$order['user_id'] = $user_id;
		$order['manage_money'] = $manage_money;
		$order['cost_money'] = $cost_money;
		$order['borrow_money'] = $borrow_money;
		$order['stock_money'] = 0;
		$order['lever'] = $lever;
		$order['is_today'] = $is_today;
		
		$order['warning_line'] = intval($parma['warning_line']);
		$order['open_line'] = intval($parma['open_line']);;
		$order['rate'] = $parma['rate'];//利率
		$order['rate_money'] = $parma['rate_money'];//每日或每月利息费用
		
		$order['time_limit_num'] = $time_limit_num;
		$order['create_time'] = to_date(TIME_UTC);
		$order['status'] = 0;//0:在申请；
		$order['memo'] = $parma['limit_info'];
		
		$order['rate_type'] = $rate_type;
		$order['first_rate_money'] = $first_rate_money;
		
		$order['contract_id'] = $peizi_conf['contract_id'];
		
		$order['is_holiday_fee'] = $is_holiday_fee;//是否周末节假日免费
		
		$order['payoff_rate'] = $parma['payoff_rate'];
		
		
		if ($is_today == 1){
			$order['begin_date'] = to_date(TIME_UTC,"Y-m-d");					
		}else{
			$order['begin_date'] = get_peizi_end_date(to_date(TIME_UTC,"Y-m-d"),1,0,1);//下一交易日
		}
		if ($type == 2){
			//按自然月计算，如使用1个月，1月8日到2月8日，当月日期没有,则该按月的最后一天计算，包含各类节假日
			$order['end_date'] = add_month($order['begin_date'], $month);
		}else{
			$order['end_date'] = get_peizi_end_date($order['begin_date'],$time_limit_num - 1,$type,$is_holiday_fee);
		}
		/*
		`begin_date` date default NULL COMMENT '开始交易时间(启息时间)',
		`end_date` date default NULL COMMENT '预计操作结束时间',
		`last_fee_date` date default NULL COMMENT '最后(近)一次扣费日期（日，月利率)',
		`next_fee_date` date default NULL COMMENT '下次扣费日期',
		
		
		$root["status"] = 0;
		$root["info"] = print_r($order,true);
		echo  json_encode($root);
		
		;exit;
		*/
		$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order",$order,"INSERT");
		
		$order_id = $GLOBALS['db']->insert_id();
		
		if ($order_id > 0){
			//30:配资本金(冻结); 31:配资预交款(冻结);32:配资审核费(冻结);33:配资日利息(平台收入);34:配资月利息(平台收入);35:配资审核费(平台收入)
			
			require_once APP_ROOT_PATH.'system/libs/user.php';
			
			
			//冻结：本金 cost_money array('money'=>-$data['money'],'lock_money'=>$data['money'])
			modify_account(array('money'=>-$cost_money,'lock_money'=>$cost_money), $user_id,'冻结配资本金,配资编号:'.$order_id,30);
			
			//冻结：首次付款  first_rate_money
			modify_account(array('money'=>-$first_rate_money,'lock_money'=>$first_rate_money), $user_id,'冻结预交款,配资编号:'.$order_id,31);
			
			//冻结：业务审核费 (32借款服务费) manage_money
			modify_account(array('money'=>-$manage_money,'lock_money'=>$manage_money), $user_id,'冻结服务费,配资编号:'.$order_id,32);
			
			
			
			$order_sn = to_date(TIME_UTC,"Y")."".str_pad($order_id,7,0,STR_PAD_LEFT);
			$data = array();
			$data['order_sn'] = $order_sn;
			$data['status'] = 1;					
			$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order",$data,"UPDATE","id=".$order_id);
			
			
			$root["status"] = 1;
			$root["jump"] = url("index","uc_trader#verify");
			$root["info"] = "订单已提交,等待审核";
			echo json_encode($root);
						
		}else{
								
			$root["status"] = 0;
			$root["info"] = "配资单创建失败,请重试";
			echo  json_encode($root);
		}
				
	}
	
	
	//电子合同
	public function contract(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！");
		}
		
		$GLOBALS['tmpl']->assign('SITE_URL',str_replace(array("https://","http://"),"",SITE_DOMAIN));
		$GLOBALS['tmpl']->assign('SITE_TITLE',app_conf("SITE_TITLE"));
		$GLOBALS['tmpl']->assign('CURRENCY_UNIT',app_conf("CURRENCY_UNIT"));
	
	
		$contract = $GLOBALS['tmpl']->fetch("str:".get_contract($id));
	
		$GLOBALS['tmpl']->assign('contract',$contract);
	
		$GLOBALS['tmpl']->display("inc/uc/uc_trader_contract.html");
	}	
	
	
}
?>