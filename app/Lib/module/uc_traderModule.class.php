<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require_once APP_ROOT_PATH.'app/Lib/uc.php';
require_once APP_ROOT_PATH.'system/libs/peizi.php';
require_once APP_ROOT_PATH.'app/Lib/page.php';

class uc_traderModule extends SiteBaseModule
{
	private $creditsettings;
	private $allow_exchange = false;

	public function __construct()
	{
		if(in_array(ACTION_NAME,array("carry","savecarry"))){
			$is_ajax = intval($_REQUEST['is_ajax']);
			//判断是否是黑名单会员
	    	if($GLOBALS['user_info']['is_black']==1){
	    		showErr("您当前无权限提现，具体联系网站客服",$is_ajax,url("index","uc_center"));
	    	}
		}
		if(file_exists(APP_ROOT_PATH."public/uc_config.php"))
		{
			require_once APP_ROOT_PATH."public/uc_config.php";
		}
		if(app_conf("INTEGRATE_CODE")=='Ucenter'&&UC_CONNECT=='mysql')
		{
			if(file_exists(APP_ROOT_PATH."public/uc_data/creditsettings.php"))
			{
				require_once APP_ROOT_PATH."public/uc_data/creditsettings.php";
				$this->creditsettings = $_CACHE['creditsettings'];
				if(count($this->creditsettings)>0)
				{
					foreach($this->creditsettings as $k=>$v)
					{
						$this->creditsettings[$k]['srctitle'] = $this->credits_CFG[$v['creditsrc']]['title'];
					}
					$this->allow_exchange = true;
					$GLOBALS['tmpl']->assign("allow_exchange",$this->allow_exchange);
				}
			}
		}
		parent::__construct();
	}
	
	public function index()
	{		
		$user_info = $GLOBALS['user_info'];
		
		$user_info["total_money"] = number_format(floatval($user_info["money"]) + floatval($user_info["lock_money"]), 2);
		
		$GLOBALS['tmpl']->assign('user_data',$user_info);
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$trader_list = $GLOBALS['db']->getAll("select po.*,pc.name as conf_type_name from ".DB_PREFIX."peizi_order po left join ".DB_PREFIX."peizi_conf pc on po.peizi_conf_id = pc.id where po.status = 4 and  po.user_id = ".intval($GLOBALS['user_info']['id'])." order by order_sn desc  limit ".$limit);		
		$trader_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."peizi_order where status = 4 and  user_id = ".intval($GLOBALS['user_info']['id']));		
		foreach($trader_list as $k => $v)
		{
			$trader_list[$k] = 	get_peizi_order_fromat($trader_list[$k]);
		}

		$page = new Page($trader_count,app_conf("PAGE_SIZE"));   //初始化分页对象 
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("trader_list",$trader_list);

		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_EXCHANGE']);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_trader_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function verify()
	{
		$user_info = $GLOBALS['user_info'];
		
		$user_info["total_money"] = number_format(floatval($user_info["money"]) + floatval($user_info["lock_money"]), 2);
		
		$GLOBALS['tmpl']->assign('user_data',$user_info);
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$trader_list = $GLOBALS['db']->getAll("select po.*,pc.name as conf_type_name from ".DB_PREFIX."peizi_order po left join ".DB_PREFIX."peizi_conf pc on po.peizi_conf_id = pc.id where po.status in (0,1,2,5) and  po.user_id = ".intval($GLOBALS['user_info']['id'])." order by order_sn desc limit ".$limit);		
		
		$trader_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."peizi_order where status in (0,1,2,5) and  user_id = ".intval($GLOBALS['user_info']['id']));		
		
		foreach($trader_list as $k => $v)
		{
			$trader_list[$k] = 	get_peizi_order_fromat($trader_list[$k]);
			if($v["status"] != 4 && $v["status"] != 6)
			{
				$trader_list[$k]["loss_money_format"] = "￥0.00";
			}
		}

		$page = new Page($trader_count,app_conf("PAGE_SIZE"));   //初始化分页对象 
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		// var_dump($trader_list);exit;
		$GLOBALS['tmpl']->assign("trader_list",$trader_list);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_trader_verify.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function history_trader()
	{		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$trader_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."peizi_order where status in(3,6) and  user_id = ".intval($GLOBALS['user_info']['id'])."  order by order_sn desc limit ".$limit);		
		
