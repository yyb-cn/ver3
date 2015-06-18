<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------


/**
 * 获取指定的投标
 */
function get_deal($id=0,$is_effect=1)
{
	$time = TIME_UTC;
	
	if($is_effect == 1)
	{
		$ext = " and is_effect = 1 ";
	}
	if($id==0)  //有ID时不自动获取
	{
		return false;
		/*$sql = "select id from ".DB_PREFIX."deal where is_effect = 1 and is_delete = 0  ";
			if($cate_id>0)
			{

		$ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));

		$sql .= " and cate_id in (".implode(",",$ids).")";
		}
			
		$sql.=" order by sort desc";
		$deal = $GLOBALS['db']->getRow($sql);
		*/
			
	}
	else{
		$deal = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id = ".intval($id)."  and is_delete <> 1  $ext");
	}

	if($deal)
	{
	//V2老标
	 if($deal['deal_status']==5){
	 
		if(!$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_repay WHERE  deal_id=$id ")){
					$data['deal_status'] = 4;
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$data,"UPDATE","id=".$id);
		$deal['deal_status']=4;
		 }
		}
		if($deal['deal_status']!=3 && $deal['deal_status']!=5)
		{		
			$temp_data =syn_deal_status($deal['id']);
			$deal = array_merge($deal,$temp_data);
		}
		format_deal_item($deal);
			
	}
	//print_r($deal);die;
	return $deal;

}


/**
 * 获取正在进行的投标列表
 */
function get_deal_list($limit="",$cate_id=0, $where='',$orderby = '',$user_name='',$user_pwd='',$is_all=false)
{

	$time = TIME_UTC;
	
	$count_sql = "select count(*) from ".DB_PREFIX."deal where 1=1 ";
	
	if($is_all==false)
		$count_sql.=" and is_effect = 1 and is_delete = 0 ";
		
	if(es_cookie::get("shop_sort_field")=="ulevel"){
		$extfield = ",(SELECT u.level_id FROM fanwe_user u WHERE u.id=user_id ) as ulevel";
	}


	$sql = "select *,start_time as last_time,(load_money/borrow_amount*100) as progress_point,(start_time + enddate*24*3600 - ".$time.") as remain_time $extfield from ".DB_PREFIX."deal where 1 = 1 ";
	
	if($is_all==false)
		$sql.=" and is_effect = 1 and is_delete = 0 ";
		
	if($cate_id>0)
	{
		$ids =load_auto_cache("deal_sub_parent_cate_ids",array("cate_id"=>$cate_id));
		$sql .= " and cate_id in (".implode(",",$ids).")";
		$count_sql .= " and cate_id in (".implode(",",$ids).")";
	}

	if($where != '')
	{
		$sql.=" and ".$where;
		$count_sql.=" and ".$where;
	}

	if($orderby=='')
		$sql.=" order by sort desc ";
	else
		$sql.=" order by ".$orderby;
	
	if($limit!=""){
		$sql .=" limit ".$limit;
	}
	$deals_count = $GLOBALS['db']->getOne($count_sql);
	if($deals_count > 0){
		$deals = $GLOBALS['db']->getAll($sql);
		//echo $sql;
		if($deals)
		{
			foreach($deals as $k=>$deal)
			{
				format_deal_item($deal,$user_name,$user_pwd);
				$deals[$k] = $deal;
			}
		}
	}
	else{
		$deals = array();
	}
	return array('list'=>$deals,'count'=>$deals_count);
}

