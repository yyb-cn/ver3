<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_voucherModule extends SiteBaseModule
{
	public function index()
	{
		 
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$result = get_voucher_list($limit,$GLOBALS['user_info']['id']);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_VOUCHER']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_voucher_index.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
//	public function incharge()
//	{
//		 
//		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_VOUCHER_INCHARGE']);
//		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_voucher_incharge.html");
//		$GLOBALS['tmpl']->display("page/uc.html");
//	}
//	
//	public function do_incharge()
//	{
//		$ecvsn = addslashes(trim($_REQUEST['sn']));
//		$ecvpassword = addslashes(trim($_REQUEST['password']));
//		$now = TIME_UTC;
//		$user_id = $GLOBALS['user_info']['id'];
//		$ecv_sql = "select e.* from ".DB_PREFIX."ecv as e left join ".
//					DB_PREFIX."ecv_type as et on e.ecv_type_id = et.id where e.sn = '".
//					$ecvsn."' and e.password = '".
//					$ecvpassword."' and ((e.begin_time <> 0 and e.begin_time < ".$now.") or e.begin_time = 0) and ".
//					"((e.end_time <> 0 and e.end_time > ".$now.") or e.end_time = 0) and ((e.use_limit <> 0 and e.use_limit > e.use_count) or (e.use_limit = 0)) ".
//					"and (e.user_id = ".$user_id." or e.user_id = 0)";
//		$ecv_data = $GLOBALS['db']->getRow($ecv_sql);
//		if($ecv_data)
//		{
//			$ecv_money = $ecv_data['money'];
//			$msg = sprintf($GLOBALS['lang']['VOUCHER_INCHARGE_LOG'],$ecv_data['sn'],format_price($ecv_money));
//			require_once APP_ROOT_PATH."system/libs/user.php";
//			modify_account(array('money'=>$ecv_money,'score'=>0),$user_id,$msg);
//			$GLOBALS['db']->query("update ".DB_PREFIX."ecv set use_count = use_count + 1 where id = ".$ecv_data['id']);
//			showSuccess($msg);
//		}
//		else
//		{
//			showErr($GLOBALS['lang']['INVALID_VOUCHER']);
//		}
//	}
	
	public function exchange()
	{
		 
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		$result = get_exchange_voucher_list($limit);
		
		$GLOBALS['tmpl']->assign("list",$result['list']);
		$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
	
		
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_VOUCHER']);
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_voucher_exchange.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	
	public function do_exchange()
	{
		$id = intval($_REQUEST['id']);
		$ecv_type = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where id = ".$id);
		if(!$ecv_type)
		{
			showErr($GLOBALS['lang']['INVALID_VOUCHER']);
		}
		else
		{
			$exchange_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ecv where ecv_type_id = ".$id." and user_id = ".intval($GLOBALS['user_info']['id']));
			if($ecv_type['exchange_limit']>0&&$exchange_count>=$ecv_type['exchange_limit'])
			{
				$msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_LIMIT'],$ecv_type['exchange_limit']);
				showErr($msg);
			}
			elseif($ecv_type['exchange_score']>intval($GLOBALS['db']->getOne("select score from ".DB_PREFIX."user where id = ".intval($GLOBALS['user_info']['id']))))
			{
				showErr($GLOBALS['lang']['INSUFFCIENT_SCORE']);
			}
			else
			{
				require_once APP_ROOT_PATH."system/libs/voucher.php";
				$rs = send_voucher($ecv_type['id'],$GLOBALS['user_info']['id'],1);
				if($rs)
				{
					require_once APP_ROOT_PATH."system/libs/user.php";
					$msg = sprintf($GLOBALS['lang']['EXCHANGE_VOUCHER_USE_SCORE'],$ecv_type['name'],$ecv_type['exchange_score']);
					modify_account(array('money'=>0,'score'=>"-".$ecv_type['exchange_score']),$GLOBALS['user_info']['id'],$msg,22);
					showSuccess($GLOBALS['lang']['EXCHANGE_SUCCESS']);
				}
				else
				{
					showSuccess($GLOBALS['lang']['EXCHANGE_FAILED']);
				}
			}
		}
	}
	  public function get_voucher ()
        {
            $eid = intval($_REQUEST['eid']);
			//print_r($eid);exit;
            $user_id = intval($_REQUEST['user_id']);
            $time_limitd = intval($_REQUEST['time_limitd']);
            
            $ecv_record = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv where id = ".$eid." and user_id = ".$user_id);
            
            if($ecv_record){
                //领取成功 将receive值改为1
                $last_time = get_gmtime()+$time_limitd*24*3600;
                $sql = '';
                if(isset($time_limitd)){
                    
                    $sql = ",last_time = ".$last_time;
                }
                $receive_time = get_gmtime();

                $re = $GLOBALS['db']->query("update ".DB_PREFIX."ecv set receive = 1,receive_time = ".$receive_time.$sql." where id = ".$eid);
                if($re){
                    
                    showSuccess("领取成功");
                    
                }  else {
                    
                    showErr("领取失败");
                    
                }
                
                
            }else{
                //用户没有代金券记录
                showErr("没有获得代金券条件");
                
            }
            
        }
	 public function get_new_voucher()
        {
            $id = $_REQUEST['id'];//23/24/25/26
            $user_id= $GLOBALS['user_info']['id'];
            

        

            $ecv = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv where user_id = ".$user_id." and ecv_type_id in ('46','45','44','43')");
            
            $ecv_record = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where id = ".$id);
           $olduser=$GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_load where user_id = ".$user_id);
         if($olduser){
		    showErr("新注册用户才可领取",0,APP_ROOT."/");
		 }
            if(!$ecv && $ecv_record){
                $ecv_info['user_id'] = $user_id;
                $ecv_info['used_yn'] = 0;
                $ecv_info['receive'] = 1;
                $ecv_info['begin_time'] = $ecv_record['begin_time'];
                $ecv_info['end_time'] = $ecv_record['end_time'];        
                $ecv_info['receive_time'] = get_gmtime();
                $ecv_info['last_time'] = get_gmtime()+ 7*24*3600;
                $ecv_info['ecv_type_id'] = $id;
                $ecv_info['password'] = rand(10000000,99999999);
                $ecv_info['sn'] = uniqid();
                $GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$ecv_info);

                showSuccess("领取成功",0,"index.php?ctl=deals");
            }   else {
                
                showErr("领取失败",0,APP_ROOT."/");
                

            }
        }

}
?>