		$trader_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."peizi_order where status in(3,6) and  user_id = ".intval($GLOBALS['user_info']['id']));		
		
		foreach($trader_list as $k => $v)
		{
			$trader_list[$k]["type"] = get_peizi_type($v["type"]);
			$trader_list[$k]["trader_money"] = $v["cost_money"] + $v["borrow_money"];
			$trader_list[$k]["loss_money"] = $v["stock_money"] - ($v["cost_money"] + $v["borrow_money"]);
			$trader_list[$k]["loss_money_format"] = format_price($trader_list[$k]["loss_money"]);
			$trader_list[$k]["loss_ratio"] = $v["stock_money"]/($v["cost_money"] + $v["borrow_money"]);
			$trader_list[$k]["status"] = get_peizi_status($v["status"]);
		}

		$page = new Page($trader_count,app_conf("PAGE_SIZE"));   //初始化分页对象 
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("trader_list",$trader_list);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_trader_history.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function detail()
	{
		$id = intval($_REQUEST["id"]);
		if($id>0)
		{
			$this->detail_action($id);
		}
		else
		{
			showErr("访问错误，请重试");
		}
	}
	public function history_detail()
	{
		$id = intval($_REQUEST["id"]);
		if($id>0)
		{
			$this->detail_action($id);
		}
		else
		{
			showErr("访问错误，请重试");
		}
	}
	public function verify_detail()
	{
		$id = intval($_REQUEST["id"]);
		if($id>0)
		{
			$this->detail_action($id);
		}
		else
		{
			showErr("访问错误，请重试");
		}
	}
	function detail_action($id)
	{
		$trader_info = $GLOBALS['db']->getRow("select po.*,AES_DECRYPT(po.stock_pwd_encrypt,'".AES_DECRYPT_KEY."') as stock_pwd,pc.name as conf_type_name from ".DB_PREFIX."peizi_order po left join ".DB_PREFIX."peizi_conf as pc on po.peizi_conf_id = pc.id where  po.user_id = ".intval($GLOBALS['user_info']['id'])." and po.id=".$id);		
		
		$trader_info = get_peizi_order_fromat($trader_info);
		
		if($trader_info["status"] != 4 && $trader_info["status"] != 6)
		{
			$trader_info["loss_money_format"] = "￥0.00";
		}
		
		//总标志 6全部禁用 4启用
		$main_flag = true;
		//0:追加保证金;1:申请延期;2:申请增资;3:申请减资;4:提取赢余;5:申请结束配资'
		$trader_info["flag_0"] = true;
		$trader_info["flag_1"] = true;
		$trader_info["flag_2"] = true;
		$trader_info["flag_3"] = true;
		$trader_info["flag_4"] = true;
		$trader_info["flag_5"] = true;
		 
		if($trader_info["status"]==6 || $trader_info["status"]== 3)
		{
			$main_flag = false;
		}
		elseif($trader_info["status"]==4)
		{
			$main_flag = true;
		}
		else
		{
			$main_flag = false;
		}
		$order_op = $GLOBALS["db"] -> getAll("select * from ".DB_PREFIX."peizi_order_op where peizi_order_id = ".$id);
		
		foreach($order_op as $k => $v)
		{
			if( $v["op_status"] != 3 || $v["op_status"] != 5 )
			{
				$trader_info["flag".$v["op_type"]] = false;
			}			
		}
		if($trader_info["type"] == 1)
		{
			$trader_info["flag_1"] = false;
			$trader_info["flag_2"] = false;
			$trader_info["flag_3"] = false;
		}
		if($main_flag == false)
		{
			$trader_info["flag_0"] = false;
			$trader_info["flag_1"] = false;
			$trader_info["flag_2"] = false;
			$trader_info["flag_3"] = false;
			$trader_info["flag_4"] = false;
			$trader_info["flag_5"] = false;
		}
		
		/*操盘列表*/
		$op_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."peizi_order_op where user_id = ".intval($GLOBALS['user_info']['id'])." and peizi_order_id=".$id." order by id desc ");		
		
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
		/*资金列表*/
		//1:业务审核费;2:日利息;3:月利息;4:其它费用',
		$fee_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."peizi_order_fee_list where user_id = ".intval($GLOBALS['user_info']['id'])." and peizi_order_id=".$id." order by id desc");		
		
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
		/*历史金额*/
		$history_list = $GLOBALS['db']->getAll("select m.stock_date,m.stock_money from ".DB_PREFIX."peizi_order_stock_money m left join ".DB_PREFIX."peizi_order po on m.peizi_order_id = po.id where po.user_id = ".intval($GLOBALS['user_info']['id'])." and peizi_order_id=".$id." order by m.id asc");		
		
		$GLOBALS['tmpl']->assign("history_list",$history_list);
		
		$GLOBALS['tmpl']->assign("fee_list",$fee_list);
		
		$GLOBALS['tmpl']->assign("op_list",$op_list);
		
		$GLOBALS['tmpl']->assign("vo",$trader_info);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_trader_detail.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function add_op()
	{
		$id = intval($_REQUEST["id"]);
		$type = intval($_REQUEST["type"]);
		$return =  array();
		$info = $GLOBALS["db"] -> getRow("select *,AES_DECRYPT(stock_pwd_encrypt,'".AES_DECRYPT_KEY."') as stock_pwd from ".DB_PREFIX."peizi_order where id = ".$id." and user_id = ".$GLOBALS["user_info"]["id"]);
		if(!$info)
		{
			$return["status"] = 0;
			$return["msg"] = "操作失败请重试";
			ajax_return($return);
			return;
		}
		$op_info = $GLOBALS["db"] -> getRow("select * from ".DB_PREFIX."peizi_order_op where peizi_order_id = ".$id." and user_id = ".$GLOBALS["user_info"]["id"]." and op_status in (0,1,4) ");
		if($op_info)
		{
			$return["status"] = 0;
			$return["msg"] = "您还有申请未审核通过，请等待申请通过后操作";
			ajax_return($return);
			return;
		}
		
		switch($type)
		{
			case 0:
			case 4:
			case 5:
				$return["status"] = 1;
				$return["title"] = "金额";
				if($type == 5)
				{
					$return["title"] = "股票账户余额";
				}
				$return["title_val"] = "<input name='op_val' id='op_val' class='f-input' value='1'/>";
				break;
			case 1:
				if($info["type"]==1)
				{
					$return["status"] = 0;
					$return["msg"] = "操作失败请重试";
					ajax_return($return);
					break;
				}
				$return["status"] = 1;
				$return["title"] = "时间";
				if($info["type"] == 0)
				{
					$return["title_val"] = "<input name='op_val' id='op_val' class='f-input' value='1'/>天";
				}
				elseif($info["type"] == 2)
				{
					$return["title_val"] = "<input name='op_val' id='op_val' class='f-input' value='1'/>月";
				}
				;
				break;
			case 2:
			case 3:
				if($info["type"]==1)
				{
					$return["status"] = 0;
					$return["msg"] = "操作失败请重试";
					ajax_return($return);
					break;
				}
				$parma = get_peizi_conf(1,$info["borrow_money"],$info["lever"],0,1);
				$lever_list = $parma["peizi_conf"]["lever_list"][0]["lever_array"];
				$max = -1;
				$min = -1;
				foreach($lever_list as $k=>$v)
				{
					if($max == -1 && $min ==-1)
					{
						$max = $min = $v["lever"];
					}
					if($v["lever"]>$max)
					{
						$max = $v["lever"];
					}
					if($v["lever"]<$min)
					{
						$min = $v["lever"];
					}
				};
				$return["status"] = 1;
				$return["title"] = "倍率";
				$return["title_val"] = "<select name='op_val' id='op_val' class='ui-select w120 select-w120 m10' value='0'>";
				if($type == 2)
				{
					$i = $info["lever"]+1;
					if($min == $max || $info["lever"] >= $max)
					{
						$return["status"] = 0;
						$return["msg"] = "当前值已经不能调整";
						ajax_return($return);
						break;
						//$return["title_val"] .= "<option value='".$info["lever"]."'>".$info["lever"]."</option>";
					}
					else
					{
						while($i <= $max)
						{
							$return["title_val"] .= "<option value='".$i."'>".$i."</option>";
							$i ++ ;
						}
					};
				}
				else
				{
					$i = $info["lever"]-1;
					if($min == $max || $info["lever"] <= $min)
					{
						$return["title_val"] .= "<option value='".$info["lever"]."'>".$info["lever"]."</option>";
					}
					else
					{
						while($i >= $min)
						{
							$return["title_val"] .= "<option value='".$i."'>".$i."</option>";
							$i -- ;
						}
					};
				};
				$return["title_val"] .= "</select>";
				break;
		}
		
		ajax_return($return);
	}
	public function save_op()
	{
		$peizi_order_id = intval($_REQUEST["id"]);
		$op_type = intval($_REQUEST["type"]);
		$op_val = strim($_REQUEST["op_val"]);
		$memo = strim($_REQUEST["memo"]);
		
		if($peizi_order_id > 0)
		{
			$info = $GLOBALS["db"] -> getRow("select *,AES_DECRYPT(stock_pwd_encrypt,'".AES_DECRYPT_KEY."') as stock_pwd from ".DB_PREFIX."peizi_order where id = ".$peizi_order_id." and user_id = ".$GLOBALS["user_info"]["id"]);
			if($info)
			{
				$op_info = $GLOBALS["db"] -> getRow("select * from ".DB_PREFIX."peizi_order_op where peizi_order_id = ".$peizi_order_id." and user_id = ".$GLOBALS["user_info"]["id"]." and op_status in (0,1,2,4)");
				if($op_info)
				{
					$return["status"] = 0;
					$return["msg"] = "您还有申请未审核通过，请等待申请通过后操作";
				}
				else
				{
					$data = array();
					$data["peizi_order_id"] = $peizi_order_id;
					$data["op_type"] = $op_type;
					$data["create_date"] = to_date(TIME_UTC);
					$data["op_val"] = $op_val;
					$data["memo"] = $memo;
					$data["user_id"] = $GLOBALS["user_info"]["id"];
					$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order_op",$data,"INSERT");
					$return["status"] = 1;
					$return["msg"] = "提交成功，请等待管理员审核";
				}
			}
			else
			{
				$return["status"] = 0;
				$return["msg"] = "操作失败请重试";
			}
		}
		else
		{
			$return["status"] = 0;
			$return["title"] = "保存失败，请刷新重新操作";
		}
		ajax_return($return);
	}
	public function cancel_op()
	{
		$id = $_REQUEST["id"];
		$info = $GLOBALS["db"] -> getRow("select * from ".DB_PREFIX."peizi_order_op where id = ".$id." and op_status = 0 and user_id = ".$GLOBALS["user_info"]["id"]);
		if($info)
		{
			$update_date = array();
			$update_date["op_status"] = 5;
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."peizi_order_op",$update_date,"UPDATE","id=".$id);
			$return["status"] = 1;
			$return["msg"] = "操作成功";
		}
		else
		{
			$return["status"] = 0;
			$return["msg"] = "保存失败，请刷新重新操作";
		}
		ajax_return($return);
	}
	//电子合同
	public function contract(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！");
		}
		$peizi_order = $GLOBALS['db']->getRow("select *,AES_DECRYPT(stock_pwd_encrypt,'".AES_DECRYPT_KEY."') as stock_pwd FROM ".DB_PREFIX."peizi_order WHERE id=".$id." and user_id=".$GLOBALS['user_info']['id']." ORDER BY create_time ASC");
		if(!$peizi_order){
			showErr("操作失败！");
		}
		$peizi_order = get_peizi_order_fromat($peizi_order);
		$GLOBALS['tmpl']->assign('vo',$peizi_order);
		
		$u_info = get_user("*",$peizi_order['user_id']);
		
		$GLOBALS['tmpl']->assign('user_info',$u_info);
		
		$GLOBALS['tmpl']->assign('SITE_URL',str_replace(array("https://","http://"),"",SITE_DOMAIN));
		$GLOBALS['tmpl']->assign('SITE_TITLE',app_conf("SITE_TITLE"));
		$GLOBALS['tmpl']->assign('CURRENCY_UNIT',app_conf("CURRENCY_UNIT"));
		
		
		$contract = $GLOBALS['tmpl']->fetch("str:".get_contract($peizi_order['contract_id']));
		
		$GLOBALS['tmpl']->assign('contract',$contract);
		
		$GLOBALS['tmpl']->display("inc/uc/uc_trader_contract.html");	
	}
	
	
	//电子合同
	public function dcontract(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！");
		}
		$peizi_order = $GLOBALS['db']->getRow("select user_id FROM ".DB_PREFIX."peizi_order WHERE id=".$id." and user_id=".$GLOBALS['user_info']['id']." ORDER BY create_time ASC");
		if(!$peizi_order){
			showErr("操作失败！");
		}
				
		$GLOBALS['tmpl']->assign('vo',$peizi_order);
		
		$u_info = get_user("*",$peizi_order['user_id']);
		
		$GLOBALS['tmpl']->assign('user_info',$u_info);
		
		$GLOBALS['tmpl']->assign('SITE_URL',str_replace(array("https://","http://"),"",SITE_DOMAIN));
		$GLOBALS['tmpl']->assign('SITE_TITLE',app_conf("SITE_TITLE"));
		$GLOBALS['tmpl']->assign('CURRENCY_UNIT',app_conf("CURRENCY_UNIT"));
		
		
		$contract = $GLOBALS['tmpl']->fetch("str:".get_contract($peizi_order['contract_id']));
	
	
		$GLOBALS['tmpl']->assign('contract',$contract);

		require APP_ROOT_PATH."/system/utils/word.php";
    	$word = new word(); 
   		$word->start(); 
   		$wordname = "借款协议.doc"; 
   		echo  $GLOBALS['tmpl']->fetch("inc/uc/uc_trader_contract.html");
   		$word->save($wordname); 
		
	}
	
}
?>