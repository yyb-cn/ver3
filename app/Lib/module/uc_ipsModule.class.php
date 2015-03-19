<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/uc.php';

class uc_ipsModule extends SiteBaseModule
{
	public function create()
	{
		$GLOBALS['tmpl']->assign("page_title","标的登记");
		
		$where = " where d.user_id = ".$GLOBALS["user_info"]["id"]." ";
		
		if(isset($_REQUEST['pMerCode'])&&strim($_REQUEST['pMerCode'])!='')
		{
			$where.=" and ".DB_PREFIX."ips_register_subject.pMerCode like '%".strim($_REQUEST['pMerCode'])."%'";
		}
		
		if(isset($_REQUEST['pBidNo'])&&strim($_REQUEST['pBidNo'])!='')
		{
			$where.=" and ".DB_PREFIX."ips_register_subject.pBidNo like '%".strim($_REQUEST['pBidNo'])."%'";
		}
		
		if(isset($_REQUEST['pMerBillNo'])&&strim($_REQUEST['pMerBillNo'])!='')
		{
			$where.=" and ".DB_PREFIX."ips_register_subject.pMerBillNo like '%".strim($_REQUEST['pMerBillNo'])."%'";
		}
		
		
		if(isset($_REQUEST['pOperationType']) && intval(strim($_REQUEST['pOperationType']))>=0)
			$where.=" and ".DB_PREFIX."ips_register_subject.pOperationType = '".intval($_REQUEST['pOperationType'])."'";
		
		
		if(isset($_REQUEST['pTrdCycleType'])&&intval(strim($_REQUEST['pTrdCycleType']))>=0)
			$where.=" and ".DB_PREFIX."ips_register_subject.pTrdCycleType = '".intval($_REQUEST['pTrdCycleType'])."'";
		

		if(isset($_REQUEST['pRepayMode']) && intval(strim($_REQUEST['pRepayMode']))>=0)
			$where.=" and ".DB_PREFIX."ips_register_subject.pRepayMode = '".intval($_REQUEST['pRepayMode'])."'";
			
		if(isset($_REQUEST['status']) && intval(strim($_REQUEST['status']))>=0)
			$where.=" and ".DB_PREFIX."ips_register_subject.status = '".intval($_REQUEST['status'])."'";	
		
		if(isset($_REQUEST['pAcctType']) && intval(strim($_REQUEST['pAcctType']))>=0)
			$where.=" and ".DB_PREFIX."ips_register_subject.pAcctType = '".intval($_REQUEST['pAcctType'])."'";	
		
		if(isset($_REQUEST['pBidStatus'])&&intval(strim($_REQUEST['pBidStatus']))>=0)
			$where.=" and ".DB_PREFIX."ips_register_subject.pBidStatus = '".intval($_REQUEST['pBidStatus'])."'";	
		
		$start_time = strim($_REQUEST['start_time']);
		$end_time = strim($_REQUEST['end_time']);
			
		$d = explode('-',$start_time);
		if (isset($_REQUEST['start_time']) && $start_time !="" && checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("开始时间不是有效的时间格式:{$start_time}(yyyy-mm-dd)");
			exit;
		}
		
		$d = explode('-',$end_time);
		if ( isset($_REQUEST['end_time']) && strim($end_time) !="" &&  checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("结束时间不是有效的时间格式:{$end_time}(yyyy-mm-dd)");
			exit;
		}
		
		if ($start_time!="" && strim($end_time) !="" && to_timespan($start_time) > to_timespan($end_time)){
			$this->error('开始时间不能大于结束时间:'.$start_time.'至'.$end_time);
			exit;
		}

		if(strim($start_time)!="")
		{
			$where .= " and UNIX_TIMESTAMP(pRegDate) >=".to_timespan(strim($start_time));
		}
		if(strim($end_time) !="")
		{
			$where .= " and UNIX_TIMESTAMP(pRegDate) <=".  to_timespan(strim($end_time));
		}
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ips_register_subject irs left join ".DB_PREFIX."deal d on irs.deal_id = d.id ".$where ." order by irs.id desc");
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$list_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ips_register_subject irs left join deal d on irs.deal_id = d.id ".$where);
		
		$page = new Page($list_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("start_time",$start_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_ips_create.html");
		$GLOBALS['tmpl']->display("page/uc.html");
		
	}
	public function recharge()
	{

		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['DO_INCHARGE']);
		
		$where = " where pErrCode ='MG00000F' and user_id = ".$GLOBALS["user_info"]["id"];
		//定义条件
		
		if(isset($_REQUEST['user_type'])&& intval(strim($_REQUEST['user_type']))>=0)
		{		
			$where.=" and user_type like '%".strim($_REQUEST['user_type'])."%'";
		}
		if(isset($_REQUEST['pMerCode']) && strim($_REQUEST['pMerCode'])!='')
		{		
			$where.=" and pMerCode like '%".strim($_REQUEST['pMerCode'])."%'";
		}
		if(isset($_REQUEST['pMerBillNo']) && strim($_REQUEST['pMerBillNo'])!='')
		{		
			$where.=" and pMerBillNo like '%".strim($_REQUEST['pMerBillNo'])."%'";
		}
		if(isset($_REQUEST['pIdentNo'])&&strim($_REQUEST['pIdentNo'])!='')
		{		
			$where.=" and pIdentNo like '%".strim($_REQUEST['pIdentNo'])."%'";
		}
		if(isset($_REQUEST['pRealName'])&&strim($_REQUEST['pRealName'])!='')
		{		
			$where.=" and pRealName like '%".strim($_REQUEST['pRealName'])."%'";
		}
		
		if(isset($_REQUEST['pIpsAcctNo']) && strim($_REQUEST['pIpsAcctNo'])!='')
		{		
			$where.=" and pIpsAcctNo like '%".strim($_REQUEST['pIpsAcctNo'])."%'";
		}
		
		if(isset($_REQUEST['pTrdAmt']) && strim($_REQUEST['pTrdAmt'])!='')
		{		
			$where.=" and pTrdAmt like '%".strim($_REQUEST['pTrdAmt'])."%'";
		}
		
		if(isset($_REQUEST['pChannelType'])&&strim($_REQUEST['pChannelType'])>=0)
		{
			$where.=" and pChannelType = ".strim($_REQUEST['pChannelType']);
		}
		if(isset($_REQUEST['pIpsFeeType'])&&strim($_REQUEST['pIpsFeeType'])>=0)
		{		
			$where.=" and pIpsFeeType = ".strim($_REQUEST['pIpsFeeType']);
		}
		$start_time = strim($_REQUEST['start_time']);
		$end_time = strim($_REQUEST['end_time']);
			
		$d = explode('-',$start_time);
		if (isset($_REQUEST['start_time']) && $start_time !="" && checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("开始时间不是有效的时间格式:{$start_time}(yyyy-mm-dd)");
			exit;
		}
		
		$d = explode('-',$end_time);
		if ( isset($_REQUEST['end_time']) && strim($end_time) !="" &&  checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("结束时间不是有效的时间格式:{$end_time}(yyyy-mm-dd)");
			exit;
		}
		
		if ($start_time!="" && strim($end_time) !="" && to_timespan($start_time) > to_timespan($end_time)){
			$this->error('开始时间不能大于结束时间:'.$start_time.'至'.$end_time);
			exit;
		}

		if(strim($start_time)!="")
		{
			$where .= " and UNIX_TIMESTAMP(pTrdDate) >=".to_timespan(strim($start_time));
		}
		if(strim($end_time) !="")
		{
			$where .= " and UNIX_TIMESTAMP(pTrdDate) <=".  to_timespan(strim($end_time));
		}
		//$name=$this->getActionName();
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ips_do_dp_trade ".$where);
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$list_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ips_do_dp_trade ".$where);
		
		$page = new Page($list_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("start_time",$start_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_ips_recharge.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function transfer()
	{
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_IPS_TRANSFER']);
		
		$where = " where pErrCode ='MG00000F' and user_id = ".$GLOBALS["user_info"]["id"];
		
		if(isset($_REQUEST['user_type'])&& intval(strim($_REQUEST['user_type']))>=0)
		{		
			$where.=" and user_type like '%".strim($_REQUEST['user_type'])."%'";
		}
		if(isset($_REQUEST['pMerCode']) && strim($_REQUEST['pMerCode'])!='')
		{		
			$where.=" and pMerCode like '%".strim($_REQUEST['pMerCode'])."%'";
		}
		if(isset($_REQUEST['pMerBillNo']) && strim($_REQUEST['pMerBillNo'])!='')
		{		
			$where.=" and pMerBillNo like '%".strim($_REQUEST['pMerBillNo'])."%'";
		}
		if(isset($_REQUEST['pContractNo']) && strim($_REQUEST['pContractNo'])!='')
		{		
			$where.=" and pContractNo like '%".strim($_REQUEST['pContractNo'])."%'";
		}
		if(isset($_REQUEST['pBidNo']) && strim($_REQUEST['pBidNo'])!='')
		{		
			$where.=" and pBidNo like '%".strim($_REQUEST['pBidNo'])."%'";
		}
		
		if(isset($_REQUEST['pIdentNo'])&&strim($_REQUEST['pIdentNo'])!='')
		{		
			$where.=" and pIdentNo like '%".strim($_REQUEST['pIdentNo'])."%'";
		}
		if(isset($_REQUEST['pRealName'])&&strim($_REQUEST['pRealName'])!='')
		{		
			$where.=" and pRealName like '%".strim($_REQUEST['pRealName'])."%'";
		}
		
		if(isset($_REQUEST['pIpsAcctNo']) && strim($_REQUEST['pIpsAcctNo'])!='')
		{		
			$where.=" and pIpsAcctNo like '%".strim($_REQUEST['pIpsAcctNo'])."%'";
		}
		
		if(isset($_REQUEST['pOutType']) && strim($_REQUEST['pOutType'])>=0)
		{		
			$where.=" and pOutType = ".strim($_REQUEST['pOutType']);
		}
		
		if(isset($_REQUEST['is_callback'])&&strim($_REQUEST['is_callback'])>=0)
		{
			$where.=" and is_callback = ".strim($_REQUEST['is_callback']);
		}
		if(isset($_REQUEST['pIpsFeeType'])&&strim($_REQUEST['pIpsFeeType'])>=0)
		{		
			$where.=" and pIpsFeeType = ".strim($_REQUEST['pIpsFeeType']);
		}
		$start_time = strim($_REQUEST['start_time']);
		$end_time = strim($_REQUEST['end_time']);
			
		$d = explode('-',$start_time);
		if (isset($_REQUEST['start_time']) && $start_time !="" && checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("开始时间不是有效的时间格式:{$start_time}(yyyy-mm-dd)");
			exit;
		}
		
		$d = explode('-',$end_time);
		if ( isset($_REQUEST['end_time']) && strim($end_time) !="" &&  checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("结束时间不是有效的时间格式:{$end_time}(yyyy-mm-dd)");
			exit;
		}
		
		if ($start_time!="" && strim($end_time) !="" && to_timespan($start_time) > to_timespan($end_time)){
			$this->error('开始时间不能大于结束时间:'.$start_time.'至'.$end_time);
			exit;
		}

		if(strim($start_time)!="")
		{
			$where .= " and UNIX_TIMESTAMP(pDwDate) >=".to_timespan(strim($start_time));
		}
		if(strim($end_time) !="")
		{
			$where .= " and UNIX_TIMESTAMP(pDwDate) <=".  to_timespan(strim($end_time));
		}
		
		$list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."ips_do_dw_trade ".$where);
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$list_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ips_do_dw_trade ".$where);
		
		$page = new Page($list_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("start_time",$start_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_ips_transfer.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function creditor()
	{
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SPACE_LEND']);
		
		$where = " where  pErrCode ='MG00000F'  and a.user_id = ".$GLOBALS["user_info"]["id"];
		
		if(isset($_REQUEST['pMerCode'])&&strim($_REQUEST['pMerCode'])!='')
		{
			$where.=" and pMerCode like '%".strim($_REQUEST['pMerCode'])."%'";
		}
		
		if(isset($_REQUEST['pBidNo'])&&strim($_REQUEST['pBidNo'])!='')
		{
			$where.=" and pBidNo like '%".strim($_REQUEST['pBidNo'])."%'";
		}
		
		
		if(isset($_REQUEST['pMerBillNo'])&&strim($_REQUEST['pMerBillNo'])!='')
		{
			$where.=" and pMerBillNo like '%".strim($_REQUEST['pMerBillNo'])."%'";
		}
		
		if(isset($_REQUEST['pContractNo'])&&strim($_REQUEST['pContractNo'])!='')
		{		
			$where.=" and pContractNo like '%".strim($_REQUEST['pContractNo'])."%'";
		}
		
		if(isset($_REQUEST['pAccountDealNo'])&&strim($_REQUEST['pAccountDealNo'])!='')
		{		
			$where.=" and pAccountDealNo like '%".strim($_REQUEST['pAccountDealNo'])."%'";
		}
		
		if(isset($_REQUEST['pRegType'])&&intval(strim($_REQUEST['pRegType']))>=0)
			$where.=" and pRegType = '".intval($_REQUEST['pRegType'])."'";
		
		
		if(isset($_REQUEST['pAcctType']) && intval(strim($_REQUEST['pAcctType']))>=0)
			$where.=" and pAcctType = '".intval($_REQUEST['pAcctType'])."'";

		
		if(isset($_REQUEST['pAccount'])&&strim($_REQUEST['pAccount'])!='')
			$where.=" and pAccount = '".intval($_REQUEST['pAccount'])."'";
			
		
		if(isset($_REQUEST['pAccount'])&& intval(strim($_REQUEST['pStatus']))>=0)
			$where.=" and pStatus = '".intval($_REQUEST['pStatus'])."'";	
		
		
		if(isset($_REQUEST['pP2PBillNo'])&&strim($_REQUEST['pP2PBillNo'])!='')
			$where.=" and pP2PBillNo = '".$_REQUEST['pP2PBillNo']."'";	
		
		
		$start_time = strim($_REQUEST['start_time']);
		$end_time = strim($_REQUEST['end_time']);
			
		$d = explode('-',$start_time);
		if (isset($_REQUEST['start_time']) && $start_time !="" && checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("开始时间不是有效的时间格式:{$start_time}(yyyy-mm-dd)");
			exit;
		}
		
		$d = explode('-',$end_time);
		if ( isset($_REQUEST['end_time']) && strim($end_time) !="" &&  checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("结束时间不是有效的时间格式:{$end_time}(yyyy-mm-dd)");
			exit;
		}
		
		if ($start_time!="" && strim($end_time) !="" && to_timespan($start_time) > to_timespan($end_time)){
			$this->error('开始时间不能大于结束时间:'.$start_time.'至'.$end_time);
			exit;
		}

		if(strim($start_time)!="")
		{
			$where .= " and UNIX_TIMESTAMP(pMerDate) >=".to_timespan(strim($start_time));
		}
		if(strim($end_time) !="")
		{
			$where .= " and UNIX_TIMESTAMP(pMerDate) <=".  to_timespan(strim($end_time));
		}
		
		$list = $GLOBALS['db']->getAll("select a.*,b.name as deal_name from ".DB_PREFIX."ips_register_creditor as a left join ".DB_PREFIX."deal as b on a.deal_id = b.id ".$where);
		
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$list_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."ips_register_creditor as a left join ".DB_PREFIX."deal as b on a.deal_id = b.id ".$where);
		
		$page = new Page($list_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign("list",$list);
		$GLOBALS['tmpl']->assign("start_time",$start_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_ips_creditor.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function repayment()
	{
		$GLOBALS['tmpl']->assign("page_title","还款单");
		
		$condition = " where ips.pErrCode = 'MG00000F' and d.user_id = ".$GLOBALS["user_info"]["id"];
		
		$sql = "select ips.*,ips.id as mid,d.`name` as deal_name,u.user_name, dr.* from ".DB_PREFIX."ips_repayment_new_trade as ips
left join ".DB_PREFIX."deal d on d.id = ips.deal_id
left join ".DB_PREFIX."deal_repay dr on dr.id = ips.deal_repay_id 
left join ".DB_PREFIX."user u on u.id = d.user_id ";
		
		$count_sql = "select count(*) from ".DB_PREFIX."ips_repayment_new_trade as ips
left join ".DB_PREFIX."deal d on d.id = ips.deal_id
left join ".DB_PREFIX."deal_repay dr on dr.id = ips.deal_repay_id 
left join ".DB_PREFIX."user u on u.id = d.user_id ";
		
		
		
		if(strim($_REQUEST['pMerCode'])!='')
		{		
			$condition .= " and ips.pMerCode like '%".strim($_REQUEST['pMerCode'])."%'";
		}

		if(strim($_REQUEST['pMerBillNo'])!='')
		{		
			$condition .= " and ips.pMerBillNo like '%".strim($_REQUEST['pMerBillNo'])."%'";
		}
		
		if(strim($_REQUEST['pBidNo'])!='')
		{		
			$condition .= " and ips.pBidNo like '%".strim($_REQUEST['pBidNo'])."%'";
		}
		
		if(isset($_REQUEST['pRepayType'])&&intval($_REQUEST['pRepayType'])!=-1)
		{		
			$condition .= " and ips.pRepayType = " .intval($_REQUEST['pRepayType']);
		}

		if(strim($_REQUEST['pIpsAuthNo'])!='')
		{		
			$condition .= " and ips.pIpsAuthNo like '%" .strim($_REQUEST['pIpsAuthNo'])."%'";
		}
		
		if(strim($_REQUEST['pOutAcctNo'])!='')
		{		
			$condition .= " and ips.pOutAcctNo like '%" .strim($_REQUEST['pOutAcctNo'])."%'";
		}
		
		if(strim($_REQUEST['pIpsBillNo'])!="")
		{		
			$condition .= " and ips.pIpsBillNo like '%" .strim($_REQUEST['pIpsBillNo'])."%'";
		}
		
		
		$start_time = strim($_REQUEST['start_time']);
		$end_time = strim($_REQUEST['end_time']);
			
		$d = explode('-',$start_time);
		if (isset($_REQUEST['start_time']) && $start_time !="" && checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("开始时间不是有效的时间格式:{$start_time}(yyyy-mm-dd)");
			exit;
		}
		
		$d = explode('-',$end_time);
		if ( isset($_REQUEST['end_time']) && strim($end_time) !="" &&  checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("结束时间不是有效的时间格式:{$end_time}(yyyy-mm-dd)");
			exit;
		}
		
		if ($start_time!="" && strim($end_time) !="" && to_timespan($start_time) > to_timespan($end_time)){
			$this->error('开始时间不能大于结束时间:'.$start_time.'至'.$end_time);
			exit;
		}
		if(strim($start_time)!="")
		{
			$condition .= " and UNIX_TIMESTAMP(pRepaymentDate) >=".to_timespan(strim($start_time));
		}
		if(strim($end_time) !="")
		{
			$condition .= " and UNIX_TIMESTAMP(pRepaymentDate) <=".  to_timespan(strim($end_time));
		}
		
		$list = $GLOBALS['db']->getAll( $sql.$condition);
		
		foreach($list as $k => $v)
		{
			if($v["status"] == 0)
			{
				$v["status"] = "提前";
			}
			if($v["status"] == 1)
			{
				$v["status"] = "准时";
			}
			if($v["status"] == 2)
			{
				$v["status"] = "逾期";
			}
			if($v["status"] == 3)
			{
				$v["status"] = "严重逾期";
			}
			$list[$k]["status"] = $v["status"];
		}

		//取得满足条件的记录数
		$list_count = $GLOBALS['db']->getOne($count_sql.$condition);
		//print_r($count);die;
		//$name=$this->getActionName();
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$page = new Page($list_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign("start_time",$start_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_ips_repayment.html");
		$GLOBALS['tmpl']->display("page/uc.html");
		
	}
	public function repayment_view()
	{
		$GLOBALS['tmpl']->assign("page_title","还款单明细");
		
		$condition = " where 1 = 1 ";
		
		$sql = "select d.*,r.*,d.id as mid ,u.user_name,dl.name as deal_name,tu.user_name as t_user_name from ".DB_PREFIX."ips_repayment_new_trade_detail as d
left JOIN ".DB_PREFIX."deal_load_repay r on r.id = d.deal_load_repay_id
left join ".DB_PREFIX."user u on u.id = r.user_id
left join ".DB_PREFIX."user tu on tu.id = r.t_user_id left join ".DB_PREFIX."deal as dl on r.deal_id = dl.id";
		
		$count_sql = "select count(*) from ".DB_PREFIX."ips_repayment_new_trade_detail as d
left JOIN ".DB_PREFIX."deal_load_repay r on r.id = d.deal_load_repay_id
left join ".DB_PREFIX."user u on u.id = r.user_id
left join ".DB_PREFIX."user tu on tu.id = r.t_user_id left join ".DB_PREFIX."deal as dl on r.deal_id = dl.id";

		//print_r($sql.$condition);die;
		
		if(isset($_REQUEST['id'])&&intval(strim($_REQUEST['id']))>0)
		{		
			$condition .= " and d.pid = ".intval(strim($_REQUEST['id']));
			
			$GLOBALS['tmpl']->assign('id', intval(strim($_REQUEST['id'])));
		}
		else
		{
			return;
			//$this->error (l("INVALID_OPERATION"),$ajax);
		}
		
		if(strim($_REQUEST['pCreMerBillNo'])!='')
		{		
			$condition .= " and pCreMerBillNo like '%".strim($_REQUEST['pCreMerBillNo'])."%'";
		}

		if(isset($_REQUEST['pStatus'])&&intval(strim($_REQUEST['pStatus']))!=-1)
		{		
			$condition .= " and pStatus ="."'". strim($_REQUEST['pStatus'])."'";
		}
		
		if(strim($_REQUEST['pInAcctNo'])!='')
		{		
			$condition .= " and pInAcctNo like '%".strim($_REQUEST['pInAcctNo'])."%'";
		}
		//print_r($sql.$condition);die;
		$list = $GLOBALS['db']->getAll( $sql.$condition);

		foreach($list as $k => $v)
		{
			if($v["status"] == 0)
			{
				$v["status"] = "提前";
			}
			if($v["status"] == 1)
			{
				$v["status"] = "准时";
			}
			if($v["status"] == 2)
			{
				$v["status"] = "逾期";
			}
			if($v["status"] == 3)
			{
				$v["status"] = "严重逾期";
			}
			$list[$k]["status"] = $v["status"];
		}

		//取得满足条件的记录数
		$list_count = $GLOBALS['db']->getOne($count_sql.$condition);
		//print_r($count);die;
		//$name=$this->getActionName();
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$page = new Page($list_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign('list',$list);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_ips_repayment_view.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function fullscale()
	{
		$GLOBALS['tmpl']->assign("page_title","满标放款");
		
		$condition = " and t.pErrCode = 'MG00000F'  and d.user_id = ".$GLOBALS["user_info"]["id"];
		
		$sql = "select t.*,d.`name`,u.user_name from ".DB_PREFIX."ips_transfer as t
LEFT JOIN ".DB_PREFIX."deal as d on d.id = t.deal_id
LEFT JOIN ".DB_PREFIX."user as u on u.id = d.user_id where t.pTransferType = 1 ";
		
		$count_sql = "select count(*) from ".DB_PREFIX."ips_transfer as t
LEFT JOIN ".DB_PREFIX."deal as d on d.id = t.deal_id
LEFT JOIN ".DB_PREFIX."user as u on u.id = d.user_id where t.pTransferType = 1 ";

		if(strim($_REQUEST['pMerCode'])!='')
		{		
			$condition .= " and t.pMerCode like '%".strim($_REQUEST['pMerCode'])."%'";
		}

		if(strim($_REQUEST['pMerBillNo'])!='')
		{		
			$condition .= " and t.pMerBillNo like '%".strim($_REQUEST['pMerBillNo'])."%'";
		}
		
		if(strim($_REQUEST['pBidNo'])!='')
		{		
			$condition .= " and t.pBidNo like '%".strim($_REQUEST['pBidNo'])."%'";
		}
		
		if(isset($_REQUEST['pTransferType'])&&intval(strim($_REQUEST['pTransferType']))!=-1)
		{		
			$condition .= " and t.pTransferType = " .intval(strim($_REQUEST['pTransferType']));
		}
		
		if(isset($_REQUEST['pTransferMode'])&&intval(strim($_REQUEST['pTransferMode']))!=-1)
		{		
			$condition .= " and t.pTransferMode = " .intval(strim($_REQUEST['pTransferMode']));
		}
		
		if(isset($_REQUEST['pIpsBillNo'])&&strim($_REQUEST['pIpsBillNo'])!="")
		{		
			$condition .= " and t.pIpsBillNo = " .strim($_REQUEST['pIpsBillNo']);
		}
		
		$start_time = strim($_REQUEST['start_time']);
		$end_time = strim($_REQUEST['end_time']);
			
		$d = explode('-',$start_time);
		if (isset($_REQUEST['start_time']) && $start_time !="" && checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("开始时间不是有效的时间格式:{$start_time}(yyyy-mm-dd)");
			exit;
		}
		
		$d = explode('-',$end_time);
		if ( isset($_REQUEST['end_time']) && strim($end_time) !="" &&  checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("结束时间不是有效的时间格式:{$end_time}(yyyy-mm-dd)");
			exit;
		}
		
		if ($start_time!="" && strim($end_time) !="" && to_timespan($start_time) > to_timespan($end_time)){
			$this->error('开始时间不能大于结束时间:'.$start_time.'至'.$end_time);
			exit;
		}
		if(strim($start_time)!="")
		{
			$condition .= " and UNIX_TIMESTAMP(pDate) >=".to_timespan(strim($start_time));
		}
		if(strim($end_time) !="")
		{
			$condition .= " and UNIX_TIMESTAMP(pDate) <=".  to_timespan(strim($end_time));
		}
		
		//取得满足条件的记录数
		$list = $GLOBALS['db']->getAll( $sql.$condition);
		$list_count = $GLOBALS['db']->getOne($count_sql.$condition);
		//print_r($count);die;
		//$name=$this->getActionName();
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$page = new Page($list_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign("start_time",$start_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_ips_fullscale.html");
		$GLOBALS['tmpl']->display("page/uc.html");
		
	}
	public function fullscale_view()
	{
		$GLOBALS['tmpl']->assign("page_title","满标放款明细");
		
		$condition = " where 1=1 ";
		
		$sql = "select t.*,t.id as mid,l.* from ".DB_PREFIX."ips_transfer_detail as t LEFT JOIN ".DB_PREFIX."ips_transfer it on t.pid = it.id
LEFT JOIN ".DB_PREFIX."deal_load l on l.deal_id = it.deal_id and l.pMerBillNo = t.pOriMerBillNo ";
		
		
		$count_sql = "select count(*) from ".DB_PREFIX."ips_transfer_detail as t LEFT JOIN ".DB_PREFIX."ips_transfer it on t.pid = it.id
LEFT JOIN ".DB_PREFIX."deal_load l on l.deal_id = it.deal_id and l.pMerBillNo = t.pOriMerBillNo ";

		
		if(isset($_REQUEST['id'])&&intval(strim($_REQUEST['id']))>0)
		{		
			$condition .= " and t.pid = ".intval(strim($_REQUEST['id']));
			$GLOBALS['tmpl']->assign('id',intval(strim($_REQUEST['id'])));
		}
		else
		{
			return;
			//$this->error (l("INVALID_OPERATION"),$ajax);
		}
		//print_r($sql);die;
		
		if(strim($_REQUEST['pOriMerBillNo'])!='')
		{		
			$condition .= " and pOriMerBillNo like '%".strim($_REQUEST['pOriMerBillNo'])."%'";
		}
		
		if(isset($_REQUEST['pFAcctType'])&&intval(strim($_REQUEST['pFAcctType']))!=-1)
		{		
			$condition .= " and pFAcctType = ". strim($_REQUEST['pFAcctType']);
		}

		if(isset($_REQUEST['pTAcctType'])&&intval(strim($_REQUEST['pTAcctType']))!=-1)
		{		
			$condition .= " and pTAcctType = ". strim($_REQUEST['pTAcctType']);
		}

		if(isset($_REQUEST['pStatus'])&&intval(strim($_REQUEST['pStatus']))!=-1)
		{		
			$condition .= " and pStatus ="."'". strim($_REQUEST['pStatus'])."'";
		}
		
		if(strim($_REQUEST['pTIpsAcctNo'])!='')
		{		
			$condition .= " and pTIpsAcctNo like '%".strim($_REQUEST['pTIpsAcctNo'])."%'";
		}
		
		if(strim($_REQUEST['pFIpsAcctNo'])!='')
		{		
			$condition .= " and pFIpsAcctNo like '%".strim($_REQUEST['pFIpsAcctNo'])."%'";
		}
		
		if(strim($_REQUEST['pIpsDetailBillNo'])!='')
		{		
			$condition .= " and pIpsDetailBillNo like '%".strim($_REQUEST['pIpsDetailBillNo'])."%'";
		}
		
		$list = $GLOBALS['db']->getAll( $sql.$condition);
		//取得满足条件的记录数
		$list_count = $GLOBALS['db']->getOne($count_sql.$condition);
		//print_r($count);die;
		//$name=$this->getActionName();
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$page = new Page($list_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign("start_time",$start_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_ips_fullscale_view.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
	public function ips_transfer()
	{
		$GLOBALS['tmpl']->assign("page_title","债权转让");

		$condition = " and t.pErrCode = 'MG00000F' and dlt.user_id = ".$GLOBALS["user_info"]["id"]." or dlt.t_user_id = ".$GLOBALS["user_info"]["id"];
		
		$sql = "select t.*,d.`name`,u.user_name,tu.user_name as t_user_name from ".DB_PREFIX."ips_transfer as t
LEFT JOIN ".DB_PREFIX."deal as d on d.id = t.deal_id
LEFT JOIN ".DB_PREFIX."deal_load_transfer as dlt on dlt.id = t.ref_data
LEFT JOIN ".DB_PREFIX."user as u on u.id = d.user_id
left join ".DB_PREFIX."user tu on tu.id = dlt.t_user_id
where t.pTransferType = 4 ";
		
		$count_sql = "select count(*) from ".DB_PREFIX."ips_transfer as t
LEFT JOIN ".DB_PREFIX."deal as d on d.id = t.deal_id
LEFT JOIN ".DB_PREFIX."deal_load_transfer as dlt on dlt.id = t.ref_data
LEFT JOIN ".DB_PREFIX."user as u on u.id = d.user_id
left join ".DB_PREFIX."user tu on tu.id = dlt.t_user_id
where t.pTransferType = 4 ";
		
		if(strim($_REQUEST['pMerCode'])!='')
		{		
			$condition .= " and t.pMerCode like '%".strim($_REQUEST['pMerCode'])."%'";
		}

		if(strim($_REQUEST['pMerBillNo'])!='')
		{		
			$condition .= " and t.pMerBillNo like '%".strim($_REQUEST['pMerBillNo'])."%'";
		}
		
		if(strim($_REQUEST['pBidNo'])!='')
		{		
			$condition .= " and t.pBidNo like '%".strim($_REQUEST['pBidNo'])."%'";
		}
		
		if(strim($_REQUEST['pIpsBillNo'])!='')
		{		
			$condition .= " and t.pIpsBillNo like '%".strim($_REQUEST['pIpsBillNo'])."%'";
		}
		
		if(isset($_REQUEST['pTransferType'])&&intval(strim($_REQUEST['pTransferType']))!=-1)
		{		
			$condition .= " and t.pTransferType = " .intval(strim($_REQUEST['pTransferType']));
		}
		
		if(isset($_REQUEST['pTransferMode'])&&intval(strim($_REQUEST['pTransferMode']))!=-1)
		{		
			$condition .= " and t.pTransferMode = " .intval(strim($_REQUEST['pTransferMode']));
		}
		
		$start_time = strim($_REQUEST['start_time']);
		$end_time = strim($_REQUEST['end_time']);
			
		$d = explode('-',$start_time);
		if (isset($_REQUEST['start_time']) && $start_time !="" && checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("开始时间不是有效的时间格式:{$start_time}(yyyy-mm-dd)");
			exit;
		}
		
		$d = explode('-',$end_time);
		if ( isset($_REQUEST['end_time']) && strim($end_time) !="" &&  checkdate($d[1], $d[2], $d[0]) == false){
			$this->error("结束时间不是有效的时间格式:{$end_time}(yyyy-mm-dd)");
			exit;
		}
		
		if ($start_time!="" && strim($end_time) !="" && to_timespan($start_time) > to_timespan($end_time)){
			$this->error('开始时间不能大于结束时间:'.$start_time.'至'.$end_time);
			exit;
		}
		if(strim($start_time)!="")
		{
			$condition .= " and UNIX_TIMESTAMP(pDate) >=".to_timespan(strim($start_time));
		}
		if(strim($end_time) !="")
		{
			$condition .= " and UNIX_TIMESTAMP(pDate) <=".  to_timespan(strim($end_time));
		}

		//取得满足条件的记录数
		$list = $GLOBALS['db']->getAll( $sql.$condition);
		$list_count = $GLOBALS['db']->getOne($count_sql.$condition);
		//print_r($count);die;
		//$name=$this->getActionName();
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$page = new Page($list_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('user_id',$GLOBALS["user_info"]["id"]);
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign("start_time",$start_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_ips_ips_transfer.html");
		$GLOBALS['tmpl']->display("page/uc.html");
		
	}
	public function ips_transfer_view()
	{
		$GLOBALS['tmpl']->assign("page_title","债权转让明细");	
			
		$condition = "  ";
		
		if(isset($_REQUEST['id'])&&intval(strim($_REQUEST['id']))>0)
		{		
			
			//$condition .= " and t.pid = ".intval(strim($_REQUEST['id']));
			$GLOBALS['tmpl']->assign('id',intval(strim($_REQUEST['id'])));
		}
		else
		{
			return ;
			//$this->error (l("INVALID_OPERATION"),$ajax);
		}
		
		$p_sql = "select dlt.* from ".DB_PREFIX."ips_transfer as t
LEFT JOIN ".DB_PREFIX."deal as d on d.id = t.deal_id
LEFT JOIN ".DB_PREFIX."deal_load_transfer as dlt on dlt.id = t.ref_data
LEFT JOIN ".DB_PREFIX."user as u on u.id = d.user_id
left join ".DB_PREFIX."user tu on tu.id = dlt.t_user_id
where t.pTransferType = 4 and t.id = ".intval($_REQUEST['id']);
		
		$load_info = $GLOBALS['db']->getRow($p_sql);
		
		if(!$load_info)
		{
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
		
		$sql = "select dlr.*,d.name as deal_name,u.user_name,tu.user_name as t_user_name from ".DB_PREFIX."deal_load_repay as dlr LEFT JOIN ".DB_PREFIX."user as u on u.id = dlr.user_id left join ".DB_PREFIX."user as tu on tu.id = dlr.t_user_id left join ".DB_PREFIX."deal as d on dlr.deal_id = d.id where dlr.load_id =".intval($load_info['load_id']) . " and dlr.user_id =".intval($load_info['user_id']) . " and dlr.deal_id = ".$load_info["deal_id"];
		
		$count_sql = "select count(*) from ".DB_PREFIX."deal_load_repay as dlr LEFT JOIN ".DB_PREFIX."user as u on u.id = dlr.user_id left join ".DB_PREFIX."user as tu on tu.id = dlr.t_user_id left join ".DB_PREFIX."user as tu on tu.id = dlr.t_user_id left join ".DB_PREFIX."deal as d on dlr.deal_id = d.id where dlr.load_id =".intval($load_info['load_id']) . " and dlr.user_id =".intval($load_info['user_id']) . " and dlr.deal_id = ".$load_info["deal_id"];
		
		if(isset($_REQUEST['status'])&&intval(strim($_REQUEST['status']))!=-1)
		{		
			$condition .= " and dlr.status = ".intval(strim($_REQUEST['status']));
		}
		
		if(isset($_REQUEST['is_site_repay'])&&intval(strim($_REQUEST['is_site_repay']))!=-1)
		{		
			$condition .= " and dlr.is_site_repay = ".intval(strim($_REQUEST['is_site_repay']));
		}
		
		if(isset($_REQUEST['has_repay'])&&intval(strim($_REQUEST['has_repay']))!=-1)
		{		
			$condition .= " and dlr.has_repay = ".intval(strim($_REQUEST['has_repay']));
		}
		
		$list = $GLOBALS['db']->getAll( $sql.$condition);
		//print_r($sql.$condition);die;
		//取得满足条件的记录数
		$list_count = $GLOBALS['db']->getOne($count_sql.$condition);
		//print_r($sql.$condition);die;
		//print_r( $sql.$condition);die;
		//print_r($count);die;
		//$name=$this->getActionName();
		$page = intval($_REQUEST['p']);
		if($page==0)
		$page = 1;
		$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
		
		$page = new Page($list_count,app_conf("PAGE_SIZE"));   //初始化分页对象 		
		$p  =  $page->show();
		$GLOBALS['tmpl']->assign('pages',$p);
		
		$GLOBALS['tmpl']->assign('list',$list);
		$GLOBALS['tmpl']->assign("start_time",$start_time);
		$GLOBALS['tmpl']->assign("end_time",$end_time);
		
		$GLOBALS['tmpl']->assign("inc_file","inc/uc/uc_ips_ips_transfer_view.html");
		$GLOBALS['tmpl']->display("page/uc.html");
	}
}
?>