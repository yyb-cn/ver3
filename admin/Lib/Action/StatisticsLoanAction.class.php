<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class StatisticsLoanAction extends CommonAction{

    public function index() {
    	if(trim($_REQUEST['search'])=="do"){
	    	$start_time = trim($_REQUEST['start_time']);
	    	if($start_time==""){
	    		$this->error("请选择开始时间");	
	    		die();
	    	}
	    	$this->assign("start_time",$start_time);
	    	$end_time = trim($_REQUEST['end_time']);
	    	
	    	if($end_time==""){
	    		$this->error("请选择结束时间");
	    		die();
	    	}
	    	
	    	$this->assign("end_time",$end_time);
	    	
	    	$start_time = to_timespan($start_time,"Y-m-d");
	    	$end_time = to_timespan($end_time,"Y-m-d");
	    	
	    	if($end_time < $start_time){
	    		$this->error("结束时间必须大于开始时间");
	    		die();
	    	}
	    	
	    	$now_time = to_timespan(to_date(TIME_UTC,"Y-m-d"),"Y-m-d");
	    	
	    	if($end_time > $now_time){
	    		$end_time = $now_time;
	    	}
	    	
	    	//开始时间跟结束时间差多少天
	    	$day = ($end_time - $start_time) /24 /3600;
	    	
	    	
	    	$list = array();
	    	$day_time = $start_time;
	    	
	    	//标分类
	    	$deal_cate = load_auto_cache("cache_deal_cate");
	    	
	    	/*foreach($deal_cate as $kk=>$vv){
	    		if(strpos($vv['name'],"智能")!==false){
	    			unset($deal_cate[$kk]);
	    		}
	    	}*/
	    	$this->assign("deal_cate",$deal_cate);
	    	
	    	//获取改时间段内所有的 还款中和 已还清的贷款
	    	//$deals = $GLOBALS['db']->getAll("SELECT id FROM ".DB_PREFIX."deal where deal_status in(4,5) and is_effect=1 and is_delete=0 and publish_wait=0 AND success_time >= $start_time and  ((loantype=1 and (success_time + repay_time*31*24*3600) >=$end_time) or (loantype=0 and (success_time + (repay_time+1)*24*3600)>=$end_time))");
	    	$deals = $GLOBALS['db']->getAll("SELECT id FROM ".DB_PREFIX."deal where deal_status in(4,5) and is_effect=1 and is_delete=0 and publish_wait=0 AND success_time >= $start_time");
	    	
	    	$temp_deals = array();
	    	
	    	require_once APP_ROOT_PATH."app/Lib/common.php";
    		require_once APP_ROOT_PATH."app/Lib/deal.php";
    		require_once APP_ROOT_PATH."system/libs/user.php";
	    	
	    	for($i = 0 ; $i<=$day; $i++){
	    		$list[$i]['day'] = $day_time;
	    		//线上充值金额
	    		$list[$i]['online_pay'] = floatval($GLOBALS['db']->getOne("SELECT sum(deal_total_price) FROM ".DB_PREFIX."deal_order where pay_status=2 and type = 1 and is_delete = 0 and create_time between $day_time and $day_time+24*3600 and payment_id not in (SELECT id from ".DB_PREFIX."payment where class_name='Otherpay') "));
	    		//线下充值金额
	    		$list[$i]['below_pay'] = floatval($GLOBALS['db']->getOne("SELECT sum(deal_total_price) FROM ".DB_PREFIX."deal_order where pay_status=2 and type = 1 and is_delete = 0 and create_time between $day_time and $day_time+24*3600 and payment_id in (SELECT id from ".DB_PREFIX."payment where class_name='Otherpay') "));
	    		
	    		foreach($deal_cate as $kk=>$vv){
	    			//if(strpos($vv['name'],"智能")===false)
	    				$list[$i][$vv['id']]['borrow_amount'] = floatval($GLOBALS['db']->getOne("SELECT sum(borrow_amount) FROM ".DB_PREFIX."deal where deal_status=4 and is_delete = 0 and publish_wait = 0 and success_time between $day_time and $day_time+24*3600 and cate_id=".$vv['id']));
	    		}
	    		
	    		//投资总额[投标者]
	    		$list[$i]['load_amount'] = floatval($GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load where is_repay=0 and create_time between $day_time and $day_time+24*3600"));
	    		
	    		//已获利息总额[投标者]
	    		$list[$i]['load_lixi_amount'] = floatval($GLOBALS['db']->getOne("SELECT (sum(repay_money) - sum(self_money)) FROM ".DB_PREFIX."deal_load_repay where true_repay_time between $day_time and $day_time+24*3600"));
	    		
	    		//应付本金
	    		$list[$i]['benjin_amount'] = 0;
	    		//应付利息 
	    		$list[$i]['pay_lxi_amount'] = 0;
	    		//应付罚息
	    		$list[$i]['impose_amount'] = 0;
	    		
	    		//已付本金
	    		$list[$i]['has_repay_benjin_amount']=0;
	    		//已付利息
	    		$list[$i]['has_repay_lxi_amount']=0;
	    		//已付罚息
	    		$list[$i]['has_repay_impose_amount'] = 0;
	    		
	    		foreach($deals as $kk=>$vv){
	    			
	    			if(!isset($temp_deals[$vv['id']])){
	    				$temp_deals[$vv['id']]['deal'] = get_deal($vv['id']);
	    				$temp_deals[$vv['id']]['loan'] = get_deal_load_list($temp_deals[$vv['id']]['deal']);
	    			}
	    			
	    			foreach($temp_deals[$vv['id']]['loan'] as $kkk=>$vvv){
	    				
	    				//如果刚好等于传入的时间就开始计算
	    				if($vvv['true_repay_time'] >= $day_time && $vvv['true_repay_time'] <= ($day_time+24*3600-1) || $vvv['repay_day'] == $day_time){
	    					
	    						if($temp_deals[$vv['id']]['deal']['month_repay_money'] > 0 || $temp_deals[$vv['id']]['deal']['last_month_repay_money'] > 0){
	    							if($temp_deals[$vv['id']]['deal']['loantype'] ==0){
	    								$benj = get_benjin($kkk,count($temp_deals[$vv['id']]['loan']),$temp_deals[$vv['id']]['deal']['borrow_amount'],$temp_deals[$vv['id']]['deal']['month_repay_money'],$temp_deals[$vv['id']]['deal']['rate']);
			    						
			    						$list[$i]['benjin_amount'] += $benj;
	    								$list[$i]['pay_lxi_amount'] += $temp_deals[$vv['id']]['deal']['month_repay_money'] - $benj;
	    							}
			    					elseif($temp_deals[$vv['id']]['deal']['loantype'] ==1)
			    					{
			    						if($kkk+1==count($temp_deals[$vv['id']]['loan'])){
			    							$list[$i]['benjin_amount'] += $temp_deals[$vv['id']]['deal']['borrow_amount'];
			    							$list[$i]['pay_lxi_amount'] += $temp_deals[$vv['id']]['deal']['last_month_repay_money'] - $list[$i]['benjin_amount'];
			    						}
			    						else{
			    							$list[$i]['pay_lxi_amount'] += $temp_deals[$vv['id']]['deal']['month_repay_money'];
			    						}
			    					}
			    					elseif($temp_deals[$vv['id']]['deal']['loantype'] ==2){
			    						if($kkk+1==count($temp_deals[$vv['id']]['loan'])){
			    							$list[$i]['benjin_amount'] += $temp_deals[$vv['id']]['deal']['borrow_amount'];
			    							$list[$i]['pay_lxi_amount'] += $temp_deals[$vv['id']]['deal']['last_month_repay_money'] -  $temp_deals[$vv['id']]['deal']['borrow_amount'];
			    						}
			    					}
			    					
		    						$list[$i]['impose_amount'] += $vvv['impose_money'];
		    						
			    					if($vvv['has_repay']==1){
			    						if($temp_deals[$vv['id']]['deal']['loantype'] ==0){
			    							$benj = get_benjin($kkk,count($temp_deals[$vv['id']]['loan']),$temp_deals[$vv['id']]['deal']['borrow_amount'],$temp_deals[$vv['id']]['deal']['month_repay_money'],$temp_deals[$vv['id']]['deal']['rate']);
			    							$list[$i]['has_repay_benjin_amount'] +=$benj;
			    							
			    							$list[$i]['has_repay_lxi_amount'] += $temp_deals[$vv['id']]['deal']['month_repay_money'] - $benj;
			    						}
			    						elseif($temp_deals[$vv['id']]['deal']['loantype'] ==1)
			    						{
			    							if($kkk+1==count($temp_deals[$vv['id']]['loan'])){
			    									$list[$i]['has_repay_benjin_amount'] +=$temp_deals[$vv['id']]['deal']['borrow_amount'];
			    									$list[$i]['has_repay_lxi_amount'] += $temp_deals[$vv['id']]['deal']['last_month_repay_money'] - $list[$i]['benjin_amount'];
			    							}
			    							else{
			    								$list[$i]['has_repay_lxi_amount'] += $temp_deals[$vv['id']]['deal']['month_repay_money'];
			    							}
			    						}
			    						elseif($temp_deals[$vv['id']]['deal']['loantype'] ==2){
			    							if($kkk+1==count($temp_deals[$vv['id']]['loan'])){
				    							$list[$i]['has_repay_benjin_amount'] += $temp_deals[$vv['id']]['deal']['borrow_amount'];
				    							$list[$i]['has_repay_lxi_amount'] += $temp_deals[$vv['id']]['deal']['last_month_repay_money'] -  $temp_deals[$vv['id']]['deal']['borrow_amount'];
				    						}
			    						}
			    						$list[$i]['has_repay_impose_amount'] +=$vvv['impose_money']; 
			    						
			    					}
			    					
	    						}
	    				}
	    			}
	    		}
	    		
	    		//待还本金
	    		$list[$i]['wait_repay_benjin_amount'] = $list[$i]['benjin_amount']-$list[$i]['has_repay_benjin_amount'];
	    		//待还利息
	    		$list[$i]['wait_repay_lxi_amount'] = $list[$i]['pay_lxi_amount']-$list[$i]['has_repay_lxi_amount'];
	    		//待还罚息
	    		$list[$i]['wait_repay_impose_amount'] = $list[$i]['impose_amount'] - $list[$i]['has_repay_impose_amount'] ;
	    		
	    		
	    		//申请提现总额
	    		$list[$i]['carry'] = floatval($GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."user_carry where create_time between $day_time and $day_time+24*3600 "));
	    		
	    		//成功提现金额
	    		$list[$i]['suc_carry'] = floatval($GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."user_carry where status=1 and update_time between $day_time and $day_time+24*3600 "));
	    		
	    		
	    		$day_time +=24 * 3600;
	    	}
	    	
	    	//待投资资金
	    	$user_amount = M("User")->where("is_delete=0 AND is_effect=1")->sum("money");
			$this->assign("user_amount",$user_amount);
	    	
	    	$this->assign("list",$list);
    	}
    	$this->display();
    }
    
    public function com_search(){
		$map = array ();
	
	
		if (!isset($_REQUEST['end_time']) || $_REQUEST['end_time'] == '') {
			$_REQUEST['end_time'] = to_date(get_gmtime(), 'Y-m-d');
		}
		
		
		if (!isset($_REQUEST['start_time']) || $_REQUEST['start_time'] == '') {
			$_REQUEST['start_time'] = dec_date($_REQUEST['end_time'], 7);// $_SESSION['q_start_time_7'];
		}
	

		$map['start_time'] = trim($_REQUEST['start_time']);
		$map['end_time'] = trim($_REQUEST['end_time']);
	
	
		$this->assign("start_time",$map['start_time']);
		$this->assign("end_time",$map['end_time']);
	
	
		$d = explode('-',$map['start_time']);
		if (checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("开始时间不是有效的时间格式:{$map['start_time']}(yyyy-mm-dd)");
			exit;
		}
	
		$d = explode('-',$map['end_time']);
		if (checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("结束时间不是有效的时间格式:{$map['end_time']}(yyyy-mm-dd)");
			exit;
		}
	
		if (to_timespan($map['start_time']) > to_timespan($map['end_time'])){
			$this->error('开始时间不能大于结束时间');
			exit;
		}
	
		$q_date_diff = 70;
		$this->assign("q_date_diff",$q_date_diff);
		//echo abs(to_timespan($map['end_time']) - to_timespan($map['start_time'])) / 86400 + 1;
		if ($q_date_diff > 0 && (abs(to_timespan($map['end_time']) - to_timespan($map['start_time'])) / 86400  + 1 > $q_date_diff)){
			$this->error("查询时间间隔不能大于  {$q_date_diff} 天");
			exit;
		}
	
		return $map;
	}	
	
	//借入总统计
	public function loan_total(){
		
		$sql_str = "select 
		sum(a.self_money) as 成功借入金额, 
		(select ifnull(sum(rebate_money),0) from ".DB_PREFIX."deal_load m where m.is_rebate = 1) as 总支出奖励,
		sum(if(a.has_repay = 0, a.repay_money,0)) as 待还总额,
		sum(if(a.has_repay = 0, a.self_money,0)) as 待还本金总额,
		sum(if(a.has_repay = 0, a.repay_money - self_money,0)) as 待还利息总额,
		sum(if(a.has_repay = 1, a.repay_money,0)) as 已还总额,
		sum(if(a.has_repay = 1, a.self_money,0)) as 已还总本金,
		sum(if(a.has_repay = 1, a.repay_money - self_money,0)) as 已还总利息,
		sum(if(a.has_repay = 1 and a.status = 0, a.impose_money,0)) as 总提前还款罚息,
		sum(if(a.has_repay = 1 and (a.status = 2 or a.status = 3), a.impose_money,0)) as 总逾期还款罚金
		from ".DB_PREFIX."deal_load_repay as a  ";
		
		$model = D();
		
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);		
		$this->display();		
		
	}
	
	//所有借款人
	public function loan_borrowers_list()
	{
		$sql_str = "select 
			c.user_name as 借款人,
			sum(a.self_money) as 成功借入金额, 
			(select ifnull(sum(rebate_money),0) from ".DB_PREFIX."deal_load m 
			LEFT JOIN ".DB_PREFIX."deal d on d.id = m.deal_id
			where d.user_id = c.id and m.is_rebate = 1) as 总支出奖励,
			sum(if(a.has_repay = 0, a.repay_money,0)) as 待还总额,
			sum(if(a.has_repay = 0, a.self_money,0)) as 待还本金总额,
			sum(if(a.has_repay = 0, a.repay_money - self_money,0)) as 待还利息总额,
			sum(if(a.has_repay = 1, a.repay_money,0)) as 已还总额,
			sum(if(a.has_repay = 1, a.self_money,0)) as 已还总本金,
			sum(if(a.has_repay = 1, a.repay_money - self_money,0)) as 已还总利息,
			sum(if(a.has_repay = 1 and a.status = 0, a.impose_money,0)) as 总提前还款罚息,
			sum(if(a.has_repay = 1 and (a.status = 2 or a.status = 3), a.impose_money,0)) as 总逾期还款罚金
			 from ".DB_PREFIX."deal_load_repay as a 
			LEFT JOIN ".DB_PREFIX."deal b on b.id = a.deal_id
			left join ".DB_PREFIX."user c on c.id = b.user_id";
		if(trim($_REQUEST['user_name'])!='')
		{
			$sql_str .= " where c.user_name like '%".trim($_REQUEST['user_name'])."%'  ";	
		}

		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str .= " group by b.user_id ";
		
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);
		
		$this->display();
		
	}
	
	//已还款
	public function loan_hasback_total(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select 
		true_repay_date as 时间,
		sum(repay_money) as	已还总额,
		sum(self_money) as	已还本金,
		sum(repay_money - self_money) as 已还利息, 	 	 
		sum(if(status = 0, impose_money,0)) as 已还提前还款罚息,
		sum(if(status = 2 or status = 3, impose_money,0)) as 已还逾期还款罚金,
		sum(manage_money) as 投资者付管理费,
		sum(repay_manage_money + repay_manage_impose_money) as 借款者付管理费,
		sum(manage_money + repay_manage_money + repay_manage_impose_money) as 平台收入,
		count(DISTINCT repay_id) as 还款人次
		from ".DB_PREFIX."deal_load_repay as a where has_repay = 1 ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and true_repay_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= "  group by true_repay_date";
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		//var_dump($voList);exit;
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('已还总额','时间','已还总额'),
					array('已还本金','时间','已还本金'),
					array('已还利息','时间','已还利息'),
					array('已还提前还款罚息','时间','已还提前还款罚息'),
					array('已还逾期还款罚金','时间','已还逾期还款罚金'),
					array('投资者付管理费','时间','投资者付管理费'),
					array('借款者付管理费','时间','借款者付管理费'),
					array('平台收入','时间','平台收入'),
					array('还款人次','时间','还款人次')
				),
		);
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}
	
	//已还款，待还款，逾期还款 明细
	public function loan_deal_info(){
		//pay_status 1 已还款、2待还款、3逾期还款
		$pay_status=intval(trim($_REQUEST['pay_status']));
		
		$time=trim($_REQUEST['time']);
				
		if(trim($_REQUEST['id'])!='')
		{
			$id=intval(trim($_REQUEST['id']));
		}
		if(trim($_REQUEST['user_name'])!='')
		{
			$user_name= trim($_REQUEST['user_name']);
		}
		if(trim($_REQUEST['deal_sn'])!='')
		{
			$deal_sn= trim($_REQUEST['deal_sn']);
		}
		if(trim($_REQUEST['sub_name'])!='')
		{
			$sub_name= trim($_REQUEST['sub_name']);
		}
		if(trim($_REQUEST['has_repay'])!='')
		{
			$has_repay= trim($_REQUEST['has_repay']);
		}
		
		
		$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		
		
		$condtion = "  (a.repay_date ='$time' )";
		
		if($pay_status==3){
			$where="((has_repay = 1 and a.true_repay_time > a.repay_time) or (has_repay = 0 and  a.repay_time < ".TIME_UTC.")) and a.repay_date ='$time' ";
//			$where="((has_repay = 1 and a.true_repay_date ='$time' ";
		}elseif($pay_status==2){
			$where="a.has_repay = 0 and $condtion ";
		}elseif($pay_status==1){
			
			$where=" a.has_repay =1 and (a.true_repay_date ='$time')  ";
		}else{
			$where="1=1";
		}
		
		$sql_str = "select a.id as 还款ID,
		u.user_name as 借款人,
		b.deal_sn as 借款标识名,
		b.sub_name as 借款标题,
		CONCAT('第',a.l_key + 1,'期') as 借款期数,
		c.`name` as 借款类型,
		FROM_UNIXTIME(a.repay_time + 28800, '%Y-%m-%d') as 应还时间,
		a.repay_money as 应还本息,
		a.manage_money as 管理费用,
		a.impose_money + a.manage_impose_money as 罚息,
		case has_repay
		when 0 then 0
		when 1 then 
		a.repay_money + a.impose_money + a.manage_impose_money + a.manage_money
		when 2 then 
		 (select sum(repay_money + impose_money + repay_manage_money + repay_manage_impose_money) from ".DB_PREFIX."deal_load_repay l where l.has_repay = 1 and l.deal_id = a.deal_id and l.repay_id = a.id)
		else 
		 '0'
		end as  实还总额,
		if (datediff(FROM_UNIXTIME(a.true_repay_time + 28800, '%Y-%m-%d'),FROM_UNIXTIME(a.repay_time + 28800, '%Y-%m-%d')) > 0, 
		datediff(FROM_UNIXTIME(a.true_repay_time + 28800, '%Y-%m-%d'),FROM_UNIXTIME(a.repay_time + 28800, '%Y-%m-%d')),
		0) as 逾期天数, 
		FROM_UNIXTIME(a.true_repay_time + 28800, '%Y-%m-%d') as 实还时间,
		case has_repay
		when 0 then '未还'
		when 1 then '已还'
		when 2 then '部分还款'
		else 
		 '已收款'
		end as 状态,
		if(is_site_bad = 1,'是','否') as 是否坏账
		from ".DB_PREFIX."deal_repay as a
		left join ".DB_PREFIX."user u on u.id = a.user_id
		LEFT JOIN ".DB_PREFIX."deal b on b.id = a.deal_id
		LEFT JOIN ".DB_PREFIX."deal_cate c on c.id = b.cate_id
		where $where";
		
		if($id){
			$sql_str="$sql_str and a.id = '$id'";
		}
		if($user_name){
			$sql_str="$sql_str and u.user_name like '%$user_name%'";
		}
		if($deal_sn){
			$sql_str="$sql_str and b.deal_sn like '%$deal_sn%'";
		}
		if($sub_name){
			$sql_str="$sql_str and b.sub_name like '%$sub_name%'";
		}
		
		
		if(isset($_REQUEST['has_repay'])){
			if($has_repay==4){
				$sql_str="$sql_str";
			}elseif($has_repay==3){
				$sql_str="$sql_str and has_repay = 0 ";
			}else{
				$sql_str="$sql_str and has_repay = '$has_repay'";
			}
			
		}
		
		//echo"$has_repay";
		
		if($begin_time > 0 || $end_time > 0){
			if($begin_time>0 && $end_time==0){
				$sql_str = "$sql_str and (a.repay_time > $begin_time)";
			}elseif($begin_time==0 && $end_time>0){
				$sql_str = "$sql_str and (a.repay_time < $end_time )";
			}elseif($begin_time >0 && $end_time>0){
				$sql_str = "$sql_str and (a.repay_time between $begin_time and $end_time )";
			}
			
		}
		
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);
		
		$this->display();			
	}
	
	//待还款
	public function loan_tobe_receivables(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select 
		repay_date as 时间,
		sum(repay_money) as	待还款总额,
		sum(self_money) as	待还本金,
		sum(repay_money - self_money) as 待还利息,
		count(DISTINCT if(has_repay = 0, repay_id, null)) as 待还人次
		from ".DB_PREFIX."deal_load_repay as a where has_repay = 0 ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and repay_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= " group by repay_date ";
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		//var_dump($voList);exit;
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('待还款总额','时间','待还款总额'),
					array('待还本金','时间','待还本金'),
					array('待还利息','时间','待还利息'),
					array('待还人次','时间','待还人次')
				),
		);
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}	
	
	//逾期还款
	public function loan_repay_late_total(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select 
		repay_date as 时间,
		sum(if(has_repay = 0,repay_money,0)) as 逾期未还总额,
		count(DISTINCT if(has_repay = 0, repay_id, null))  as 逾期未还期数,
		sum(if(has_repay = 1,repay_money,0)) as 逾期已还总额,
		count(DISTINCT if(has_repay = 1, repay_id, null)) as 逾期已还期数
		 from ".DB_PREFIX."deal_load_repay as a where 
		((has_repay = 1 and true_repay_time > repay_time) or (has_repay = 0 and  repay_date < CURDATE())) ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and repay_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= " group by repay_date ";
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		//var_dump($voList);exit;
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('逾期未还总额','时间','逾期未还总额'),
					array('逾期未还期数','时间','逾期未还期数'),
					array('逾期已还总额','时间','逾期已还总额'),
					array('逾期已还期数','时间','逾期已还期数')
				),
		);
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}	
	
	//借款人数
	public function loan_usernum_total(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select 
		start_date as 时间,
		count(user_id) as	借款用户数
		 from ".DB_PREFIX."deal where deal_status > 0 ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and start_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= " group by start_date ";
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		//var_dump($voList);exit;
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('借款用户数','时间','借款用户数')
				),
		);
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}
	
	//借款金额
	public function loan_account_total(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$where .= " and start_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
					
		$date_list = explode(",",date_in($map['start_time'],$map['end_time'],false));
		
		$sql = "select start_date as time,sum(borrow_amount) as shenqing,count(user_id) as	usernum1 from ".DB_PREFIX."deal where deal_status > 0 and start_date in (".date_in($map['start_time'],$map['end_time']).") group by start_date";
		$apply_list = $GLOBALS['db']->getAll($sql);
		
		$sql = "select repay_start_date as time,sum(borrow_amount) as manbiao,count(user_id) as	usernum2 from ".DB_PREFIX."deal where deal_status > 0 and is_has_loans = 1 and repay_start_date in (".date_in($map['start_time'],$map['end_time']).")  group by repay_start_date";
		$full_list = $GLOBALS['db']->getAll($sql);
		
		$sql = "select bad_date as time,sum(borrow_amount) as liubiao,count(user_id) as	usernum3 from ".DB_PREFIX."deal where deal_status = 3 and bad_date in (".date_in($map['start_time'],$map['end_time']).")  group by bad_date ";
		$flow_list = $GLOBALS['db']->getAll($sql);
			
			
		$list = array();
		foreach($date_list as $k=>$v){
			$row = array();
			$row['date'] = $v;
			$row['申请借款金额'] = 0;
			$row['用户数1'] = 0;
			$row['满标放款金额'] = 0;
			$row['用户数2'] = 0;	
			$row['流标失败金额'] = 0;	
			$row['用户数3'] = 0;	
							
			foreach($apply_list as $ak=>$av){
				if ($av['time'] == $v){
					$row['申请借款金额'] = $av['shenqing'];
					$row['用户数1'] = $av['usernum1'];
					break;
				}				
			}
			
							
			foreach($full_list as $fk=>$fv){
				if ($fv['time'] == $v){
					$row['满标放款金额'] = $fv['manbiao'];
					$row['用户数2'] = $fv['usernum2'];
					break;
				}				
			}
			
			foreach($flow_list as $flk=>$flv){
				if ($flv['time'] == $v){
					$row['流标失败金额'] = $flv['liubiao'];
					$row['用户数3'] = $flv['usernum3'];
				}	
			}
			
			$row['usernum']=$row['用户数1']+$row['用户数2']+$row['用户数3'];
			$list[] = $row;
			
		}
		$this->assign("list",$list);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('申请借款金额','date','申请借款金额'),
					array('满标放款金额','date','满标放款金额'),
					array('流标失败金额','date','流标失败金额'),
					array('usernum','date','用户数')
				),
		);
		
		$chart_list=$this->get_jx_json_all($list,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}
	
	
	//标种借款
	public function loan_borrow_type(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$cate_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete = 0 order by id");
		$sql_str = "select a.repay_start_date as 时间,";
		
		$item_array = array();
		foreach ( $cate_list as $key => $val ) {
			$sql_str .= "sum(if( a.cate_id = ".$val['id'].", borrow_amount, 0)) as ".$val['name'].",";
		
			$item_array[] = array($val['name'],'时间',$val['name']);
		}
		
		$item_array[] = array('成功次数','时间','成功次数');
		
		$total_array=array($item_array);
		
		$sql_str .= " count(*) as 成功次数 
		from ".DB_PREFIX."deal a where a.is_has_loans = 1 ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and a.repay_start_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= " group by a.repay_start_date";
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();	
			
	}
	
}
?>