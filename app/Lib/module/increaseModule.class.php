<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/deal.php';
class increaseModule extends SiteBaseModule
{
             //进入领加息劵规则页面
	public function in_add(){
	
	
	    $u_info = get_user("*",$GLOBALS['user_info']['id']);
		
		$id=$u_info['id'];
	
			
		//用户抽奖的记录
		$user_increase_record=$GLOBALS['db']->getAll("SELECT `create_time` FROM ".DB_PREFIX."user_increase where user_id=".$id);
		 $arr=array();
		foreach($user_increase_record as $k=>$v){
			$date=date('Y-m-d H:i:s',$v['create_time']);
			 
					$arr[]=$date;	
					
					}
		
		//用户拥有没使用的加息劵利率查询;
		$user_increase_yields=$GLOBALS['db']->getAll("SELECT `yields`,`id` FROM ".DB_PREFIX."user_increase where is_used='0' and user_id=".$id);
		
		
		
		
		
		//获取当前的标的ID进行查询
	
		$deal_id=$_POST['deal_id'];
		
		
		
	
	
	
		//标名称
	 $deal_name=$GLOBALS['db']->getOne("SELECT `name` FROM ".DB_PREFIX."deal where id=".$deal_id); 

		
		$GLOBALS['tmpl']->assign("user_id",$id);
		$GLOBALS['tmpl']->assign("deal_name",$deal_name);
		$GLOBALS['tmpl']->assign("deal_id",$deal_id);
		$GLOBALS['tmpl']->assign("user_increase_yields",$user_increase_yields);
		$GLOBALS['tmpl']->assign("arr",$arr);
		$GLOBALS['tmpl']->assign("u_info",$u_info);
		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->display("page/increase.html");
	}
	
	
	
	
	
	
	
	public function every_lottery()
	{  
	//获取领奖人加息劵的ID
	 $id=$GLOBALS['user_info']['id'];
		
	 
	 //获取用户最新领取加息劵的时间，和当前时间进行判断用户当天是否领取过加息劵；
	 $max_id=$GLOBALS['db']->getOne("SELECT max(id) FROM ".DB_PREFIX."user_increase where user_id=".$id); 
     $log_time=$GLOBALS['db']->getOne("SELECT `create_time` FROM ".DB_PREFIX."user_increase where id =".$max_id);
	 
    $log_data=date('Y-m-d',$log_time);
	$now_time=date('Y-m-d',get_gmtime()); 

	//判断今天是否领取过加息劵
	if($log_data==$now_time){
	  echo 2;exit;
	 }
			
	  $award = array(
			   ////// 奖品ID => array('奖品名称',概率)
			   1 => array('1.00',0.30),
			   2 => array('2.00',0.15),
			   3 => array('3.00',0.15),
			   4 => array('4.00',0.10),
			   5 => array('5.00',0.10),
			   6 => array('6.00',0.8),
			   7 => array('7.00',0.6),
			   8 => array('8.00',0.6),
			  );
			  //获取一个随机数;
			  $r =rand(1,100);
			  $num = 0;
			  $award_id = 0;
	
		 foreach($award as $k=>$v){
			 $tmp = $num;
			 $num += $v[1]*100;
			 if($r>$tmp && $r<=$num){
			$award_id = $k;
		
			break;
		      }
	     }
		 
		 
	
	  	
		
	     //会员ID
      $user_id=$id;
      //抽奖获得
	  $create_source=1;
	  //获取当前领取加息劵时间
	 $create_time=get_gmtime();
	 
	 //获取抽到的加息劵利率;
	  $yields=$award[$award_id][0];
	  //加息劵到期时间
     $expires_time=strtotime("4 day 8 may 2015");
	
	
	
	 $sql="insert into `fanwe_user_increase`(`user_id`,`create_source`,`create_time`,`yields`,`expires_time`) values('$user_id','$create_source','$create_time','$yields','$expires_time')";
	    //执行一条AQL语句的添加;
   	      mysql_query($sql);
		
		
     
	  
	  
	
	  $lottery_log_id= mysql_affected_rows();		
      $data=array('award_id'=>$award_id,'award_name'=>$award[$award_id][0]);
		 if($lottery_log_id>0){
		
		
		
			echo json_encode($data);
			
		 }else{
		   echo 1;
		 }
      
		
		
		
	}
	
	public function increase_use(){
		
	
		
		if($_POST){
		
			  //使用的加息劵的ID
			$id=$_POST['user_increase_id'];
			
			//加息劵使用到标的ID
		    $user_increase_target_id=$_POST['deal_id'];
			
			//使用的加息劵利率
			$user_increase_yields=$_POST['user_increase_yields'];
			//使用加息劵的会员ID
			$user_id=$_POST['user_id'];
			
			
			//当前使用加息劵的时间；
		    $data['used_time']=get_gmtime();
			//改is_used为1；
			$data['is_used']=1;
			//加息劵使用到标的ID
			$data['target_id']=$user_increase_target_id;
		
		
		
		
		
		//查询投标时的投资金额
		
		$user_rate=$GLOBALS['db']->getOne("SELECT `money` FROM ".DB_PREFIX."deal_load where user_id=".$user_id);
		
       	$frist = substr($user_rate, 0, -3 );
		//查询投标时的投资金额乘以当前使用的利率除以360得到利息；
		$accrual=($frist*($user_increase_yields/100))/365;
		//round() 对浮点数进行四舍五入;
		//最后得到的利息；
	    $accrual=round($accrual, 2);
		
		$data['money']=$accrual;
	
		
	
			}
			      //查询用户是否对当前使用加息劵的标投过标；
				$user_deal=$GLOBALS['db']->getOne("SELECT `id` FROM ".DB_PREFIX."deal_load where user_id='$user_id' and deal_id=".$user_increase_target_id);
               
			if($user_deal){
				 
		$one=$GLOBALS['db']->autoExecute(DB_PREFIX."user_increase",$data,"UPDATE","id=".$id);
		
			if($one==1){
		
	
		showSuccess('加息劵使用成功',intval($_REQUEST['is_ajax']),'index.php');   
		
		
		
		}else{
	
	showERR('加息劵使用失败',intval($_REQUEST['is_ajax']),'index.php');
	}
		
		}else{
			
			
			showERR('你没对当前标进行投资不能使用加息劵',intval($_REQUEST['is_ajax']),'index.php');
			
			
			
			
			}
	
	}
	
}
?>
