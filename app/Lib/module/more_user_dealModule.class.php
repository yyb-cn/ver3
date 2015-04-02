<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统 
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/deal.php';
class more_user_dealModule extends SiteBaseModule
{
	public function dodie(){  
	// echo 1;exit;
	// var_dump($GLOBALS['user_info']['login_ip']);exit;
	// var_dump($mag_box_id);exit;
      if(!$GLOBALS['user_info']){
		      showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],3);
			}
	  if(trim($_REQUEST["shiwu"])=="" || !is_numeric($_REQUEST["shiwu"]) || floatval($_REQUEST["shiwu"])<=0){
			showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],3);
		}
		if((int)trim(app_conf('DEAL_BID_MULTIPLE')) > 0){
			 if(intval($_REQUEST["bid_money"])%(int)trim(app_conf('DEAL_BID_MULTIPLE'))!=0){
			 	showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],3);
			 	exit();
			 }
		}
		if(intval(trim($_REQUEST["shiwu"]))<1000){
		  showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],3);
		}	
        if(intval(trim($_REQUEST["shiwu"]))>$GLOBALS['user_info']['money']){
		  showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],3);
		}	   
		  if(!empty($_POST))
		  {
		  $money_obs=$GLOBALS['user_info']['money'];
		  $tshiwu=intval(ltrim($_REQUEST["shiwu"]));
		  $money_ob=$GLOBALS['user_info']['money']-$tshiwu;
		  $sql="select max(sort) from `fanwe_deal` where is_delete=0";
		  $maxs=$GLOBALS['db']->getRow($sql);
		  $max=$maxs['max(sort)']+1;
		  
		  
		  
		  
		  $name='用户id为'.$GLOBALS['user_info']['user_name'].'所投！'.$max;
		  $id=$GLOBALS['user_info']['id'];
		  // 标的信息
		  
		  if(intval(trim($_REQUEST["ob"]))==1){
		    $type=0;
		    $rates=8;
		    $date=15;
		  }
		  if(intval(trim($_REQUEST["ob"]))==2){
		    $rates=8.5;
		    $date=1;
			$type=1;
		  }
		  if(intval(trim($_REQUEST["ob"]))==3){
		    $rates=10.3;
		    $date=3;
			$type=1;
		  }	  
          if(!empty($tshiwu)){
		    $money=$tshiwu;
		    $deal_data['name']=$name;
            $deal_data['sub_name']=$name;	
            $deal_data['cate_id'] =4;
            $deal_data['user_id']=6;
            $deal_data['is_effect']=1;
            $deal_data['is_delete']=0 ;
            $deal_data['sort']=$max;         
            $deal_data['type_id']=10;
            $deal_data['borrow_amount']=$money;
            $deal_data['min_loan_money']=1000;    
            $deal_data['deal_status']=4;
            $deal_data['enddate']=1;
            $deal_data['create_time']=get_gmtime();
            $deal_data['update_time']=get_gmtime();
            $deal_data['name_match_row']=$name;
			$deal_data['is_has_loans']=1;
            $deal_data['buy_count']=1;
            $deal_data['loantype'] =0 ;
            $deal_data['warrant'] = 2 ;
            $deal_data['services_fee']=0;
			$deal_data['repay_time']=$date;
			$deal_data['rate']=$rates;
            $deal_data['repay_time_type']=$type; 
            $deal_data['load_money']=$deal_data['borrow_amount'];
            $deal_data['enddate']=1;
            $deal_data['start_time']=get_gmtime();
			$deal_data['is_send_half_msg']=1;
            $deal_data['success_time']=get_gmtime()+608;
            $deal_data['repay_start_time']=get_gmtime()+86400;
			if($type==1){
			$deal_data['next_repay_time']=get_gmtime()+$date*30*86400;
			$interest_money=$money*$date*$rates*30/36500;
			}
			if($type==0){
			$deal_data['next_repay_time']=get_gmtime()+$date*86400;
			$interest_money=$money*$date*$rates/36500;
			}
		 $next_repay_time=$deal_data['next_repay_time'];	
		$GLOBALS['db']->autoExecute(DB_PREFIX."deal",$deal_data,"INSERT");//插入一条投资目录
		   $deal_id= $GLOBALS['db']->insert_id();//获取插入的ID	
          }
	  
		  if($deal_id){
                $deal_repay['deal_id']=$deal_id;
				$deal_repay['user_id']=6;
                $deal_repay['repay_money']=$money+$interest_money;
                $deal_repay['repay_mamage']=0;
				$deal_repay['repay_time']=$next_repay_time;
				$deal_repay['repay_date']=date("Y-m-d",$next_repay_time);
				$deal_repay['interest_money']=$interest_money;
				$deal_repay['self_money']=$money;
           $GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$deal_repay,"INSERT");  
		        $deal_repay_id= $GLOBALS['db']->insert_id();//获取插入的ID	  	   
			   $log_a['log_info']="编号".$name."添加成功";
			   $log_a['log_time']=get_gmtime();
			   $log_a['log_admin']=1; 
			   $log_a['log_ip']=$GLOBALS['user_info']['login_ip']; 
			   $log_a['log_status']=1;
			   $log_a['module']='Deal';
			   $log_a['action']='insert';
			 $GLOBALS['db']->autoExecute(DB_PREFIX."log",$log_a,"INSERT");  //资金日记；	
			   $log_b['log_info']=$name."启用成功";
			   $log_b['log_time']=get_gmtime()+250;
			   $log_b['log_admin']=1; 
			   $log_b['log_ip']=$GLOBALS['user_info']['login_ip']; 
			   $log_b['log_status']=1;
			   $log_b['module']='Deal';
			   $log_b['action']='set_effect';
			 $GLOBALS['db']->autoExecute(DB_PREFIX."log",$log_b,"INSERT");  	//资金日记；		   
			    $user_lock_money_log_a['user_id']=$GLOBALS['user_info']['id'];
			    $user_lock_money_log_a['lock_money']=$money;
			    $user_lock_money_log_a['accont_lock_money']=$GLOBALS['user_info']['money'];
				$user_lock_money_log_a['memo']="['".$name."']的投标,付款单号".$deal_id;
				$user_lock_money_log_a['type']=2;
			    $user_lock_money_log_a['create_time']=get_gmtime();
				$user_lock_money_log_a['create_time_ymd']=date("Y-m-d",get_gmtime());
		       	$user_lock_money_log_a['create_time_ym']=date("Y-m",get_gmtime());
                $user_lock_money_log_a['create_time_y']=date("Y",get_gmtime());		
			   $GLOBALS['db']->autoExecute(DB_PREFIX."user_lock_money_log",$user_lock_money_log_a,"INSERT");  //冻结金额日记；
			   	$user_lock_money_log_b['user_id']=$GLOBALS['user_info']['id'];
			    $user_lock_money_log_b['lock_money']=-$money;
			    $user_lock_money_log_b['accont_lock_money']=$money_ob;
				$user_lock_money_log_b['memo']="['".$name."']的投标,投标成功";
				$user_lock_money_log_b['type']=2;
			    $user_lock_money_log_b['create_time']=get_gmtime();
				$user_lock_money_log_b['create_time_ymd']=date("Y-m-d",get_gmtime());
		       	$user_lock_money_log_b['create_time_ym']=date("Y-m",get_gmtime());
                $user_lock_money_log_b['create_time_y']=date("Y",get_gmtime());		
			   $GLOBALS['db']->autoExecute(DB_PREFIX."user_lock_money_log",$user_lock_money_log_b,"INSERT");    //冻结金额日记；
$group_key="0_1";
                $mag_boxa['content']="<p>感谢您使用p2p信贷贷款融资，很高兴的通知您，您于".date("Y-m-d",get_gmtime())."投标的借款列表-标名为！".$name."满标</p>";
		        $mag_boxa['to_user_id']=$GLOBALS['user_info']['id'];
				$mag_boxa['ceate_time']=get_gmtime();
		        $mag_boxa['group_key']=$group_key;
				$mag_boxa['is_notice']=16;
				$mag_boxa['is_read']=1;
		  $mag_box_id=$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$mag_boxa,"INSERT");  //msg  系统通知表
			   
			   
			   
			   
			   
			   
			   
			   
		        $deal_msg_list_a['dest']=$user_id_six['mobile'];
				$deal_msg_list_a['content']="尊敬的用户".$user_id_six['user_name']."，很高兴的通知您，您于".date("Y-m-d",get_gmtime())."发布的借款".$name."满标";
		        $deal_msg_list_a['send_time']=get_gmtime();
				$deal_msg_list_a['is_send']=1;
		        $deal_msg_list_a['ceate_time']=get_gmtime();
		        $deal_msg_list_a['user_id']=6;
		        $deal_msg_list_a['title']=$user_id_six['user_name']."的借款".$name."满标通知";
		   $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$deal_msg_list_a,"INSERT");             //投标通知/满标通知
		        $deal_msg_list_b['dest']=$user_id_six['mobile'];
				$deal_msg_list_b['content']="尊敬的用户".$user_id_six['user_name']."，很高兴的通知您，您于".date("Y-m-d",get_gmtime())."所投的借款".$name."满标通知";
		        $deal_msg_list_b['send_time']=get_gmtime();
				$deal_msg_list_b['is_send']=1;
		        $deal_msg_list_b['ceate_time']=get_gmtime();
		        $deal_msg_list_b['user_id']=$GLOBALS['user_info']['id'];
		        $deal_msg_list_b['title']=$GLOBALS['user_info']['user_name']."的投资".$name."满标通知";
		   $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$deal_msg_list_b,"INSERT");          //投标通知/满标通知
		   
		        $user_log_a['log_info']=$name."的投标,付款单".$deal_id;
			    $user_log_a['log_time']=get_gmtime();
			    $user_log_a['log_admin_id']=1;
			    $user_log_a['money']=-$money;
				$user_log_a['lock_money']=$money;
		        $user_log_a['user_id']=$GLOBALS['user_info']['id'];		  
		  $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$user_log_a,"INSERT");       //会员个人中心
	            $user_log_b['log_info']=$name."招标成功".$deal_id;
			    $user_log_b['log_time']=get_gmtime();
			    $user_log_b['log_admin_id']=1;
			    $user_log_b['money']=$money;
				$user_log_b['lock_money']=$money;
		        $user_log_b['user_id']=6;		  
		  $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$user_log_b,"INSERT");  //会员个人中心
	            $user_log_c['log_info']=$name."投标成功";
			    $user_log_c['log_time']=get_gmtime();
			    $user_log_c['log_admin_id']=1;
			    $user_log_c['money']=0;
				$user_log_c['lock_money']=-$money;
		        $user_log_c['user_id']=$GLOBALS['user_info']['id'];		  
		  $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$user_log_c,"INSERT");   //会员个人中心
		  
		        $user_money_log_a['user_id']=$GLOBALS['user_info']['id'];	
				$user_money_log_a['money']=-$money;
				$user_money_log_a['account_money']=$money_ob;
	            $user_money_log_a['memo']=$name."的投标,付款单".$deal_id;
			    $user_money_log_a['type']=2;
			    $user_money_log_a['create_time']=get_gmtime();
				$user_money_log_a['create_time_ymd']=date("Y-m-d",get_gmtime());
		       	$user_money_log_a['create_time_ym']=date("Y-m",get_gmtime());
                $user_money_log_a['create_time_y']=date("Y",get_gmtime());				
		  $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$user_money_log_a,"INSERT");     //会员个人中心
		        $user_money_log_b['user_id']=6;	
				$user_money_log_b['money']=$money;
				$user_money_log_b['account_money']=$now_id_six_money;
	            $user_money_log_b['memo']=$name."招标成功";
			    $user_money_log_b['type']=3;
			    $user_money_log_b['create_time']=get_gmtime();
				$user_money_log_b['create_time_ymd']=date("Y-m-d",get_gmtime());
		       	$user_money_log_b['create_time_ym']=date("Y-m",get_gmtime());
                $user_money_log_b['create_time_y']=date("Y",get_gmtime());				
		  $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$user_money_log_b,"INSERT");    //会员个人中心
		        $user_money_log_c['user_id']=6;	
				$user_money_log_c['money']=0;
				$user_money_log_c['account_money']=$now_id_six_money;
	            $user_money_log_c['memo']=$name."服务费";
			    $user_money_log_c['type']=14;
			    $user_money_log_c['create_time']=get_gmtime();
				$user_money_log_c['create_time_ymd']=date("Y-m-d",get_gmtime());
		       	$user_money_log_c['create_time_ym']=date("Y-m",get_gmtime());
                $user_money_log_c['create_time_y']=date("Y",get_gmtime());				
		  $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$user_money_log_c,"INSERT");    //会员个人中心
		 
		      $deal=get_deal($deal_id);
		      $data['user_id'] = $user_id=$id;
		      $data['user_name'] = $GLOBALS['user_info']['user_name'];
		      $data['deal_id'] = $deal_id;
		      $data['money'] = $money;
		      $data['create_time'] = get_gmtime();
			  $data['is_has_loans'] =1;
		 $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",$data,"INSERT");   //投标记录
         $deal_load_id = $GLOBALS['db']->insert_id();//获取插入的ID
		if($deal_load_id > 0){
               $deal_load_repay['deal_id']=$deal_id;
               $deal_load_repay['user_id']=$GLOBALS['user_info']['id'];
               $deal_load_repay['self_money']=$money;
               $deal_load_repay['repay_money']=$money+$interest_money;
               $deal_load_repay['repay_time']=$next_repay_time;
               $deal_load_repay['repay_date']=date("Y-m-d",$next_repay_time);
               $deal_load_repay['interest_money']=$interest_money;
               $deal_load_repay['repay_id']=deal_repay_id;
               $deal_load_repay['load_id']=deal_load_id;
               $deal_load_repay['repay_mamage']=0;
               $deal_load_repay['loantype']=2;			   
          $GLOBALS['db']->autoExecute(DB_PREFIX."deal_load_repay",$deal_load_repay,"INSERT");   //还款表
		
			}
			$GLOBALS['db']->query("update ".DB_PREFIX."user set `money`=".$money_ob." where id = ".$id);
			showSuccess($GLOBALS['lang']['DEAL_BID_SUCCESS'],3,url("index","deal",array("id"=>$deal_id)));
		}
		else{
			showErr($GLOBALS['lang']['ERROR_TITLE'],3);
		}
	     }
     }

  

 
 
 
 
}

?>
