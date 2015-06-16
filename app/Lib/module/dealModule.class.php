<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/deal.php';
class dealModule extends SiteBaseModule
{
	
	
	public function index(){
	
			//判断加息活动的开启和关闭的查询和传值
		$ease_val=$GLOBALS['db']->getOne("SELECT `value` FROM ".DB_PREFIX."ease where name ='CACHE_PLUS'");
	    $GLOBALS['tmpl']->assign("ease_val",$ease_val);
		
		
		
		/*if(!$GLOBALS['user_info']){
			set_gopreview();
			app_redirect(url("index","user#login")); 
		}*/
		// print_r($GLOBALS['user_info']['pfcf_money']);
		
		
		$id = intval($_REQUEST['id']);
		
		$deal = get_deal($id);
		// var_dump($data);exit;
		$b_id=$deal['id'];
		
		send_deal_contract_email($id,$deal,$deal['user_id']);
		
		if(!$deal)
			app_redirect(url("index")); 
		
		//借款列表
		$load_list = $GLOBALS['db']->getAll("SELECT deal_id,user_id,user_name,money,is_auto,create_time,virtual_money FROM ".DB_PREFIX."deal_load WHERE deal_id = ".$id." order by id ASC ");
		// var_dump($load_list);exit;
		
		$u_info = get_user("*",$deal['user_id']);
		
		if($deal['view_info']!=""){
			$view_info_list = unserialize($deal['view_info']);
			$GLOBALS['tmpl']->assign('view_info_list',$view_info_list);
		}
		
		
		//可用额度
		$can_use_quota=get_can_use_quota($deal['user_id']);
		$GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);
		
