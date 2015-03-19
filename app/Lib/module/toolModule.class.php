<?php
// +----------------------------------------------------------------------
// | Fanwe 方维订餐小秘书商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class toolModule extends SiteBaseModule
{
    function index() {
    	toolModule::calculate();
    }
    function calculate(){
    	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['CALCULATE'].' - '.$GLOBALS['lang']['TOOLS']);
    	
    	$loantype_list = load_auto_cache("loantype_list");
    	
    	$GLOBALS['tmpl']->assign("loantype_list",$loantype_list);
    	
    	$level_list = load_auto_cache("level");
		$GLOBALS['tmpl']->assign("level_list",$level_list['list']);
    	
    	$GLOBALS['tmpl']->assign("inc_file","inc/tool/calculate.html");
		$GLOBALS['tmpl']->display("page/tool.html");
    }
    
    function ajax_calculate(){
    	
    	$deal['loantype'] = intval($_REQUEST['borrowpay']);
    	$deal['borrow_amount'] = intval($_REQUEST['borrowamount']);
    	$deal['repay_time'] = intval($_REQUEST['repaytime']);
    	$deal['repay_time_type'] = intval($_REQUEST['repaytimetype']);
    	$deal['rate'] = trim($_REQUEST['apr']);
    	$deal['repay_start_time'] = to_timespan(to_date(TIME_UTC,"Y-m-d"));
    	
    	$deal_repay_rs =  deal_repay_money($deal);
    	
    	$deal['month_repay_money'] = $deal_repay_rs['month_repay_money'];
    	//总的必须还多少本息
		$deal['remain_repay_money'] = $deal_repay_rs['remain_repay_money'];
		
    	//最后一期还款
		$deal['last_month_repay_money'] = $deal_repay_rs['last_month_repay_money'];
    	
    	$deal['month_manage_money'] = $deal['borrow_amount']*(float)app_conf('MANAGE_FEE')/100;
    	//总的多少管理费
		if($deal['repay_time_type']==1)
			$deal['all_manage_money'] = $deal['month_manage_money'] * $deal["repay_time"];
		else
			$deal['all_manage_money'] = $deal['month_manage_money'] ;
    	
    	$GLOBALS['tmpl']->assign("borrowpay",$deal['loantype']);
    	$GLOBALS['tmpl']->assign("borrowamount",$deal['borrow_amount']);
    	$GLOBALS['tmpl']->assign("apr",$deal['rate']);
    	if($deal['repay_time_type']==1)
    		$GLOBALS['tmpl']->assign("rate",$deal['rate']/12);
    	else
    		$GLOBALS['tmpl']->assign("rate",$deal['rate']/12/30);
    	
    	$GLOBALS['tmpl']->assign("repaytime",$deal['repay_time'] );
    	$GLOBALS['tmpl']->assign("repaytimetype",$deal['repay_time_type'] );
    	$GLOBALS['tmpl']->assign("repayamount",$deal['month_repay_money']);
    	$GLOBALS['tmpl']->assign("repayallamount",$deal['remain_repay_money']);
    	
    	$level = intval($_REQUEST['level']);
    	$level_list = load_auto_cache("level");
    	$GLOBALS['tmpl']->assign("services_fee",$level_list['services_fee'][$level]/100*$deal['borrow_amount']);
    	
    	
    	if($deal['repay_time_type']==0){
    		$inrepayshow = 0;
    	}
    	else{
    		$inrepayshow = intval($_REQUEST['inrepayshow']);
    	}
    	
    	$impose_day = intval($_REQUEST['impose_day']);
    	
    	if(isset($_REQUEST['isshow']) && intval($_REQUEST['isshow'])==1)
    	{
    		
    		$loantype = $deal['loantype'];
	    	$LoanModule = LoadLoanModule($loantype);
			$list = $LoanModule->make_repay_plan($deal);
			
			if($impose_day >= app_conf('YZ_IMPSE_DAY')){
				$impose_fee = app_conf('IMPOSE_FEE_DAY2');
				$manage_impose_fee = app_conf('MANAGE_IMPOSE_FEE_DAY2');
			}
			else{
				$impose_fee = app_conf('IMPOSE_FEE_DAY1');
				$manage_impose_fee = app_conf('MANAGE_IMPOSE_FEE_DAY1');
			}
			$left_repay_money = $deal['remain_repay_money'];
	
    		foreach($list as $k=>$v){
    			$list[$k]['impose_money'] = $v['repay_money'] * $impose_fee*$impose_day*0.01;
    			$list[$k]['manage_impose_money'] = $v['repay_money'] * $manage_impose_fee*$impose_day*0.01;
    			
    			$list[$k]['left_repay_money'] = $left_repay_money = $left_repay_money - round($v['repay_money'],2);
    			
    		}
    		
    		$GLOBALS['tmpl']->assign("list",$list);
		}
		
		//提前还款
		if($inrepayshow == 1){
			
			$tq_list = array();
			$deal['compensate_fee'] = app_conf('COMPENSATE_FEE');
			for($i=0;$i<$deal['repay_time'];$i++){
				$loaninfo['deal']=$deal;
				if(is_last_repay($deal['loantype'])){
					$loaninfo['deal']['month_manage_money']=$deal['all_manage_money'];
				}
				
    			$tq_list[$i] = inrepay_repay($loaninfo,$i,next_replay_month(TIME_UTC,$i+1));
    			
    			if(is_last_repay($deal['loantype'])){
					$tq_list[$i]['month_repay_money'] = 0;
					$tq_list[$i]['month_repay_money'] = 0;
					if($i+1 == $deal['repay_time']){
						$tq_list[$i]['manage_money'] = $deal['all_manage_money'];
						$tq_list[$i]['month_repay_money'] = $deal['last_month_repay_money'];
					}
    			}
    			else{
    				$tq_list[$i]['manage_money'] = $deal['month_manage_money'];
    				$tq_list[$i]['month_repay_money'] = $deal['month_repay_money'];
    				if($i+1 == $deal['repay_time']){
						$tq_list[$i]['month_repay_money'] = $deal['last_month_repay_money'];
					}
    			}
    			
    			
    		}
    		
    		$GLOBALS['tmpl']->assign("tq_list",$tq_list);
		}
		
    	$GLOBALS['tmpl']->display("inc/tool/calculate_result.html");
    }
    
    function contact(){
    	require APP_ROOT_PATH."app/Lib/deal.php";
    	$win = intval($_REQUEST['win']);
    	$id = intval($_REQUEST['id']);
    	
    	
    	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['T_CONTACT'].' - '.$GLOBALS['lang']['TOOLS']);
    	if($win)
    	{
    		$GLOBALS['tmpl']->assign("win",$win);
    		echo $GLOBALS['tmpl']->fetch("inc/tool/contact.html");
    	}
    	else
    	{
    		$GLOBALS['tmpl']->assign("inc_file","inc/tool/contact.html");
			$GLOBALS['tmpl']->display("page/tool.html");
    	}
    	
   		/************
    		$GLOBALS['tmpl']->assign('load_list',$load_list);
    		print_r($load_list);
    		$GLOBALS['tmpl']->display("inc/tool/contact.html");
    	*/
    	
    }
    
    function dcontact(){
    	 
    	$win = 1;
    	$id = intval($_REQUEST['id']);
    	
    	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['T_CONTACT'].' - '.$GLOBALS['lang']['TOOLS']);
    	/*header("Content-type:text/html;charset=utf-8");
    	header("Content-Disposition: attachment; filename=借款协议.html");
    	
    	echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>";
    	echo '<html>';
    	echo '<head>';
    	echo '<title>借款协议</title>';
    	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    	echo '<meta http-equiv="X-UA-Compatible" content="IE=7" />';
    	echo  $GLOBALS['tmpl']->fetch("inc/tool/contact.html");
    	echo '</body>';
    	echo '</html>';
    	*/
    	require APP_ROOT_PATH."/system/utils/word.php";
    	$word = new word(); 
   		$word->start(); 
   		$wordname = "借款协议.doc"; 
   		echo  $GLOBALS['tmpl']->fetch("inc/tool/contact.html");
   		$word->save($wordname); 
    }
    
    
	function tcontact(){
    	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['TT_CONTACT'].' - '.$GLOBALS['lang']['TOOLS']);
    	$win = intval($_REQUEST['win']);
    	if($win)
    	{
    		$GLOBALS['tmpl']->assign("win",$win);
    		echo $GLOBALS['tmpl']->fetch("inc/tool/tcontact.html");
    	}
    	else
    	{
	    	$GLOBALS['tmpl']->assign("inc_file","inc/tool/tcontact.html");
			$GLOBALS['tmpl']->display("page/tool.html");
    	}
    }
    
    function dtcontact(){
    	$win = 1;
    	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['TT_CONTACT'].' - '.$GLOBALS['lang']['TOOLS']);
    	/*header("Content-type:text/html;charset=utf-8");
    	header("Content-Disposition: attachment; filename=债权转让及受让协议.html");
    	 
    	echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>";
    	echo '<html>';
    	echo '<head>';
    	echo '<title>债权转让及受让协议</title>';
    	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    	echo '<meta http-equiv="X-UA-Compatible" content="IE=7" />';
    	echo  $GLOBALS['tmpl']->fetch("inc/tool/tcontact.html");
    	echo '</body>';
    	echo '</html>';*/
    	require APP_ROOT_PATH."/system/utils/word.php";
    	$word = new word(); 
   		$word->start(); 
   		$wordname = "债权转让及受让协议.doc"; 
   		echo  $GLOBALS['tmpl']->fetch("inc/tool/tcontact.html");
   		$word->save($wordname); 
    	
    }
    
    function mobile(){
    	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['T_CHECK_MOBILE'].' - '.$GLOBALS['lang']['TOOLS']);
    	
    	$GLOBALS['tmpl']->assign("inc_file","inc/tool/mobile.html");
		$GLOBALS['tmpl']->display("page/tool.html");
    }
    
    function ajax_mobile(){
    	$url = "http://api.showji.com/Locating/www.showji.com.aspx?m=".trim($_REQUEST['mobile'])."&output=json&callback=querycallback";
		$content = @file_get_contents($url);
		preg_match("/querycallback\((.*?)\)/",$content,$rs);
		echo $rs[1];
    } 
    
    function ip(){
    	$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['T_CHECK_IP'].' - '.$GLOBALS['lang']['TOOLS']);
    	
    	$GLOBALS['tmpl']->assign("inc_file","inc/tool/ip.html");
		$GLOBALS['tmpl']->display("page/tool.html");
    }
}
?>