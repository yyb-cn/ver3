<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_activityModule extends SiteBaseModule
{

	private $space_user;
	public function init_main()
	{
//		$user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']));		
//		require_once APP_ROOT_PATH."system/extend/ip.php";		
//		$iplocation = new iplocate();
//		$address=$iplocation->getaddress($user_info['login_ip']);
//		$user_info['from'] = $address['area1'].$address['area2'];
		$GLOBALS['tmpl']->assign('user_auth',get_user_auth());
	}
	
	public function init_user(){
		$this->user_data = $GLOBALS['user_info'];
		
		$province_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".$this->user_data['province_id']);
		$city_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".$this->user_data['city_id']);
		if($province_str.$city_str=='')
			$user_location = $GLOBALS['lang']['LOCATION_NULL'];
		else 
			$user_location = $province_str." ".$city_str;
		
		$this->user_data['fav_count'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where user_id = ".$this->user_data['id']." and fav_id <> 0");
		$this->user_data['user_location'] = $user_location;
		$this->user_data['group_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_group where id = ".$this->user_data['group_id']." ");
		
		$GLOBALS['tmpl']->assign('user_statics',sys_user_status($GLOBALS['user_info']['id'],true));
	}
	
	
	
	
	public function index()
	{
		//分享注册人id 
		$this->init_user();
		$user_info = $this->user_data;	
		
	// $user_info =$GLOBALS['user_info'];
	 $GLOBALS['tmpl']->assign("lpl",$GLOBALS['user_info']['id']);
	  $list_html = $GLOBALS['tmpl']->fetch("inc/uc/uc_links.html");	
	     $create_time= strtotime('2015-04-15 00:00:00');
	  $pid_num = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where pid = ".$GLOBALS['user_info']['id']." and create_time>=".$create_time);
	
      $user_info['pid_num']=$pid_num;
	// var_dump($user_info);exit;
	  $GLOBALS['tmpl']->assign("user_data",$user_info);
	  
	  $GLOBALS['tmpl']->assign("list_html",$list_html);
	  $GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_INDEX']);
	  $GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_INDEX']);		
	
	
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_activity_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function incharge()
	{
		//分享注册人id 
		$this->init_user();
		$user_info = $this->user_data;	
	    $GLOBALS['tmpl']->assign("lpl",$GLOBALS['user_info']['id']);  
	   //推荐人数
	     $create_time= strtotime('2015-04-15 00:00:00');
	  $pid_num = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where pid = ".$GLOBALS['user_info']['id']." and create_time>=".$create_time);
	
	  //有效推荐人数
	  $pid_n = $GLOBALS['db']->getAll("select id from ".DB_PREFIX."user where pid = ".$GLOBALS['user_info']['id']." and create_time>=". $create_time);
		$n=0;
	  foreach($pid_n as $k=>$v){
		$a=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load where user_id=".$v['id'] );
		
		if($a){
			$n++;
			}
		
		}
		
	 //获得推荐人浦发币
	  $pid_pfb = $GLOBALS['db']->getRow("select `pfcfb`,`referee_money` from ".DB_PREFIX."user where  id = ".$GLOBALS['user_info']['id']);
	     //时间	
	  //推荐人信息
      $pid_nm = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."user where create_time>".$create_time." and pid = ".$GLOBALS['user_info']['id']);
	  //注册时间
	
		foreach($pid_nm as $k=>$v){
			$pid_nm[$k]['create_time']=to_date($v['create_time'],'Y-m-d H:i:s');
			$a=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal_load where user_id=".$v['id'] );
			$pid_nm[$k]['deal_yn']=$a?'是':'否';
		}
		
	
	//好友是否以投资
	     $GLOBALS['tmpl']->assign("pid_pfb_j",$pid_pfb_j);
	   $GLOBALS['tmpl']->assign("pid_pfb",$pid_pfb);
	  $GLOBALS['tmpl']->assign("pid_n",$n);
	 $GLOBALS['tmpl']->assign("pid_num",$pid_num);
	  $GLOBALS['tmpl']->assign("pid_nm",$pid_nm);
	
      
      
	   
	
	  $GLOBALS['tmpl']->assign("list_html",$list_html);
	  $GLOBALS['tmpl']->assign("list_html",$list_html);
	  $GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_INDEX']);
	  $GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_INDEX']);		
	
	$user_info =$GLOBALS['user_info'];
	 $GLOBALS['tmpl']->assign("lpl",$GLOBALS['user_info']['id']);

	  	$user_info['pid_num']=$pid_num;
	  $GLOBALS['tmpl']->assign("user_data",$user_info);
	  
		
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_activity_incharge.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function incharge_log()
	{
		  $user_info_id =$GLOBALS['user_info']['id'];
$sql_no_limit = "select d.name,d.rate,d.repay_time,d.repay_time_type, dl.money as u_load_money,dl.virtual_money  from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id  left join ".DB_PREFIX."user_group as g on u.group_id = g.id where u.id=".$user_info_id ;
	
		$list_no_limit = $GLOBALS['db']->getAll($sql_no_limit);
		foreach($list_no_limit as $k=>$v)
		
		{
			$total_no_limit+=$v['u_load_money'];
			if($v['repay_time_type']==1){ //1表示月0表示日
	$list_no_limit[$k]['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/12)*$v['repay_time']*0.01,2);
			//计算利率
			}
			else{
	$list_no_limit[$k]['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/365)*$v['repay_time']*0.01,2);
			}
		}
  $get_money = 0;
  $time=get_gmtime();  
     	foreach($list_no_limit as $k=>$v)
		{
	      $get_money+=$v['get_money'];
	    }
	   $GLOBALS['tmpl']->assign("get_money",$get_money);//收益
	$money=$GLOBALS['user_info']['money']; 
	  $GLOBALS['tmpl']->assign("money",$money); //本金
	$referee_money=$GLOBALS['user_info']['referee_money']; //推荐奖励卷
	  $GLOBALS['tmpl']->assign("referee_money",$referee_money);
	
	$ecv_type_id=$GLOBALS['db']->getRow("select `ecv_type_id` from ".DB_PREFIX."ecv  where user_id=".$user_info_id." and ecv_type_id=27 and receive=1 and used_yn=0 and last_time >".$time);
	if($ecv_type_id){
	 $zhuce_ecv=$GLOBALS['db']->getRow("select `money` from ".DB_PREFIX."ecv_type where id=".$ecv_type_id['ecv_type_id']);
	}
	 $GLOBALS['tmpl']->assign("zhuce_ecv",$zhuce_ecv['money']);
 if($user_info_id>0){
	$nodeal=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_load where user_id=".$user_info_id);
  if($nodeal){ 	
	 $old=$GLOBALS['db']->getRow("select `ecv_type_id` from ".DB_PREFIX."ecv where user_id =".$user_info_id." and receive=1 and (ecv_type_id =31 or ecv_type_id=32 or ecv_type_id=33 or ecv_type_id=34)");
	 if($old){
	  $tuijiang=$GLOBALS['db']->getRow("select `money` from ".DB_PREFIX."ecv_type where id=".$old['ecv_type_id']);
	}
	}
	else{
	 $now=$GLOBALS['db']->getRow("select `ecv_type_id` from ".DB_PREFIX."ecv where user_id =".$user_info_id." and receive=1 and (ecv_type_id =23 or ecv_type_id=24 or ecv_type_id=25 or ecv_type_id=26)");
	 if($now){
	  $tuijiang=$GLOBALS['db']->getRow("select `money` from ".DB_PREFIX."ecv_type where id=".$now['ecv_type_id']);
	}
	}
 }
   // var_dump($tuijiang_money);exit;
 	  $GLOBALS['tmpl']->assign("tuijiang_money",$tuijiang['money']);//代金卷
 
	 
	  $GLOBALS['tmpl']->assign("list_html",$list_html);
	  $GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_INDEX']);
	  $GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_INDEX']);		
	 
	   $user_info =$GLOBALS['user_info'];
	   // print_r($user_info);exit;
		  $GLOBALS['tmpl']->assign("pfcfb",$GLOBALS['user_info']['pfcfb']);   
	  $GLOBALS['tmpl']->assign("lpl",$GLOBALS['user_info']['id']);
	  $GLOBALS['tmpl']->assign("user_data",$user_info);
		
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_activity_incharge_log.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function carry()
	{   $page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$result = get_voucher_list($limit,$GLOBALS['user_info']['id']);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		
		   //推荐人数
	  $create_time= strtotime('2015-04-15 00:00:00');
	  $pid_num = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where pid = ".$GLOBALS['user_info']['id']." and create_time>=".$create_time);
	  
		
	 	
	 
      	$time_dq=get_gmtime();
		$GLOBALS['tmpl']->assign("time_dq",$time_dq);
      

	
	  $GLOBALS['tmpl']->assign("list_html",$list_html);
	  $GLOBALS['tmpl']->assign("list_html",$list_html);
	
	  $GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_CENTER_INDEX']);
	  $GLOBALS['tmpl']->assign("post_title",$GLOBALS['lang']['UC_CENTER_INDEX']);		
	
	$user_info =$GLOBALS['user_info'];
	 $GLOBALS['tmpl']->assign("lpl",$GLOBALS['user_info']['id']);
	
	  	$user_info['pid_num']=$pid_num;
	  $GLOBALS['tmpl']->assign("user_data",$user_info);
		
		
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_activity_carry.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}


}
?>