		$credit_file = get_user_credit_file($deal['user_id'],$u_info);
		$deal['is_faved'] = 0;
		if($GLOBALS['user_info']){
			if($u_info['user_type']==1)
				$company = $GLOBALS['db']->getRowCached("SELECT * FROM ".DB_PREFIX."user_company WHERE user_id=".$u_info['id']);
			
			$deal['is_faved'] = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_collect WHERE deal_id = ".$id." AND user_id=".intval($GLOBALS['user_info']['id']));
			
			if($deal['deal_status'] >=4){
				//还款列表
				$loan_repay_list = get_deal_load_list($deal);
				$GLOBALS['tmpl']->assign("loan_repay_list",$loan_repay_list);
				
				if($loan_repay_list){
					$temp_self_money_list = $GLOBALS['db']->getAllCached("SELECT sum(self_money) as total_money,u_key FROM ".DB_PREFIX."deal_load_repay WHERE has_repay=1 AND deal_id=".$id." group by u_key ");
					$self_money_list = array();
					foreach($temp_self_money_list as $k=>$v){
						$self_money_list[$v['u_key']]= $v['total_money'];
					}
					
					foreach($load_list as $k=>$v){
						$load_list[$k]['remain_money'] = $v['money'] -$self_money_list[$k];
						if($load_list[$k]['remain_money'] <=0){
							$load_list[$k]['remain_money'] = 0;
							$load_list[$k]['status'] = 1;
						}
					}
				}
				
				
			}	
			$user_statics = sys_user_status($deal['user_id'],true);
			$GLOBALS['tmpl']->assign("user_statics",$user_statics);
			$GLOBALS['tmpl']->assign("company",$company);
			
			
			if($deal['uloadtype'] == 1){
				$has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id);
				$GLOBALS['tmpl']->assign("has_bid_money",$has_bid_money);
				$GLOBALS['tmpl']->assign("has_bid_portion",intval($has_bid_money)/($deal['borrow_amount']/$deal['portion']));
			}
		}
		// var_dump($load_list);exit;
		$GLOBALS['tmpl']->assign("load_list",$load_list);	 // 投资列表
		$GLOBALS['tmpl']->assign("credit_file",$credit_file);
		$GLOBALS['tmpl']->assign("u_info",$u_info);
				
		if($deal['type_match_row'])
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
		else
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']: $deal['name'];
			
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
		$GLOBALS['tmpl']->assign("seo_description",$seo_description.",");
		
		//留言
		require APP_ROOT_PATH.'app/Lib/message.php';
		require APP_ROOT_PATH.'app/Lib/page.php';
		
		$rel_table = 'deal';
		
		$message_type = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
		$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;
	
		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
			$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
		}
		else 
		{
			if($message_type['is_effect']==0)
			{
				$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
			}
		}
		
		//message_form 变量输出
		$GLOBALS['tmpl']->assign('rel_id',$id);
		$GLOBALS['tmpl']->assign('rel_table',$rel_table);
		
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$msg_condition = $condition." AND is_effect = 1 ";
		$message = get_message_list($limit,$msg_condition);
		
		$page = new Page($message['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		foreach($message['list'] as $k=>$v){
			$msg_sub = get_message_list("","pid=".$v['id'],false);
			$message['list'][$k]["sub"] = $msg_sub["list"];
		}
		
		$GLOBALS['tmpl']->assign("message_list",$message['list']);
		if(!$GLOBALS['user_info'])
		{
			$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url("shop","user#login"),url("shop","user#register")));
		}
	  if($deal['borrow_amount']<10000){
		  $deal['borrow_amount_format']=$deal['borrow_amount']."元";
		
		}
		$GLOBALS['tmpl']->assign("b_id",$b_id);
		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->display("page/deal.html");
	}
	
	public function mobile(){
	
		/*if(!$GLOBALS['user_info']){
			set_gopreview();
			app_redirect(url("index","user#login"));
		}*/
	
		$id = intval($_REQUEST['id']);
	
		$deal = get_deal($id);
		send_deal_contract_email($id,$deal,$deal['user_id']);
	
		if(!$deal)
			app_redirect(url("index"));
	
		//借款列表
		$load_list = $GLOBALS['db']->getAll("SELECT deal_id,user_id,user_name,money,is_auto,create_time FROM ".DB_PREFIX."deal_load WHERE deal_id = ".$id);
	
	
		$u_info = get_user("*",$deal['user_id']);
	
		//可用额度
		$can_use_quota=get_can_use_quota($deal['user_id']);
		$GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);
	
		$credit_file = get_user_credit_file($deal['user_id']);
		$deal['is_faved'] = 0;
		if($GLOBALS['user_info']){
			$deal['is_faved'] = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_collect WHERE deal_id = ".$id." AND user_id=".intval($GLOBALS['user_info']['id']));
				
			if($deal['deal_status'] >=4){
				//还款列表
				$loan_repay_list = get_deal_load_list($deal);
				$GLOBALS['tmpl']->assign("loan_repay_list",$loan_repay_list);
				foreach($load_list as $k=>$v){
					$load_list[$k]['remain_money'] = $v['money'] - $GLOBALS['db']->getOne("SELECT sum(self_money) FROM ".DB_PREFIX."deal_load_repay WHERE user_id=".$v['user_id']." AND deal_id=".$id);
					if($load_list[$k]['remain_money'] <=0){
						$load_list[$k]['remain_money'] = 0;
						$load_list[$k]['status'] = 1;
					}
				}
			}
			$user_statics = sys_user_status($deal['user_id'],true);
			$GLOBALS['tmpl']->assign("user_statics",$user_statics);
		}
	
		$GLOBALS['tmpl']->assign("load_list",$load_list);
		$GLOBALS['tmpl']->assign("credit_file",$credit_file);
		$GLOBALS['tmpl']->assign("u_info",$u_info);
	
		//工作认证是否过期
		//$GLOBALS['tmpl']->assign('expire',user_info_expire($u_info));
	
		if($deal['type_match_row'])
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
		else
			$seo_title = $deal['seo_title']!=''?$deal['seo_title']: $deal['name'];
			
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
		$GLOBALS['tmpl']->assign("seo_description",$seo_description.",");
	
		//留言
		require APP_ROOT_PATH.'app/Lib/message.php';
		require APP_ROOT_PATH.'app/Lib/page.php';
		$rel_table = 'deal';
		$message_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."message_type where type_name='".$rel_table."'");
		$condition = "rel_table = '".$rel_table."' and rel_id = ".$id;
	
		if(app_conf("USER_MESSAGE_AUTO_EFFECT")==0)
		{
			$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
		}
		else
		{
			if($message_type['is_effect']==0)
			{
				$condition.= " and user_id = ".intval($GLOBALS['user_info']['id']);
			}
		}
	
		//message_form 变量输出
		$GLOBALS['tmpl']->assign('rel_id',$id);
		$GLOBALS['tmpl']->assign('rel_table',"deal");
	
		//分页
		$page = intval($_REQUEST['p']);
		if($page==0)
			$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$msg_condition = $condition." AND is_effect = 1 ";
		$message = get_message_list($limit,$msg_condition);
	
		$page = new Page($message['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
		foreach($message['list'] as $k=>$v){
			$msg_sub = get_message_list("","pid=".$v['id'],false);
			$message['list'][$k]["sub"] = $msg_sub["list"];
		}
	
		$GLOBALS['tmpl']->assign("message_list",$message['list']);
		if(!$GLOBALS['user_info'])
		{
			$GLOBALS['tmpl']->assign("message_login_tip",sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'],url("shop","user#login"),url("shop","user#register")));
		}

		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->display("deal_mobile.html");
	}
	
	
	function preview(){
		$deal['id'] = 'XXX';
		
		$deal_loan_type_list = load_auto_cache("deal_loan_type_list");
		if(intval($_REQUEST['quota'])==1){
			$deal = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."deal_quota_submit WHERE status=1 and user_id = ".$GLOBALS['user_info']['id']." ORDER BY id DESC");
			$type_id = intval($deal['type_id']);
			$deal['rate_foramt'] = number_format($deal['rate'],2);
			$data['view_info'] = unserialize($deal['view_info']);
			if($deal['cate_id'] > 0){
				$deal['cate_info'] = $GLOBALS['db']->getRowCached("select id,name,brief,uname,icon from ".DB_PREFIX."deal_cate where id = ".$deal['cate_id']." and is_effect = 1 and is_delete = 0");
			}
			
			$deal['repay_time'] = strim($_REQUEST['repay_time']);
			$deal['repay_time_type'] = 1;
		}
		else{
			$deal['name'] = strim($_REQUEST['borrowtitle']);
			$type_id = intval($_REQUEST['borrowtype']);
			$deal['repay_time_type'] = intval($_REQUEST['repaytime_type']);
			$deal['rate_foramt'] = number_format(strim($_REQUEST['apr']),2);
			$deal['repay_time'] = strim($_REQUEST['repaytime']);
			
			$icon_type = strim($_REQUEST['imgtype']);
		
			$icon_type_arr = array(
	    		'upload' =>1,
	    		'userImg' =>2,
	    		'systemImg' =>3,
	    	);
	    	$data['icon_type'] = $icon_type_arr[$icon_type];
	    	
	    	switch($data['icon_type']){
	    		case 1 :
	    			$deal['icon'] = replace_public(strim($_REQUEST['icon']));
	    			break;
	    		case 2 :
	    			$deal['icon'] = replace_public(get_user_avatar($GLOBALS['user_info']['id'],'big'));
	    			break;
	    		case 3 :
	    			$deal['icon'] = $GLOBALS['db']->getOneCached("SELECT icon FROM ".DB_PREFIX."deal_loan_type WHERE id=".intval($_REQUEST['systemimgpath']));
	    	}
	    	
	    	
			$deal['description']= replace_public(valid_str(btrim($_REQUEST['borrowdesc'])));
			
			
			$user_view_info = $GLOBALS['user_info']['view_info'];
	    	$user_view_info = unserialize($user_view_info);
	    	
	    	$new_view_info_arr = array();	
	    	for($i=1;$i<=intval($_REQUEST['file_upload_count']);$i++){
	    		$img_info = array();
	    		$img = replace_public(strim($_REQUEST['file_'.$i]));
	    		if($img!=""){
	    			$img_info['name'] = strim($_REQUEST['file_name_'.$i]);
	    			$img_info['img'] = $img;
	    			$img_info['is_user'] = 1;
	    			
	    			$user_view_info[] = $img_info;
	    			$ss = $user_view_info;
					end($ss);
					$key = key($ss);
	    			$new_view_info_arr[$key] = $img_info;
	    		}
	    	}
	    	    	
	    	
	    	$data['view_info'] = array();
	    	foreach($_REQUEST['file_key'] as $k=>$v){
	    		if(isset($user_view_info[$v])){
	    			$data['view_info'][$v] = $user_view_info[$v];
	    		}
	    	}
	    	
	    	foreach($new_view_info_arr as $k=>$v){
	    		$data['view_info'][$k] = $v;
	    	}
	    	
	    	if($deal['cate_id'] > 0){
				$deal['cate_info']['name'] = "借款预览标";
			}
	    	
		}
		
		$deal['borrow_amount'] = strim($_REQUEST['borrowamount']);
		$deal['borrow_amount_format'] = format_price($deal['borrow_amount']/10000)."万";
		
		$GLOBALS['tmpl']->assign('view_info_list',$data['view_info']);
		unset($data['view_info']);
		
		foreach($deal_loan_type_list as $k=>$v){
			if($v['id'] == $type_id){
				$deal['type_info'] = $v;
			}
		}
		
		
		$deal['min_loan_money'] = 50;
		$deal['need_money'] = $deal['borrow_amount_format'];
		
		
		
		//本息还款金额
		$deal['month_repay_money'] = format_price(pl_it_formula($deal['borrow_amount'],strim($deal['rate'])/12/100,$deal['repay_time']));
		
		
		if($deal['agency_id'] > 0){
			$deal['agency_info'] = $GLOBALS['db']->getRowCached("select * from ".DB_PREFIX."deal_agency where id = ".$deal['agency_id']." and is_effect = 1");
		}
		
		$deal['progress_point'] = 0;
		$deal['buy_count'] = 0;
		$deal['voffice'] = 1;
		$deal['vjobtype'] = 1;
		
		
		$deal['is_delete'] = 2;
		
		$u_info = get_user("*",$GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign("u_info",$u_info);
		
		$can_use_quota=get_can_use_quota($GLOBALS['user_info']['id']);
		$GLOBALS['tmpl']->assign('can_use_quota',$can_use_quota);
		
		$credit_file = get_user_credit_file($GLOBALS['user_info']['id'],$u_info);
		$GLOBALS['tmpl']->assign("credit_file",$credit_file);
		$user_statics = sys_user_status($GLOBALS['user_info']['id'],true);
		$GLOBALS['tmpl']->assign("user_statics",$user_statics);
		
		
		$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
		
		$GLOBALS['tmpl']->assign("seo_description",$seo_description.",");
		
		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->display("page/deal.html");
	}
	
	function bid(){
	// echo 1;exit;
		if(!$GLOBALS['user_info']){
			set_gopreview();
			app_redirect(url("index","user#login")); 
		}
		

		// $noshenfenzheng=$GLOBALS['db']->getOne("SELECT * FROM ".DB_PREFIX."user_bank where status=0 and user_id=".$GLOBALS['user_info']['id']);
  
	 // if(!$noshenfenzheng){
		 // showErr("请绑定银行卡",0,url("shop","uc_money#bank"));
	   // }

		//注册送的
		$ecv_user_id=$GLOBALS['user_info']['id'];
		//修改活动的类型：
		
		
		
	    
			
		//查询领取了的代金劵数量的总额
		$total_money_3=$GLOBALS['db']->getOne("SELECT `referee_money` FROM ".DB_PREFIX."user where id=".$ecv_user_id);
		
		//有数据为老客户，没数据为新客户；
		$nodeal=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_load where user_id=".$GLOBALS['user_info']['id']);
        //老用户有数据为使用过投资劵。
		$total_money_4=$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."ecv where used_yn=2 and user_id=".$ecv_user_id);
		
	
	
		 //搜索出该用户有多少张注册和投资获取的代金券，
        $user_ecv = $GLOBALS['db']->getAll("SELECT *,e.id AS eid,et.id AS etid FROM ".DB_PREFIX."ecv AS e LEFT JOIN ".DB_PREFIX."ecv_type AS et ON e.ecv_type_id = et.id WHERE e.used_yn=0 AND e.receive=1 AND e.user_id = ".$ecv_user_id." ORDER BY e.id DESC");
	  //var_dump($user_ecv);exit;
	//老用户的券
	$laoyonghuyongdequan=$GLOBALS['db']->getAll("SELECT *,e.id AS eid,et.id AS etid FROM ".DB_PREFIX."ecv AS e LEFT JOIN ".DB_PREFIX."ecv_type AS et ON e.ecv_type_id = et.id WHERE e.used_yn=0 AND e.receive=1 AND et.id in(40,39,38,37)  and  e.user_id = ".$ecv_user_id." ORDER BY et.money asc");
	//特殊劵
	$teishujuan=$GLOBALS['db']->getAll("SELECT *,e.id AS eid,et.id AS etid FROM ".DB_PREFIX."ecv AS e LEFT JOIN ".DB_PREFIX."ecv_type AS et ON e.ecv_type_id = et.id WHERE e.used_yn=0 AND e.receive=1 AND et.id in(35,36)  and  e.user_id = ".$ecv_user_id." ORDER BY et.money asc");
	//print_r($laoyonghuyongdequan);exit;
	$GLOBALS['tmpl']->assign("laoyonghuyongdequan",$laoyonghuyongdequan);

	
	    //判断劵是否过期
		$get_gmtime=get_gmtime();
		//print_r($get_gmtime);exit;
		$GLOBALS['tmpl']->assign("get_gmtime",$get_gmtime);
		
		
		
		
		
		//如果未绑定手机
	if($GLOBALS['user_info']['idcardpassed'] == 0){
		app_redirect(url("index","uc_account#security"),3,"请实名认证,填写身份证"); 
		}
		
	if(!$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."user_bank where user_id=".$GLOBALS['user_info']['id'])){
	app_redirect(url("index","uc_money#bank"),3,"请添加银行卡"); 
		}
		
		
		$id = intval($_REQUEST['id']);
		$deal = get_deal($id);
		if(!$deal)
			app_redirect(url("index")); 
		
		if($deal['user_id'] == $GLOBALS['user_info']['id']){
			showErr($GLOBALS['lang']['CANT_BID_BY_YOURSELF']);}
		
		if($deal['ips_bill_no']!="" && $GLOBALS['user_info']['ips_acct_no']==""){
			showErr("此标为第三方托管标，请先绑定第三方托管账户",0,url("index","uc_center"));}
	     
		
		$has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."deal_load WHERE deal_id=".$id);
		$GLOBALS['tmpl']->assign("has_bid_money",$has_bid_money);
		if($deal['uloadtype'] == 1){
			$GLOBALS['tmpl']->assign("has_bid_portion",intval($has_bid_money)/($deal['borrow_amount']/$deal['portion']));
		}
		
		// $result = get_voucher_list_can($GLOBALS['user_info']['id']);//查询代金券
		// $GLOBALS['tmpl']->assign("voucher",$result['list']);//查询代金券
		
		$seo_title = $deal['seo_title']!=''?$deal['seo_title']:$deal['type_match_row'] . " - " . $deal['name'];
		$GLOBALS['tmpl']->assign("page_title",$seo_title);
		$seo_keyword = $deal['seo_keyword']!=''?$deal['seo_keyword']:$deal['type_match_row'].",".$deal['name'];
		$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
		$seo_description = $deal['seo_description']!=''?$deal['seo_description']:$deal['name'];
        
		$GLOBALS['tmpl']->assign("total_money_3",$total_money_3);
		$GLOBALS['tmpl']->assign("total_money_4",$total_money_4);
		$GLOBALS['tmpl']->assign("nodeal",$nodeal);
		$GLOBALS['tmpl']->assign("user_ecv",$user_ecv);
		$GLOBALS['tmpl']->assign("deal",$deal);
		$GLOBALS['tmpl']->display("page/deal_bid.html");
	}
	function dobidstepone(){
		if(!$GLOBALS['user_info'])
			showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],1);
		
		if(strim($_REQUEST['name'])==""){
			showErr($GLOBALS['lang']['PLEASE_INPUT'].$GLOBALS['lang']['URGENTCONTACT'],1);
		}
		$data['real_name'] = strim($_REQUEST['name']);
		if($GLOBALS['user_info']['idcardpassed'] == 0){
			if(strim($_REQUEST['idno'])==""){
				showErr($GLOBALS['lang']['PLEASE_INPUT'].$GLOBALS['lang']['IDNO'],1);
			}
			
			if(getIDCardInfo(strim($_REQUEST['idno']))==0){  //身份证正则表达式
				showErr($GLOBALS['lang']['FILL_CORRECT_IDNO'],1);
			}
			
			if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where idno = '".strim($_REQUEST['idno'])."' and id <> ".intval($GLOBALS['user_info']['id']))>0)
			{
				showErr(sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'],$GLOBALS['lang']['IDNO']),1);
			}
			if(strim($_REQUEST['idno'])!=strim($_REQUEST['idno_re'])){
				showErr($GLOBALS['lang']['TWO_ENTER_IDNO_ERROR'],1);
			}
			$data['idno'] = strim($_REQUEST['idno']);
			$data['idcardpassed'] = 0;
		}
		
		/*手机*/
		if($GLOBALS['user_info']['mobilepassed'] == 0){
			if(strim($_REQUEST['phone'])==""){
				showErr($GLOBALS['lang']['MOBILE_EMPTY_TIP'],1);
			}
			if(!check_mobile(strim($_REQUEST['phone']))){
				showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'],1);
			}
			if(strim($_REQUEST['validateCode'])==""){
				showErr($GLOBALS['lang']['PLEASE_INPUT'].$GLOBALS['lang']['VERIFY_CODE'],1);
			}
			if(strim($_REQUEST['validateCode'])!=$GLOBALS['user_info']['bind_verify']){
				showErr($GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'],1);
			}
			$data['mobile'] = strim($_REQUEST['phone']);
			$data['mobilepassed'] = 1;
		}
	// if($GLOBALS['user_info']['mobilepassed'] == 0||$GLOBALS['user_info']['idcardpassed']==0){	
	// $pfcfbss= $GLOBALS['db']->getRow("select * from ".DB_PREFIX."pfcfb_huodong where id=1");
	// $time=get_gmtime();
	// if($time<$pfcfbss['end_time'] && $pfcfbss['open_off']==1 && $time>$pfcfbss['start_time']){
			// $unjh_pfcfb=$GLOBALS['user_info']['unjh_pfcfb']+$pfcfbss['song_pfcfb'];
			// $GLOBALS['db']->query("update ".DB_PREFIX."user set unjh_pfcfb =".$unjh_pfcfb." where id = ".$GLOBALS['user_info']['id']);
            // $user_log_b['log_info']="_415活动_注册就送".$pfcfbss['song_pfcfb']."浦发币";
                  // $user_log_b['log_time']=get_gmtime();                
                  // $user_log_b['log_admin_id']=1;
                  // $user_log_b['user_id']=$GLOBALS['user_info']['id'];
                  // $user_log_b['unjh_pfcfb']=$pfcfbss['song_pfcfb'];
                // $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$user_log_b,"INSERT");//插入一条投资目录		
			// }
	// }	
	// if($GLOBALS['user_info']['mobilepassed'] == 0||$GLOBALS['user_info']['idcardpassed']==0){	
	    // $ecv['user_id'] =$GLOBALS['user_info']['id'];
  		// $ecv['receive'] = 1;
		// $ecv['receive_time'] = get_gmtime();
		// $ecv['ecv_type_id'] = 27;	
		// $ecv['last_time'] = get_gmtime()+604800;
		// $ecv['password']=rand(10000000,99999999);
        // $ecv['sn'] = uniqid();
   // $GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$ecv);    
    // $user_ecv['log_info'] ="注册就送20投资代金劵";
  	// $user_ecv['log_time'] =get_gmtime();
	// $user_ecv['money'] =0;
	// $user_ecv['account_money'] =$GLOBALS['user_info']['money'];
	// $user_ecv['user_id'] =$GLOBALS['user_info']['id'];
   // $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$user_ecv);   
   
	   // } 	
			if($data)
			$GLOBALS['db']->autoExecute(DB_PREFIX."user",$data,"UPDATE","id=".$GLOBALS['user_info']['id']);		
		
		showSuccess($GLOBALS['lang']['SUCCESS_TITLE'],1);
	}
		
	
	function dobid(){
		 
		$ajax = intval($_REQUEST["ajax"]);
		$bid_paypassword = strim(FW_DESPWD($_REQUEST['bid_paypassword']));
		$id = intval($_REQUEST["id"]);
	if(md5($bid_paypassword)!=$GLOBALS['user_info']['paypassword']){
	  showErr("支付密码错误",$ajax);
	}
		//用户ID；
		$ecv_user_id=$GLOBALS['user_info']['id'];
		
		/*这里开始*/
			if(floatval($_REQUEST["bid_money"]) > $GLOBALS['user_info']['money']){
			showErr($GLOBALS['lang']['MONEY_NOT_ENOUGHT'],$ajax);
		}	
	
			//使用活动卷的值；
       
	     $total_money_1=$_REQUEST["total_money_1"];  //注册
     	 $total_money_2=$_REQUEST["total_money_2"];  //新用户投资
		 $total_money_3=$_REQUEST["total_money_3"];  //推荐
     	$total_money_4=$_REQUEST["total_money_4"];  //老用户投资
		$last_time_1=intval($_REQUEST["last_time_1"]);        //新用户注册劵的使用期限
		$last_time_2=intval($_REQUEST["last_time_2"]);      //老用户注册劵的使用期限
		$get_gmtime=get_gmtime();
		$ee_id=$_REQUEST["ee_id"];   
		$virtual_money=$total_money_1+$total_money_2+$total_money_3+$total_money_4;
		
		//判断过期劵不能使用
		if($last_time_1>0){
		if($last_time_1<$get_gmtime){
				 showErr('当前劵使用期限已过',$ajax);
			}
		}
				if($last_time_2>0){
	    if($last_time_2<$get_gmtime){
				 showErr('当前劵使用期限已过',$ajax);
			}
				}
		//新用户的得到的劵的判断
		if($total_money_2!=0 && $total_money_2){
			if($total_money_2!=10 && $_REQUEST["bid_money"]>=1000 && $_REQUEST["bid_money"]<5000){
				 showErr('1K-5K只能使用10元的代金券',$ajax);
			}
		    if($total_money_2!=58 && $_REQUEST["bid_money"]>=5000 && $_REQUEST["bid_money"]<10000){
				 showErr('5K-1W只能使用58元的代金券',$ajax);
			}
			if($total_money_2!=108 && $_REQUEST["bid_money"]>=10000 && $_REQUEST["bid_money"]<50000){
				 showErr('1W-5W只能使用108元的代金券',$ajax);
			}
			if($total_money_2!=388 && $_REQUEST["bid_money"]>=50000){
				 showErr('5W只能使用388元的代金券',$ajax);
			}
		}
		//老用户的投资得到的劵的判断  1000    0
		if($total_money_4!=0){
			
			
		if($total_money_4!=20 && $_REQUEST["bid_money"]>=1000 && $_REQUEST["bid_money"]<=5000){
				 showErr('1K-5K只能使用20元的代金券',$ajax);
			}
		
		if($total_money_4!=30 && $_REQUEST["bid_money"]>5000&& $_REQUEST["bid_money"]<=10000){
				  showErr('5K-1W只能使用30元的代金券',$ajax);
			}
       // 10000W  
        if($total_money_4!=50 && $_REQUEST["bid_money"]>10000 && $_REQUEST["bid_money"]<=50000){
				  showErr('1w-5w只能使用50元的代金券',$ajax);
			}
       
        if($total_money_4!=100 && $_REQUEST["bid_money"]>50000){
				  showErr('5W以上只能使用100元的代金券',$ajax);
			} 	
		}
		
			

		
		//推荐金处理
	$referee_money=$GLOBALS['db']->getOne("SELECT `referee_money` FROM ".DB_PREFIX."user where id=".$ecv_user_id);
		    $adw['referee_money']=$referee_money-$total_money_3;
		//判断推荐奖励劵金额是有这么多
		if($adw['referee_money']<0){
			 showErr('你的推荐奖励劵金额不足',$ajax);
			}
			
			// 如果用过一次就补能在用了
		$n=(floor(($_REQUEST["bid_money"]+$total_money_3)/1000))*200;
		
		if($total_money_3<=$n){
			 //判断$total_money_3没有值时,$_REQUEST["bid_money"]不能少于1000;
			 
	 if(!$total_money_3){
   	  if($_REQUEST["bid_money"]<1000){
			   showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],$ajax);
           }		
	 } 
	 if($total_money_3){
   if($total_money_3+$_REQUEST["bid_money"]<1000){
		   showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],$ajax);
          }
	 }
		
	 
	 
	 
		if(!$GLOBALS['user_info'])
		showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'],$ajax);
		$deal = get_deal($id);
		$unjh_pfcfb=$_REQUEST["unjh_pfcfb"];
		if(trim($_REQUEST["bid_money"])=="" || !is_numeric($_REQUEST["bid_money"]) || floatval($_REQUEST["bid_money"])<=0){

			showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],$ajax);
		}  //2015-05-07改动、
		
		
		if((int)trim(app_conf('DEAL_BID_MULTIPLE')) > 0){
			 if(intval($_REQUEST["bid_money"])%(int)trim(app_conf('DEAL_BID_MULTIPLE'))!=0){
			 	showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],$ajax);
			 	exit();
			 }
		}
		
		if($unjh_pfcfb>$GLOBALS['user_info']['unjh_pfcfb']){
		  showErr("虚拟币操作错误",$ajax);
		}  //判断投资虚拟币是否大于本身拥有
		
		
		if(!$deal){
			showErr($GLOBALS['lang']['PLEASE_SPEC_DEAL'],$ajax);
		}
		
		if(floatval($deal['progress_point']) >= 100){
			showErr($GLOBALS['lang']['DEAL_BID_FULL'],$ajax);
		}
		
		if(floatval($deal['deal_status']) != 1 ){
			showErr($GLOBALS['lang']['DEAL_FAILD_OPEN'],$ajax);
		}
		

		if(floatval($_REQUEST["unjh_pfcfb"]) > $GLOBALS['user_info']['unjh_pfcfb']){
			showErr('浦发币不足，无法投标',$ajax);
		}
	//浦发财富b
		if($_REQUEST['unjh_pfcfb']){
			if($deal['repay_time']>=1&&$deal['repay_time_type']==1){
			$data['unjh_pfcfb']=$_REQUEST['unjh_pfcfb'];
			}
			else{
				showErr('只能用于投资1个月的标',$ajax);
			}
		}
		//判断所投的钱是否超过了剩余投标额度
		if(floatval($_REQUEST["bid_money"]) > ($deal['borrow_amount'] - $deal['load_money'])){
			showErr(sprintf($GLOBALS['lang']['DEAL_LOAN_NOT_ENOUGHT'],format_price($deal['borrow_amount'] - $deal['load_money'])),$ajax);
		}
		//判断推荐红包使用数额是否正确；
	 	require_once APP_ROOT_PATH."system/libs/user.php";	 
  	    if($total_money_3){
     	if($_REQUEST["bid_money"]+$total_money_3<1000){
			   showErr($GLOBALS['lang']['BID_MONEY_NOT_TRUE'],$ajax);
            }
		modify_account(array('referee_money'=>-$total_money_3,'score'=>0),$GLOBALS['user_info']['id'],"推荐人送投资卷扣".$total_money_3);

			}
	   if($total_money_4){
       if($total_money_4!='选择投资券'){
			$assa['user_id']=$ecv_user_id;
			
			//老用户使用字段['used_yn']=2;
			$assa['used_yn']=2;
			$assa['receive']=1;
			$assa['last_time']=get_gmtime();	
		    $GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$assa,"INSERT");//插入一条投资目录
     $assa_ecv['log_info'] ="使用投资代金劵".$total_money_4;
  	 $assa_ecv['log_time'] =get_gmtime();
	 $assa_ecv['money'] =0;
	  $assa_ecv['account_money'] =$GLOBALS['user_info']['money'];
	 $assa_ecv['user_id'] =$GLOBALS['user_info']['id'];
     $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$assa_ecv); 
             $laoyonghu['used_yn']=1;
	$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$laoyonghu,"UPDATE","ecv_type_id in(40,39,38,37) and user_id=".$ecv_user_id);	
  	
		}
}

     // 注册
			if($total_money_1){
		 $nob['log_time']= get_gmtime();
	   $assa_ecv['money'] =0;
	  $assa_ecv['account_money'] =$GLOBALS['user_info']['money'];
		 $nob['log_info']="使用了注册代金卷20金额";
		 $nob['user_id']= $ecv_user_id;
	  	 $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$nob,"INSERT");//
		    $ecec['used_yn']=1;
			$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$ecec,"UPDATE","ecv_type_id=42 and user_id=".$ecv_user_id);
			}
			// 投资
	        if($total_money_2){
     $user_ecv['log_info'] ="使用投资代金劵".$total_money_2;
  	 $user_ecv['log_time'] =get_gmtime();
		 $nob['log_time']= get_gmtime();
	   $assa_ecv['money'] =0;
	  $assa_ecv['account_money'] =$GLOBALS['user_info']['money'];
	 $user_ecv['user_id'] =$GLOBALS['user_info']['id'];
     $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$user_ecv); 	
				
		    $ecec['used_yn']=1;
			$GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$ecec,"UPDATE","ecv_type_id=".$ee_id." and user_id=".$ecv_user_id);
			}
	
	

		
		$data['user_id'] = $user_id=$GLOBALS['user_info']['id'];
		$data['user_name'] = $GLOBALS['user_info']['user_name'];
		$data['deal_id'] = $id;
		$data['money'] = trim($_REQUEST["bid_money"]);
		$data['create_time'] = get_gmtime();
		
		
			/*送浦发币
			if($deal['repay_time']>=1&&$deal['repay_time_type']==1){	
	           $nodeal=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_load where user_id=".$GLOBALS['user_info']['id']);
                if(!$nodeal){ //客户首次投资给推荐他的人送20可提现的现金红包
				/*
		        if($_REQUEST["bid_money"]>=10000){ 
		          $more_unjh_pfcfb=$GLOBALS['user_info']['unjh_pfcfb']+108;
                  $GLOBALS['db']->query("update ".DB_PREFIX."user set `unjh_pfcfb`=".$more_unjh_pfcfb." where id = ".$GLOBALS['user_info']['id']);    //投资一万送108	
                  $user_log_a['log_info']="_415活动_首次投资一个月资金10000以上就送108浦发币";
                  $user_log_a['log_time']=get_gmtime();                
                  $user_log_a['log_admin_id']=1;
                  $user_log_a['user_id']=$GLOBALS['user_info']['id'];
                  $user_log_a['unjh_pfcfb']=108;
                $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$user_log_a,"INSERT");//插入一条投资目录
		          }
	             if($_REQUEST["bid_money"]<10000){
		          $more_unjh_pfcfb=$GLOBALS['user_info']['unjh_pfcfb']+20;
                  $GLOBALS['db']->query("update ".DB_PREFIX."user set `unjh_pfcfb`=".$more_unjh_pfcfb." where id = ".$GLOBALS['user_info']['id']);    //投资一万送20
                  $user_log_a['log_info']="_415活动_首次投资一个月资金10000以下就送20浦发币";
                  $user_log_a['log_time']=get_gmtime();                
                  $user_log_a['log_admin_id']=1;
                  $user_log_a['user_id']=$GLOBALS['user_info']['id'];
                  $user_log_a['unjh_pfcfb']=20;
                $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$user_log_a,"INSERT");//插入一条投资目录				  
		          }
				  
				  
	             $pid_id=$GLOBALS['db']->getOne("select `pid` from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);
				 $pid_user_id=$GLOBALS['db']->getOne("select `pid` from ".DB_PREFIX."user where id=".$pid_id);
		 if($pid_id!=$pid_user_id){//本公司员工除外
		if($pid_id!=0){
		         $pid_pfcfb=$GLOBALS['db']->getOne("select `pfcfb` from ".DB_PREFIX."user where id=".$pid_id);	
		         $pid_pfcfbs=$pid_pfcfb+20;
		         $GLOBALS['db']->query("update ".DB_PREFIX."user set `pfcfb`=".$pid_pfcfbs." where id = ".$pid_id);   //推荐人得20浦发富币
                  $user_log_b['log_info']="_415活动_你推荐了用户".$GLOBALS['user_info']['user_name']."获得20可提现的浦发币";
                  $user_log_b['log_time']=get_gmtime();                
                  $user_log_b['log_admin_id']=1;
                  $user_log_b['user_id']=$pid_id;
                  $user_log_b['pfcfb']=20;
                $GLOBALS['db']->autoExecute(DB_PREFIX."user_log",$user_log_b,"INSERT");//插入一条投资目录							 
                                }
	               }	
            }				   
		}	
		
		*/

	//有效推荐人 $asa
	
