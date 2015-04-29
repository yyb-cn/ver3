<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class pfcfbAction extends CommonAction{

	public function index()
	{
	
	
	
	$pfcfbs = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."pfcfb_huodong");
	$pfcfbss= $GLOBALS['db']->getRow("select * from ".DB_PREFIX."pfcfb_huodong where id=1");
	$time=get_gmtime();
	if($time<$pfcfbss['end_time'] && $pfcfbss['open_off']==1 && $time>$pfcfbss['start_time']){

	
	}
	
	
	
	
	
	
	
	 // var_dump($pfcfbss);exit;
	 $data=array();
	foreach($pfcfbs as $k=>$v){
	  $data['start_time']=date("Y-m-d,H:i:s",$v['start_time']);
	  $pfcfbs[$k]['start_time']=$data['start_time'];
	  $data['end_time']=date("Y-m-d,H:i:s",$v['end_time']);
	  $pfcfbs[$k]['end_time']=$data['end_time'];
	
	}
	$this->assign("pfcfb_list",$pfcfbs);
		 // var_dump($pfcfb_list);exit;
		$this->display();
	}
	public function update_pfcfb()
	{
   // var_dump($_REQUEST);exit;
   //成为理财人就送
     if($_REQUEST['Financial_planner_one']){
	 // xzecho 1;exit;
     if($_REQUEST['zc_end_time']<0){
      $this->error("请正确选择时间");
    }	
     if($_REQUEST['zc_song_pfcfb']<=0){
      $this->error("请输入注册所送浦发币");
    }		
	 
     $start=strtotime($_REQUEST['zc_start_time']);
	 $end_time=strtotime($_REQUEST['zc_end_time']);
     $data['id']=1;
	 $data['start_time']=$start;
	 $data['song_pfcfb']=$_REQUEST['zc_song_pfcfb'];
	 $data['open_off']=$_REQUEST['Financial_planner_one'];
	  $data['end_time']=$end_time;
	if(!$ov=$GLOBALS['db']->autoExecute(DB_PREFIX."pfcfb_huodong",$data,"UPDATE","id=".$data['id'])){
        $this->error("设置失败");
	 }
	 }
	 
	 //投资就送
     if($_REQUEST['Financial_planner_twe']){
     if($_REQUEST['tz_end_time']<0){
      $this->error("请正确选择时间");
    }		
     if($_REQUEST['tz_song_pfcfb']<=0){
      $this->error("请输入投资所送浦发币");
    }		
	 
     $tz_start=strtotime($_REQUEST['tz_start_time']);
	 $tz_end_time=strtotime($_REQUEST['tz_end_time']);
     $data_o['id']=2;
	 $data_o['start_time']=$tz_start;
	 $data_o['song_pfcfb']=$_REQUEST['tz_song_pfcfb'];
	 $data_o['open_off']=$_REQUEST['Financial_planner_twe'];
	 $data_o['end_time']=$tz_end_time;
	if(!$op=$GLOBALS['db']->autoExecute(DB_PREFIX."pfcfb_huodong",$data_o,"UPDATE","id=".$data_o['id'])){
			$this->error("设置失败");
	 }
	 }

	 if($ov && $op){
	    $this->success("设置成功");
	 
	 }
	 $this->error("请选着开关");

	}

}
?>