function format_deal_item(&$deal,$user_name="",$user_pwd=""){
	
	//判断是否已经开始
	$deal['is_wait'] = 0;
	if($deal['start_time'] > TIME_UTC){
		$deal['is_wait'] = 1;
		$deal['remain_time'] = $deal['start_time'] - TIME_UTC;
	}
	else{
		$deal['remain_time'] = $deal['start_time'] + $deal['enddate']*24*3600 - TIME_UTC;
	}
		
	//当为天的时候
	if($deal['repay_time_type'] == 0){
		$true_repay_time = 1;
	}
	else{
		$true_repay_time = $deal['repay_time'];
	}
		
	if(trim($deal['titlecolor']) != ''){
		$deal['color_name'] = "<span style='color:#".$deal['titlecolor']."'>".$deal['name']."</span>";
	}
	else{
		$deal['color_name'] = $deal['name'];
	}
	//格式化数据
	if($deal['apart_borrow_amount'])
		$deal['borrow_amount_format'] = format_price($deal['apart_borrow_amount']/10000)."万";
	else
		$deal['borrow_amount_format'] = format_price($deal['borrow_amount']/10000)."万";
		
	$deal['load_money_format'] = format_price($deal['load_money']/10000)."万";
		
	$deal['rate_foramt'] = number_format($deal['rate'],2);
		
	//$deal['borrow_amount_format_w'] = format_price($deal['borrow_amount']/10000)."万";
	$deal['rate_foramt_w'] = number_format($deal['rate'],2)."%";
	
	$deal_repay_rs = deal_repay_money($deal);
		
	//本息还款金额
	$deal['month_repay_money'] = $deal_repay_rs['month_repay_money'];
	
	//最后一期还款
	$deal['last_month_repay_money'] = $deal_repay_rs['last_month_repay_money'];

	$deal['month_repay_money_format'] = format_price($deal['month_repay_money']);
		
	//到期还本息管理费
	$deal['month_manage_money'] = $deal['borrow_amount']*(float)$deal['manage_fee']/100;

	$deal['month_manage_money_format'] = format_price($deal['month_manage_money']);
	
	//总的多少管理费
	if($deal['repay_time_type']==1)
		$deal['all_manage_money'] = $deal['month_manage_money'] * $deal["repay_time"];
	else
		$deal['all_manage_money'] = $deal['month_manage_money'] ;
	
	if(is_last_repay($deal['loantype'])){
		$deal['true_month_repay_money'] = $deal['month_repay_money'] + $deal['all_manage_money'];
		$deal['true_last_month_repay_money'] = $deal['last_month_repay_money'] + $deal['all_manage_money'];
	}
	else{
		$deal['true_month_repay_money'] = $deal['month_repay_money'] + $deal['month_manage_money'];
		$deal['true_last_month_repay_money'] = $deal['last_month_repay_money'] + $deal['month_manage_money'];
	}
	
	
	//还需多少钱
	$deal['need_money'] = format_price($deal['borrow_amount'] - $deal['load_money']);
	//百分比
	$deal['progress_point'] = $deal['load_money']/$deal['borrow_amount']*100;
		
	$deal['user'] = get_user("user_name,level_id,province_id,city_id",$deal['user_id']);
		
	if($deal['cate_id'] > 0){
		$deal['cate_info'] = $GLOBALS['db']->getRowCached("select id,name,brief,uname,icon from ".DB_PREFIX."deal_cate where id = ".$deal['cate_id']." and is_effect = 1 and is_delete = 0");
	}
	if($deal['type_id'] > 0){
		$deal['type_info'] = $GLOBALS['db']->getRowCached("select id,name,brief,uname,icon from ".DB_PREFIX."deal_loan_type where id = ".$deal['type_id']." and is_effect = 1 and is_delete = 0");
	}
	
	if($deal['agency_id'] > 0){
		$deal['agency_info'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_agency where id = ".$deal['agency_id']." and is_effect = 1");
		if($deal['agency_info']['view_info']!=""){
			$deal['agency_info']['view_info_list'] = unserialize($deal['agency_info']['view_info']);
		}
	}
	
	if($deal['deal_status'] <> 1 || $deal['remain_time'] <= 0){
		$deal['remain_time_format'] = "0".$GLOBALS['lang']['DAY']."0".$GLOBALS['lang']['HOUR']."0".$GLOBALS['lang']['MIN'];
	}
	else{
		$deal['remain_time_format'] = remain_time($deal['remain_time']);
	}
		
	$deal['min_loan_money_format'] = format_price($deal['min_loan_money']);
	
	if($deal['uloadtype'] == 1){
		if($deal['buy_count'] == 0)
			$deal['buy_portion'] = 0;
		else
			$deal['buy_portion'] = intval($deal['load_money']/$deal['min_loan_money']);
			
		$deal['need_portion'] = intval(($deal['borrow_amount'] - $deal['load_money']) / $deal['min_loan_money']);
	}	
		
	if($deal['deal_status']>=4){
		
		//总的必须还多少本息
		$deal['remain_repay_money'] = $deal_repay_rs['remain_repay_money'];
		
		//还有多少需要还
		$deal['need_remain_repay_money'] = floatval($deal['remain_repay_money']) - floatval($deal['repay_money']);
		
		//还款进度条
		if($deal['remain_repay_money'] > 0)
			$deal['repay_progress_point'] =  $deal['repay_money']/$deal['remain_repay_money']*100;
		else
			$deal['repay_progress_point'] =  0;


		//最后的还款日期
		if($deal['repay_time_type'] == 0)
			$deal["end_repay_time"] =  $deal['repay_start_time'] + $deal['repay_time']*24*3600;
		else
			$deal["end_repay_time"] =  next_replay_month($deal['repay_start_time'],$true_repay_time);
		
		if($deal['deal_status']==4){
	
			$deal["next_repay_time_format"] = to_date($deal['next_repay_time'],'Y-m-d');
			
			if(to_date($deal["end_repay_time"],"Ymd") < to_date(TIME_UTC,"Ymd")){
				$deal['exceed_the_time'] = true;
			}
	
			//罚息
			$is_check_impose = true;
			//到期还本息 只有最后一个月后才算罚息
			if($deal_repay_rs['is_check_impose'] == true){
				//算出到期还本息的最后一个月是否小于今天
				if($deal['exceed_the_time']){
					$is_check_impose = true;
				}
				else{
					$is_check_impose = false;
				}
			}
			if($deal["next_repay_time"] - TIME_UTC < 0 && $is_check_impose){
				//晚多少天
				$time_span = to_timespan(to_date(TIME_UTC,"Y-m-d"),"Y-m-d");
				$next_time_span = to_timespan(to_date($deal['next_repay_time'],"Y-m-d"),"Y-m-d");
				$day  = ceil(($time_span-$next_time_span)/24/3600);
					
				$impose_fee = trim($deal['impose_fee_day1']);
				$manage_impose_fee = trim($deal['manage_impose_fee_day1']);
				//判断是否严重逾期
				if($day >= app_conf('YZ_IMPSE_DAY')){
					$impose_fee = trim($deal['impose_fee_day2']);
					$manage_impose_fee = trim($deal['manage_impose_fee_day2']);
				}
				
				$impose_fee = floatval($impose_fee);
				$manage_impose_fee = floatval($manage_impose_fee);
					
				//罚息
				if((int)$deal['next_repay_time'] == (int)$deal['end_repay_time']){
					$deal['impose_money'] = $deal['last_month_repay_money']*$impose_fee*$day/100;
					$deal['manage_impose_money'] = $deal['last_month_repay_money']*$manage_impose_fee*$day/100;
				}
				else{
					$deal['impose_money'] = $deal['month_repay_money']*$impose_fee*$day/100;
					//罚管理费
					$deal['manage_impose_money'] = $deal['month_repay_money']*$manage_impose_fee*$day/100;
				}	
				$deal['impose_money'] += $deal['manage_impose_money'];
			}
		}
	}
		
	if($deal['publish_wait'] == 1 || $deal['publish_wait'] == 0){
		$deal['publish_time_format'] = to_date($deal['create_time'],'Y-m-d H:i');
	}else{
		$deal['publish_time_format'] = to_date($deal['start_time'],'Y-m-d H:i');
	}
	
	$durl = url("index","deal",array("id"=>$deal['id']));
	$deal['share_url'] = SITE_DOMAIN.APP_ROOT.$durl;
	if($GLOBALS['user_info'])
	{
		if(app_conf("URL_MODEL")==0)
		{
			$deal['share_url'] .= "&r=".base64_encode(intval($GLOBALS['user_info']['id']));
		}
		else
		{
			$deal['share_url'] .= "?r=".base64_encode(intval($GLOBALS['user_info']['id']));
		}
	}
	
	$deal['url'] = $durl;
	if (!empty($user_name) && !empty($user_pwd)){
		$durl = "/index.php?ctl=uc_deal&act=mrefdetail&is_sj=1&id=".$deal['id']."&user_name=".$user_name."&user_pwd=".$user_pwd;
	}else{
		$durl = "/index.php?ctl=deal&act=mobile&is_sj=1&id=".$deal['id'];
	}
		
	$deal['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.APP_ROOT.$durl);
}

/**
 * 还款列表
 */
function get_deal_load_list($deal){
	
	//当为天的时候
	if($deal['repay_time_type'] == 0){
		$true_repay_time = 1;
	}

	
	$deal_repay_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_repay where deal_id=".$deal['id']." order by l_key ASC ");

	foreach($deal_repay_list as $k=>$v){
		
		$i = $v['l_key'];
		$loan_list[$i]['l_key'] = $v['l_key'];
		$loan_list[$i]['repay_id'] = $v['id'];
		$loan_list[$i]['impose_day'] = 0;
		/**
		 * status 1提前,2准时还款，3逾期还款 4严重逾期 5部分还款 6还款中
		 */
		if($v['has_repay'] == 2){
			$loan_list[$i]['status'] = 5;
		}
		elseif($v['has_repay'] == 3){
			$loan_list[$i]['status'] = 6;
		}
		
		$loan_list[$i]['repay_day'] = $v['repay_time'];
				
			//加息劵利息
			$loan_list[$i]['incerease_money'] = $v['incerease_money'];
				
		//月还本息
		$loan_list[$i]['month_repay_money'] = $v['repay_money'];
		//判断是否已经还完
		$loan_list[$i]['true_repay_time'] = $v['true_repay_time'];
		//管理费
		$loan_list[$i]['month_manage_money'] = $v['manage_money'];
		
		//has_repay：1：已还款;0:未还款
		$loan_list[$i]['has_repay'] = $v['has_repay'];
		
		//已还多少
		$loan_list[$i]['month_has_repay_money'] = 0;
		
		//总罚息 =  罚息管理费 + 逾期管理费
		$loan_list[$i]['impose_all_money'] = 0;
		if($v['has_repay'] == 1){
			$loan_list[$i]['month_has_repay_money'] = $v['true_repay_money'];
			$loan_list[$i]['month_manage_money'] = $v['true_manage_money'];
				
			$loan_list[$i]['status'] = $v['status']+1;
			
			$loan_list[$i]['month_repay_money'] =0;
			
			//逾期罚息
			$loan_list[$i]['impose_money'] = $v['impose_money'];
			
			//逾期管理费
			$loan_list[$i]['manage_impose_money'] = $v['manage_impose_money'];
		
			//真实还多少
			$loan_list[$i]['month_has_repay_money_all'] = $loan_list[$i]['month_has_repay_money'] + $loan_list[$i]['month_manage_money']+$loan_list[$i]['impose_money']+$loan_list[$i]['manage_impose_money'];
			
			//总的必须还多少
			$loan_list[$i]['month_need_all_repay_money'] = 0;
			
			$loan_list[$i]['impose_all_money'] = $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'];
			
		}
		elseif($v['has_repay'] == 0){
			//判断是否罚息
			if(TIME_UTC > ($v['repay_time']+ 24*3600 -1)&& $loan_list[$i]['month_repay_money'] > 0){
				//晚多少天
				$loan_list[$i]['status'] = 3;
				$time_span = to_timespan(to_date(TIME_UTC,"Y-m-d"),"Y-m-d");
				$next_time_span = $v['repay_time'];
				$day  = ceil(($time_span-$next_time_span)/24/3600);

				$loan_list[$i]['impose_day'] = $day;

				$impose_fee = trim($deal['impose_fee_day1']);
				$manage_impose_fee = trim($deal['manage_impose_fee_day1']);
				//严重逾期费率
				if($day >= app_conf('YZ_IMPSE_DAY')){
					$loan_list[$i]['status'] = 4;
					$impose_fee = trim($deal['impose_fee_day2']);
					$manage_impose_fee = trim($deal['manage_impose_fee_day2']);
				}
				
				$impose_fee = floatval($impose_fee);
				$manage_impose_fee = floatval($manage_impose_fee);

				//罚息
				$loan_list[$i]['impose_money'] = $loan_list[$i]['month_repay_money']*$impose_fee*$day/100;
				
				//罚管理费
				$loan_list[$i]['manage_impose_money'] = $loan_list[$i]['month_repay_money']*$manage_impose_fee*$day/100;
				$loan_list[$i]['impose_all_money'] = $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'];
			}
			/*elseif(to_date(TIME_UTC,"Y-m-d") == to_date($v['repay_time'],"Y-m-d") || (((int)$v['repay_time'] - TIME_UTC)/24/3600 <=3 && ((int)$v['repay_time'] - TIME_UTC)/24/3600 >=0)){
				$loan_list[$i]['status'] =  2;
			}
			else{
				$loan_list[$i]['status'] =  1;
			}*/
			else{
				$loan_list[$i]['status'] =  2;
			}
				
			//真实还多少
			$loan_list[$i]['month_has_repay_money_all'] = 0;
				
			//总的必须还多少
			$loan_list[$i]['month_need_all_repay_money'] =  $loan_list[$i]['month_repay_money'] + $loan_list[$i]['month_manage_money'] + $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'];
		}
		elseif($v['has_repay'] == 2){
			//判断是否罚息
			$ss_repay_info = $GLOBALS['db']->getRow("SELECT sum(repay_money) as month_repay_money,sum(repay_manage_money) as month_manage_money FROM ".DB_PREFIX."deal_load_repay WHERE l_key =".$i." and deal_id=".$deal['id']." and has_repay=0 ");
			
			$tmp_month_repay_money = $loan_list[$i]['month_repay_money'];
			$loan_list[$i]['month_repay_money'] = $ss_repay_info['month_repay_money'];
			$loan_list[$i]['month_manage_money']= $ss_repay_info['month_manage_money'];
			if(TIME_UTC > ($v['repay_time']+ 24*3600 -1)&& $loan_list[$i]['month_repay_money'] > 0){
				$loan_list[$i]['status'] = 3;
				//晚多少天
				$time_span = to_timespan(to_date(TIME_UTC,"Y-m-d"),"Y-m-d");
				$next_time_span = $v['repay_time'];
				$day  = ceil(($time_span-$next_time_span)/24/3600);

				$loan_list[$i]['impose_day'] = $day;

				$impose_fee = trim($deal['impose_fee_day1']);
				$manage_impose_fee = trim($deal['manage_impose_fee_day1']);
				//严重逾期费率
				if($day >= app_conf('YZ_IMPSE_DAY')){
					$loan_list[$i]['status'] = 4;
					$impose_fee = trim($deal['impose_fee_day2']);
					$manage_impose_fee = trim($deal['manage_impose_fee_day2']);
				}
				
				$impose_fee = floatval($impose_fee);
				$manage_impose_fee = floatval($manage_impose_fee);

				//罚息
				$loan_list[$i]['impose_money'] = $loan_list[$i]['month_repay_money']*$impose_fee*$day/100;
					//加息劵利息
			     $loan_list[$i]['incerease_money'] = $v['incerease_money'];
				
				//罚管理费
				$loan_list[$i]['manage_impose_money'] = $loan_list[$i]['month_repay_money']*$manage_impose_fee*$day/100;
				$loan_list[$i]['impose_all_money'] = $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'];
			}
			/*elseif(to_date(TIME_UTC,"Y-m-d") == to_date($v['repay_time'],"Y-m-d") || (((int)$v['repay_time'] - TIME_UTC)/24/3600 <=3 && ((int)$v['repay_time'] - TIME_UTC)/24/3600 >=0)){
				
				$loan_list[$i]['status'] =  2;
			}
			elseif(round($tmp_month_repay_money) <= $loan_list[$i]['month_repay_money']){
				$loan_list[$i]['has_repay'] =  0;
			}
			else{
				$loan_list[$i]['status'] =  1;
			}*/
			else{
				$loan_list[$i]['status'] =  2;
			}
			$loan_list[$i]['has_repay'] =  0;
				
			//真实还多少
			$loan_list[$i]['month_has_repay_money_all'] = 0;
				
			//总的必须还多少
			$loan_list[$i]['month_need_all_repay_money'] =  $loan_list[$i]['month_repay_money'] + $loan_list[$i]['month_manage_money'] + $loan_list[$i]['impose_money'] + $loan_list[$i]['manage_impose_money'];
		}


		//还款日
		$loan_list[$i]['repay_day_format'] = to_date($loan_list[$i]['repay_day'],'Y-m-d');
		//已还金额
		$loan_list[$i]['month_has_repay_money_all_format'] = format_price($loan_list[$i]['month_has_repay_money_all']);
		//待还金额
		$loan_list[$i]['month_need_all_repay_money_format'] = format_price($loan_list[$i]['month_need_all_repay_money']);

		//待还本息
		$loan_list[$i]['month_repay_money_format'] = format_price($loan_list[$i]['month_repay_money']);
		//借款管理费
		$loan_list[$i]['month_manage_money_format'] = format_price($loan_list[$i]['month_manage_money']);
		
		//借款管理费
		$loan_list[$i]['manage_money_impose_format'] = format_price($loan_list[$i]['manage_impose_money']);
		//加息劵利息
	     $loan_list[$i]['manage_incerease_money_format'] = format_price($loan_list[$i]['incerease_money']);

		//逾期费用
		$loan_list[$i]['impose_money_format'] = format_price($loan_list[$i]['impose_money']);
		
		//逾期、违约金
		$loan_list[$i]['impose_all_money_format'] = format_price($loan_list[$i]['impose_all_money']);
		
		//状态
		if($loan_list[$i]['has_repay'] == 0){
			$loan_list[$i]['status_format'] = '待还';
		}
		elseif($loan_list[$i]['status'] == 1){
			$loan_list[$i]['status_format'] = '提前还款';
		}elseif($loan_list[$i]['status'] == 2){
			$loan_list[$i]['status_format'] = '正常还款';
		}elseif($loan_list[$i]['status'] == 3){
			$loan_list[$i]['status_format'] = '逾期还款';
		}elseif($loan_list[$i]['status'] == 4){
			$loan_list[$i]['status_format'] = '严重逾期';
		}elseif($loan_list[$i]['status'] == 5){
			$loan_list[$i]['status_format'] = '部分还款';
		}
		elseif($loan_list[$i]['status'] == 6){
			$loan_list[$i]['status_format'] = '还款中';
		}
		
		
	}
	

	return $loan_list;
}


/**
 * 获取某一期的用户还款列表
 * array $deal_info 借款信息 
 * int $user_id 用户ID 为0代表全部
 * int $lkey  第几期 -1 全部
 * int $ukey 第几个投标人 -1 全部
 * int $true_time  真实还款时间
 * int $get_type  0 全部 1代表未还的  2 代表已还的
 * int $r_type = 0; 返回类型; 1:只返回一个数组; $result['item']
 * string $limit; 查询限制数量; 0,20  $result['count']
 */
 
function get_deal_user_load_list($deal_info, $user_id = 0 ,$lkey = -1 , $ukey = -1,$true_time=0,$get_type = 0, $r_type = 0, $limit = ""){
	if(!$deal_info){
		return false;
	}

	$result = array();
	
		if($get_type > 0){
			if($get_type==1)
				$extW = " AND dlr.has_repay = 0 ";
			else
				$extW = " AND dlr.has_repay = 1 ";
		}
		
		if($user_id > 0){
			$extW .= " AND dlr.user_id =  ".$user_id;
		}
		
		if($lkey >= 0){
			$extW .= " AND dlr.l_key =  ".$lkey;
		}
				
		if (!empty($limit)){ 
			$limit = " limit ".$limit;
		
			$sql = "SELECT count(*) FROM ".DB_PREFIX."deal_load_repay dlr ".					
					" WHERE dlr.deal_id=".$deal_info['id']." $extW";
			
			$count = $GLOBALS['db']->getOne($sql);
			$result['count'] = $count;
		}
		$sql = "SELECT dlr.*,dl.pMerBillNo,dl.money,dl.unjh_pfcfb,u.ips_acct_no,u.mobile,u.email,u.user_name,tu.ips_acct_no as t_ips_acct_no,tu.id as t_user_id,tu.user_name as t_user_name,tu.mobile as t_mobile,tu.email as t_email  FROM ".DB_PREFIX."deal_load_repay dlr ".
				" LEFT JOIN ".DB_PREFIX."deal_load dl ON dl.id =dlr.load_id  ".
				" LEFT OUTER JOIN ".DB_PREFIX."user u ON u.id = dlr.user_id ".
				" LEFT OUTER JOIN ".DB_PREFIX."deal_load_transfer dlt ON dlt.load_id = dl.id and dlt.near_repay_time <=dlr.repay_time ".
				" LEFT OUTER JOIN ".DB_PREFIX."user tu ON tu.id = dlt.t_user_id ".
				" WHERE dlr.deal_id=".$deal_info['id']." $extW ORDER BY dlr.l_key ASC,dlr.u_key ASC ".$limit;
		
		//echo $sql; exit;
		$load_users = $GLOBALS['db']->getAll($sql);
	
	if($true_time == 0)
		$true_time = TIME_UTC;
	
	
	
	$loan_list = array();
	foreach($load_users as $k=>$v){
			//转出方手续费  ===》收取：借款者 的管理费 + 管理逾期罚息   $item['repay_manage_money']  + $item['repay_manage_impose_money']
			//转入方手续费  ===》收取：投资者 的管理费  $item['manage_money']
			//转入金额 ===》还款金额 + 逾期罚息 $item['month_repay_money'] + $item['impose_money']  
		
			$item = array();
			
			//deal_load_repay 编号
			$item['id'] = $v['id'];
			
			//status 1提前,2准时还款，3逾期还款 4严重逾期 数据库里的参数 + 1
			if($v['has_repay'] == 1){
				$item['status'] = $v['status'] +1;
			}
			
			//实际投标金额
			$item['money'] = $v['money']; 
			
			//还款日
			$item['repay_day'] = $v['repay_time'];
			
			//实际还款日
			$item['true_repay_time'] = $v['true_repay_time'];
			
			//月还本息
			$item['month_repay_money']= $v['true_repay_money'];
			
			//当前期本金
			$item['self_money'] = $v['true_self_money'];
			
			//罚息
			$item['impose_money'] =$v['impose_money'];
			
			
			$item['interest_money'] = $v['true_interest_money'];
			//投标者信息
			$item['user_id'] =$v['user_id'];
			$item['user_name'] =$v['user_name'];
			$item['email'] =$v['email'];
			$item['mobile'] =$v['mobile'];
			$item['ips_acct_no'] =$v['ips_acct_no'];
			
			//承接者信息
			$item['t_user_id'] =$v['t_user_id'];
			$item['t_user_name'] =$v['t_user_name'];
			$item['t_ips_acct_no'] =$v['t_ips_acct_no'];
			$item['t_email'] =$v['t_email'];
			$item['t_mobile'] =$v['t_mobile'];
			
			//管理费
			$item['manage_money'] =$v['true_manage_money'];
			
			//借款者均摊下来的管理费
			$item['repay_manage_money'] =$v['true_repay_manage_money'];
			
			//是否还款 0未还 1已还
			$item['has_repay'] =$v['has_repay'];
			
			//对应deal_repay的编号
			$item['repay_id'] =$v['repay_id'];
			//投标编号 对应 deal_load 的编号
			$item['load_id'] =$v['load_id'];
			//第几期
			$item['l_key'] =$v['l_key'];
			//对应借款的第几个投标人
			$item['u_key'] =$v['u_key'];
			//登记债权人时提 交的订单号
			$item['pMerBillNo'] =$v['pMerBillNo'];
			//逾期借入者管理费罚息
			$item['repay_manage_impose_money'] = $v['repay_manage_impose_money'];
			//返佣
			$item['manage_interest_money_rebate'] = $v['manage_interest_money_rebate'];
			$item['true_manage_interest_money_rebate'] = $v['true_manage_interest_money_rebate'];
			
			
			if($v['has_repay'] == 0){
				//月还本息
				$item['month_repay_money']= $v['repay_money'];
				//管理费
				$item['manage_money'] =$v['manage_money'];
				$item['repay_manage_money'] =$v['repay_manage_money'];
				$item['self_money'] = $v['self_money'];
				$item['interest_money'] = $v['interest_money'];
				$item['repay_manage_impose_money'] = $v['repay_manage_impose_money'];
				$item['month_has_repay_money'] = 0;
				if($true_time > ($v['repay_time'] + 24*3600 -1 ) && $item['month_repay_money'] > 0){
					$time_span = to_timespan(to_date($true_time,"Y-m-d"),"Y-m-d");
					$next_time_span = $v['repay_time'];
					$item['impose_day'] = $day  = ceil(($time_span-$next_time_span)/24/3600);
					
		
					if($day >0){
						//普通逾期
						$item['status'] = 3;
						$impose_fee = trim($deal_info['impose_fee_day1']);
						$manage_impose_fee = trim($deal_info['manage_impose_fee_day1']);
						if($day >= app_conf('YZ_IMPSE_DAY')){//严重逾期
							$impose_fee = trim($deal_info['impose_fee_day2']);
							$manage_impose_fee = trim($deal_info['manage_impose_fee_day2']);
							$item['status'] = 4;
						}
						
						$impose_fee = floatval($impose_fee);
							
						//罚息
						$item['impose_money'] = $item['month_repay_money'] *$impose_fee*$day/100;
						
						$item['repay_manage_impose_money'] = $item['month_repay_money']*$manage_impose_fee*$day/100;
					}
					
				}
				/*elseif(to_date($true_time,"Y-m-d") == to_date($v['repay_time'],"Y-m-d")  || (((int)$v['repay_time'] - $true_time)/24/3600 <=3 && ((int)$v['repay_time'] - $true_time)/24/3600 >=0)){
					$item['status'] = 2;
				}
				else{
					$item['status'] = 1;
				}*/
				else{
					$item['status'] = 2;
				}
				$item['month_has_repay_money'] = 0;
				$item['month_has_repay_money_all'] = 0;					
			}
			elseif($v['has_repay'] == 2){
				//月还本息
				$item['month_repay_money']= $v['repay_money'];
				//管理费
				$item['manage_money'] =$v['manage_money'];
				$item['repay_manage_money'] =$v['repay_manage_money'];
				$item['self_money'] = $v['self_money'];
				$item['interest_money'] = $v['interest_money'];
				$item['repay_manage_impose_money'] = $v['repay_manage_impose_money'];
				$item['month_has_repay_money'] = 0;
				$item['month_has_repay_money_all'] = 0;
				
			}
			else{
				$item['month_has_repay_money'] = $item['month_repay_money'];
				$item['month_has_repay_money_all'] = $item['month_repay_money'] + $item['month_manage_money']+$item['impose_money'];
			}
			
			$item['repay_day_format'] = to_date($item['repay_day'],"Y-m-d");
			$item['true_repay_time_format'] = to_date($item['true_repay_time']);
			$item['manage_money_format'] = format_price($item['manage_money']);
			$item['impose_money_format'] = format_price($item['impose_money']);
			$item['repay_manage_impose_money_format'] = format_price($item['repay_manage_impose_money']);
			$item['month_repay_money_format'] = format_price($item['month_repay_money']);
			$item['month_has_repay_money_format'] = format_price($item['month_has_repay_money']);
			$item['month_has_repay_money_all_format'] = format_price($item['month_has_repay_money_all']);
			//状态
			if($item['has_repay'] == 0){
				$item['status_format'] = '待还';
			}elseif($item['status'] == 1){
				$item['status_format'] = '提前还款';
			}elseif($item['status'] == 2){
				$item['status_format'] = '正常还款';
			}elseif($item['status'] == 3){
				$item['status_format'] = '逾期还款';
			}elseif($item['status'] == 4){
				$item['status_format'] = '严重逾期';
			}
			
			$item['site_repay_format'] = "";
			if($v['has_repay']==1){
				if($v['is_site_repay'] == 0){
					$item['site_repay_format'] = "会员";
				}
				elseif($v['is_site_repay'] == 1){
					$item['site_repay_format'] = "网站";
				}
				elseif($v['is_site_repay'] == 2){
					$item['site_repay_format'] = "机构";
				}
			}	
			
			if ($r_type == 0){
				if($lkey >= 0){
					if($lkey == $item['l_key']){
						$loan_list[$item['u_key']][$item['l_key']] = $item;
					}
				}
				else
					$loan_list[$item['u_key']][$item['l_key']] = $item;
			}else{
				$loan_list[] = $item;
			}
	}
	
	if ($r_type == 0){	
		if($ukey >= 0)
			return $loan_list[$ukey];
		else{
			return $loan_list;
		}
	}else{
		$result['item'] = $loan_list;
		return $result;
	}
}


/**
 * 动态输出成功案例， 不受缓存限制
 */
function insert_success_deal_list(){
	//输出成功案例
	$GLOBALS['tmpl']->caching = true;
	$GLOBALS['tmpl']->cache_lifetime = 120;  //首页缓存10分钟
	$cache_id  = md5("success_deal_list");	
	if (!$GLOBALS['tmpl']->is_cached("inc/insert/success_deal_list.html", $cache_id))
	{	
		$suc_deal_list =  get_deal_list(11,0,"deal_status in(4,5) "," success_time DESC,sort DESC,id DESC");
		$GLOBALS['tmpl']->assign("succuess_deal_list",$suc_deal_list['list']);
	}
	return $GLOBALS['tmpl']->fetch("inc/insert/success_deal_list.html",$cache_id);
}


//更改过期流标状态
function change_deal_status(){
	//$sql = "select id from ".DB_PREFIX."deal where is_effect = 1 and deal_status = 1 and is_delete = 0 AND load_money/borrow_amount < 1 AND (start_time + enddate*24*3600 - ".TIME_UTC.") <=0  ";
	/*$sql = "select id from ".DB_PREFIX."deal where is_effect = 1 and deal_status = 1 and is_delete = 0 AND load_money/borrow_amount <= 1 ";
	 $deal_ids = $GLOBALS['db']->getAll($sql);

	foreach($deal_ids as $k=>$v)
	{
	syn_deal_status($v['id']);
	}*/
	syn_dealing();
}



function check_dobid2($deal_id,$bid_money,$bid_paypassword,$is_pc = 0){	
	$root = array();
	$root["status"] = 0;//0:出错;1:正确;
	
	$bid_money = floatval($bid_money);
	$bid_paypassword = strim($bid_paypassword);
	
	
	if(!$GLOBALS['user_info']){
		$root["show_err"] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		return $root;
	}
	
	
	if($bid_paypassword==""){
		$root["show_err"] = $GLOBALS['lang']['PAYPASSWORD_EMPTY'];
		return $root;
	}
	
	if(md5($bid_paypassword)!=$GLOBALS['user_info']['paypassword']){
		$root["show_err"] = $GLOBALS['lang']['PAYPASSWORD_ERROR'];
		return $root;
	}
	
	$deal = get_deal($deal_id);
	if(!$deal){
		$root["show_err"] = $GLOBALS['lang']['PLEASE_SPEC_DEAL'];
		return $root;
	}
	
	if($deal['user_id'] == $GLOBALS['user_info']['id']){
		$root["show_err"] = $GLOBALS['lang']['CANT_BID_BY_YOURSELF'];
		return $root;
	}
	
	if($deal['ips_bill_no']!="" && $GLOBALS['user_info']['ips_acct_no']==""){
		$root["show_err"] = "此标为第三方托管标，请先绑定第三方托管账户,<a href=\"".url("index","uc_center")."\" target='_blank'>点这里设置</a>";
		return $root;
	}
	
	if($deal['is_wait'] == 1){
		$root["show_err"] = $GLOBALS['lang']['DEAL_IS_WAIT'];
		return $root;
	}
	
	
	if(floatval($deal['progress_point']) >= 100){
		$root["show_err"] = $GLOBALS['lang']['DEAL_BID_FULL'];
		return $root;
	}
	
	if(floatval($deal['deal_status']) != 1 ){
		$root["show_err"] = $GLOBALS['lang']['DEAL_FAILD_OPEN'];
		return $root;
	}
	
	
	//@file_put_contents("/Public/sqlog.txt",print_r($_REQUEST,1));
	//手机端或者 按份数 默认跑到这里
	if ($deal['uloadtype'] == 0 || $is_pc == 0){
		// if($bid_money <=0 || $bid_money < $deal['min_loan_money'] || ($bid_money * 100)%100!=0){
			// $root["show_err"] = $GLOBALS['lang']['BID_MONEY_NOT_TRUE'];
		///	print_r($deal);
			// return $root;
		// }
		// if(floatval($deal['max_loan_money']) >0){
			// if($bid_money > floatval($deal['max_loan_money'])){
				// $root["show_err"] = $GLOBALS['lang']['BID_MONEY_NOT_TRUE'];
			// /*	//print_r($deal);
				// /*
				 // $root["bid_money"] = $bid_money;
				// $root["max_loan_money"] = floatval($deal['max_loan_money']);
				// $root["show_err"] = 'ddd2';
				// print_r($root);
				// die();
				// */////
				// return $root;
			// }
		// }
		
		if((int)strim(app_conf('DEAL_BID_MULTIPLE')) > 0){
			if($bid_money%(int)strim(app_conf('DEAL_BID_MULTIPLE'))!=0){
				$root["show_err"] = $GLOBALS['lang']['BID_MONEY_NOT_TRUE'];
				return $root;
			}
		}
		
		
		//判断所投的钱是否超过了剩余投标额度
		if($bid_money > (round($deal['borrow_amount'],2) - round($deal['load_money'],2))){
			$root["show_err"] = sprintf($GLOBALS['lang']['DEAL_LOAN_NOT_ENOUGHT'],format_price($deal['borrow_amount'] - $deal['load_money']));
			return $root;
		}
		
		
		//判断所投的全部金额是否超过了所限制的金额
		if(floatval($deal['max_loan_money']) > 0){
			$has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load WHERE deal_id=".$deal_id);
			if($has_bid_money > 0){
				if($has_bid_money > floatval($deal['max_loan_money'])){
					$root["show_err"] = "您已经投满该借款所限制的额度：".format_price($deal['max_loan_money']);
					return $root;
				}
				
				if($has_bid_money + $bid_money > floatval($deal['max_loan_money'])){
					$root["show_err"] = "您已经投了".format_price($has_bid_money);
					if(floatval($deal['max_loan_money']) - $has_bid_money > 0){
						$root["show_err"] .= ",只能再投".format_price(floatval($deal['max_loan_money']) - $has_bid_money);
					}
					else{
						$root["show_err"] .= ",不能再投了";
					}
					return $root;
				}
			}
		}
		
		$root["bid_money"] = $bid_money;
	}
	else{
		if(intval($bid_money) <=0 || ($bid_money * 100)%100!=0){
			$root["show_err"] = $GLOBALS['lang']['BID_MONEY_NOT_TRUE'];
			//print_r($deal);
			return $root;
		}
		
		//判断所投的钱是否超过了剩余投标额度
		$has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load WHERE deal_id=".$deal_id);
		$has_bid_portion = $has_bid_money/($deal['borrow_amount'] / $deal['portion']);
		if(intval($deal['max_portion']) > 0  && intval($bid_money) > (intval($deal['max_portion'] - intval($has_bid_portion)))){
			$root["show_err"] = "您已经购买了$has_bid_portion份，还能购买".intval($deal['max_portion'] - intval($has_bid_portion))."份";
			return $root;
		}
		elseif(intval($bid_money) > intval($deal['need_portion'])){
			$root["show_err"] = "您已经购买了$has_bid_portion份，还能购买".intval($deal['need_portion'])."份";
			return $root;
		}
		
		$root["bid_money"] = $bid_money * ($deal['borrow_amount'] / $deal['portion']);
	}
	
	$root["deal"] = $deal;
	if($deal['ips_bill_no']==""){
		$root["status"] = 1;//0:出错;1:正确;
		
		return $root;
	}
	else{
		$root["status"] = 2;//第三方托管标 正确
	
		return $root;
	}
	
}

function dobid2_ok($deal_id,$user_id){
	$deal = get_deal($deal_id);
	sys_user_status($user_id);
	//超过一半的时候
	
	if($deal['deal_status']==1 && $deal['progress_point'] >= 50 && $deal['progress_point']<=60 && $deal['is_send_half_msg'] == 0)
	{
		$msg_conf = get_user_msg_conf($deal['user_id']);
		//邮件
		if(app_conf("MAIL_ON")){
			if(!$msg_conf || intval($msg_conf['mail_half'])==1){
				$load_tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_HALF_EMAIL'");
				$user_info = $GLOBALS['db']->getRow("select email,user_name from ".DB_PREFIX."user where id = ".$deal['user_id']);
				$tmpl_content = $load_tmpl['content'];
				$notice['user_name'] = $user_info['user_name'];
				$notice['deal_name'] = $deal['name'];
				$notice['deal_url'] = SITE_DOMAIN.$deal['url'];
				$notice['site_name'] = app_conf("SHOP_TITLE");
				$notice['site_url'] = SITE_DOMAIN.APP_ROOT;
				$notice['help_url'] = SITE_DOMAIN.url("index","helpcenter");
				$notice['msg_cof_setting_url'] = SITE_DOMAIN.url("index","uc_msg#setting");
	
	
				$GLOBALS['tmpl']->assign("notice",$notice);
	
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $user_info['email'];
				$msg_data['send_type'] = 1;
				$msg_data['title'] = "您的借款列表“".$deal['name']."”招标过半！";
				$msg_data['content'] = addslashes($msg);
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = TIME_UTC;
				$msg_data['user_id'] =  $deal['user_id'];
				$msg_data['is_html'] = $load_tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			}
		}
	
		//站内信
		if(intval($msg_conf['sms_half'])==1){
			$content = "<p>您在".app_conf("SHOP_TITLE")."的借款“<a href=\"".$deal['url']."\">".$deal['name']."</a>”完成度超过50%";
			send_user_msg("",$content,0,$deal['user_id'],TIME_UTC,0,true,15);
		}
	
		//更新
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal",array("is_send_half_msg"=>1),"UPDATE","id=".$deal_id);
	}	
}

function dobid2($deal_id,$bid_money,$bid_paypassword,$is_pc=0,$unjh_pfcfb,$virtual_money){
	$root = check_dobid2($deal_id,$bid_money,$bid_paypassword,$is_pc);
	if ($root["status"] == 0){
		return $root;
	}
	elseif($root["status"] == 2){
		$root['jump'] = APP_ROOT."/index.php?ctl=collocation&act=RegisterCreditor&deal_id=$deal_id&user_id=".$GLOBALS['user_info']['id']."&bid_money=".$root['bid_money']."&bid_paypassword=$bid_paypassword"."&from=".$GLOBALS['request']['from'];		
		$root['jump'] = str_replace("/mapi", "", SITE_DOMAIN.$root['jump']);
		return $root;
	}
	$root["status"] = 0;
	$bid_money = floatval($root['bid_money']);
	$bid_paypassword = strim($bid_paypassword);

	if($bid_money > $GLOBALS['user_info']['money']){
		$root["show_err"] = $GLOBALS['lang']['MONEY_NOT_ENOUGHT'];
		return $root;
	}
	require_once APP_ROOT_PATH."system/libs/user.php";
/*virtual_money*/
 if($virtual_money){
   		$data['virtual_money']=$virtual_money;
    }
	
/*浦发币操作*/
$deal = get_deal($deal_id);
		if($unjh_pfcfb>0){
			if($deal['repay_time']>=1 && $deal['repay_time_type']==1){
			$data['unjh_pfcfb']=$unjh_pfcfb;
		modify_account(array('unjh_pfcfb'=>-$unjh_pfcfb,'lock_money'=>0),$GLOBALS['user_info']['id'],"投标成功",2);
			}
		}
	$pfcfbss= $GLOBALS['db']->getRow("select * from ".DB_PREFIX."pfcfb_huodong where id=2");
	$pd_time=get_gmtime();
	if($pd_time<$pfcfbss['end_time'] && $pfcfbss['open_off']==1 && $pd_time>$pfcfbss['start_time']){
			if($deal['repay_time']>=1&&$deal['repay_time_type']==1){	
	           $nodeal=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_load where user_id=".$GLOBALS['user_info']['id']);
                if(!$nodeal){ //客户首次投资给推荐他的人送20可提现的现金红包
				/*
		        if($_REQUEST["bid_money"]>=10000){ 
		          $more_unjh_pfcfb=$GLOBALS['user_info']['unjh_pfcfb']+108;
                  $GLOBALS['db']->query("update ".DB_PREFIX."user set `unjh_pfcfb`=".$more_unjh_pfcfb." where id = ".$GLOBALS['user_info']['id']);    //投资一万送108	
                  $user_log_a['log_info']="_415活动_首次投资一个月资金10000以上就送108浦发币";
                  $user_log_a['log_time']=get_gmtime();                
                  $user_log_a['log_admin_id']=1;
                  $user_log_a['user_id']=$GLOBALS['user_info']['id'];
                  $user_log_a['unjh_pfcfb']=108;
                $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$user_log_a,"INSERT");//插入一条投资目录
		          }
	             if($_REQUEST["bid_money"]<10000){
		          $more_unjh_pfcfb=$GLOBALS['user_info']['unjh_pfcfb']+20;
                  $GLOBALS['db']->query("update ".DB_PREFIX."user set `unjh_pfcfb`=".$more_unjh_pfcfb." where id = ".$GLOBALS['user_info']['id']);    //投资一万送20
                  $user_log_a['log_info']="_415活动_首次投资一个月资金10000以下就送20浦发币";
                  $user_log_a['log_time']=get_gmtime();                
                  $user_log_a['log_admin_id']=1;
                  $user_log_a['user_id']=$GLOBALS['user_info']['id'];
                  $user_log_a['unjh_pfcfb']=20;
                $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$user_log_a,"INSERT");//插入一条投资目录				  
		          }
				  */
	            $pid_id=$GLOBALS['db']->getOne("select `pid` from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);
				 $pid_user_id=$GLOBALS['db']->getOne("select `pid` from ".DB_PREFIX."user where id=".$pid_id);
		 if($pid_id!=$pid_user_id){//本公司员工除外
		if($pid_id!=0){
		         $pid_pfcfb=$GLOBALS['db']->getOne("select `pfcfb` from ".DB_PREFIX."user where id=".$pid_id);	
		         $pid_pfcfbs=$pid_pfcfb+$pfcfbss['song_pfcfb'];
		         $GLOBALS['db']->query("update ".DB_PREFIX."user set `pfcfb`=".$pid_pfcfbs." where id = ".$pid_id);   //推荐人得20浦发富币
                  $user_log_b['log_info']="_415活动_你推荐了用户".$GLOBALS['user_info']['user_name']."获得20可提现的浦发币";
                  $user_log_b['log_time']=get_gmtime();                
                  $user_log_b['log_admin_id']=1;
                  $user_log_b['user_id']=$pid_id;
                  $user_log_b['pfcfb']=$pfcfbss['song_pfcfb'];
                $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$user_log_b,"INSERT");//插入一条投资目录							 
                                }
	               }	
            }				   
		}	
}		
	/*浦发币操作结束*/	
	$data['user_id'] = $GLOBALS['user_info']['id'];
	$data['user_name'] = $GLOBALS['user_info']['user_name'];
	$data['deal_id'] = $deal_id;
	$data['money'] = $bid_money;
	$data['create_time'] = TIME_UTC;
	$data['create_date'] = to_date(TIME_UTC);
	$data['rebate_money'] = $bid_money * floatval(trim($root["deal"]['user_bid_rebate'])) * 0.01;

	$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$data,"INSERT");
	$load_id = $GLOBALS['db']->insert_id();
	if($load_id > 0){
	//-------购买成功，新增购物确认函----------author :@313616432
			$data_rg['deal_time']=TIME_UTC+24*3600;//录入时间
			$data_rg['admin_name']='系统';
			$data_rg['admin_id']=0;
			$data_rg['user_name']=$GLOBALS['user_info']['real_name'];
			$data_rg['produce_name']=$deal['name'];
			$data_rg['deal_monney']=$bid_money;
			$data_rg['deal_sn']=$load_id;
			$data_rg['check_yn']=0;
			$data_rg['check_time']=0;
			$data_rg['voucher']=0;
			$deal['deal_time_type']=$deal['repay_time_type']?'个月':'天';
			$data_rg['longtime']=$deal['repay_time'].$deal['deal_time_type'];
			$data_rg['check_name']='';//确认时间
			$data_rg['cus_time']=TIME_UTC;//购物时间
			$host='rdsf6fn32zmbb7j.mysql.rds.aliyuncs.com';
			$user='rengou';
			$password='dontGuess777';
			if($_SERVER['SERVER_NAME']!='localhost'){
			$con=mysql_connect("$host","$user","$password");
			
			mysql_select_db("rengou", $con);
			mysql_query("set names utf8"); 
			$sql="INSERT INTO `deal` (`".implode('`,`', array_keys($data_rg))."`) VALUES ('".implode("','", $data_rg)."')";
			$result=mysql_query($sql);//插入语句
			mysql_close($con);
			}
	//-------购买成功，新增购物确认函------------author :@313616432
		//更改资金记录
		$msg = '[<a href="'.$root['deal']['url'].'" target="_blank">'.$root['deal']['name'].'</a>]的投标,付款单号'.$load_id.'<br/>'.'&nbsp;&nbsp;'.'投资金额：'.$bid_money.'元'.'&nbsp;&nbsp;'.'年利率：'.$deal['rate'].'%'.'&nbsp;&nbsp;'.'借款期限：'.$deal['repay_time'].'个月';
		// require_once APP_ROOT_PATH."system/libs/user.php";
		modify_account(array('money'=>-$bid_money,'lock_money'=>$bid_money),$GLOBALS['user_info']['id'],$msg,2);
		
		dobid2_ok($deal_id,$GLOBALS['user_info']['id']);
		
		//$root["show_err"] = $GLOBALS['lang']['ERROR_TITLE'];
		$root["status"] = 1;//0:出错;1:正确;
		return $root;
		//showSuccess($GLOBALS['lang']['DEAL_BID_SUCCESS'],$ajax,url("index","deal",array("id"=>$id)));
	}
	else{
		$root["show_err"] = $GLOBALS['lang']['ERROR_TITLE'];
		return $root;
	}
}



function get_transfer($union_sql,$condition){

	$sql = 'SELECT dlt.id,dlt.transfer_amount,dlt.near_repay_time,dlt.user_id,d.loantype,d.next_repay_time,d.last_repay_time,d.rate,d.repay_start_time,d.repay_time,dlt.load_money,d.name as deal_name,dlt.load_money,dlt.id as dltid,dlt.status as tras_status,dlt.t_user_id,dlt.transfer_amount,dlt.create_time as tras_create_time,dlt.transfer_time,dlt.load_id,d.user_load_transfer_fee FROM '.DB_PREFIX.'deal_load dl LEFT JOIN '.DB_PREFIX.'deal d ON d.id = dl.deal_id '.$union_sql.' WHERE 1=1 '.$condition;

	$transfer = $GLOBALS['db']->getRow($sql);

	if($transfer){
		//下个还款日
		$transfer['next_repay_time_format'] = to_date($transfer['near_repay_time'],"Y-m-d");
		$transfer['near_repay_time_format'] = to_date(next_replay_month($transfer['near_repay_time']," -1 "),"Y 年 m 月 d 日");
		
		//什么时候开始借
		$transfer['repay_start_time_format']  = to_date($transfer['repay_start_time'],"Y 年 m 月 d 日");

		//还款日
		$transfer['final_repay_time'] = next_replay_month($transfer['repay_start_time'],$transfer['repay_time']);
		$transfer['final_repay_time_format'] = to_date($transfer['final_repay_time'],"Y-m-d");
		//剩余期数
		$transfer['how_much_month'] = how_much_month($transfer['near_repay_time'],$transfer['final_repay_time']) +1;
		
		$transfer_rs = deal_transfer($transfer);
				
		$transfer['month_repay_money'] = $transfer_rs['month_repay_money'];
		$transfer['all_must_repay_money'] = $transfer_rs['all_must_repay_money'];
		$transfer['left_benjin'] = $transfer_rs['left_benjin'];
		
		$transfer['month_repay_money_format'] = format_price($transfer['month_repay_money']);
		
		$transfer['all_must_repay_money_format'] = format_price($transfer['all_must_repay_money']);
		
		$transfer['left_benjin_format'] = format_price($transfer['left_benjin']);
		//剩多少利息
		$transfer['left_lixi'] = $transfer['all_must_repay_money'] - $transfer['left_benjin'];
		$transfer['left_lixi_format'] = format_price($transfer['left_lixi']);

		//转让价格
		$transfer['transfer_amount_format'] =  format_price($transfer['transfer_amount']);
		
		//投标价格
		$transfer['load_money_format'] =  format_price($transfer['load_money']);
		
		//转让管理费
		$transfer['transfer_fee_format'] = format_price($transfer['transfer_amount']*(float)$transfer["user_load_transfer_fee"]);

		//转让收益
		$transfer['transfer_income_format'] =  format_price($transfer['all_must_repay_money']-$transfer['transfer_amount']);

		if($transfer['tras_create_time'] !=""){
			$transfer['tras_create_time_format'] =  to_date($transfer['tras_create_time'],"Y-m-d");
		}

		if(intval($transfer['transfer_time'])>0){
			$transfer['transfer_time_format'] =  to_date($transfer['transfer_time'],"Y-m-d");
		}
		
		if($transfer['tras_create_time'] !=""){
			$transfer['tras_create_time_format'] =  to_date($transfer['tras_create_time'],"Y 年 m 月 d 日");
		}
		
		$transfer['transfer_time_format'] =  to_date($transfer['transfer_time'],"Y 年 m 月 d 日");

		$transfer['user'] = get_user("user_name,email,real_name,idno,level_id",$transfer['user_id']);
		if($transfer['t_user_id'] > 0)
			$transfer['tuser'] = get_user("user_name,email,real_name,idno,level_id",$transfer['t_user_id']);
		
		$transfer['duser'] = get_user("user_name,real_name,idno,level_id",$transfer['duser_id']);
		
		$transfer['url'] = url("index","transfer#detail",array("id"=>$transfer['id']));
	}

	return $transfer;

}

function get_transfer_list($limit,$condition='',$extfield,$union_sql,$orderby = ''){
	//获取转让列表
	$count_sql = 'SELECT count(dlt.id) FROM '.DB_PREFIX.'deal_load_transfer dlt LEFT JOIN '.DB_PREFIX.'deal d ON d.id =dlt.deal_id WHERE  d.is_effect=1 AND d.is_delete = 0 '.$condition;

	$rs_count = $GLOBALS['db']->getOne($count_sql);

	if($rs_count > 0){
		$list_sql = 'SELECT dlt.*,d.loantype,d.name,d.icon,d.cate_id,d.user_id as duser_id,d.rate,d.repay_time,d.repay_time_type '.$extfield.'  FROM '.DB_PREFIX.'deal_load_transfer dlt LEFT JOIN '.DB_PREFIX.'deal d ON d.id =dlt.deal_id '.$union_sql.' WHERE d.is_effect=1 AND d.is_delete = 0 '.$condition;
		$list_sql .= ' ORDER BY '.$orderby;
		$list_sql .=' LIMIT '.$limit;

		$list = $GLOBALS['db']->getAll($list_sql);
		foreach($list as $k=>$v){
			$list[$k]['duser'] = get_user("user_name,level_id,province_id,city_id",$v['duser_id']);
			$list[$k]['user'] = get_user("user_name,level_id,province_id,city_id",$v['user_id']);
			if($v['t_user_id'] > 0)
				$list[$k]['tuser'] = get_user("user_name,level_id,province_id,city_id",$v['t_user_id']);
			else
				$list[$k]['tuser'] = null;
				
				
			if($list[$k]['tuser'] === false){
				$list[$k]['tuser'] = null;
			}
				
			if($list[$k]['duser'] === false){
				$list[$k]['duser'] = null;
			}
				
			if($list[$k]['user'] === false){
				$list[$k]['user'] = null;//new ArrayObject(); {}
			}
				
				
			$list[$k]['url'] = url("index","transfer#detail",array("id"=>$v['id']));
			//$deal['url'] = $durl;
		//x	$durl = "/index.php?ctl=deal&act=mobile&id=".$v['deal_id'];
			$durl = APP_ROOT."/wap/index.php?ctl=transfer_mobile&is_sj=1&id=".$v['deal_id'];
			$list[$k]['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.$durl);
				
			//剩余期数
			$list[$k]['how_much_month'] = how_much_month($v['near_repay_time'],$v['last_repay_time'])+1;
				
			if($v['cate_id'] > 0){
				$list[$k]['cate_info'] = $GLOBALS['db']->getRowCached("select id,name,brief,uname,icon from ".DB_PREFIX."deal_cate where id = ".$v['cate_id']." and is_effect = 1 and is_delete = 0");
			}
			
			$transfer_rs = deal_transfer($list[$k]);
			$list[$k]['month_repay_money'] = $transfer_rs['month_repay_money'];
			$list[$k]['all_must_repay_money'] = $transfer_rs['all_must_repay_money'];
			$list[$k]['left_benjin'] = $transfer_rs['left_benjin'];
				
			
			if($list[$k]['left_benjin'] < 100)
				$list[$k]['left_benjin_format'] = format_price($list[$k]['left_benjin']);
			else
				$list[$k]['left_benjin_format'] = format_price($list[$k]['left_benjin']/10000)."万";
				
			//剩多少利息
			$list[$k]['left_lixi'] = $list[$k]['all_must_repay_money'] - $list[$k]['left_benjin'];
			$list[$k]['left_lixi_format'] = format_price($list[$k]['left_lixi']);
				
			$list[$k]['remain_time'] =$v['near_repay_time'] - TIME_UTC + 24*3600 - 1;
			$list[$k]['remain_time_format'] = remain_time($list[$k]['remain_time']);
				

			$list[$k]['near_repay_time_format'] = to_date($v['near_repay_time'],"Y-m-d");
			if($v['transfer_amount'] < 100)
				$list[$k]['transfer_amount_format'] = format_price($v['transfer_amount']);
			else
				$list[$k]['transfer_amount_format'] = format_price($v['transfer_amount']/10000)."万";
				
			//转让收益
			$list[$k]['transfer_income'] =  $list[$k]['all_must_repay_money']-$list[$k]['transfer_amount'];
			$list[$k]['transfer_income_format'] =  format_price($list[$k]['transfer_income']);
				
			//
			$list[$k]['transfer_time_format'] = to_date($v['transfer_time'],"Y-m-d");
				
		}
		
		$result["list"] =  $list;
	}
	$result["rs_count"] =  $rs_count;
	return $result;
}


//正常还款执行界面
function getUcRepayBorrowMoney($id,$ids){
	$id = intval($id);
	$root = array();
	$root["status"] = 0;//0:出错;1:正确;
    $luo_id=intval($ids);//luo+还款id；
	$luo_ids=$luo_id+1;//luo+还款id；
	if($id == 0){
		$root["show_err"] = "操作失败！";
		return $root;
	}

	$deal = get_deal($id);
	if(!$deal)
	{
		$root["show_err"] = "借款不存在！";
		return $root;
	}
	if($deal['ips_bill_no']!=""){
		$root["status"] = 2;
		$root["jump"] = APP_ROOT.'/index.php?ctl=collocation&act=RepaymentNewTrade&deal_id='.$deal['id'].'&l_key='.$ids."&from=".$GLOBALS['request']['from'];
		$root['jump'] = str_replace("/mapi", "", SITE_DOMAIN.$root['jump']);
		return $root;
	}
	if($deal['user_id']!=$GLOBALS['user_info']['id']){
		$root["show_err"] = "不属于你的借款！";
		return $root;
	}
	if($deal['deal_status']!=4){
		$root["show_err"] = "借款不是还款状态！";
		return $root;
	}
	
	$ids = explode(",",$ids);
	
	//当前用户余额
	$user_total_money = (float)$GLOBALS['user_info']['money'];
	
	if($user_total_money< 0){
		$root["show_err"] = "余额不足";
		return $root;
	}
	
	$last_repay_key = -1;
	require APP_ROOT_PATH.'system/libs/user.php';
	
	foreach($ids as $lkey){
		//还了多少人
		$repay_user_count = 0;
		//多少人未还
		$no_repay_user_count =0;
		//还了多少本息
		$repay_money = 0;
		//还了多少逾期罚息
		$repay_impose_money = 0;
		//还了多少管理费
		$repay_manage_money = 0;
		//还了多少逾期管理费
		$repay_manage_impose_money = 0;
		
		//用户回款 get_deal_user_load_list($deal_info, $user_id = 0 ,$lkey = -1 , $ukey = -1,$true_time=0,$get_type = 0, $r_type = 0, $limit = "")
		$user_loan_list = get_deal_user_load_list($deal, 0 , $lkey , -1 , 0 , 1);

		//===============还款================
		foreach($user_loan_list as $lllk=>$lllv){
			foreach($lllv as $kk=>$vv){
				if($vv['has_repay']==0 ){//借入者已还款，但是没打款到借出用户中心
					$user_load_data = array();
	
					$user_load_data['true_repay_time'] = TIME_UTC;
					$user_load_data['true_repay_date'] = to_date(TIME_UTC);
					$user_load_data['is_site_repay'] = 0;
					$user_load_data['status'] = 0;
						
					$user_load_data['true_repay_money'] = (float)$vv['month_repay_money'];
					$user_load_data['true_self_money'] = (float)$vv['self_money'];
					$user_load_data['true_interest_money'] = (float)$user_load_data['interest_money'];
					$user_load_data['true_manage_money'] = (float)$vv['manage_money'];
					$user_load_data['true_repay_manage_money'] = (float)$vv['repay_manage_money'];
					$user_load_data['impose_money'] = (float)$vv['impose_money'];
					$user_load_data['repay_manage_impose_money'] = (float)$vv['repay_manage_impose_money'];
					
					
					$need_repay_money = 0;
					$need_repay_money += $user_load_data['true_repay_money']  + $user_load_data['impose_money'] + $user_load_data['true_repay_manage_money'] + $user_load_data['repay_manage_impose_money'];
					//=============余额足够才进行还款=================
					if((float)$need_repay_money <= $user_total_money){
						$last_repay_key = $lkey;
						$repay_user_count +=1;
						$repay_money +=$user_load_data['true_repay_money'];
						$repay_impose_money += $user_load_data['impose_money'];
						$repay_manage_money += $user_load_data['true_repay_manage_money'];
						$repay_manage_impose_money += $user_load_data['repay_manage_impose_money'];
						$user_total_money = $user_total_money - $need_repay_money;

						if($vv['status']>0)
							$user_load_data['status'] = $vv['status'] - 1;
							
						$user_load_data['has_repay'] = 1;
						$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$user_load_data,"UPDATE","id=".$vv['id']."  AND has_repay = 0  ","SILENT");
					
						if($GLOBALS['db']->affected_rows() > 0){
			
							$content = "您好，您在".app_conf("SHOP_TITLE")."的投标 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”成功还款".($user_load_data['true_repay_money']+$user_load_data['impose_money'])."元，";
							$unext_loan = $user_loan_list[$vv['u_key']][$kk+1];
								
							if($unext_loan){
								$content .= "本笔投标的下个还款日为".to_date($unext_loan['repay_day'],"Y年m月d日")."，需还本息".number_format($unext_loan['month_repay_money'],2)."元。";
							}
							else{
								$load_repay_rs = $GLOBALS['db']->getOne("SELECT (sum(true_interest_money) + sum(impose_money)) as shouyi,sum(impose_money) as total_impose_money FROM ".DB_PREFIX."deal_load_repay WHERE deal_id=".$deal['id']." AND user_id=".$vv['user_id']);
								$all_shouyi_money= number_format($load_repay_rs['shouyi'],2);
								$all_impose_money = number_format($load_repay_rs['total_impose_money'],2);
								$content .= "本次投标共获得收益:".$all_shouyi_money."元,其中违约金为:".$all_impose_money."元,本次投标已回款完毕！";
							}
							if($user_load_data['impose_money'] !=0 || $user_load_data['true_manage_money'] !=0 || $user_load_data['true_repay_money']!=0){
								$in_user_id  = $vv['user_id'];
								//如果是转让债权那么将回款打入转让者的账户
								if((int)$vv['t_user_id']== 0){
									$loan_user_info['user_name'] = $vv['user_name'];
									$loan_user_info['email'] = $vv['email'];
									$loan_user_info['mobile'] = $vv['mobile'];
								}
								else{
									$in_user_id = $vv['t_user_id'];
									$loan_user_info['user_name'] = $vv['t_user_name'];
									$loan_user_info['email'] = $vv['t_email'];
									$loan_user_info['mobile'] = $vv['t_mobile'];
								}
			
								//更新用户账户资金记录
                         //改动回报本息明细 开始、唯一系统改动的地方
						if($deal['repay_time_type']==0){		
								modify_account(array("money"=>$user_load_data['true_repay_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,回报本息",5);
						  }
						if($deal['repay_time_type']==1){
                           if($deal['loantype']==1){
                                if($luo_ids!=$deal['repay_time']){       						   
								modify_account(array("money"=>$user_load_data['true_repay_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,纯利息",5);
							    }
                                if($luo_ids==$deal['repay_time']){       						   
								modify_account(array("money"=>$user_load_data['true_repay_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,回报本息",5);
							    }
							}
						
                           if($deal['loantype']!=1){					
								modify_account(array("money"=>$user_load_data['true_repay_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,回报本息",5);
							}
                          }								
                           //改动回报本息明细 结束 @luo
								modify_account(array("money"=>-$user_load_data['true_manage_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,投标管理费",20);	
								if($user_load_data['impose_money'] != 0)
									modify_account(array("money"=>$user_load_data['impose_money']),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,逾期罚息",21);
								
								$msg_conf = get_user_msg_conf($in_user_id);
			
			
								//短信通知
								if(app_conf("SMS_ON")==1&&app_conf('SMS_REPAY_TOUSER_ON')==1){
									
									$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_SMS'");
									$tmpl_content = $tmpl['content'];
										
									$notice['user_name'] = $loan_user_info['user_name'];
									$notice['deal_name'] = $deal['sub_name'];
									$notice['deal_url'] = $deal['url'];
									$notice['site_name'] = app_conf("SHOP_TITLE");
									$notice['repay_money'] = number_format(($user_load_data['true_repay_money']+$user_load_data['impose_money']),2);
									if($unext_loan){
										$notice['need_next_repay'] = $unext_loan;
										$notice['next_repay_time'] = to_date($unext_loan['repay_day'],"Y年m月d日");
										$notice['next_repay_money'] = number_format($unext_loan['month_repay_money'],2);
									}
									else{
										$notice['all_repay_money'] = $all_shouyi_money;
										$notice['impose_money'] = $all_impose_money;
									}
										
									$GLOBALS['tmpl']->assign("notice",$notice);
									$sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
										
									$msg_data['dest'] = $loan_user_info['mobile'];
									$msg_data['send_type'] = 0;
									$msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
									$msg_data['send_time'] = 0;
									$msg_data['is_send'] = 0;
									$msg_data['create_time'] = TIME_UTC;
									$msg_data['user_id'] = $in_user_id;
									$msg_data['is_html'] = 0;
									$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
								}
			
								//站内信
								if($msg_conf['sms_bidrepaid']==1)
									send_user_msg("",$content,0,$in_user_id,TIME_UTC,0,true,9);
								//邮件
								if($msg_conf['mail_bidrepaid']==1 && app_conf('MAIL_ON')==1){
									
									$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_EMAIL'");
									$tmpl_content = $tmpl['content'];
										
									$notice['user_name'] = $loan_user_info['user_name'];
									$notice['deal_name'] = $deal['sub_name'];
									$notice['deal_url'] = $deal['url'];
									$notice['site_name'] = app_conf("SHOP_TITLE");
									$notice['site_url'] = SITE_DOMAIN.APP_ROOT;
									$notice['help_url'] = SITE_DOMAIN.url("index","helpcenter");
									$notice['msg_cof_setting_url'] = SITE_DOMAIN.url("index","uc_msg#setting");
									$notice['repay_money'] = number_format(($user_load_data['true_repay_money']+$user_load_data['impose_money']),2);
									if($unext_loan){
										$notice['need_next_repay'] = $unext_loan;
										$notice['next_repay_time'] = to_date($unext_loan['repay_day'],"Y年m月d日");
										$notice['next_repay_money'] = number_format($unext_loan['month_repay_money'],2);
									}
									else{
										$notice['all_repay_money'] = $all_shouyi_money;
										$notice['impose_money'] = $all_impose_money;
									}
										
									$GLOBALS['tmpl']->assign("notice",$notice);
										
									$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
									$msg_data['dest'] = $loan_user_info['email'];
									$msg_data['send_type'] = 1;
									$msg_data['title'] = "“".$deal['name']."”回款通知";
									$msg_data['content'] = addslashes($msg);
									$msg_data['send_time'] = 0;
									$msg_data['is_send'] = 0;
									$msg_data['create_time'] = TIME_UTC;
									$msg_data['user_id'] = $in_user_id;
									$msg_data['is_html'] = $tmpl['is_html'];
									$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
								}
			
							}
						}
					}
					
					//=============余额足够才进行还款=================
					
				}
			}
		}
		//===============还款================
		if($repay_user_count > 0){
			//判断当前期是否还款完毕
			$true_repay_count = $GLOBALS['db']->getOne("SELECT count(*)  FROM ".DB_PREFIX."deal_load_repay WHERE deal_id = ".$deal['id']." and l_key=".$lkey." AND has_repay=1 ");
		
			$ext_str= "";
			if($true_repay_count<>$repay_user_count){
				$ext_str="[部分]";
			}
			//更新用户账户资金记录
			modify_account(array("money"=>-$repay_money),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,偿还本息$ext_str",4);
			if($repay_impose_money!=0)
				modify_account(array("money"=>-$repay_impose_money),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,逾期罚息$ext_str",11);
			modify_account(array("money"=>-$repay_manage_money),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,借款管理费$ext_str",10);
			if($repay_manage_impose_money!=0)
				modify_account(array("money"=>-$repay_manage_impose_money),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,逾期管理费$ext_str",12);
				
				
		}
		
		$r_msg = "会员还款$ext_str";
		if($repay_money > 0){
			$r_msg .=",本息：".format_price($repay_money);
		}
		if($repay_impose_money> 0){
			$r_msg .=",逾期费用：".format_price($repay_impose_money);
		}
		if($repay_manage_money > 0){
			$r_msg .=",管理费：".format_price($repay_manage_money);
		}
		if($repay_manage_impose_money > 0){
			$r_msg .=",逾期管理费：".format_price($repay_manage_impose_money);
		}
		$repay_id = $GLOBALS['db']->getOne("SELECT id  FROM ".DB_PREFIX."deal_repay WHERE deal_id = ".$deal['id']." and l_key=".$lkey);
		repay_log($repay_id,$r_msg,$GLOBALS['user_info']['id'],0);
		
		
		$content = "您好，您在".app_conf("SHOP_TITLE")."的借款 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”的借款第".($lkey+1)."期还款".number_format(($repay_money+$repay_impose_money+$repay_manage_money+$repay_manage_impose_money),2)."元，";
		//如果还款完毕
		$sms_ext_str = "成功";
		if($left_user_count = $GLOBALS['db']->getOne("SELECT count(*)  FROM ".DB_PREFIX."deal_load_repay WHERE deal_id = ".$deal['id']." and l_key=".$lkey." AND has_repay = 0 ") == 0){
			$content .="本期还款完毕。";
			$impose_rs = $GLOBALS['db']->getRow("SELECT sum(true_self_money) as total_self_money,sum(true_interest_money) as total_interest_money,sum(true_repay_money) as total_repay_money,sum(impose_money) as total_impose_money,sum(true_repay_manage_money) as total_repay_manage_money,sum(repay_manage_impose_money) as total_repay_manage_impose_money  FROM ".DB_PREFIX."deal_load_repay WHERE deal_id = ".$deal['id']." and l_key=".$lkey." AND has_repay = 1");
			//判断是否逾期
			$repay_update_data['has_repay'] = 1;
			$repay_update_data['true_repay_time'] = TIME_UTC;
			$repay_update_data['true_repay_date'] = to_date(TIME_UTC);
			$repay_update_data['true_repay_money'] = floatval($impose_rs['total_repay_money']);
			$repay_update_data['true_self_money'] =  floatval($impose_rs['total_self_money']);
			$repay_update_data['true_interest_money'] =  floatval($impose_rs['total_interest_money']);
			$repay_update_data['impose_money'] =floatval($impose_rs['total_impose_money']);
			$repay_update_data['true_manage_money'] =floatval($impose_rs['total_repay_manage_money']);
			$repay_update_data['manage_impose_money']=floatval($impose_rs['total_repay_manage_impose_money']);
			
			if($vv['impose_day'] > 0){
				if($vv['impose_day'] < app_conf('YZ_IMPSE_DAY')){
					modify_account(array("point"=>trim(app_conf('IMPOSE_POINT'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,逾期还款",11);
					$repay_update_data['status'] = 2;
				}
				else{
					modify_account(array("point"=>trim(app_conf('YZ_IMPOSE_POINT'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],第".($kk+1)."期,严重逾期",11);
					$repay_update_data['status'] = 3;
				}
			}
			elseif(TIME_UTC<=(int)$vv['repay_day']){
				$repay_update_data['status'] = 1;
			}
			
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$repay_update_data,"UPDATE","deal_id = ".$deal['id']." and l_key=".$lkey);
			
			if($next_loan =$GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal['id']." and l_key > ".$last_repay_key." ORDER BY  l_key ASC")){
				$content .= "本笔借款的下个还款日为".to_date($next_loan['repay_day'],"Y年m月d日")."，需要本息".number_format($next_loan['repay_money'],2)."元。";
			}
		}
		else{
			$content .="本期部分还款，还有".$left_user_count."个投资人待还。";
			$sms_ext_str = "部分";
			$GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_repay SET has_repay = 2 WHERE deal_id = ".$deal['id']." and l_key=".$lkey);
		}
		
		
		send_user_msg("",$content,0,$GLOBALS['user_info']['id'],TIME_UTC,0,true,8);
		//短信通知
		if(app_conf("SMS_ON")==1&&app_conf('SMS_SEND_REPAY')==1){
			$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_REPAY_SUCCESS_MSG'");
			$sms_content = $tmpl['content'];
			//$sms_content = "尊敬的".app_conf("SHOP_TITLE")."用户".$GLOBALS['user_info']['user_name']."，您的借款“".$deal['name']."”第".($lkey+1)."期".$sms_ext_str."还款".number_format(($repay_money+$repay_impose_money+$repay_manage_money+$repay_manage_impose_money),2)."元，感谢您的关注和支持。";
			$notice['user_name'] = $GLOBALS['user_info']['user_name'];
			$notice['deal_name'] = $deal['sub_name'];
			$notice['site_name'] = app_conf("SHOP_TITLE");
			$notice['index'] = $lkey+1;
			$notice['status'] = $sms_ext_str;
			$notice['all_money'] = number_format(($repay_money+$repay_impose_money+$repay_manage_money+$repay_manage_impose_money),2);
			$notice['repay_money'] = number_format($repay_money,2);
			$notice['impose_money'] = number_format($repay_impose_money,2);
			$notice['manage_money'] = number_format($repay_manage_money,2);
			$notice['manage_impose_money'] = number_format($repay_manage_impose_money,2);
			
			$GLOBALS['tmpl']->assign("notice",$notice);
			$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
			$msg_data['dest'] = $GLOBALS['user_info']['mobile'];
			$msg_data['send_type'] = 0;
			$msg_data['title'] = "还款短信通知";
			$msg_data['content'] = $msg;
			$msg_data['send_time'] = 0;
			$msg_data['is_send'] = 0;
			$msg_data['create_time'] = TIME_UTC;
			$msg_data['user_id'] = $GLOBALS['user_info']['id'];
			$msg_data['is_html'] = 0;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
		}
		
	}
	
	//判断本借款是否还款完毕
	if($GLOBALS['db']->getOne("SELECT count(*)  FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal['id']." and l_key=".$last_repay_key." AND has_repay <> 1)") == 0){
		//未全部还完
		if($next_loan =$GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$deal['id']." and l_key > ".$last_repay_key." ORDER BY  l_key ASC")){
			//$content .= "本笔借款的下个还款日为".to_date($next_loan['repay_day'],"Y年m月d日")."，需要本息".number_format($next_loan['repay_money'],2)."元。";
			//$GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal SET next_repay_time = '".$next_loan['repay_day']."' WHERE id=".$id);
		}
		//全部还完
		else{
			//判断获取的信用是否超过限制
			if($GLOBALS['db']->getOne("SELECT sum(point) FROM ".DB_PREFIX."user_money_log WHERE  `type`=6 AND user_id=".$GLOBALS['user_info']['id']) < (int)trim(app_conf('REPAY_SUCCESS_LIMIT'))){
				//获取上一次还款时间
				$befor_repay_time = $GLOBALS['db']->getOne("SELECT MAX(log_time) FROM ".DB_PREFIX."user_money_log WHERE  `type`=6 AND user_id=".$GLOBALS['user_info']['id']);
				$day = ceil((TIME_UTC-$befor_repay_time)/24/3600);
				//当天数大于等于间隔时间 获得信用
				if($day >= (int)trim(app_conf('REPAY_SUCCESS_DAY'))){
					modify_account(array("point"=>trim(app_conf('REPAY_SUCCESS_POINT'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],还清借款",4);
				}
			}
				
			//用户获得额度
			modify_account(array("quota"=>trim(app_conf('USER_REPAY_QUOTA'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],还清借款获得额度",4);
			
		}
	}
	
	
	$GLOBALS['db']->query("UPDATE ".DB_PREFIX."generation_repay_submit SET `memo`='因还款失效',`status`=2 WHERE deal_id=".$$deal['id']);
		
	sys_user_status($GLOBALS['user_info']['id'],false,true);
	syn_deal_status($id);
	syn_transfer_status(0,$id);
	$root["status"] = 1;//0:出错;1:正确;
	$root["show_err"] = "还款完毕，本次还款人数:$repay_user_count";
	return $root;
}

//提前还款操作界面
function getUcInrepayRefund($id){
	$id = intval($id);
	$root = array();
	$root["status"] = 0;//0:出错;1:正确;

	if($id == 0){
		$root["show_err"] = "操作失败！";
		return $root;
	}

	$deal = get_deal($id);
	if(!$deal)
	{
		$root["show_err"] = "借款不存在！";
		return $root;
	}
	if($deal['user_id']!=$GLOBALS['user_info']['id']){
		$root["show_err"] = "不属于你的借款！";
		return $root;
	}
	if($deal['deal_status']!=4){
		$root["show_err"] = "借款不是还款状态！";
		return $root;
	}

	$root["deal"] = $deal;

	$time = TIME_UTC;
	$impose_money = 0;
	//还了几期了
	$has_repay_count =  $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_repay WHERE has_repay = 1 and deal_id=".$id);
	//计算罚息
	$loan_list = get_deal_load_list($deal);
	
	foreach($loan_list as $k=>$v){
		if($v['has_repay'] == 0)
		{
			$impose_money += floatval($v['impose_money']);
		}
	}
	
	if($impose_money > 0){
		$root["show_err"] = "请将逾期未还的借款还完才可以进行此操作！";
		return $root;
	}
	
	$loaninfo['deal'] = $deal;
	$loaninfo['loanlist'] = $loan_list;

	$inrepay_info = inrepay_repay($loaninfo,$has_repay_count);
	
	$root["true_all_manage_money"] = $inrepay_info["true_manage_money"];
	$root["true_all_manage_money_format"] = format_price($inrepay_info["true_manage_money"]);

	$root["status"] = 1;//0:出错;1:正确;
	$root["impose_money"] = $inrepay_info['impose_money'];
	$root["impose_money_format"] = format_price($root["impose_money"]);

	$root["total_repay_money"] = $inrepay_info['true_repay_money'];
	$root["total_repay_money_format"] = format_price($root["total_repay_money"]);

	$true_total_repay_money = $inrepay_info['true_repay_money'] + $inrepay_info['impose_money'] + $inrepay_info["true_manage_money"];
	$root["true_total_repay_money"] = $true_total_repay_money;
	$root["true_total_repay_money_format"] = format_price($root["true_total_repay_money"]);

	return $root;
}

//提前还款执行程序
function getUCInrepayRepayBorrowMoney($id){
	$id = intval($id);

	$root = array();
	$root["status"] = 0;//0:出错;1:正确;

	if($id == 0){
		$root["show_err"] = "操作失败！";
		return $root;
	}

	$deal = get_deal($id);
	if(!$deal)
	{
		$root["show_err"] = "借款不存在！";
		return $root;
	}
	if($deal['user_id']!=$GLOBALS['user_info']['id']){
		$root["show_err"] = "不属于你的借款！";
		return $root;
	}
	if($deal['deal_status']!=4){
		$root["show_err"] = "借款不是还款状态！";
		return $root;
	}


	
	$time = TIME_UTC;
	$impose_money = 0;
	//是否有部分还款的
	$repay_count_ing =  $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_repay WHERE has_repay=2 and deal_id=".$id);
	if($repay_count_ing){
		$root["show_err"] = "请将部分还款的借款还完才可以进行此操作！";
		return $root;
	}
	
	//计算罚息
	$loan_list = get_deal_load_list($deal);
	
	$k_repay_key = -1;
	$k_repay_time = 0;
	foreach($loan_list as $k=>$v){
		if($v['has_repay'] == 0)
		{
			if($k_repay_key==-1){
				$k_repay_key = $v['l_key'];
				$k_repay_time = $v['repay_day'];
			}
			$impose_money +=$v['impose_all_money'];
		}
	}

	if($impose_money > 0){
		$root["show_err"] = "请将逾期未还的借款还完才可以进行此操作！";
		return $root;
	}

	if($deal['ips_bill_no']!=""){
		$root["status"] = 2;
		$root["jump"] = APP_ROOT.'/index.php?ctl=collocation&act=RepaymentNewTrade&deal_id='.$deal['id'].'&l_key=all&from='.$GLOBALS['request']['from'];
		$root['jump'] = str_replace("/mapi", "", SITE_DOMAIN.$root['jump']);
		return $root;
	}
	
		
	//还了几期了
	$has_repay_count =  $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_repay WHERE has_repay=1 and deal_id=".$id);
	
	$loaninfo['deal'] = $deal;
	$loaninfo['loanlist'] = $loan_list;

	$inrepay_info = inrepay_repay($loaninfo,$has_repay_count);
	
	$true_repay_money = (float)$inrepay_info['true_repay_money'];
	$true_self_money = (float)$inrepay_info['true_self_money'];
	$impose_money  = (float)$inrepay_info['impose_money'];
	$true_manage_money = (float)$inrepay_info['true_manage_money'];
		
	$true_total_repay_money = $true_repay_money + $impose_money + $true_manage_money;

	if($true_total_repay_money > $GLOBALS['user_info']['money']){
		$root["show_err"] = "对不起，您的余额不足！";
		return $root;
	}


	//录入到提前还款列表
	$inrepay_data['deal_id'] = $id;
	$inrepay_data['user_id'] = $GLOBALS['user_info']['id'];
	$inrepay_data['repay_money'] = $true_repay_money;
	$inrepay_data['self_money'] = $true_self_money;
	$inrepay_data['impose_money'] = $impose_money;
	$inrepay_data['manage_money'] = $true_manage_money;
	$inrepay_data['repay_time'] = $k_repay_time;
	$inrepay_data['true_repay_time'] = $time;
	
	$GLOBALS['db']->autoExecute(DB_PREFIX."deal_inrepay_repay",$inrepay_data,"INSERT");
	$inrepay_id = $GLOBALS['db']->insert_id();
	if($inrepay_id==0){
		$root["show_err"] = "对不起，数据处理失败，请联系客服！";
		return $root;
	}

	//录入还款列表
	$wait_repay_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_repay WHERE deal_id=".$id." and has_repay=0 ORDER BY l_key ASC");
	$temp_ids = array();
	foreach($wait_repay_list as $k=>$v){
		$repay_data =array();
		$repay_data['has_repay'] = 1;
		$repay_data['true_repay_time'] = $time;
		$repay_data['true_repay_date'] = to_date($time);
		$repay_data['status'] = 0;
		if($k_repay_key==$v['l_key']){
			$repay_data['true_repay_money'] = $true_repay_money;
			$repay_data['impose_money'] = $impose_money;
			$repay_data['true_manage_money'] = $true_manage_money;
			$repay_data['true_self_money'] = $true_self_money;
			$repay_data['true_interest_money'] = $true_repay_money - $true_self_money;
		}
		
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$repay_data,"UPDATE","id=".$v['id']);
		
		//假如出错 删除掉原来的以插入的数据
		if($GLOBALS['db']->affected_rows() == 0)
		{	
			if(count($temp_ids) > 0){
				$GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_repay SET has_repay=0 WHERE id in ".implode(",",$temp_ids)."");
				make_repay_plan($deal);
			}
			$root["show_err"] = "对不起，处理数据失败请联系客服！";
			return $root;
		}
		else{
			$temp_ids[] = $v['id'];
		}
		
	}
	
	if(count($temp_ids)==0){
		$root["show_err"] = "对不起，处理数据失败请联系客服！";
		return $root;
	}
	
	//更新用户账户资金记录
	require APP_ROOT_PATH.'system/libs/user.php';
	
	modify_account(array("money"=>-round($impose_money,2)),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],提前还款违约金",6);
	modify_account(array("money"=>-round($true_manage_money,2)),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],提前还款管理费",10);
	modify_account(array("money"=>-round($true_repay_money,2)),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],提前还款本息",6);
	//用户获得额度
	modify_account(array("quota"=>trim(app_conf('USER_REPAY_QUOTA'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],还清借款获得额度",6);
	
	

	//判断获取的信用是否超过限制
	if($GLOBALS['db']->getOne("SELECT sum(point) FROM ".DB_PREFIX."user_point_log WHERE `type`=6 AND user_id=".$GLOBALS['user_info']['id']) < (int)trim(app_conf('REPAY_SUCCESS_LIMIT'))){
		//获取上一次还款时间
		$befor_repay_time = $GLOBALS['db']->getOne("SELECT MAX(create_time) FROM ".DB_PREFIX."user_point_log WHERE `type`=6 AND user_id=".$GLOBALS['user_info']['id']);
		$day = ceil(($time-$befor_repay_time)/24/3600);
		//当天数大于等于间隔时间 获得信用
		if($day >= (int)trim(app_conf('REPAY_SUCCESS_DAY'))){
			modify_account(array("point"=>trim(app_conf('REPAY_SUCCESS_POINT'))),$GLOBALS['user_info']['id'],"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],还清借款",6);
		}
	}



	//用户回款
	/**
	 * 获取某一期的用户还款列表
	 * array $deal_info 借款信息 
	 * int $user_id 用户ID 为0代表全部
	 * int $lkey  第几期 -1 全部
	 * int $ukey 第几个投标人 -1 全部
	 * int $true_time  真实还款时间
	 * int $get_type  0 全部 1代表未还的  2 代表已还的
	 * int $r_type = 0; 返回类型; 1:只返回一个数组; $result['item']
	 * string $limit; 查询限制数量; 0,20  $result['count']
	 */
	$user_loan_list = get_deal_user_load_list($deal,0,-1,-1,$time,1,0,'');
		
	foreach($user_loan_list as $lllk=>$lllv){//循环用户
		//本金
		$user_self_money = 0;
		//本息
		$user_repay_money = 0;
		//违约金
		$user_impose_money = 0;
		//管理费
		$user_manage_money = 0;
	
		foreach($lllv as $kk=>$vv){//循环期数
			
			$in_user_id = $vv['user_id'];
			//判断是否转让了债权
			if((int)$vv['t_user_id'] == 0){
				$loan_user_info['user_name'] = $vv['user_name'];
				$loan_user_info['email'] = $vv['email'];
				$loan_user_info['mobile'] = $vv['mobile'];
			}
			else{
				$in_user_id = $vv['t_user_id'];
				$loan_user_info['user_name'] = $vv['t_user_name'];
				$loan_user_info['email'] = $vv['t_email'];
				$loan_user_info['mobile'] = $vv['t_mobile'];
			}
			
			$user_load_data = array();	
			$user_load_data['true_repay_time'] = $time;
			$user_load_data['true_repay_date'] = to_date($time);
			$user_load_data['is_site_repay'] = 0;
			$user_load_data['status'] = 0;
			
			if($k_repay_key==$vv['l_key']){
				$loadinfo['deal']['rate'] = $deal['rate'];
				$loadinfo['deal']['loantype'] = $deal['loantype'];
				$loadinfo['deal']['repay_time'] = $deal['repay_time'];
				$loadinfo['deal']['borrow_amount'] = $vv['money'];
				$loadinfo['deal']['repay_start_time'] = $deal['repay_start_time'];
				$loadinfo['deal']['month_manage_money'] = $vv['manage_money'];
				$loadinfo['deal']['month_repay_money'] = $vv['month_repay_money'];
				$loadinfo['deal']['compensate_fee'] = $deal['compensate_fee'];
				if($deal['repay_time_type'] == 1)
					$loadinfo['deal']['all_manage_money'] = $vv['manage_money'];
				else
					$loadinfo['deal']['all_manage_money'] = $vv['manage_money'] * $deal['repay_time'];
				
				$loadinfo['deal']['repay_time_type'] = $deal['repay_time_type'];
				
				$user_load_rs = inrepay_repay($loadinfo,$has_repay_count);
				
				
				$user_load_data['true_repay_money'] = $user_load_rs['true_repay_money'];
				$user_load_data['true_self_money'] = $user_load_rs['true_self_money'];
				$user_load_data['impose_money'] = $user_load_rs['impose_money'];
				$user_load_data['true_interest_money'] = $user_load_rs['true_repay_money'] - $user_load_rs['true_self_money'];
				$user_load_data['true_manage_money'] = $user_load_rs['true_manage_money'];
				$user_load_data['true_repay_manage_money'] = $true_manage_money / count($user_loan_list);
				
				$user_self_money = $user_load_data['true_self_money'];
				$user_repay_money = $user_load_data['true_repay_money'];
				$user_impose_money = $user_load_data['impose_money'];
				$user_manage_money = $user_load_data['true_manage_money'];
				
			}

			$user_load_data['has_repay'] = 1;
			$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$user_load_data,"UPDATE","id=".$vv['id']);
			
		}
		
		if($user_repay_money >0 || $user_impose_money >0 || $user_manage_money > 0){
			$all_repay_money = number_format($GLOBALS['db']->getOne("SELECT (sum(repay_money)-sum(self_money) + sum(impose_money)) as shouyi FROM ".DB_PREFIX."deal_load_repay WHERE  has_repay = 1 and deal_id=".$v['deal_id']." AND user_id=".$v['user_id']),2);
			$all_impose_money = number_format($GLOBALS['db']->getOne("SELECT sum(impose_money) FROM ".DB_PREFIX."deal_load_repay WHERE has_repay = 1 and deal_id=".$v['deal_id']." AND user_id=".$v['user_id']),2);

			$content = "您好，您在".app_conf("SHOP_TITLE")."的投标 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”提前还款,";
			$content .= "本次投标共获得收益:".$all_repay_money."元,其中违约金为:".$all_impose_money."元,本次投标已回款完毕！";

			//更新用户账户资金记录
			modify_account(array("money"=>$user_repay_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],回报本息",5);
			
			modify_account(array("money"=>$user_impose_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],提前回收违约金",7);

			modify_account(array("money"=>-$user_manage_money),$in_user_id,"[<a href='".$deal['url']."' target='_blank'>".$deal['name']."</a>],投标管理费",20);


			$msg_conf = get_user_msg_conf($in_user_id);
			//短信通知
			if(app_conf("SMS_ON")==1&&app_conf('SMS_REPAY_TOUSER_ON')==1){
					
				$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_SMS'");
				$tmpl_content = $tmpl['content'];
					
				$notice['user_name'] = $loan_user_info['user_name'];
				$notice['deal_name'] = $deal['sub_name'];
				$notice['deal_url'] = $deal['url'];
				$notice['site_name'] = app_conf("SHOP_TITLE");
				$notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];
					
				$notice['all_repay_money'] = $all_repay_money;
				$notice['impose_money'] = $all_impose_money;
					
				$GLOBALS['tmpl']->assign("notice",$notice);
				$sms_content = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					
				$msg_data['dest'] = $loan_user_info['mobile'];
				$msg_data['send_type'] = 0;
				$msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = $time;
				$msg_data['user_id'] = $in_user_id;
				$msg_data['is_html'] = 0;
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			}
			//站内信
			if($msg_conf['sms_bidrepaid']==1)
				send_user_msg("",$content,0,$in_user_id,$time,0,true,9);
			//邮件
			if($msg_conf['mail_bidrepaid']==1 && app_conf('MAIL_ON')==1){
				
				$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_DEAL_LOAD_REPAY_EMAIL'");
				$tmpl_content = $tmpl['content'];
					
				$notice['user_name'] = $loan_user_info['user_name'];
				$notice['deal_name'] = $deal['sub_name'];
				$notice['deal_url'] = $deal['url'];
				$notice['site_name'] = app_conf("SHOP_TITLE");
				$notice['site_url'] = SITE_DOMAIN.APP_ROOT;
				$notice['help_url'] = SITE_DOMAIN.url("index","helpcenter");
				$notice['msg_cof_setting_url'] = SITE_DOMAIN.url("index","uc_msg#setting");
				$notice['repay_money'] = $vv['month_repay_money']+$vv['impose_money'];
					
				$notice['all_repay_money'] = $all_repay_money;
				$notice['impose_money'] = $all_impose_money;
					
				$GLOBALS['tmpl']->assign("notice",$notice);
					
				$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
				$msg_data['dest'] = $loan_user_info['email'];
				$msg_data['send_type'] = 1;
				$msg_data['title'] = "“".$deal['name']."”回款通知";
				$msg_data['content'] = addslashes($msg);
				$msg_data['send_time'] = 0;
				$msg_data['is_send'] = 0;
				$msg_data['create_time'] = $time;
				$msg_data['user_id'] = $in_user_id;
				$msg_data['is_html'] = $tmpl['is_html'];
				$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
			}
		}
		
	}
	
	
	$content = "您好，您在".app_conf("SHOP_TITLE")."的借款 “<a href=\"".$deal['url']."\">".$deal['name']."</a>”成功提前还款".number_format($true_total_repay_money,2)."元，";
	$content .= "其中违约金为:".number_format($impose_money,2)."元,本笔借款已还款完毕！";

	send_user_msg("",$content,0,$GLOBALS['user_info']['id'],$time,0,true,8);
	//短信通知
	if(app_conf("SMS_ON")==1&&app_conf('SMS_SEND_REPAY')==1){
		//$sms_content = "尊敬的".app_conf("SHOP_TITLE")."用户".$GLOBALS['user_info']['user_name']."，您成功提前还款".number_format($true_total_repay_money,2)."元，其中违约金为:".number_format($impose_money,2)."元,感谢您的关注和支持。【".app_conf("SHOP_TITLE")."】";
		$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_REPAY_SUCCESS_MSG'");
		$tmpl_content = $tmpl['content'];
		$notice['user_name'] = $GLOBALS['user_info']['user_name'];
		$notice['deal_name'] = $deal['sub_name'];
		$notice['site_name'] = app_conf("SHOP_TITLE");
		$notice['index'] = $has_repay_count+1;
		$notice['status'] = "成功提前";
		$notice['all_money'] = number_format($true_total_repay_money,2);
		$notice['repay_money'] = number_format($true_repay_money,2);
		$notice['impose_money'] = number_format($impose_money,2);
		$notice['manage_money'] = number_format($true_manage_money,2);
		$notice['manage_impose_money'] = 0;
		
		$GLOBALS['tmpl']->assign("notice",$notice);
		$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
		
		$msg_data['dest'] = $GLOBALS['user_info']['mobile'];
		$msg_data['send_type'] = 0;
		$msg_data['title'] = "提前还款短信通知";
		$msg_data['content'] = $msg;
		$msg_data['send_time'] = 0;
		$msg_data['is_send'] = 0;
		$msg_data['create_time'] = $time;
		$msg_data['user_id'] = $GLOBALS['user_info']['id'];
		$msg_data['is_html'] = 0;
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	}
	
	
	syn_deal_status($id);
	sys_user_status($GLOBALS['user_info']['id'],false,true);
	syn_transfer_status(0,$id);
	$root["status"] = 1;//0:出错;1:正确;
	$root["show_err"] = "操作成功!";
	return $root;
}



//债权转让常规检测;
function check_trans($id,$paypassword){
	$paypassword = strim($paypassword);
	$id = intval($id);

	$root = array();
	$root["status"] = 0;//0:出错;1:正确;

	if(!$GLOBALS['user_info']){
		$root["show_err"] = $GLOBALS['lang']['PLEASE_LOGIN_FIRST'];
		return $root;
	}


	if($paypassword==""){
		$root["show_err"] = $GLOBALS['lang']['PAYPASSWORD_EMPTY'];
		return $root;
	}

	if(md5($paypassword)!=$GLOBALS['user_info']['paypassword']){
		$root["show_err"] = $GLOBALS['lang']['PAYPASSWORD_ERROR'];//.$GLOBALS['user_info']['paypassword'].';'.md5($paypassword).';'.$paypassword;
		return $root;
	}



	$deal_id = $GLOBALS['db']->getOne("SELECT deal_id FROM ".DB_PREFIX."deal_load_transfer WHERE id=".$id);
	if($deal_id==0){
		$root["show_err"] = "不存在的债权";
		return $root;
	}
	else{
		syn_deal_status($deal_id);
	}

	$condition = ' AND dlt.id='.$id.' AND d.deal_status = 4 and d.is_effect=1 and d.is_delete=0 and d.repay_time_type =1 and  d.publish_wait=0 ';
	$union_sql = " LEFT JOIN ".DB_PREFIX."deal_load_transfer dlt ON dlt.deal_id = dl.deal_id ";

	$sql = 'SELECT dlt.load_id,dlt.id,dlt.t_user_id,dlt.transfer_amount,dlt.user_id,dlt.near_repay_time,d.next_repay_time,d.last_repay_time,d.rate,d.repay_start_time,d.repay_time,dlt.load_money,dlt.id as dltid,dlt.status as tras_status,dlt.t_user_id,dlt.transfer_amount,dlt.create_time as tras_create_time,d.user_id as duser_id,d.ips_bill_no FROM '.DB_PREFIX.'deal_load dl LEFT JOIN '.DB_PREFIX.'deal d ON d.id = dl.deal_id '.$union_sql.' WHERE 1=1 '.$condition;

	$transfer = $GLOBALS['db']->getRow($sql);

	if($transfer){
		if($transfer['user_id']==$GLOBALS['user_info']['id']){
			$root["show_err"] = "不能购买自己转让的债权";
			return $root;
		}
			
		if($transfer['duser_id']==$GLOBALS['user_info']['id']){
			$root["show_err"] = "不能购买自己的的借贷债权";
			return $root;
		}
			
		if($transfer['tras_status']==0){
			$root["show_err"] = "债权已撤销";
			return $root;
		}
			
		if(intval($transfer['t_user_id'])>0){
			$root["show_err"] = "债权已转让";
			return $root;
		}

		//下个还款日
		if(intval($transfer['next_repay_time']) == 0){
			$transfer['next_repay_time'] = next_replay_month($transfer['repay_start_time']);
		}
			
		if($transfer['next_repay_time'] - TIME_UTC  + 24*3600 - 1 <= 0){
			$root["show_err"] = "债权转让已过期";
			return $root;
		}
			
		$root["transfer"] = $transfer;
		$root["deal_id"] = $deal_id;
	}
	else{
		$root["show_err"] = "债权转让不存在";
		return $root;
	}
	if($transfer['ips_bill_no']!="")
		$root["status"] = 2;
	else
		$root["status"] = 1;//0:出错;1:正确;
	return $root;
}

//债权转让;
function dotrans($id,$paypassword){
	$paypassword = strim($paypassword);
	$id = intval($id);

	$root = array();
	$root["status"] = 0;//0:出错;1:正确;
	
	$result = check_trans($id,$paypassword);
	
	if ($result['status'] == 0){
		$root["show_err"] = $result["show_err"];
		return $root;
	}
	
	if ($result['status'] == 2){
		$root["status"] = 2;
		$root["jump"] = APP_ROOT."/index.php?ctl=collocation&act=RegisterCretansfer&id=$id&t_user_id=".$GLOBALS['user_info']['id']."&paypassword=".$paypassword."&from=".$GLOBALS['request']['from'];
		$root['jump'] = str_replace("/mapi", "", SITE_DOMAIN.$root['jump']);
		return $root;
	}
	
	$transfer = $result["transfer"];
	$deal_id = $result["deal_id"];
	

	if($transfer){		
		if(floatval($transfer['transfer_amount']) > floatval($GLOBALS['user_info']['money'])){
			$root["show_err"] = "账户余额不足";
			return $root;
		}
			
		$GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_load_transfer set t_user_id = ".$GLOBALS['user_info']['id'].",transfer_time='".TIME_UTC."' WHERE id=".$id." and t_user_id =0 AND status=1 AND near_repay_time- ".TIME_UTC." + 24*3600 - 1 > 0 ");
		if($GLOBALS['db']->affected_rows()){
			
			//更新相应的回款计划
			$GLOBALS['db']->query("UPDATE ".DB_PREFIX."deal_load_repay SET t_user_id='".$GLOBALS['user_info']['id']."' WHERE  user_id=".$transfer['user_id']." and load_id=".$transfer['load_id']." and repay_time >= ".$transfer['near_repay_time'] );
			
			require APP_ROOT_PATH."/system/libs/user.php";
			//承接人扣除转让费
			modify_account(array("money"=>-floatval($transfer['transfer_amount'])),$GLOBALS['user_info']['id'],"债:Z-".$transfer['load_id'].",承接金",16);
			//转让人接受转让费
			modify_account(array("money"=>floatval($transfer['transfer_amount'])),$transfer['user_id'],"债:Z-".$transfer['load_id'].",转让金",15);
			
			$user_load_transfer_fee = $GLOBALS['db']->getOne("SELECT user_load_transfer_fee FROM ".DB_PREFIX."deal WHERE id=".$deal_id);
			//扣除转让人的手续费
			if(trim($user_load_transfer_fee)!=""){
				$transfer_fee = $transfer['transfer_amount']*floatval(trim($user_load_transfer_fee));
				if($transfer_fee!=0){
					$transfer_fee = $transfer_fee / 100;
				}
				modify_account(array("money"=>-floatval($transfer_fee)),$transfer['user_id'],"债:Z-".$transfer['load_id'].",转让管理费",17);
			}
				
			
			dotrans_ok($id);
			

			$root["status"] = 1;//0:出错;1:正确;
			$root["show_err"] = "转让成功";
			return $root;
		}
		else{
			$root["show_err"] = "转让失败";
			return $root;
		}
	}
	else{
		$root["show_err"] = "债权转让不存在";
		return $root;
	}

}

function dotrans_ok($transfer_id){
	
	$transfer = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load_transfer where id = ".$transfer_id);
	//发送消息
	$msg_conf = get_user_msg_conf($transfer['user_id']);
	//if($msg_conf['sms_transfer']==1 || $msg_conf['mail_transfer']==1){
		$transfer['tuser'] = get_user("user_name,mobile,email",$transfer['t_user_id']);
		$transfer['user'] = get_user("user_name,mobile,email",$transfer['user_id']);
	//}
	
	if($msg_conf['sms_transfer']==1){
			
		$content = "您好，您在".app_conf("SHOP_TITLE")."的债权 “<a href=\"".url("index","transfer#detail",array("id"=>$transfer['id']))."\">Z-".$transfer['load_id']."</a>” 成功转让给：<a href=\"".$transfer['tuser']['url']."\">".$transfer['tuser']['user_name']."</a>";
		send_user_msg("",$content,0,$transfer['user_id'],TIME_UTC,0,true,18);
	}
	//邮件
	if($msg_conf['mail_transfer']==1 && app_conf('MAIL_ON')==1){
		$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_MAIL_TRANSFER_SUCCESS'");
		$tmpl_content = $tmpl['content'];
			
		$notice['user_name'] = $transfer['user']['user_name'];
		$notice['transfer_time'] = to_date($transfer['create_time'],"Y年m月d日");
		$notice['transfer_id'] = "Z-".$transfer['load_id'];
		$notice['deal_url'] = SITE_DOMAIN.url("index","transfer#detail",array("id"=>$transfer['id']));
		$notice['site_name'] = app_conf("SHOP_TITLE");
		$notice['site_url'] = SITE_DOMAIN.APP_ROOT;
		$notice['help_url'] = SITE_DOMAIN.url("index","helpcenter");
		$notice['msg_cof_setting_url'] = SITE_DOMAIN.url("index","uc_msg#setting");
			
	
			
		$GLOBALS['tmpl']->assign("notice",$notice);
			
		$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
		$msg_data['dest'] = $transfer['user']['email'];
		$msg_data['send_type'] = 1;
		$msg_data['title'] = "“债权：Z-".$transfer['load_id']."”转让通知";
		$msg_data['content'] = addslashes($msg);
		$msg_data['send_time'] = 0;
		$msg_data['is_send'] = 0;
		$msg_data['create_time'] = TIME_UTC;
		$msg_data['user_id'] = $transfer['user_id'];
		$msg_data['is_html'] = $tmpl['is_html'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	}
	
	if(app_conf('SMS_ON')==1){
		$tmpl = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."msg_template where name = 'TPL_SMS_TRANSFER_SUCCESS'");
		$tmpl_content = $tmpl['content'];
			
		$notice['user_name'] = $transfer['user']['user_name'];
		$notice['transfer_time'] = to_date($transfer['create_time'],"Y年m月d日");
		$notice['transfer_id'] = "Z-".$transfer['load_id'];
		$notice['site_name'] = app_conf("SHOP_TITLE");
			
			
		$GLOBALS['tmpl']->assign("notice",$notice);
			
		$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
		$msg_data['dest'] = $transfer['user']['mobile'];
		$msg_data['send_type'] = 0;
		$msg_data['title'] = "“债权：Z-".$transfer['load_id']."”转让通知";
		$msg_data['content'] = addslashes($msg);
		$msg_data['send_time'] = 0;
		$msg_data['is_send'] = 0;
		$msg_data['create_time'] = TIME_UTC;
		$msg_data['user_id'] = $transfer['user_id'];
		$msg_data['is_html'] = $tmpl['is_html'];
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入
	}
	
	//发送债权协议
	send_transfer_contract_email($transfer_id);	
}
	
?>