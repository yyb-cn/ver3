<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

class IpsFullscaleAction extends CommonAction{
	public function index()
	{
		$condition = " and t.pErrCode = 'MG00000F' ";
		
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
		$count = $GLOBALS['db']->getOne($count_sql.$condition);
		//print_r($count);die;
		//$name=$this->getActionName();
		if ($count > 0) {
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = '';
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据
			$voList = $GLOBALS['db']->getAll($sql.$condition." limit ".$p->firstRow . ',' . $p->listRows);
//			echo $model->getlastsql();
			//分页跳转的时候保证查询条件
			foreach ( $_REQUEST as $key => $val ) {
				if (! is_array ( $val )) {
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
			}
			
			$page = $p->show ();
			//模板赋值显示
			$this->assign ( 'list', $voList );
			$this->assign ( "page", $page );
			$this->assign ( "nowPage",$p->nowPage);
		}
		$this->display ();
		
	}
	/*
	public function delete() {
		//删除指定记录
		
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("ips_transfer")->where($condition)->findAll();
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
					$result = M("ips_transfer_detail")->where(" pid = ".$data["id"])->count();
					if($result>0)
					{
						save_log($info.l("DELETE_FAILED"),0);
						$this->error (l("INVALID_DELETE"),$ajax);
						//return;
					}
				}
				if($info) $info = implode(",",$info);
				$list = M("ips_transfer")->where ( $condition )->delete();
				if ($list!==false) {
					save_log($info.l("DELETE_SUCCESS"),1);
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}*/
	public function view()
	{
		$id = intval($_REQUEST['id']);
		if(!$id)
		{
			$this->error(l("INVALID_ORDER"));
			return;
		}
		$sql = "select t.*,d.`name`,u.user_name from ".DB_PREFIX."ips_transfer as t
LEFT JOIN ".DB_PREFIX."deal as d on d.id = t.deal_id
LEFT JOIN ".DB_PREFIX."user as u on u.id = d.user_id where t.pTransferType = 1 and t.id =".$id;
		
		$ips_info = $GLOBALS['db']->getRow($sql);
		
		if(!$ips_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		
		//
		
		if($ips_info["deal_id"])
		{
			$ips_info["deal_name"] = M("deal")->where(" id=".$ips_info['deal_id'])->getField("name");
		}
	
		$ips_info["is_callback"] =  l("IPS_IS_CALLBACK_".$ips_info["is_callback"]);
		
		if($ips_info["pTransferType"]!="")
		{
			$ips_info["pTransferType"] =  l("P_TRANSFER_TYPE_".$ips_info["pTransferType"]);
		}
		if($ips_info["pTransferMode"]!="")
		{
			$ips_info["pTransferMode"] =  l("P_TRANSFER_MODE_".$ips_info["pTransferMode"]);
		}
		
		$this->assign("ips_info",$ips_info);
		
		$this->display();
	}

	public function export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		$sql = "select t.*,d.`name`,u.user_name from ".DB_PREFIX."ips_transfer as t
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

		$list = $GLOBALS['db']->getAll($sql.$condition." limit ".$limit);
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'export_csv'), $page+1);
			
