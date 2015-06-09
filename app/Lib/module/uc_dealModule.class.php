<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';
require APP_ROOT_PATH."app/Lib/deal.php";
class uc_dealModule extends SiteBaseModule
{
	public function refund(){
	
	
	
	

		$user_id = $GLOBALS['user_info']['id'];
		
		$status = intval($_REQUEST['status']);
		
		$GLOBALS['tmpl']->assign("status",$status);
		
		//输出借款记录
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			
		$deal_status = 4;
		if($status == 1){
			$deal_status = 5;
		}
		$deal_name=trim($_REQUEST['deal_name']);
		$result = get_deal_list($limit,0,"deal_status =$deal_status AND user_id=".$user_id,"id DESC");
		if($deal_name!='')
		{
		$result = get_deal_list($limit,0,"deal_status =4 AND name='$deal_name' and user_id=".$user_id,"id DESC");
		}
		$deal_ids = array();
		foreach($result['list'] as $k=>$v){
			if($v['repay_progress_point'] >= $v['generation_position'])
				$result['list'][$k]["can_generation"] = 1;
			
			$deal_ids[] = $v['id'];
		}
		if($deal_ids){
			$temp_ids = $GLOBALS['db']->getAll("SELECT `deal_id`,`status` FROM ".DB_PREFIX."generation_repay_submit WHERE deal_id in(".implode(",",$deal_ids).") ");
			$deal_g_ids = array();
			foreach($temp_ids as $k=>$v){
				$deal_g_ids[$v['deal_id']] = $v;
			}
		
		
			foreach($result['list'] as $k=>$v){
				if(isset($deal_g_ids[$v['id']])){
					//申请中
					$result['list'][$k]['generation_status'] = $deal_g_ids[$v['id']]['status'] + 1; 
				}
			}
		}
		// var_dump($result['list']);exit;
		$GLOBALS['tmpl']->assign("deal_list",$result['list']);
		
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_refund.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	
	
//电子合同
	public function contract(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！");
		}
		$deal = get_deal($id);
		if(!$deal){
			showErr("操作失败！");
		}
		$load_user_id = $GLOBALS['db']->getOne("select user_id FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." and user_id=".$GLOBALS['user_info']['id']." ORDER BY create_time ASC");
		if($load_user_id == 0  && $deal['user_id']!=$GLOBALS['user_info']['id'] ){
			showErr("操作失败！");
		}
		if($deal['agency_id'] > 0){
			$agency = $GLOBALS['db']->getRow("select * FROM ".DB_PREFIX."deal_agency WHERE id=".$deal['agency_id']." ");
			$deal['agency_name'] = $agency['name'];
			$deal['agency_address'] = $agency['address'];
		}
		
		$GLOBALS['tmpl']->assign('deal',$deal);
		
		$loan_list = $GLOBALS['db']->getAll("select * FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." ORDER BY create_time ASC");
		foreach($loan_list as $k=>$v){
			$vv_deal['borrow_amount'] = $v['money'];
			$vv_deal['rate'] = $deal['rate'];
			$vv_deal['repay_time'] = $deal['repay_time'];
			$vv_deal['loantype'] = $deal['loantype'];
			$vv_deal['repay_time_type'] = $deal['repay_time_type'];
			
			$deal_rs =  deal_repay_money($vv_deal);
			$loan_list[$k]['get_repay_money'] = $deal_rs['month_repay_money'];
			if(is_last_repay($deal['loantype']))
				$loan_list[$k]['get_repay_money'] = $deal_rs['remain_repay_money'];
		}
		
		$GLOBALS['tmpl']->assign('loan_list',$loan_list);
		
		
	
		$u_info = get_user("*",$deal['user_id']);
		$GLOBALS['tmpl']->assign('user_info',$u_info);
		if($u_info['sealpassed'] == 1){
			$credit_file = get_user_credit_file($deal['user_id']);
			$GLOBALS['tmpl']->assign('user_seal_url',$credit_file['credit_seal']['file_list'][0]);
		}
		
		if($deal['agency_id'] > 0){
			$contract = $GLOBALS['tmpl']->fetch("str:".app_conf("CONTRACT_1"));
		}
		else
			$contract = $GLOBALS['tmpl']->fetch("str:".app_conf("CONTRACT_0"));
		
		
		$GLOBALS['tmpl']->assign('contract',$contract);
		
		$GLOBALS['tmpl']->display("inc/uc/uc_deal_contract.html");	
	}
	
	
	//电子合同
	public function dcontract(){
		$id = intval($_REQUEST['id']);
		if($id == 0){
			showErr("操作失败！");
		}
		$deal = get_deal($id);
		if(!$deal){
			showErr("操作失败！");
		}
		$load_user_id = $GLOBALS['db']->getOne("select user_id FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." and user_id=".$GLOBALS['user_info']['id']." ORDER BY create_time ASC");
		if($load_user_id == 0  && $deal['user_id']!=$GLOBALS['user_info']['id'] ){
			showErr("操作失败！");
		}
		if($deal['agency_id'] > 0){
			$agency = $GLOBALS['db']->getRow("select * FROM ".DB_PREFIX."deal_agency WHERE id=".$deal['agency_id']." ");
			$deal['agency_name'] = $agency['name'];
			$deal['agency_address'] = $agency['address'];
		}
	
		$GLOBALS['tmpl']->assign('deal',$deal);
	
		$loan_list = $GLOBALS['db']->getAll("select * FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id." ORDER BY create_time ASC");
		foreach($loan_list as $k=>$v){
			$vv_deal['borrow_amount'] = $v['money'];
			$vv_deal['rate'] = $deal['rate'];
			$vv_deal['repay_time'] = $deal['repay_time'];
			$vv_deal['loantype'] = $deal['loantype'];
			$vv_deal['repay_time_type'] = $deal['repay_time_type'];
			
			$deal_rs =  deal_repay_money($vv_deal);
			$loan_list[$k]['get_repay_money'] = $deal_rs['month_repay_money'];
			if(is_last_repay($deal['loantype']))
				$loan_list[$k]['get_repay_money'] = $deal_rs['remain_repay_money'];
		}
	
		$GLOBALS['tmpl']->assign('loan_list',$loan_list);
	
	
	
		$u_info = get_user("*",$deal['user_id']);
		$GLOBALS['tmpl']->assign('user_info',$u_info);
		if($u_info['sealpassed'] == 1){
			$credit_file = get_user_credit_file($deal['user_id']);
			$GLOBALS['tmpl']->assign('user_seal_url',$credit_file['credit_seal']['file_list'][0]);
		}
	
		if($deal['agency_id'] > 0){
			$contract = $GLOBALS['tmpl']->fetch("str:".app_conf("CONTRACT_1"));
		}
		else
			$contract = $GLOBALS['tmpl']->fetch("str:".app_conf("CONTRACT_0"));
	
	
		$GLOBALS['tmpl']->assign('contract',$contract);
		/*header("Content-type:text/html;charset=utf-8");
		header("Content-Disposition: attachment; filename=借款协议.html");
		
		echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>";
		echo '<html>';
		echo '<head>';
		echo '<title>借款协议</title>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
		echo '<meta http-equiv="X-UA-Compatible" content="IE=7" />';
		echo  $GLOBALS['tmpl']->fetch("inc/uc/uc_deal_contract.html");
		echo '</body>';
		echo '</html>';*/
		require APP_ROOT_PATH."/system/utils/word.php";
    	$word = new word(); 
   		$word->start(); 
   		$wordname = "借款协议.doc"; 
   		echo  $GLOBALS['tmpl']->fetch("inc/uc/uc_deal_contract.html");
   		$word->save($wordname); 
		
	}
	
	
	
	//正常还款操作界面
	public function quick_refund(){
		//标的ID
		$id = intval($_REQUEST['id']);
		
		if($id == 0){
			showErr("操作失败！");
		}
		$deal = get_deal($id);
		if(!$deal)
		{
			showErr("借款不存在！");
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款！");
		}
		if($deal['deal_status']!=4){
			showErr("借款不是还款状态！");
		}
		
		$GLOBALS['tmpl']->assign('deal',$deal);
		
		
		
		
		
		//最后还款的时候吧是用来加息劵的利息加进去

		//当前登陆的用户ID
		$user_id=$deal['user_id'];
		//用户对标使用了加息劵的标的利息；
		$user_money=$GLOBALS['db']->getAll("SELECT `money` FROM ".DB_PREFIX."user_increase where target_id='$id' and is_used='1' and target_id=".$id);
				
		//根据用户ID查询还款deal_load_repay表获得ID
	    $pay_id=$GLOBALS['db']->getAll("SELECT `id` FROM ".DB_PREFIX."deal_repay where deal_id=".$id);
		foreach($pay_id as $k=>$v){
			
			
		} 
		
		//差最后一次还款的那一条的ID
		$repay_id=$GLOBALS['db']->getOne("SELECT `id` FROM ".DB_PREFIX."deal_repay where l_key='$k' and deal_id=".$id);
		
		
		//判断使用了加息劵的金额
		if($user_money){
		//进行所有的是用来加息劵的利息加起来；
		$r=0;
		foreach($user_money as $k=>$v){
			
			
			
		
		for($i=0;$i<=$k;$i++){
			
			$r=$r+$user_money[$i]['money'];
			
		}
		
			
            }
			
			//使用加息劵的总利息$r加没使用加息劵$user_statics['load_repay_money']的金额；
			$money=$r;
			$data['incerease_money']=$money;
		 
			//进行修改
			$one=$GLOBALS['db']->autoExecute(DB_PREFIX."deal_repay",$data,"UPDATE","id=".$repay_id);
	
		
			}
	
		

		//还款列表
		$loan_list = get_deal_load_list($deal);
	// var_dump($loan_list);exit;
        		// $deal_repay_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_repay where deal_id=".$deal['id']." order by l_key ASC ");
			 // var_dump($deal_repay_list);exit;	
		$GLOBALS['tmpl']->assign("loan_list",$loan_list);
		$GLOBALS['tmpl']->assign("deal_id",$id);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_quick_refund.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	
	//正常还款执行界面
	public function repay_borrow_money(){
	
		$id = intval($_REQUEST['id']);
		$ids = strim($_REQUEST['ids']);
			// showErr("$ids",1);
		$paypassword = strim(FW_DESPWD($_REQUEST['paypassword']));
		if($paypassword==""){
			showErr($GLOBALS['lang']['PAYPASSWORD_EMPTY'],1);
		}		
		// if(md5($paypassword)!=$GLOBALS['user_info']['paypassword']){
			// showErr($GLOBALS['lang']['PAYPASSWORD_ERROR'],1);
		// }
		$deal_loadxx=$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_load where deal_id=".$id);
		$deal_s=$GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal where id=".$id);
		$deal_key=$deal_s['repay_time']-1;
		$status = getUcRepayBorrowMoney($id,$ids);
	
	// $status['status']=1;
	// $status['show_err']="yes";
		if ($status['status'] == 2){
			ajax_return($status);
			die();
		}
		elseif ($status['status'] == 0){
			showErr($status['show_err'],1);
		}else{
/*
- 	//除本金外的虚拟币还款都在这 在这啊在这啊在这啊‘ pfcfb\refree_money	
- // reapy_time 投资时间  repay_time_type 天或者月  rate 年利息  
-  modify_account  加减资金
*/
  if($deal_loadxx)
	{	
	   foreach($deal_loadxx as $k=>$v)
	   {
	if($deal_s['repay_time_type']==0) //标的日期为天
     {
		if($v['unjh_pfcfb']!=0)
		{  
       		
      $user_pfcfb=$v['unjh_pfcfb']+$v['unjh_pfcfb']*$deal_s['repay_time']*$deal_s['rate']/36500; 
    modify_account(array("pfcfb"=>$user_pfcfb),$v['user_id'],"[<a href='".$deal_s['url']."' target='_blank'>".$deal_s['name']."</a>],现金红包回报本息",5);
		}
		if($v['virtual_money']!=0)
		{  	 		
    modify_account(array("money"=>$v['virtual_money']),$v['user_id'],"[<a href='".$deal_s['url']."' target='_blank'>".$deal_s['name']."</a>],代金卷回报本息",5);
		}
	 }
	 
	 
	 
	if($deal_s['repay_time_type']==1) //标的日期为月
     {	 
      if($deal_s['loantype']!=2)    
	   {
		if($v['virtual_money']!=0)
		{ 
	 if($deal_s['create_time']<1429027200)
        {	
        $virtual_money=	$v['virtual_money']*$deal_s['rate']/1200;
    modify_account(array("money"=>$virtual_money),$v['user_id'],"[<a href='".$deal_s['url']."' target='_blank'>".$deal_s['name']."</a>],代金卷回报本息",5);
		}
     }		
 if($ids==$deal_key)  //判断是否是最后一次执行还款
   {	  
		if($v['unjh_pfcfb']!=0)
		{  	 		
      $user_pfcfb=$v['unjh_pfcfb']+$v['unjh_pfcfb']*$deal_s['repay_time']*$deal_s['rate']/1200; 
    modify_account(array("pfcfb"=>$user_pfcfb),$v['user_id'],"[<a href='".$deal_s['url']."' target='_blank'>".$deal_s['name']."</a>],现金红包回报本息",5);
		}
		if($v['virtual_money']!=0)
		{ 
	 if($deal_s['create_time']>1429027200)
        {			
    modify_account(array("money"=>$v['virtual_money']),$v['user_id'],"[<a href='".$deal_s['url']."' target='_blank'>".$deal_s['name']."</a>],代金卷回报本息",5);
		}		
	}
     }	
	  }
	  
      if($deal_s['loantype']==2)  // 到期还本息款
	   {	   
		if($v['unjh_pfcfb']!=0)
		{  	 		
      $user_pfcfb=$v['unjh_pfcfb']+$v['unjh_pfcfb']*$deal_s['rate']*$deal_s['repay_time']/1200; 
    modify_account(array("pfcfb"=>$user_pfcfb),$v['user_id'],"[<a href='".$deal_s['url']."' target='_blank'>".$deal_s['name']."</a>],现金红包回报本息",5);
		}
		if($v['virtual_money']!=0)
		{  	 		
	 if($deal_s['create_time']<1429027200)
        {	
        $virtual_money=	$v['virtual_money']*$deal_s['rate']/1200;
       $virtual_moneys=$virtual_money*$deal_s['repay_time'];	
    modify_account(array("money"=>$virtual_moneys),$v['user_id'],"[<a href='".$deal_s['url']."' target='_blank'>".$deal_s['name']."</a>],代金卷回报本息",5);
		}
	 if($deal_s['create_time']>1429027200)
        {			
    modify_account(array("money"=>$v['virtual_money']),$v['user_id'],"[<a href='".$deal_s['url']."' target='_blank'>".$deal_s['name']."</a>],代金卷回报本息",5);
		}
		}
	  } 
	 }
	  }
    }	
	
	// 虚拟币还款结束 xiaohuya	
			showSuccess($status['show_err'],1);
		}
				
	}
	
	//提前还款操作界面
	public function inrepay_refund(){
		$id = intval($_REQUEST['id']);		
		
		
		$status = getUcInrepayRefund($id);
	      //使用加息劵的利息查询
		$id=$status['deal']['id'];
		  $pay=$GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_repay where  deal_id='$id' order by id desc");
		     
		if ($status['status'] == 1){		
			//$deal = $status['deal'];
			$GLOBALS['tmpl']->assign("incere", $pay['incerease_money']);
			$GLOBALS['tmpl']->assign("deal",$status['deal']);
			$GLOBALS['tmpl']->assign("true_all_manage_money",$status['true_all_manage_money']);
			
			$GLOBALS['tmpl']->assign("impose_money",$status['impose_money']);
			$GLOBALS['tmpl']->assign("total_repay_money",$status['total_repay_money']);
						
			$GLOBALS['tmpl']->assign("true_total_repay_money",$status['true_total_repay_money']);
			
			$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
			$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_inrepay_refund.html");
			$GLOBALS['tmpl']->display("page/uc.html");	
		}else{
			showErr($status['show_err']);
		}
	}
	//提前还款执行程序
	public function inrepay_repay_borrow_money(){
		$id = intval($_REQUEST['id']);
		$paypassword = strim(FW_DESPWD($_REQUEST['paypassword']));
		if($paypassword==""){
			showErr($GLOBALS['lang']['PAYPASSWORD_EMPTY'],1);
		}
		// if(md5($paypassword)!=$GLOBALS['user_info']['paypassword']){
			// showErr($GLOBALS['lang']['PAYPASSWORD_ERROR'],1);
		// }
		$status = getUCInrepayRepayBorrowMoney($id);
		if ($status['status'] == 0){
			showErr($status['show_err'],1);
		}else{
			showSuccess($status['show_err'],1);
		}
				
	}
	
	public function refdetail(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		
		$deal = get_deal($id);
		if(!$deal)
		{
			showErr("借款不存在！");
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款！");
		}
		if($deal['deal_status']!=5){
			showErr("借款状态不正确！");
		}
		$GLOBALS['tmpl']->assign('deal',$deal);
		
	
	
		
		//还款列表
		$loan_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_repay where deal_id=$id ORDER BY repay_time ASC");
		$manage_fee = 0;
		$impose_money = 0;
		$repay_money = 0;
		$manage_impose_fee = 0;
		foreach($loan_list as $k=>$v){
			$manage_fee += $v['true_manage_money'];
			$impose_money += $v['impose_money'];
			$repay_money += $v['true_repay_money'];
			$manage_impose_fee +=$v['manage_impose_money'];
			$manage_impose_fee +=$v['manage_impose_money'];
			//还款日
			$loan_list[$k]['repay_time_format'] = to_date($v['repay_time'],'Y-m-d');
			$loan_list[$k]['true_repay_time_format'] = to_date($v['true_repay_time'],'Y-m-d');
	
			//已还本息
			$loan_list[$k]['repay_money_format'] = format_price($v['true_repay_money']);
			
			//逾期费用
			$loan_list[$k]['impose_money_format'] = format_price($v['impose_money']);
			
			//借款管理费
			$loan_list[$k]['manage_money_format'] = format_price($v['true_manage_money']);
			
			$loan_list[$k]['manage_impose_money_format'] = format_price($v['manage_impose_money']);
			 
			
			//状态
			if($v['status'] == 0){
				$loan_list[$k]['status_format'] = '提前还款';
			}elseif($v['status'] == 1){
				$loan_list[$k]['status_format'] = '正常还款';
			}elseif($v['status'] == 2){
				$loan_list[$k]['status_format'] = '逾期还款';
			}elseif($v['status'] == 3){
				$loan_list[$k]['status_format'] = '严重逾期';
			}
			
		}
		
		
		$GLOBALS['tmpl']->assign("manage_fee",$manage_fee);
		$GLOBALS['tmpl']->assign("impose_money",$impose_money);
		$GLOBALS['tmpl']->assign("repay_money",$repay_money);
		$GLOBALS['tmpl']->assign("manage_impose_fee",$manage_impose_fee);
		$GLOBALS['tmpl']->assign("loan_list",$loan_list);
		
		$inrepay_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_inrepay_repay WHERE deal_id=$id");
		$GLOBALS['tmpl']->assign("inrepay_info",$inrepay_info);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_quick_refdetail.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	
	
	public function mrefdetail(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
	
		$deal = get_deal($id);
		if(!$deal)
		{
			showErr("借款不存在！");
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款！");
		}
		if($deal['deal_status']!=5){
			showErr("借款状态不正确！");
		}
		$GLOBALS['tmpl']->assign('deal',$deal);
	
		//还款列表
		$loan_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_repay where deal_id=$id ORDER BY repay_time ASC");
		$manage_fee = 0;
		$impose_money = 0;
		$repay_money = 0;
		$manage_impose_fee = 0;
		foreach($loan_list as $k=>$v){
			$manage_fee += $v['true_manage_money'];
			$impose_money += $v['impose_money'];
			$repay_money += $v['true_repay_money'];
			$manage_impose_fee +=$v['manage_impose_money'];
			
			//还款日
			$loan_list[$k]['repay_time_format'] = to_date($v['repay_time'],'Y-m-d');
			$loan_list[$k]['true_repay_time_format'] = to_date($v['true_repay_time'],'Y-m-d');
	
			//已还本息
			$loan_list[$k]['repay_money_format'] = format_price($v['true_repay_money']);
			
			//逾期费用
			$loan_list[$k]['impose_money_format'] = format_price($v['impose_money']);
			
			//借款管理费
			$loan_list[$k]['manage_money_format'] = format_price($v['true_manage_money']);
			
			$loan_list[$k]['manage_impose_money_format'] = format_price($v['manage_impose_money']);
			 
			
			//状态
			if($v['status'] == 0){
				$loan_list[$k]['status_format'] = '提前还款';
			}elseif($v['status'] == 1){
				$loan_list[$k]['status_format'] = '正常还款';
			}elseif($v['status'] == 2){
				$loan_list[$k]['status_format'] = '逾期还款';
			}elseif($v['status'] == 3){
				$loan_list[$k]['status_format'] = '严重逾期';
			}
			
		}
		
		$GLOBALS['tmpl']->assign("manage_fee",$manage_fee);
		$GLOBALS['tmpl']->assign("impose_money",$impose_money);
		$GLOBALS['tmpl']->assign("repay_money",$repay_money);
		$GLOBALS['tmpl']->assign("manage_impose_fee",$manage_impose_fee);
		$GLOBALS['tmpl']->assign("loan_list",$loan_list);
	
		$inrepay_info = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_inrepay_repay WHERE deal_id=$id");
		$GLOBALS['tmpl']->assign("inrepay_info",$inrepay_info);
	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_REFUND']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_quick_refdetail.html");
		$GLOBALS['tmpl']->display("uc_deal_mrefdetail.html");
	}
	
	public function borrowed(){
		$user_id = $GLOBALS['user_info']['id'];
		
		//输出借款记录
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			
		
		$result = get_deal_list($limit,0," (is_delete=0 or is_delete = 2 or is_delete =3) and user_id=".$user_id,"id DESC",'','',true);

		$GLOBALS['tmpl']->assign("deal_list",$result['list']);
		
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_BORROWED']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_borrowed.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	
	public function borrow_stat(){
		$user_statics = sys_user_status($GLOBALS['user_info']['id'],false,true);
		$GLOBALS['tmpl']->assign("user_statics",$user_statics);
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_BORROW_STAT']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_borrow_stat.html");
		$GLOBALS['tmpl']->display("page/uc.html");	
	}
	public function mborrow_stat(){
		$user_statics = sys_user_status($GLOBALS['user_info']['id'],false,true);
		$GLOBALS['tmpl']->assign("user_statics",$user_statics);
	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_DEAL_BORROW_STAT']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_deal_borrow_stat.html");
		$GLOBALS['tmpl']->display("uc_deal_mborrow_stat.html");
	}
	
	function generation(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		$is_ajax = intval($_REQUEST['is_ajax']);
	
		$deal = get_deal($id);
		if(!$deal)
		{
			showErr("借款不存在",$is_ajax);
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款",$is_ajax);
		}
		if($deal['repay_progress_point'] < $deal['generation_position']){
			showErr("已还金额不足够续约",$is_ajax);
		}
		$GLOBALS['tmpl']->assign("deal",$deal);
		echo $GLOBALS['tmpl']->fetch("inc/uc/uc_deal_generation.html");
		
	}
	
	function dogeneration(){
		$user_id = $GLOBALS['user_info']['id'];
		$id = intval($_REQUEST['id']);
		$is_ajax = intval($_REQUEST['is_ajax']);
	
		$deal = get_deal($id);
		if(!$deal)
		{
			showErr("借款不存在",$is_ajax);
		}
		if($deal['user_id']!=$GLOBALS['user_info']['id']){
			showErr("不属于你的借款",$is_ajax);
		}
		if($deal['repay_progress_point'] < $deal['generation_position']){
			showErr("已还金额不足够续约",$is_ajax);
		}
		
		$data['deal_id'] = $id;
		$data['user_id'] = $GLOBALS['user_info']['id'];
		$data['money'] = $deal['need_remain_repay_money'];
		$data['create_time'] = TIME_UTC; 
		
		$rs_id = $GLOBALS['db']->getOne("SELECT id FROM ".DB_PREFIX."generation_repay_submit WHERE deal_id=".$id." AND user_id=$user_id");
		
		if(!$rs_id){
			$GLOBALS['db']->autoExecute(DB_PREFIX."generation_repay_submit",$data);
			if($GLOBALS['db']->insert_id() > 0){
				showSuccess("申请续约成功",$is_ajax);
			}
			else{
				showErr("申请续约失败",$is_ajax);
			}
		}
		else{
			$GLOBALS['db']->autoExecute(DB_PREFIX."generation_repay_submit",$data,"UPDATE","id=".$rs_id);
			if($GLOBALS['db']->affected_rows() > 0){
				showSuccess("申请续约成功",$is_ajax);
			}
			else{
				showErr("申请续约失败",$is_ajax);
			}
		}
	}
}
?>