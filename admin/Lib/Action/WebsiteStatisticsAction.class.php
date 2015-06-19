<?php

class WebsiteStatisticsAction extends CommonAction {
	
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
	
	//充值统计
	public function website_recharge_total(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select 
		n.create_date as 时间,
		sum(if(is_paid=1,money,0)) as 成功充值总额
		from ".DB_PREFIX."payment_notice as n ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " where n.create_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= " group by n.create_date ";
		$model = D();
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('成功充值总额','时间','成功充值总额'),
					
				),
		);
		
		//echo $sql_str;
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		$this->display();		
		
	}
	
	//提现统计
	public function website_extraction_cash(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select 
		c.create_date as 时间,
		sum(if(status=1,money,0)) as 成功提现总额,
		count(*) as 人次
		from ".DB_PREFIX."user_carry as c ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " where c.create_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= " group by c.create_date ";
		$model = D();
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('成功提现总额','时间','成功提现总额'),
					array('人次','时间','人次'),
				),
		);
		
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		$this->display();		
		
	}
	
	
	//用户统计
	public function website_users_total(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select 
		u.create_date as 时间,
		count(*) as 用户注册人数
		from ".DB_PREFIX."user as u ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " where u.create_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= " group by u.create_date ";
		$model = D();
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('用户注册人数','时间','用户注册人数'),
					
				),
		);
		
		//echo $sql_str;
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		$this->display();		
		
	}
	
	//网站垫付
	public function website_advance_total(){
		
		$map =  $this->com_search();
		
		foreach ( $map as $key => $val ) {
			//dump($key);
			if ((!is_array($val)) && ($val <> '')){
				$parameter .= "$key=" . urlencode ( $val ) . "&";
			}
		}
		
		$sql_str = "select 
		g.create_date as 时间,
		sum(repay_money) as 代还本息总额,
		sum(manage_money) as 代还管理费总额,
		sum(impose_money) as 代还罚息总额,
		sum(manage_impose_money) as 代还逾期管理费总额
		from ".DB_PREFIX."generation_repay as g ";
		
		//日期期间使用in形式，以确保能正常使用到索引
		if( isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> ''){
			$sql_str .= " where g.create_date in (".date_in($map['start_time'],$map['end_time']).")";
		}
		
		$sql_str .= " group by g.create_date ";
		$model = D();
		$voList = $this->_Sql_list($model, $sql_str, "&".$parameter, '时间', false);
		
		require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');
		
		$total_array=array(
				array(
					array('代还本息总额','时间','代还本息总额'),
					array('代还管理费总额','时间','代还管理费总额'),
					array('代还罚息总额','时间','代还罚息总额'),
					array('代还逾期管理费总额','时间','代还逾期管理费总额'),
					
				),
		);
		
		//echo $sql_str;
		$chart_list=$this->get_jx_json_all($voList,$total_array);
		$this->assign("chart_list",$chart_list);
		$this->display();		
		
	}
	
	//网站费用统计
	public function website_cost_total(){
		
		$sql_str = "select 
		count(DISTINCT user_id) as 关联用户数,
		sum(if(type = 7,money,0)) as 提前回收,
		sum(if(type = 9,money,0)) as 提现手续费,
		sum(if(type = 10,money,0)) as 借款管理费,
		sum(if(type = 12,money,0)) as 逾期管理费,
		sum(if(type = 13,money,0)) as 人工充值,
		sum(if(type = 14,money,0)) as 借款服务费,
		sum(if(type = 17,money,0)) as 债权转让管理费,
		sum(if(type = 18,money,0)) as 开户奖励,
		sum(if(type = 20,money,0)) as 投标管理费,
		sum(if(type = 22,money,0)) as 兑换,
		sum(if(type = 23,money,0)) as 邀请返利,
		sum(if(type = 24,money,0)) as 投标返利,
		sum(if(type = 25,money,0)) as 签到成功,
		sum(if(type = 26,money,0)) as 逾期罚金（垫付后）,
		sum(if(type = 27,money,0)) as 其他费用
		from ".DB_PREFIX."site_money_log  ";
		
		$model = D();
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);		

		$this->display();		
	}
	//普通会员交易金额统计
	public function website_cost_user_money(){
		
		$list_no_limit = M("User")->where('group_id=1')->findAll ( );
		
		foreach($list_no_limit as $k=>$v)
		{
			$total_no_limit+=$v['money'];
			$ta_limit=$k;
		}
		
		$total_no_limit=number_format($total_no_limit);
		$this->assign('ta_limit',$ta_limit);
		$this->assign('total_no_limit',$total_no_limit);
			

		$this->display();		
	}
	
	//充值明细
	public function website_recharge_info(){
		
		$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		$time=trim($_REQUEST['time']);
		if(trim($_REQUEST['time'])){
			$condtion = " where  (n.create_date = '$time')";
		}else{
			$condtion = " where 1=1 ";
		}
		
		if(trim($_REQUEST['notice_sn'])!='')
		{
			$notice_sn=trim($_REQUEST['notice_sn']);
		}
		if(trim($_REQUEST['user_name'])!='')
		{
			$user_name= trim($_REQUEST['user_name']);
		}
		if(trim($_REQUEST['is_paid'])!='')
		{
			$is_paid= trim($_REQUEST['is_paid']);
		}
		if(trim($_REQUEST['memo'])!='')
		{
			$memo= trim($_REQUEST['memo']);
		}
		
		
		
		$sql_str = "select 
		n.create_date as 时间,
		n.notice_sn as 支付单号,
		u.user_name as 会员名称,
		n.money as 应付金额,
		p.name as 支付方式,
		if(n.is_paid = 1,'已支付','未支付') as 支付状态,
		n.memo as 支付备注
		from ".DB_PREFIX."payment_notice as n LEFT JOIN ".DB_PREFIX."user as u on u.id=n.user_id LEFT JOIN ".DB_PREFIX."payment as p on  p.id=n.payment_id $condtion ";
		
		if($notice_sn){
			$sql_str="$sql_str  and n.notice_sn = '$notice_sn'";
		}
		if($user_name){
			$sql_str="$sql_str and u.user_name like '%$user_name%'";
		}
		if($memo){
			$sql_str="$sql_str and n.memo like '%$memo%'";
		}
		
		
		if(isset($_REQUEST['is_paid'])){
			if($is_paid==4){
				$sql_str="$sql_str";
			}elseif($is_paid==1){
				$sql_str="$sql_str and n.is_paid = 1 ";
			}elseif($is_paid==2){
				$sql_str="$sql_str and n.is_paid = 0 ";
			}
			
		}
		
		if($begin_time > 0 || $end_time > 0){
			if($begin_time>0 && $end_time==0){
				$sql_str = "$sql_str and (n.create_time > $begin_time)";
			}elseif($begin_time==0 && $end_time>0){
				$sql_str = "$sql_str and (n.create_time < $end_time )";
			}elseif($begin_time >0 && $end_time>0){
				$sql_str = "$sql_str and (n.create_time between $begin_time and $end_time )";
			}
			
		}
		
		$model = D();
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);
		
		$this->display();		
	}
	
	//提现明细
	public function website_extraction_cash_info(){
		
		$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		$time=trim($_REQUEST['time']);
		if(trim($_REQUEST['time'])){
			$condtion = " where  (c.create_date = '$time')";
		}else{
			$condtion = " where 1=1 ";
		}
		
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$user_name= trim($_REQUEST['user_name']);
		}
		if(trim($_REQUEST['status'])!='')
		{
			$status= trim($_REQUEST['status']);
		}
		
		$sql_str = "select 
		c.create_date as 时间,
		u.user_name as 会员名称,
		c.money as 提现金额,
		c.fee as 手续费,
		case c.status 
		when 0 then '待审核'
		when 1 then '已付款'
		when 2 then '未通过'
		when 3 then '待付款'
		else 
		 '撤销'
		end as 提现状态,
		FROM_UNIXTIME(c.update_time + 28800, '%Y-%m-%d') as 处理时间
		from ".DB_PREFIX."user_carry as c left join ".DB_PREFIX."user as u on u.id=c.user_id  $condtion ";
		
		if($user_name){
			$sql_str="$sql_str and u.user_name like '%$user_name%'";
		}
		
		if(isset($_REQUEST['status'])){
			if($status==5){
				$sql_str="$sql_str";
			}elseif($status==1){
				$sql_str="$sql_str and c.status = 0 ";
			}elseif($status==2){
				$sql_str="$sql_str and c.status = 1 ";
			}elseif($status==3){
				$sql_str="$sql_str and c.status = 2 ";
			}elseif($status==4){
				$sql_str="$sql_str and c.status = 4 ";
			}
			
		}
		
		if($begin_time > 0 || $end_time > 0){
			if($begin_time>0 && $end_time==0){
				$sql_str = "$sql_str and (c.create_time > $begin_time)";
			}elseif($begin_time==0 && $end_time>0){
				$sql_str = "$sql_str and (c.create_time < $end_time )";
			}elseif($begin_time >0 && $end_time>0){
				$sql_str = "$sql_str and (c.create_time between $begin_time and $end_time )";
			}
			
		}
		
		$model = D();
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);
		
		$this->display();		
	}
	
	//用户明细
	public function website_users_info(){
		
		$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		$time=trim($_REQUEST['time']);
		if(trim($_REQUEST['time'])){
			$condtion = " where  (u.create_date = '$time')";
		}else{
			$condtion = " where 1=1 ";
		}
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$user_name= trim($_REQUEST['user_name']);
		}
		if(trim($_REQUEST['email'])!='')
		{
			$email= trim($_REQUEST['email']);
		}
		if(trim($_REQUEST['mobile'])!='')
		{
			$mobile= trim($_REQUEST['mobile']);
		}
		
		if(trim($_REQUEST['level_id'])!='')
		{
			$level_id= trim($_REQUEST['level_id']);
		}
		
		$this->assign("level_list",M("UserLevel")->findAll());
		
		
		$sql_str = "select
		u.create_date as 注册时间,
		u.user_name as 会员名称,
		u.email as 会员邮件,
		u.mobile as 手机号,
		u.money as 会员余额,
		u.lock_money as 冻结资金,
		l.name as 会员等级
		from ".DB_PREFIX."user as u left join ".DB_PREFIX."user_level as l on l.id=u.level_id  $condtion ";
		
		if($user_name){
			$sql_str="$sql_str and u.user_name like '%$user_name%'";
		}
		if($email){
			$sql_str="$sql_str and u.email like '%$email%'";
		}
		if($mobile){
			$sql_str="$sql_str and u.mobile like '%$mobile%'";
		}
		
		if($level_id){
			$sql_str="$sql_str and l.id = '$level_id'";
		}
		
		if($begin_time > 0 || $end_time > 0){
			if($begin_time>0 && $end_time==0){
				$sql_str = "$sql_str and (u.create_time > $begin_time)";
			}elseif($begin_time==0 && $end_time>0){
				$sql_str = "$sql_str and (u.create_time < $end_time )";
			}elseif($begin_time >0 && $end_time>0){
				$sql_str = "$sql_str and (u.create_time between $begin_time and $end_time )";
			}
			
		}
		
		$model = D();
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);
		
		$this->display();		
	}
	
	//垫付明细
	public function website_advance_info(){
		
		$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		$time=trim($_REQUEST['time']);
		if(trim($_REQUEST['time'])){
			$condtion = " where  (r.create_date = '$time')";
		}else{
			$condtion = " where 1=1 ";
		}
		
		if(trim($_REQUEST['name'])!='')
		{
			$name= trim($_REQUEST['name']);
		}
		if(trim($_REQUEST['adm_name'])!='')
		{
			$adm_name= trim($_REQUEST['adm_name']);
		}
		if(trim($_REQUEST['agency_id'])!='')
		{
			$agency_id= trim($_REQUEST['agency_id']);
		}
		
		$this->assign("agency_list",M("DealAgency")->findAll());
		
		$sql_str = "select 
		r.create_date as 代还时间,
		d.name as 贷款名称,
		lr.l_key as 第几期,
		a.adm_name as 管理员,
		da.name as 担保机构,
		r.repay_money as 代还本息,
		r.manage_money as 代还管理费,
		r.impose_money as 代还罚息,
		r.manage_impose_money 代还多少逾期管理费
		from ".DB_PREFIX."generation_repay as r
		left join ".DB_PREFIX."deal as d on d.id=r.deal_id
		left join ".DB_PREFIX."deal_load_repay as lr on lr.id=r.repay_id
		left join ".DB_PREFIX."admin as a on a.id=r.admin_id
		left join ".DB_PREFIX."deal_agency as da on da.id=r.agency_id
		$condtion ";
		
		if($name){
			$sql_str="$sql_str and d.name like '%$name%'";
		}
		if($adm_name){
			$sql_str="$sql_str and a.adm_name like '%$adm_name%'";
		}
		
		if($agency_id){
			$sql_str="$sql_str and da.id = '$agency_id'";
		}
		
		
		if($begin_time > 0 || $end_time > 0){
			if($begin_time>0 && $end_time==0){
				$sql_str = "$sql_str and (r.create_time > $begin_time)";
			}elseif($begin_time==0 && $end_time>0){
				$sql_str = "$sql_str and (r.create_time < $end_time )";
			}elseif($begin_time >0 && $end_time>0){
				$sql_str = "$sql_str and (r.create_time between $begin_time and $end_time )";
			}
			
		}
		
		$model = D();
		//echo $sql_str;
		$voList = $this->_Sql_list($model, $sql_str, '时间', false);
		
		$this->display();		
	}
}
?>