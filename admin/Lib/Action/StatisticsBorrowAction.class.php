<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class StatisticsBorrowAction extends CommonAction{

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
//		echo abs(to_timespan($map['end_time']) - to_timespan($map['start_time'])) / 86400 + 1;
		if ($q_date_diff > 0 && (abs(to_timespan($map['end_time']) - to_timespan($map['start_time'])) / 86400  + 1 > $q_date_diff)){
			$this->error("查询时间间隔不能大于  {$q_date_diff} 天");
			exit;
		}
		
		
		return $map;
	}	
	
	//投资金额
	public function tender_account_total(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select		
		create_date as 时间,
		count(*) as	投资人次,
		sum(money) as 投资总额,
		sum(if(is_has_loans = 1, money,0)) as 投资成功,
		sum(if(is_has_loans = 0 and is_repay = 0, money,0)) as 冻结投资额,
		sum(if(is_has_loans = 0 and is_repay = 1, money,0)) as 投资失败,
		sum(if(is_has_loans = 1, rebate_money,0)) as 已获奖励
		
		from ".DB_PREFIX."deal_load where 1 = 1 ";
		
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and create_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		
		$sql_str .= " group by create_date ";
		
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		//var_dump($voList);exit;
		
		//$this->assign("list",$voList);
		//$this->assign("new_sort", M("Delivery")->max("sort")+1);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('投资人次','时间','投资人次'),
					array('投资总额','时间','投资总额'),
					array('投资成功','时间','投资成功'),
					array('冻结投资额','时间','冻结投资额'),
					array('投资失败','时间','投资失败'),
					array('已获奖励','时间','已获奖励')
				),
		);
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}
	
	//投资金额明细
	public function tender_account_info(){
		
		$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		$time=trim($_REQUEST['time']);
		if(trim($_REQUEST['time'])){
			$condtion = " where  (a.create_date = '$time')";
		}else{
			$condtion = " where 1=1 ";
		}
		
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
		if(trim($_REQUEST['is_has_loans'])!='')
		{
			$is_has_loans= trim($_REQUEST['is_has_loans']);
		}
		
		
		$sql_str = "select 
		a.id as 投资ID,
		a.user_name as	投资人,
		a.money as	投资金额,
		case 
		 when a.is_has_loans = 1 then '成功'
		 when a.is_has_loans = 0 and a.is_repay = 0  then '冻结'
		when a.is_repay = 1  then '失败'
		else ''
		end 投资状态,
		if((select count(*) from ".DB_PREFIX."deal_load_transfer t where t.user_id != t.t_user_id and t.t_user_id > 0 and t.deal_id = a.deal_id and t.load_id = a.id) > 0, '是','否') as 是否转让,
		FROM_UNIXTIME(a.create_time + 28800, '%Y-%m-%d %H:%i:%S') as 投资时间,
		b.sub_name as 借款标题,
		b.deal_sn as 借款编号,
		b.borrow_amount as	借款总额,
		if(is_auto = 1,'是','否') as	自动投标
		from ".DB_PREFIX."deal_load as a LEFT JOIN ".DB_PREFIX."deal as b on b.id = a.deal_id $condtion  ";
		
		if($id){
			$sql_str="$sql_str  and a.id = '$id'";
		}
		if($user_name){
			$sql_str="$sql_str and a.user_name like '%$user_name%'";
		}
		if($deal_sn){
			$sql_str="$sql_str and b.deal_sn like '%$deal_sn%'";
		}
		if($sub_name){
			$sql_str="$sql_str and b.sub_name like '%$sub_name%'";
		}
		
		if(isset($_REQUEST['is_has_loans'])){
			if($is_has_loans==4){
				$sql_str="$sql_str";
			}elseif($is_has_loans==1){
				$sql_str="$sql_str and a.is_has_loans = 1 ";
			}elseif($is_has_loans==2){
				$sql_str="$sql_str and a.is_has_loans = 0 and a.is_repay = 0 ";
			}elseif($is_has_loans==3){
				$sql_str="$sql_str and a.is_repay = 1 ";
			}
			
		}
		
		if($begin_time > 0 || $end_time > 0){
			if($begin_time>0 && $end_time==0){
				$sql_str = "$sql_str and (a.create_time > $begin_time)";
			}elseif($begin_time==0 && $end_time>0){
				$sql_str = "$sql_str and (a.create_time < $end_time )";
			}elseif($begin_time >0 && $end_time>0){
				$sql_str = "$sql_str and (a.create_time between $begin_time and $end_time )";
			}
			
		}
		
		$model = D();
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);
		
		$this->display();		
	}
	
	//已回款
	public function tender_hasback_total(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select 
		true_repay_date as 时间,
		sum(repay_money + impose_money - manage_money) as	投资者回款总额,
		sum(self_money) as	投资者回款本金,
		sum(repay_money - self_money) as 投资者回款利息, 	 	 
		sum(if(status = 0, impose_money,0)) as 提前还款罚息,
		sum(if(status = 2 or status = 3, impose_money,0)) as 逾期还款罚金,
		sum(manage_money) as投资者付管理费,
		sum(repay_manage_money + repay_manage_impose_money) as 借款者付管理费,
		sum(manage_money + repay_manage_money + repay_manage_impose_money) as 平台收入,
		count(*) as 收款人次
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
					array('投资者回款总额','时间','投资者回款总额'),
					array('投资者回款本金','时间','投资者回款本金'),
					array('投资者回款利息','时间','投资者回款利息'),
					array('提前还款罚息','时间','提前还款罚息'),
					array('逾期还款罚金','时间','逾期还款罚金'),
					array('投资者付管理费','时间','投资者付管理费'),
					array('借款者付管理费','时间','借款者付管理费'),
					array('平台收入','时间','平台收入'),
					array('收款人次','时间','收款人次')
				),
		);
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}
	
	//已回款明细
	public function tender_hasback_info(){
		
		$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		//$condtion = "  (a.repay_time between $start_time and $end_time )";
		$time=trim($_REQUEST['time']);
		if(trim($_REQUEST['time'])){
			$condtion = " and  (a.repay_date = '$time')";
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
		if(trim($_REQUEST['status'])!='')
		{
			$status= trim($_REQUEST['status']);
		}
		
		if(trim($_REQUEST['cate_id'])!='')
		{
			$cate_id= trim($_REQUEST['cate_id']);
		}
		$this->assign("cate_list",M("DealCate")->where('is_effect = 1 and is_delete = 0 order by sort')->findAll());
		
		
		$sql_str = "select 
		u.user_name as 收款人,
		d.deal_sn as 贷款号,
		d.sub_name as 借款标题,
		c.`name` as 借款类型,
		a.repay_money as 还款本息,
		a.impose_money as 投资者罚息收入,
		a.manage_money as 投资者付管理费,
		a.repay_manage_money + a.repay_manage_impose_money as 借款者付管理费,
		if (datediff(a.true_repay_date,FROM_UNIXTIME(a.repay_time + 28800, '%Y-%m-%d')) > 0, 
		datediff(a.true_repay_date,FROM_UNIXTIME(a.repay_time + 28800, '%Y-%m-%d')),
		0) as 逾期天数, 
		FROM_UNIXTIME(a.repay_time + 28800, '%Y-%m-%d') as  应收时间,
		 a.true_repay_date as	实收时间,
		 a.repay_money + a.impose_money - a.manage_money as	投资者实收总额,	
		a.manage_money + a.repay_manage_money + a.repay_manage_impose_money as 平台收入,
		if(has_repay = 1,
		case status
		when 0 then '提前收款'
		when 1 then '准时收款'
		when 2 then '逾期收款'
		when 3 then '严重逾期收款'
		else 
		 '已收款'
		end
		,'未收款') as 状态
		 from ".DB_PREFIX."deal_load_repay as a 
		LEFT JOIN ".DB_PREFIX."user u on u.id = a.user_id
		LEFT JOIN ".DB_PREFIX."deal d on d.id = a.deal_id
		LEFT JOIN ".DB_PREFIX."deal_cate c on c.id = d.cate_id
		where a.has_repay = 1 $condtion ";
		
		if($user_name){
			$sql_str="$sql_str and u.user_name like '%$user_name%'";
		}
		if($deal_sn){
			$sql_str="$sql_str and d.deal_sn like '%$deal_sn%'";
		}
		if($sub_name){
			$sql_str="$sql_str and d.sub_name like '%$sub_name%'";
		}
		
		if($cate_id){
			$sql_str="$sql_str and c.id = '$cate_id'";
		}
		
		if(isset($_REQUEST['status'])){
			if($status==4){
				$sql_str="$sql_str";
			}elseif($status==0){
				$sql_str="$sql_str and status = 0 ";
			}elseif($status==1){
				$sql_str="$sql_str and status = 1 ";
			}elseif($status==2){
				$sql_str="$sql_str and status = 2 ";
			}elseif($status==3){
				$sql_str="$sql_str and status = 3 ";
			}
			
		}
		
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
	
	//待收款
	public function tender_tobe_receivables(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select 
		repay_date as 时间,
		sum(repay_money + impose_money - manage_money) as	待收总额,
		sum(self_money) as	待收本金,
		sum(repay_money - self_money) as 待收利息, 	 	 
		count(*) as 待收款人次

		from ".DB_PREFIX."deal_load_repay as a where has_repay = 0 ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and repay_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= "  group by repay_date";
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('待收总额','时间','待收总额'),
					array('待收本金','时间','待收本金'),
					array('待收利息','时间','待收利息'),
					array('待收款人次','时间','待收款人次')
				),
		);
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}
	
	//待收款明细
	public function tender_tobe_receivablesinfo(){
		
		$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		//$condtion = "  (a.repay_time between $start_time and $end_time )";
		$time=trim($_REQUEST['time']);
		if(trim($_REQUEST['time'])){
			$condtion = " and  (a.repay_date = '$time')";
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
		if(trim($_REQUEST['cate_id'])!='')
		{
			$cate_id= trim($_REQUEST['cate_id']);
		}
		
		//$model = D();
		//echo $sql_str;
		$this->assign("cate_list",M("DealCate")->where('is_effect = 1 and is_delete = 0 order by sort')->findAll());
		
		$sql_str = "select 
		u.user_name as 收款人,
		d.deal_sn as	贷款号,
		d.sub_name as 借款标题,
		c.`name` as 借款类型,
		a.repay_money as 还款本息,
		if (datediff(CURDATE() ,FROM_UNIXTIME(a.repay_time + 28800, '%Y-%m-%d')) > 0, 
		datediff(CURDATE(),FROM_UNIXTIME(a.repay_time + 28800, '%Y-%m-%d')),
		0) as 逾期天数, 
		FROM_UNIXTIME(a.repay_time + 28800, '%Y-%m-%d') as  应收时间,
		'未收款' as 状态
		 from ".DB_PREFIX."deal_load_repay as a 
		LEFT JOIN ".DB_PREFIX."user u on u.id = a.user_id
		LEFT JOIN ".DB_PREFIX."deal d on d.id = a.deal_id
		LEFT JOIN ".DB_PREFIX."deal_cate c on c.id = d.cate_id
		where a.has_repay = 0  $condtion ";
		
		if($user_name){
			$sql_str="$sql_str and u.user_name like '%$user_name%'";
		}
		if($deal_sn){
			$sql_str="$sql_str and d.deal_sn like '%$deal_sn%'";
		}
		if($sub_name){
			$sql_str="$sql_str and d.sub_name like '%$sub_name%'";
		}
		
		if($sub_name){
			$sql_str="$sql_str and d.sub_name like '%$sub_name%'";
		}
		
		if($cate_id){
			$sql_str="$sql_str and c.id = '$cate_id'";
		}
		
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
	
	//标种投资
	public function tender_borrow_type(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$cate_list = $GLOBALS['db']->getAll("select id,name from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete = 0 order by sort");
		$sql_str = "select a.create_date as 时间,";
		
		/*
		$total_array=array(
				array(
					array('信用认证标','时间','信用认证标'),
					array('实地认证标','时间','实地认证标'),
					array('机构担保标','时间','机构担保标'),
					array('智能理财标','时间','智能理财标'),
					array('旅游考察标','时间','旅游考察标'),
					array('抵押标','时间','抵押标'),
					array('成功总人次','时间','成功总人次')
				),
		);
		*/
		$item_array = array();
		foreach ( $cate_list as $key => $val ) {
			$sql_str .= "sum(if( b.cate_id = ".$val['id'].", 1, 0)) as ".$val['name'].",";
		
			$item_array[] = array($val['name'],'时间',$val['name']);
		}
		
		$item_array[] = array('成功总人次','时间','成功总人次');
		
		$total_array=array($item_array);
		
		$sql_str .= " count(*) as 成功总人次
		from ".DB_PREFIX."deal_load a LEFT JOIN ".DB_PREFIX."deal b on b.id = a.deal_id where a.is_has_loans = 1 ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and a.create_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= "  group by a.create_date";
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		
		//var_dump($voList);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}
	
	//投资人数
	public function tender_usernum_total(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select create_date as 时间, count(DISTINCT user_id) as 投资用户数, sum(money) as 投资统计 
		from ".DB_PREFIX."deal_load  where is_has_loans = 1 ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and create_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= "  group by create_date ";
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(array('投资用户数','时间','投资用户数'),array('投资统计','时间','投资统计')),
		);
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}
	
	//投资人数明细
	public function tender_usernum_info(){
		
		$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		
		$time=trim($_REQUEST['time']);
		if(trim($_REQUEST['time'])){
			$condtion = " and  (a.create_date = '$time')";
		}
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$user_name= trim($_REQUEST['user_name']);
		}
		
		
		$sql_str = "select  
		u.user_name as 用户名, 
		a.money as 投资金额, 
		FROM_UNIXTIME(a.create_time + 28800, '%Y-%m-%d %H:%i:%S') as 时间 
		from ".DB_PREFIX."deal_load a  
		left join ".DB_PREFIX."user u on u.id = a.user_id
		where a.is_has_loans = 1  $condtion  ";
		
		if($user_name){
			$sql_str="$sql_str and u.user_name like '%$user_name%'";
		}
		
		if($begin_time > 0 || $end_time > 0){
			if($begin_time>0 && $end_time==0){
				$sql_str = "$sql_str and (a.create_time > $begin_time)";
			}elseif($begin_time==0 && $end_time>0){
				$sql_str = "$sql_str and (a.create_time < $end_time )";
			}elseif($begin_time >0 && $end_time>0){
				$sql_str = "$sql_str and (a.create_time between $begin_time and $end_time )";
			}
			
		}
		
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);
		
		$this->display();		
	}
	
	
	//借出总统计
	public function tender_total(){
		
		$sql_str = "select 
		count(DISTINCT user_id) as 投资人数,
		sum(self_money) as 成功投资金额, 
		(select ifnull(sum(rebate_money),0) from ".DB_PREFIX."deal_load m where m.is_has_loans = 1 and m.is_rebate = 1 ) as 奖励总额,
		sum(if(has_repay = 0, repay_money,0)) as 待收总额,
		sum(if(has_repay = 0, self_money,0)) as 待收本金总额,
		sum(if(has_repay = 0, repay_money - self_money,0)) as 待收利润总额,
		sum(if(has_repay = 1, repay_money,0)) as 已收总额,
		sum(if(has_repay = 1, self_money,0)) as 已收本金总额,
		sum(if(has_repay = 1, repay_money - self_money,0)) as 已收利润总额,
		sum(if(has_repay = 1 and status = 0, impose_money,0)) as 提前还款罚息总额,
		sum(if(has_repay = 1 and (status = 2 or status = 3), impose_money,0)) as 逾期还款罚金总额
		from ".DB_PREFIX."deal_load_repay as a  ";
		
		$model = D();
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);		

		$this->display();		
	}
	//所有投资人
	public function tender_total_info()
	{
		$sql_str = "select 
			(select u.user_name from ".DB_PREFIX."user u where u.id=a.user_id) as 投资人,
			sum(self_money) as 成功投资金额, 
			(select ifnull(sum(rebate_money),0) from ".DB_PREFIX."deal_load m where m.is_has_loans = 1 and m.is_rebate = 1 and m.user_id = 
			a.user_id) as 奖励总额,
			sum(if(has_repay = 0, repay_money,0)) as 待收总额,
			sum(if(has_repay = 0, self_money,0)) as 待收本金总额,
			sum(if(has_repay = 0, repay_money - self_money,0)) as 待收利润总额,
			sum(if(has_repay = 1, repay_money,0)) as 已收总额,
			sum(if(has_repay = 1, self_money,0)) as 已收本金总额,
			sum(if(has_repay = 1, repay_money - self_money,0)) as 已收利润总额,
			sum(if(has_repay = 1 and status = 0, impose_money,0)) as 提前还款罚息总额,
			sum(if(has_repay = 1 and (status = 2 or status = 3), impose_money,0)) as 逾期还款罚金总额
			from ".DB_PREFIX."deal_load_repay as a  ";
		if(trim($_REQUEST['user_name'])!='')
		{
			$sql_str .= " ,".DB_PREFIX."user as u  ";
			$sql_str .= " where u.user_name like '%".trim($_REQUEST['user_name'])."%' and  u.id=a.user_id ";	
		}
		
		$sql_str .= "  group by user_id ";
		$model = D();
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);
		
		$this->display();
		
	}
	//投资排名
	public function tender_rank_list(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "SELECT
			(@rowNO := @rowNo + 1) AS 排名,
			c.投资人,
			c.成功投资总额
		FROM
			(
				SELECT
					a.user_name AS 投资人,
					sum(money) AS 成功投资总额
				FROM
					".DB_PREFIX."deal_load AS a,
					(SELECT @rowNO := 0) b
				WHERE
					a.is_has_loans = 1
			";	
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and create_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= " GROUP BY a.user_id) c  ";
		
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);

		$this->display();		
	}
	
	//投资额比例
	public function tender_account_ratio(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select create_date as 时间,
		sum(if(money < 5000, 1, 0)) as 5千以下,
		sum(if(money >= 5000 and money < 10000, 1, 0)) as 5千至1万,
		sum(if(money >= 10000 and money < 50000, 1, 0)) as 1至5万,
		sum(if(money >= 50000 and money < 100000, 1, 0)) as 5至10万,
		sum(if(money >= 100000 and money < 200000, 1, 0)) as 10至20万,
		sum(if(money >= 200000 and money < 500000, 1, 0)) as 20至50万,
		sum(if(money >= 500000, 1, 0)) as 50万以上,
		count(*) as 成功总人次
		from ".DB_PREFIX."deal_load where is_has_loans = 1 ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " and create_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= " GROUP BY create_date ";
		$model = D();
		
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('5千以下','时间','5千以下'),
					array('5千至1万','时间','5千至1万'),
					array('1至5万','时间','1至5万'),
					array('5至10万','时间','5至10万'),
					array('10至20万','时间','10至20万'),
					array('20至50万','时间','20至50万'),
					array('50万以上','时间','50万以上'),
					array('成功总人次','时间','成功总人次')
				),
		);
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		//dump($chart_list);
		
		$this->display();		
	}
	
}
?>