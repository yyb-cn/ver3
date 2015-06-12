<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
	header("Content-Type:text/html;charset=utf-8");
class huodongModule extends SiteBaseModule
{
	public function index()
	{

		//投资排行榜
  	$time='2015-06-13 00:00:00';
	$create_time=strtotime($time);
	$touzipanghangbang=$GLOBALS['db']->getAll("select SUM(dl.money) as t_m,u.user_name,u.mobile from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."user u on dl.user_id=u.id  where dl.create_time>".$create_time." group by user_id order by t_m desc limit 8" );	
	foreach($touzipanghangbang as $k=>$v){
	$touzipanghangbang[$k]['sec']=$k+1;
		$touzipanghangbang[$k]['user_name']=cut_str($v['user_name'], 1, 0).'***'.cut_str($v['user_name'], 1, -1);
		$touzipanghangbang[$k]['mobile']=cut_str($v['mobile'], 3, 0).'***'.cut_str($v['mobile'], 2, -2);
	}
	$GLOBALS['tmpl']->assign("touzipanghangbang",$touzipanghangbang);  //最高投资金额  
	 // var_dump($touzipanghangbang);exit;
	$oneaaa= $GLOBALS['db']->getAll("select pid, count(*) num  from ".DB_PREFIX."user  where create_time>".$create_time." and pid<>0 and id<>pid  group by pid  order by num desc limit 5" );
	
	foreach($oneaaa as $k=>$v){
	$a=$GLOBALS['db']->getRow("select user_name,id,pid  from ".DB_PREFIX."user where id=".$v['pid'] );
	// if($a['id']!=$a['pid']){

		$new_rank[$k]['user_name']=cut_str($a['user_name'], 1, 0).'***'.cut_str($a['user_name'], 1, -1);
		$new_rank[$k]['num']=$v['num'];
		
		// }
	}
// var_dump($new_rank);exit;
	$GLOBALS['tmpl']->assign("tuijianpanghangbang",$new_rank); //推荐彷徨帮
	// var_dump($new_rank);exit;

	$one= $GLOBALS['db']->getAll("select MAX(money),`user_id`   from ".DB_PREFIX."deal_load  where create_time>".$create_time );
	$max_money=$one[0]['MAX(money)'];
	$user_sb=$one[0]['user_id'];
	if($user_sb){
	$a=$GLOBALS['db']->getRow("select user_name from ".DB_PREFIX."user where id=".$user_sb );
	$max_user=$a['user_name'];    //投资最多者
	}
	
	

	$onesss= $GLOBALS['db']->getAll("select `deal_id`   from ".DB_PREFIX."deal_load where create_time>".$create_time  ." group by deal_id desc  " );
	foreach($onesss as $k=>$v){
	 $first_all[]=$GLOBALS['db']->getRow("select min(id) min,deal_id,user_name,money,create_time  from ".DB_PREFIX."deal_load where create_time>".$create_time." and deal_id=".$v['deal_id']);
    //$first_all 活动开始，最少投标者、标id
	$deal=$GLOBALS['db']->getRow("select deal_status  from ".DB_PREFIX."deal where  id=".$v['deal_id'] );
	if($deal['deal_status']!=0 && $deal['deal_status']!=1){ //标的最后一名投资者\
	 $last=$GLOBALS['db']->getRow("select max(id) max  from ".DB_PREFIX."deal_load where create_time>".$create_time." and deal_id=".$v['deal_id'] ); 
	 $last_all[]=$GLOBALS['db']->getRow("select id,user_name,money,create_time  from ".DB_PREFIX."deal_load where create_time>".$create_time." and id=".$last['max'] ); 
	 
	}
	}
	// $dare=array();
    foreach($last_all as $k=>$v){
	  $last_all[$k]['create_time']=date("Y-m-d",$v['create_time']);
	
	}
    foreach($first_all as $k=>$v){
	  $first_all[$k]['create_time']=date("Y-m-d",$v['create_time']);
	
	}
	
 // var_dump($last_all);	exit;
	$GLOBALS['tmpl']->assign("last_all",$last_all);  //标 第一个投资者
	$GLOBALS['tmpl']->assign("first_all",$first_all);  //标 第一个投资者
	
	$GLOBALS['tmpl']->assign("max_money",$max_money);  //最高投资金额  
	$GLOBALS['tmpl']->assign("max_user",$max_user); //最高投资用户名
	
	$GLOBALS['tmpl']->display("page/May/may_huodong.html");
	}

	
	
	
	
	
	
	
	
        public function huodong5 ()
        {
            
            $GLOBALS['tmpl']->display("get_voucher_new.html");  
            
        }
        
        
}

?>