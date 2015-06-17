<?php

class DataanalysisAction extends CommonAction{
	public function index()
	{
	//每日报表
	//日期
	//新增注册人数
	//新增投资人数
	//送出推荐红包数
	//支出
	//收入
	//支出占比：满标时长
	//一个月标：满标时长
	//三个月标：满标时长
	//六个月标：满标时长
	//设置时间区
	date_default_timezone_set(PRC);
	 $yesterday=strtotime(date("Y-m-d",strtotime("-1 day")));
	 $today=strtotime(date("Y-m-d")); 
	//今天到现在
	// echo '昨日：'.date('Y-m-d H:i:s',$yesterday).'<br />';
	// echo '今日'.date('Y-m-d H:i:s',$today).'<br />';
	// echo '现在：'.date('Y-m-d H:i:s').'<br />';
	// exit;
	 $begin_time=$_REQUEST['start_time']?strtotime($_REQUEST['start_time']):$yesterday;
	
	$end_time=$_REQUEST['end_time']?strtotime($_REQUEST['end_time']):$today;
	echo '开始时间：'.date("Y-m-d H:i:s",$begin_time).'<hr />';
	echo '结束时间：'.date("Y-m-d H:i:s",$end_time).'<hr />';
	$zuotian="Between  ".$begin_time."   and  ".$end_time;
	//今日新增注册量
	$jintian="Between  ".intval($today)."   and  ". intval(time());
	$analysis['b_time']=date("Y-m-d",$begin_time);
	//注册人数
	$analysis['reg']=	$yes_user=M("User")->where("create_time ".$zuotian." and is_effect=1 and id<>pid and group_id=1")->count();
	//投资人数
	$analysis['reg_tz']=$one= $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user  where  create_time ".$zuotian." and id<>pid and group_id=1 and id in(select user_id from ".DB_PREFIX."deal_load group by user_id) " );
	// $one= $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user  where  create_time ".$jintian." and id<>pid and id in(select user_id from ".DB_PREFIX."deal_load group by user_id) " );
	
	//推荐送出红包
	//记录表中查询 pfcfb=20
	
	//送的红包
	 $analysis['hongbao_tuijian']=M("UserLog")->where("log_time ".$zuotian ." and  pfcfb=20 ")->count();
	 //使用掉的浦发币
	 
	 
	$analysis['hongbao_touzi']= $GLOBALS['db']->getOne("select sum(unjh_pfcfb) as pfb from ".DB_PREFIX."deal_load  where  create_time ".$zuotian."  and user_id in(select id from ".DB_PREFIX."user where group_id=1) " );
	 
	 $analysis['hongbao_total']=$analysis['hongbao_tuijian']*20+$analysis['hongbao_touzi'];
	//收入
	
	 $analysis['money_in']= $GLOBALS['db']->getOne("select sum(money) as pfb from ".DB_PREFIX."deal_load  where  create_time ".$zuotian."  and user_id in(select id from ".DB_PREFIX."user where group_id=1) " );
	//支出比
	$analysis['money_percent']=$analysis['hongbao_total']/$analysis['money_in']*100;
	//今天满标的标
	$analysis['biao']=$GLOBALS['db']->getAll("select dl.create_time,d.borrow_amount,d.id,d.repay_time from ".DB_PREFIX."deal_load as dl inner join ".DB_PREFIX."deal d on d.id = dl.deal_id
	where dl.create_time in (select max(create_time) from ".DB_PREFIX."deal_load group by deal_id) and dl.create_time ".$zuotian." and deal_status=2");
	//3个标
		foreach($analysis['biao'] as $k=>$v){
			 $deal_id=$v['id'];
			$x_time=$GLOBALS['db']->getRow("select max(create_time) as max,(max(create_time)-min(create_time)) as manbiao_time,min(create_time) as min from ".DB_PREFIX."deal_load where deal_id= '".$v['id']."'");
			 $timediff=$x_time['manbiao_time'] ;  //miao
			$days = intval($timediff/86400); 
			$remain = $timediff%86400; 
			$hours = intval($remain/3600); 
			$remain = $remain%3600; 
			$mins = intval($remain/60); 
			$secs = $remain%60; 
			$res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs); 
			$res=$days.'天'. $hours.'时'.$mins.'分'.$secs.'秒';
			$analysis['biao'][$k]['newest']=$x_time['min'];
			$analysis['biao'][$k]['oldest']=$x_time['max'];
			$analysis['biao'][$k]['manbiaotime']= $res;
		}
		
		$this->assign('list',$analysis);
		$this->display('index');
		
	}
	
		
}
?>