			$list_value_old = array(
				'id'=>'""', 
				'name'=>'""', 
				'ref_data' => '""',
				'pMerCode'=>'""',
				'pMerBillNo'=>'""', 
				'pBidNo'=>'""', 
				'pDate'=>'""',
				'pTransferType'=>'""',
				'pTransferMode'=>'""', 
				'pMemo1'=>'""',
				'pMemo2'=>'""',
				'pMemo3'=>'""', 
				'pIpsBillNo'=>'""', 
				'pIpsTime'=>'""', 
				'user_name'=>'""'
			);
			
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,贷款名称,参考,平台账号,商户开户流水号,标的号,商户日期,转账类型,转账方式,备注1,备注2,备注3,IPS订单号,IPS处理时间,借款人");	    		    	
		    	$content = $content . "\n";
	    	}

			foreach($list as $k=> $v)
			{
				$list_value = $list_value_old;
				
				$list_value["id"] = '"' . iconv('utf-8','gbk', $v['id']) . '"';
				
				$list_value["name"] = '"' . iconv('utf-8','gbk', $v['name']) . '"';

				$list_value["ref_data"] =  '"' . iconv('utf-8','gbk',  $v["ref_data"]). '"';
				
				$list_value["pMerCode"] =  '"' . iconv('utf-8','gbk', $v["pMerCode"]). '"';
				
				$list_value["pMerBillNo"] =  '"' . iconv('utf-8','gbk', $v["pMerBillNo"]). '"';
				
				$list_value["pBidNo"] =  '"' . iconv('utf-8','gbk', $v["pBidNo"]). '"';
				
				$list_value["pDate"] =  '"' . iconv('utf-8','gbk', $v["pDate"]). '"';
				
				$list_value["pTransferType"] =  '"' . iconv('utf-8','gbk', l("P_TRANSFER_TYPE_".$v["pTransferType"])). '"';
				
				$list_value["pTransferMode"] =  '"' . iconv('utf-8','gbk', l("P_TRANSFER_MODE_".$v["pTransferMode"])). '"';

				$list_value["pMemo1"] =  '"' . iconv('utf-8','gbk', $v["pMemo1"]).'"';
				
				$list_value["pMemo2"] =  '"' . iconv('utf-8','gbk', $v["pMemo2"]). '"';
				
				$list_value["pMemo3"] =  '"' . iconv('utf-8','gbk', $v["pMemo3"]). '"';
				
				$list_value["pIpsTime"] =  '"' . iconv('utf-8','gbk', $v["pIpsTime"]). '"';
				
				$list_value["pIpsBillNo"] =  '"' . iconv('utf-8','gbk', $v["pIpsBillNo"]). '"';
				
				$list_value["user_name"] =  '"' . iconv('utf-8','gbk', $v["user_name"]). '"';

				$content .= implode(",", $list_value) . "\n";
			}	
			
			header("Content-Disposition: attachment; filename=order_list.csv");
	    	echo $content;  
		}
		else
		{
			if($page==1)
			$this->error(L("NO_RESULT"));
		}	
		
	}

	public function relation_list()
	{
		/**
			id
			pid
			pOriMerBillNo  		//原商户订单号
			pTrdAmt				//转账金额
			pFAcctType			//转出方账户类型   0#机构；1#个人
			pFIpsAcctNo			//转出方IPS托管账户号
			pFTrdFee			//转出方明细手续
			pTAcctType			//转入方账户类型  否  0#机构；1#个人
			pTIpsAcctNo			//转入方IPS托管账户号
			pTTrdFee			//转入方明细手续
			pIpsDetailBillNo	//IPS明细订单号
			pIpsDetailTime		//IPS明细处理时间
			pIpsFee				//IPS手续费
			pStatus				//转帐状态 Y#转账成功；N#转账失败
			pMessage			//转账备注
			mid
			deal_id				//借款ID
			user_id 			//投标人ID
			user_name			//用户名
			money				//投标金额
			create_time			//投标时间
			is_repay			//流标是否已返还
			is_rebate			//是否已返利
			is_auto				//是否为自动投标    0:收到 1:自动
			pP2PBillNo			//IPS P2P订单号
			pConTractNo			//合同号
			pMerBillNo			//登记债权人时提 交的订单号
			is_has_loans		//是否已经放款给招标人
			msg					//转账备注
		**/
		
		/*
			
			 ,deal_id|get_deal_name:{%DEAL_NAME}
			 ,user_id|get_user_name_by_id:{%USER_NAME}
			 ,user_name:{%P_USER_NAME}
			 ,money:{%P_TRANSFER_MONEY}
			 ,create_time:{%P_CREATE_TIME}
			 ,is_repay|get_is_repay:{%IS_REPAY}
			 ,is_rebate|get_is_rebate:{%IS_REBATE}
			 ,is_auto|get_is_auto:{%IS_AUTO}
			 ,pP2PBillNo:{%P_P2P_BILL_NO}
			 ,pConTractNo:{%P_CON_TRACT_NO}
			 ,pMerBillNo:{%P_MER_BILL_NO}
			 ,is_has_loans|get_is_has_loans:{%IS_HAS_LOANS}
		*/
		
		$condition = " where 1=1 ";
		
		$sql = "select t.*,t.id as mid,l.* from ".DB_PREFIX."ips_transfer_detail as t
LEFT JOIN ".DB_PREFIX."deal_load l on l.deal_id = t.pid and l.pMerBillNo = t.pOriMerBillNo ";
		
		$count_sql = "select count(*) from ".DB_PREFIX."ips_transfer_detail as t
LEFT JOIN ".DB_PREFIX."deal_load l on l.deal_id = t.pid and l.pMerBillNo = t.pOriMerBillNo ";

		
		if(isset($_REQUEST['id'])&&intval(strim($_REQUEST['id']))>0)
		{		
			$condition .= " and t.pid = ".intval(strim($_REQUEST['id']));
			$this->assign ( "id", intval(strim($_REQUEST['id'])) );
		}
		else
		{
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
		
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
		
		//取得满足条件的记录数
		$count = $GLOBALS['db']->getOne($count_sql.$condition);
		//print_r($count);die;
		//$name=$this->getActionName();
		if ($count > 0) {
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = '';
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据
			$voList = $GLOBALS['db']->getAll($sql.$condition." limit ".$p->firstRow . ',' . $p->listRows);
			//print_r($condition);
//			echo $model->getlastsql();
			//分页跳转的时候保证查询条件
			foreach ( $_REQUEST as $key => $val ) {
				if (! is_array ( $val )) {
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
			}
			
			$page = $p->show ();
			//模板赋值显示

			$this->assign ( 'list', $voList );
			$this->assign ( "page", $page );
			$this->assign ( "nowPage",$p->nowPage);
		}
		$this->display ();
	}
	/*
	public function relation_delete() {
		//删除指定记录
		
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("ips_transfer_detail")->where($condition)->findAll();
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M("ips_transfer_detail")->where ( $condition )->delete();
				if ($list!==false) {
					save_log($info.l("DELETE_SUCCESS"),1);
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}*/
	public function relation_view()
	{
		$id = intval($_REQUEST['id']);
		if(!$id)
		{
			$this->error(l("INVALID_ORDER"));
			return;
		}
		$sql = "select t.*,t.id as mid,l.* from ".DB_PREFIX."ips_transfer_detail as t LEFT JOIN ".DB_PREFIX."ips_transfer it on t.pid = it.id
LEFT JOIN  ".DB_PREFIX."deal_load l on l.deal_id = it.deal_id and l.pMerBillNo = t.pOriMerBillNo  where t.id =".$id;
		
		$ips_info = $GLOBALS['db']->getRow($sql);
		
		if(!$ips_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		
		if(strim($ips_info['pFAcctType'])!='')
		{
			$ips_info["pFAcctType"]= l("P_ACCT_TYPE_".strim($ips_info['pFAcctType']));
		}
		if(strim($ips_info['pTAcctType'])!='')
		{
			$ips_info["pTAcctType"]= l("P_ACCT_TYPE_".strim($ips_info['pTAcctType']));
		}
		if(strim($ips_info['pStatus'])!='')
		{
			$ips_info["pStatus"]= l("P_TRANSFER_STATUS_".strim($ips_info['pStatus']));
		}
		if(strim($ips_info['deal_id']))
		{
			$ips_info["deal_name"]= M("Deal")->where(" id=".strim($ips_info['deal_id']))->getField("name");
		}
		if(strim($ips_info['user_id'])!='')
		{
			$ips_info["p_user_name"] = M("User")->where(" id=".strim($ips_info['user_id']))->getField("user_name");;
		}
		
		if(strim($ips_info['is_repay'])!='')
		{
			$ips_info["is_repay"]= l("IS_REPAY_".strim($ips_info['is_repay']));
		}
		
		if(strim($ips_info['is_rebate'])!='')
		{
			$ips_info["is_rebate"]= l("IS_REBATE_".strim($ips_info['is_rebate']));
		}
		
		if(strim($ips_info['is_auto'])!='')
		{
			$ips_info["is_auto"]= l("IS_AUTO_".strim($ips_info['is_auto']));
		}
		
		if(strim($ips_info['is_has_loans'])!='')
		{
			$ips_info["is_has_loans"]= l("IS_HAS_LOANS_".strim($ips_info['is_has_loans']));
		}
		
		
		$this->assign("ips_info",$ips_info);
		
		$this->display();
	}
	public function relation_export_csv($page = 1)
	{
		
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		$condition = " where 1=1 ";
		
		$sql = "select t.*,t.id as mid,l.* from ".DB_PREFIX."ips_transfer_detail as t LEFT JOIN ".DB_PREFIX."ips_transfer it on t.pid = it.id
LEFT JOIN ".DB_PREFIX."deal_load l on l.deal_id = it.deal_id and l.pMerBillNo = t.pOriMerBillNo ";
		
		if(isset($_REQUEST['id'])&&intval(strim($_REQUEST['id']))>0)
		{		
			$condition .= " and t.pid = ".intval(strim($_REQUEST['id']));
			$this->assign ( "id", intval(strim($_REQUEST['id'])) );
		}
		else
		{
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
		
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
		
		
		$list = $GLOBALS['db']->getAll($sql.$condition." limit ".$limit);
			
		if($list)
		{
			register_shutdown_function(array(&$this, 'relation_export_csv'), $page+1);
			
			$list_value = array('id'=>'""', 'pOriMerBillNo'=>'""', 'pTrdAmt'=>'""','pFAcctType'=>'""', 'pFIpsAcctNo'=>'""', 'pFTrdFee'=>'""','pTAcctType'=>'""','pTIpsAcctNo'=>'""','pTTrdFee'=>'""', 'pIpsDetailBillNo'=>'""', 'pIpsDetailTime'=>'""', 'pIpsFee'=>'""','pStatus'=>'""',  'pMessage'=>'""','deal_name'=>'""','p_user_name'=>'""', 'user_name'=>'""', 'money'=>'""', 'create_time'=>'""', 'is_repay'=>'""', 'is_rebate'=>'""','is_auto'=>'""','pP2PBillNo'=>'""', 'pContractNo'=>'""','pMerBillNo'=>'""','is_has_loans'=>'""','msg'=>'""');
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,原商户订单号,转账金额,转出方账户类型,转出方IPS托管账户号,转出方明细手续费,转入方账户类型,转入方IPS托管账户号,转入方明细手续费,IPS明细订单号,IPS明细处理时间,IPS手续费,转帐状态,转账备注,借款名称,投标人,用户名,投标金额,投标时间,流标是否已返还,是否已返利,是否为自动投标,IPS P2P订单号,合同号,登记债权人时提交的订单号,是否已经放款给招标人,转账备注");	    		    	
		    	$content = $content . "\n";
	    	}

			foreach($list as $k=> $v)
			{
				$list_value["pOriMerBillNo"] =  '"' . iconv('utf-8','gbk', $v["pOriMerBillNo"]). '"';
				
				$list_value["id"] =  '"' . iconv('utf-8','gbk', $v["mid"]). '"';
				
				$list_value["pTrdAmt"] =  '"' . iconv('utf-8','gbk', $v["pTrdAmt"]). '"';
				
				if($v['pFAcctType']!='')
				{
					$list_value["pFAcctType"] =  '"' . iconv('utf-8','gbk', l("P_ACCT_TYPE_".strim($v['pFAcctType']))). '"';
				}
				else
				{
					$list_value["pFAcctType"] = "";
				}
				
				if($v['pTAcctType']!='')
				{
					$list_value["pTAcctType"] =  '"' . iconv('utf-8','gbk', l("P_ACCT_TYPE_".strim($v['pTAcctType']))). '"';		
				}
				else
				{
					$list_value["pTAcctType"] = "";
				}
				
				$list_value["pFIpsAcctNo"] = '"' . iconv('utf-8','gbk', $v["pFIpsAcctNo"]) . '"';
				
				$list_value["pTIpsAcctNo"] = '"' . iconv('utf-8','gbk', $v["pTIpsAcctNo"]) . '"';
				
				$list_value["pFTrdFee"] = '"' . iconv('utf-8','gbk', $v["pFTrdFee"]) . '"';
				
				$list_value["pTTrdFee"] = '"' . iconv('utf-8','gbk', $v["pTTrdFee"]) . '"';
				
				if(strim($v['pStatus'])!="")
				{
					$list_value["pStatus"] =  '"' . iconv('utf-8','gbk', l("P_TRANSFER_STATUS_".strim($v['pStatus']))) . '"';
				}
				else
				{
					$list_value["pStatus"] = '""';
				}
				
				$list_value["pIpsDetailBillNo"] = '"' . iconv('utf-8','gbk', $v["pIpsDetailBillNo"]) . '"';
				
				$list_value["pIpsDetailTime"] = '"' . iconv('utf-8','gbk', $v["pIpsDetailTime"]) . '"';
				
				$list_value["pIpsFee"] = '"' . iconv('utf-8','gbk', $v["pIpsFee"]) . '"';
				
				$list_value["deal_name"] =  '"' . iconv('utf-8','gbk',  M("deal")->where(" id=".strim($v['deal_id']))->getField("name")). '"';
				
				$list_value["pMessage"] =  '"' . iconv('utf-8','gbk', $v["pMessage"]). '"';
				
				if(strim($v['user_id'])!='')
				{
					$list_value["p_user_name"] =  '"' . iconv('utf-8','gbk',  M("user")->where(" id=".strim($v['user_id']))->getField("user_name")). '"';
				}
				else
				{
					$list_value["p_user_name"] = "";
				}
				
				$list_value["user_name"] =  '"' . iconv('utf-8','gbk',  $v["user_name"]). '"';
				
				$list_value["money"] =  '"' . iconv('utf-8','gbk',  $v["money"]). '"';
				
				$list_value["create_time"] =  '"' . iconv('utf-8','gbk', $v["create_time"]). '"';
				
				if(strim($v['is_repay'])!="")
				{
					$list_value["is_repay"] =  '"' . iconv('utf-8','gbk', l("IS_REPAY_".strim($v['is_repay']))) . '"';
				}
				else
				{
					$list_value["is_repay"] = '""';
				}
				
				if(strim($v['is_rebate'])!="")
				{
					$list_value["is_rebate"] =  '"' . iconv('utf-8','gbk', l("IS_REBATE_".strim($v['is_rebate']))) . '"';
				}
				else
				{
					$list_value["is_rebate"] = '""';
				}
				
				if(strim($v['is_auto'])!="")
				{
					$list_value["is_auto"] =  '"' . iconv('utf-8','gbk', l("IS_AUTO_".strim($v['is_auto']))) . '"';
				}
				else
				{
					$list_value["is_auto"] = '""';
				}
				
				$list_value["pP2PBillNo"] =  '"' . iconv('utf-8','gbk',  $v["pP2PBillNo"]). '"';
				
				$list_value["pContractNo"] =  '"' . iconv('utf-8','gbk',  $v["pContractNo"]). '"';
				
				$list_value["pMerBillNo"] =  '"' . iconv('utf-8','gbk',  $v["pMerBillNo"]). '"';
				
				if(strim($v['is_has_loans'])!="")
				{
					$list_value["is_has_loans"] =  '"' . iconv('utf-8','gbk', l("IS_HAS_LOANS_".strim($v['is_has_loans']))) . '"';
				}
				else
				{
					$list_value["is_has_loans"] = '""';
				}
				
				$list_value["msg"] =  '"' . iconv('utf-8','gbk',  $v["msg"]). '"';

				$content .= implode(",", $list_value) . "\n";
			}	
			
			
			header("Content-Disposition: attachment; filename=order_list.csv");
	    	echo $content;  
		}
		else
		{
			if($page==1)
			$this->error(L("NO_RESULT"));
		}	
		
	}
	
}
?>