// $huodong_time=1431619200; //2015.04-15  时间戳

 $huodong_time=1431619200;
$nodeal=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_load where user_id=".$GLOBALS['user_info']['id']);
	// $uc=array(1871,959,1877,971,1995,2006,2054);
	$no=0;
 if(!$nodeal){ 	
   $pid_id=$GLOBALS['db']->getOne("select `pid` from ".DB_PREFIX."user where id=".$GLOBALS['user_info']['id']);
           if($pid_id!=0){
          $w=0;   
         $asa= $GLOBALS['db']->getAll("select `id` from ".DB_PREFIX."user where `pid`=".$pid_id." and create_time>".$huodong_time);
	 
		foreach($asa as $k=>$v){
	      if($GLOBALS['db']->getAll("select id  from ".DB_PREFIX."deal_load where user_id=".$v['id'] )){
	     $w++;
	       } 
          }     
     
       if($w+1<10){

		$ox_money=15;
        }
       if($w+1>=11 && $w+1<=60){
		$ox_money=20; 
         }
       if($w+1>=61 && $w+1<=100){
		$ox_money=25; 
        }
       if($w+1>=101){
		 $ox_money=30;
       }
 	// foreach($uc as $k=>$v){
	  // if($v==$pid_id){	
	  // modify_account(array('money'=>30,'score'=>0),$pid_id,"推荐了".$GLOBALS['user_info']['id']."送30现金");
	   // $no=1;
	   // }
	 // }   
        // if($no==0){
		 modify_account(array('pfcfb'=>$ox_money,'score'=>0),$pid_id,"推荐了".$GLOBALS['user_info']['id']."获得".$ox_money."推荐奖励6月豪礼");
		// }
	
		
  }
  
  }
  
  	//以下为代金券判断操作
	 // if($_REQUEST['virtual_money']!=0)//判断复选框是否为勾选
		// {	
		// }
			// if($_REQUEST['virtual_money']!=0)//判断复选框是否为勾选
			// {
		
			// if($_REQUEST['virtual_money']>3200){
				// showErr('单笔投资代金券不能超过3200',$ajax);
			// }
			// if($deal['repay_time']==1&&$deal['repay_time_type']==1){  //一个月的标
				// $i=0;
				// foreach ($_REQUEST['v_money'] as $k=>$v){
				
				 // $sql = "select *,e.id as ecv_id from ".DB_PREFIX."ecv as e left join ".DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.user_id = ".$user_id." and e.used_yn=0 and (et.end_time=0 or et.end_time>" .time().  " ) and e.password=".$k." and et.money=".$v;
				/// showErr($sql,$ajax);
				// $one = $GLOBALS['db']->getRow($sql);
				// $virtual_money+=$one['money'];
					
				 // if(!$one)
				 // {
				 // showErr('代金券不存在,请联系客服',$ajax);
				 // }
				
					// $id_str.=($i==0)?$one['ecv_id']:','.$one['ecv_id'];
					// $i+=1; 
				 
				// }
				///showErr('代金券'.$id_str,$ajax);
				// if($virtual_money!=$_REQUEST['virtual_money']){
					// showErr('单笔投资代金券不能超过'.$_REQUEST['v_money'],$ajax);
				// showErr('代金券金额出错',$ajax);
				// }
				// else{
					// $data['virtual_money']=$_REQUEST['virtual_money'];  //记录data的虚拟金额
					///修改代金券已用
					// $GLOBALS['db']->query("update ".DB_PREFIX."ecv set used_yn = 1 where id in (".$id_str.")");											
					// }
				// require APP_ROOT_PATH.'app/Lib/uc.php';
			// }
			// else{
			// showErr('只能用于投资1个月的标',$ajax);
			// }
		// }
		
	   



		
		/*这里结束*/
		$bid_money = floatval($_REQUEST["bid_money"]);
	   if(!$bid_money){ 
	      showSuccess("金额错误",$ajax,url("index","uc_money#incharge"));
		 }		
		   // $user_deal= $GLOBALS['db']->getRow("select * from ".DB_PREFIX."deal where id= ".$id); 
		if($unjh_pfcfb>$GLOBALS['user_info']['unjh_pfcfb']){
		  showErr("虚拟币操作错误",$ajax);
		}  //判断投资虚拟币是否大于本身拥有
	    $status = dobid2($id,$bid_money,$bid_paypassword,1,$unjh_pfcfb,$virtual_money);
		
		if($status['status'] == 0){
			showErr($status['show_err'],$ajax);
		}elseif($status['status'] == 2){
			ajax_return($status);
		}elseif($status['status'] == 3){
			showSuccess("余额不足，请先去充值",$ajax,url("index","uc_money#incharge"));
		}else{	 
		//插入一条认购确认函 author @313616432
		//顶标用户除外$GLOBALS['user_info']['group_id']==1
  			$data_rg['deal_time']= get_gmtime()+24*3600;//录入时间
			$data_rg['admin_name']='系统';
			$data_rg['admin_id']=0;
			$data_rg['user_name']=$GLOBALS['user_info']['real_name'];
			$data_rg['produce_name']=$deal['name'];
			$data_rg['deal_monney']=trim($_REQUEST["bid_money"]);
			$data_rg['deal_sn']=$load_id;
			$data_rg['check_yn']=0;
			$data_rg['check_time']=0;
			$data_rg['voucher']=0;
			$data_temp['deal_time_type']=$deal['repay_time_type']?'个月':'天';
			$data_rg['longtime']=$deal['repay_time'].$data_temp['deal_time_type'];
			$data_rg['check_name']='';//确认时间
			$data_rg['cus_time']= get_gmtime();//购物时间
			$host='rdsf6fn32zmbb7j.mysql.rds.aliyuncs.com';
			$user='rengou';
			$password='dontGuess777';
			if($_SERVER['SERVER_NAME']!='localhost'){
			$con=mysql_connect("$host","$user","$password");
			mysql_select_db("rengou", $con);
			mysql_query("set names utf8"); 
			$sql="INSERT INTO `deal` (`".implode('`,`', array_keys($data_rg))."`) VALUES ('".implode("','", $data_rg)."')";
			$result=mysql_query($sql);//插入语句
			mysql_close($con);
			}		
			//showSuccess($GLOBALS['lang']['DEAL_BID_SUCCESS'],$ajax,url("index","deal",array("id"=>$id)));
			showSuccess($GLOBALS['lang']['DEAL_BID_SUCCESS'],$ajax,url("index","uc_invest"));
		}	
}else{
		 showErr('请输入正确的奖励劵金额',$ajax);
			
		
			
			
		}
			
	}
		
}
?>
