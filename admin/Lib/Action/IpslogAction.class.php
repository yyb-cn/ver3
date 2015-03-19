<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

class IpslogAction extends CommonAction{
	public function create()
	{
		$condition = " pErrCode ='MG00000F' ";
		//平台账号
		if(strim($_REQUEST['argMerCode'])!='')
		{		
			$condition .= " and argMerCode like '%".strim($_REQUEST['argMerCode'])."%'";
		}
		//开户流水号
		if(strim($_REQUEST['pMerBillNo'])!='')
		{		
			$condition .= " and pMerBillNo like '%".strim($_REQUEST['pMerBillNo'])."%'";
		}
		//用户类型 P_USER_TYPE
		if(isset($_REQUEST['user_type'])&&intval(strim($_REQUEST['user_type']))!=-1)
		{		
			$condition .= " and user_type =" .intval(strim($_REQUEST['user_type']));
		}

		//手机号 P_MOBILE_NO
		if(strim($_REQUEST['pMobileNo'])!='')
		{		
			$condition .= " and pMobileNo  like '%".strim($_REQUEST['pMobileNo'])."%'";
		}
		
		//开户状态
		if(isset($_REQUEST['pStatus'])&&intval(strim($_REQUEST['pStatus']))!=-1)
		{		
			$condition .= " and pStatus = ".intval(strim($_REQUEST['pStatus']));
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
			$condition .= " and UNIX_TIMESTAMP(pSmDate) >=".to_timespan(strim($start_time));
		}
		if(strim($end_time) !="")
		{
			$condition .= " and UNIX_TIMESTAMP(pSmDate) <=".  to_timespan(strim($end_time));
		}

		//$name=$this->getActionName();
		$model = D ("ips_create_new_acct");
		if (! empty ( $model )) {
			$this->_list ( $model, $condition );
		}
		
		$this->display ();
		
	}
	
	public function view()
	{
		$id = intval($_REQUEST['id']);
		$ips_info = M("ips_create_new_acct")->where("id=".$id)->find();
		if(!$ips_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		//$ips_info_items = M("ips_create_new_acct")->where(" id=".$ips_info['id'])->findAll();

		if($ips_info["user_type"] == 0 )
		{
			$ips_info["user_name"] = M("user")->where(" id=".$ips_info['user_id'])->getField("user_name");
		}
		else if($ips_info["user_type"] == 1)
		{
			$ips_info["user_name"] = M("deal_agency")->where(" id=".$ips_info['user_id'])->getField("name");
		}
		if($ips_info["pStatus"])
		{		
			$ips_info["pStatus"] = l("IPS_STATUS_".$ips_info["pStatus"]);
		}
		
		
		$ips_info["user_type"] = l("IPS_TYPE_".$ips_info["user_type"]);
		$ips_info["pIdentType"] =  l("IPS_IDENT_TYPE_".$ips_info["pIdentType"]);
		$ips_info["is_callback"] = l("IPS_IS_CALLBACK_".$ips_info["is_callback"]);
		$ips_info["pPhStatus"] = l("IPS_PASS_".intval($ips_info["pPhStatus"]));
		$ips_info["pCardStatus"] = l("IPS_PASS_".intval($ips_info["pCardStatus"]));
		
		$this->assign("ips_info",$ips_info);
		
		$this->display();
	}

	public function export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		$where = "  pErrCode ='MG00000F'  ";

		
		//定义条件
		
		if(isset($_REQUEST['argMerCode'])&&strim($_REQUEST['argMerCode'])!='')
		{
			$where.=" and ".DB_PREFIX."ips_create_new_acct.argMerCode like '%".strim($_REQUEST['argMerCode'])."%'";
		}
		
		if(isset($_REQUEST['pMerBillNo'])&&strim($_REQUEST['pMerBillNo'])!='')
		{
			$where.=" and ".DB_PREFIX."ips_create_new_acct.pMerBillNo like '%".strim($_REQUEST['pMerBillNo'])."%'";
		}
		
		if(intval(strim($_REQUEST['user_type']))>=0)
			$where.=" and ".DB_PREFIX."ips_create_new_acct.user_type = '".intval(strim($_REQUEST['user_type']))."'";
		
		if(strim($_REQUEST['pMobileNo'])!='')
		{		
			$where .= " and ips_create_new_acct.pMobileNo  like '%".strim($_REQUEST['pMobileNo'])."%'";
		}		

		if(isset($_REQUEST['pStatus'])&&intval(strim($_REQUEST['pStatus']))>=0)
			$where.=" and ".DB_PREFIX."ips_create_new_acct.pStatus = '".intval($_REQUEST['pStatus'])."'";
		
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
			$where .= " and UNIX_TIMESTAMP(pSmDate) >=".to_timespan(strim($start_time));
		}
		if(strim($end_time) !="")
		{
			$where .= " and UNIX_TIMESTAMP(pSmDate) <=".  to_timespan(strim($end_time));
		}
		
		$list = M("ips_create_new_acct")
				->where($where)
				->field(DB_PREFIX.'ips_create_new_acct.*')
				->limit($limit)->findAll ( );
				
		if($list)
		{
			register_shutdown_function(array(&$this, 'export_csv'), $page+1);
			
			$list_value_old = array('id'=>'""', 'user_name'=>'""', 'user_type'=>'""','argMerCode'=>'""', 'pMerBillNo'=>'""', 'pIdentType'=>'""','pIdentNo'=>'""','pRealName'=>'""', 'pMobileNo'=>'""', 'pEmail'=>'""', 'pSmDate'=>'""', 'pMemo1'=>'""', 'pMemo2'=>'""','pMemo3'=>'""','pStatus'=>'""', 'pBankName'=>'""', 'pBkAccName'=>'""', 'pBkAccNo'=>'""', 'pCardStatus'=>'""','pPhStatus'=>'""', 'pIpsAcctNo'=>'""', 'pIpsAcctDate'=>'""','pMerCode'=>'""');
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,用户名,用户类型,平台账号,商户开户流水号,证件类型,证件号码,姓名,手机号,注册邮箱,提交日期,备注1,备注2,备注3,开户状态,银行名称,户名,银行卡账号,身份证状态,手机状态,IPS托管平台账户号,IPS开户日期,平台账号(返回)");	    		    	
		    	$content = $content . "\n";
	    	}

			
			foreach($list as $k=> $v)
			{
				$list_value = $list_value_old;
				if($v["user_type"] == 0)
				{
					$list_value["user_name"] = '"' . iconv('utf-8','gbk', M("deal_agency")->where(" id=".$v['user_id'])->getField("name")) . '"';
				}
				else if($v["user_type"] == 1)
				{
					$list_value["user_name"] = '"' . iconv('utf-8','gbk', M("user")->where(" id=".$v['user_id'])->getField("user_name")) . '"';
					
				}

				$list_value["user_type"] = '"' . iconv('utf-8','gbk', l("IPS_TYPE_".$v["user_type"])) . '"';
				$list_value["pIdentType"] =  '"' . iconv('utf-8','gbk',  l("IPS_IDENT_TYPE_".$v["pIdentType"])) . '"';
				$list_value["is_callback"] = '"' . iconv('utf-8','gbk', l("IPS_IS_CALLBACK_".$v["is_callback"])) . '"';
				$list_value["pPhStatus"] = '"' . iconv('utf-8','gbk', l("IPS_PASS_".$v["pPhStatus"])) . '"';
				$list_value["pCardStatus"] = '"' . iconv('utf-8','gbk', l("IPS_PASS_".$v["pCardStatus"])) . '"';
				
				$list_value["id"] =  '"' . iconv('utf-8','gbk', $v["id"]). '"';
				
				$list_value["argMerCode"] =  '"' . iconv('utf-8','gbk',  $v["argMerCode"]). '"';
				
				$list_value["pMerBillNo"] =  '"' . iconv('utf-8','gbk', $v["pMerBillNo"]). '"';
				
				$list_value["pIdentNo"] =  '"' . iconv('utf-8','gbk',  $v["pIdentNo"]). '"';
				
				$list_value["pRealName"] =  '"' . iconv('utf-8','gbk',  $v["pRealName"]). '"';
				
				$list_value["pMobileNo"] =  '"' . iconv('utf-8','gbk',  $v["pMobileNo"]). '"';
				
				$list_value["pEmail"] =  '"' . iconv('utf-8','gbk',  $v["pEmail"]). '"';
				
				$list_value["pSmDate"] =  '"' . iconv('utf-8','gbk',  $v["pSmDate"]). '"';
				
				$list_value["pMemo1"] =  '"' . iconv('utf-8','gbk',  $v["pMemo1"]). '"';
				
				$list_value["pMemo2"] =  '"' . iconv('utf-8','gbk',  $v["pMemo2"]). '"';
				
				$list_value["pMemo3"] =  '"' . iconv('utf-8','gbk',  $v["pMemo3"]). '"';
				
				$list_value["pBankName"] =  '"' . iconv('utf-8','gbk',  $v["pBankName"]). '"';
				
				$list_value["pBkAccName"] =  '"' . iconv('utf-8','gbk',  $v["pBkAccName"]). '"';
				
				$list_value["pBkAccNo"] =  '"' . iconv('utf-8','gbk',  $v["pBkAccNo"]). '"';
				
				$list_value["pIpsAcctNo"] =  '"' . iconv('utf-8','gbk',  $v["pIpsAcctNo"]). '"';
				
				$list_value["pIpsAcctDate"] =  '"' . iconv('utf-8','gbk',  $v["pIpsAcctDate"]). '"';
				
				$list_value["pMerCode"] =  '"' . iconv('utf-8','gbk',  $v["pMerCode"]). '"';

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

	/*public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("ips_create_new_acct")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['pMerBillNo'];	
				}
				if($info) $info = implode(",",$info);
				$list = M("ips_create_new_acct")->where ( $condition )->delete();
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
	}
	*/
	/*标的登记*/
	public function trade()
	{
		$where = "  1=1  ";
		
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
		//print_r($where);die;
		//$name=$this->getActionName();
		$model = D ("ips_register_subject");
		if (! empty ( $model )) {
			$this->_list ( $model, $where );
		}
		
		$this->display ();
	}
	
	public function trade_export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		

		$where = "  pErrCode ='MG00000F'  ";
		//定义条件
		
		
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

		
		$list = M("ips_register_subject")
				->where($where)
				->field(DB_PREFIX.'ips_register_subject.*')
				->limit($limit)->findAll ( );
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'trade_export_csv'), $page+1);
			
			$list_value_old = array('id'=>'""', 'pMerCode'=>'""', 'deal_name'=>'""','status'=>'""', 'pMerBillNo'=>'""', 'pBidNo'=>'""','pRegDate'=>'""','pLendAmt'=>'""', 'pGuaranteesAmt'=>'""', 'pTrdLendRate'=>'""', 'pTrdCycleValue'=>'""','pTrdCycleType'=>'""',  'pLendPurpose'=>'""','pRepayMode'=>'""','pOperationType'=>'""', 'pLendFee'=>'""', 'pAcctType'=>'""', 'pIdentNo'=>'""', 'pRealName'=>'""','pIpsAcctNo'=>'""', 'pMemo1'=>'""', 'pMemo2'=>'""','pMemo3'=>'""', 'pIpsBillNo'=>'""', 'pIpsTime'=>'""','pBidStatus'=>'""', 'pRealFreezenAmt'=>'""');
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,平台账号,贷款名称,状态,商户订单号,标的号,商户日期,借款金额,借款保证金,借款利率,借款周期值,借款周期类型,借款用途,还款方式,标的操作类型,借款人手续费,账户类型,证件号码,姓名,IPS账户号,备注1,备注2,备注3,IPS商户订单号,IPS处理时间,标的状态,实际冻结金额");	    		    	
		    	$content = $content . "\n";
	    	}

			
			foreach($list as $k=> $v)
			{
				$list_value = $list_value_old;
				
				$list_value["id"] =  '"' . iconv('utf-8','gbk', $v["id"]). '"';
				
				$list_value["status"] =  '"' . iconv('utf-8','gbk', l("P_T_STATUS_".$v["pTrdCycleType"])). '"';
				
				$list_value["pMerCode"] =  '"' . iconv('utf-8','gbk', $v["pMerCode"]). '"';
				
				$list_value["deal_name"] = '"' . iconv('utf-8','gbk', M("deal")->where(" id=".$v["id"])->getField("name")) . '"';
				
				$list_value["pMerBillNo"] = '"' . iconv('utf-8','gbk', $v["pMerBillNo"]) . '"';
				
				$list_value["pBidNo"] =  '"' . iconv('utf-8','gbk', $v["pBidNo"]) . '"';
				
				$list_value["pRegDate"] = '"' . iconv('utf-8','gbk', $v["pRegDate"]) . '"';
				
				$list_value["pLendAmt"] = '"' . iconv('utf-8','gbk', $v["pLendAmt"]) . '"';
				
				$list_value["pGuaranteesAmt"] = '"' . iconv('utf-8','gbk', $v["pGuaranteesAmt"]) . '"';
				
				$list_value["pTrdLendRate"] =  '"' . iconv('utf-8','gbk',  $v["pTrdLendRate"]). '"';
				
				$list_value["pTrdCycleType"] =  '"' . iconv('utf-8','gbk', l("P_TRD_CYCLE_TYPE_".$v["pTrdCycleType"])). '"';
				
				$list_value["pTrdCycleValue"] =  '"' . iconv('utf-8','gbk',  $v["pTrdCycleValue"]). '"';
				
				$list_value["pLendPurpose"] =  '"' . iconv('utf-8','gbk',  $v["pLendPurpose"]). '"';
				
				$list_value["pRepayMode"] =  '"' . iconv('utf-8','gbk',  l("P_REPAY_MODE_".$v["pRepayMode"])). '"';
				
				$list_value["pOperationType"] =  '"' . iconv('utf-8','gbk', l("P_OPERACTION_TYPE_".$v["pOperationType"])). '"';
				
				$list_value["pLendFee"] =  '"' . iconv('utf-8','gbk',  $v["pLendFee"]). '"';
				
				$list_value["pAcctType"] =  '"' . iconv('utf-8','gbk', l("P_ACCT_TYPE_".$v["pAcctType"])). '"';
				
				$list_value["pIdentNo"] =  '"' . iconv('utf-8','gbk',  $v["pIdentNo"]). '"';
				
				$list_value["pRealName"] =  '"' . iconv('utf-8','gbk',  $v["pRealName"]). '"';
				
				$list_value["pIpsAcctNo"] =  '"' . iconv('utf-8','gbk',  $v["pIpsAcctNo"]). '"';
				
				$list_value["pMemo1"] =  '"' . iconv('utf-8','gbk',  $v["pMemo1"]). '"';
				
				$list_value["pMemo2"] =  '"' . iconv('utf-8','gbk',  $v["pMemo2"]). '"';
				
				$list_value["pMemo3"] =  '"' . iconv('utf-8','gbk',  $v["pMemo3"]). '"';
				
				$list_value["pIpsBillNo"] =  '"' . iconv('utf-8','gbk',  $v["pIpsBillNo"]). '"';
				
				$list_value["pIpsTime"] =  '"' . iconv('utf-8','gbk',  $v["pIpsTime"]). '"';
				if(intval($v["pBidStatus"])>0)
				{
					$list_value["pBidStatus"] =  '"' . iconv('utf-8','gbk', l("P_BID_STATUS_".$v["pBidStatus"])). '"';
				}
				else
				{
					$list_value["pBidStatus"] = "";
				}
				
				$list_value["pRealFreezenAmt"] =  '"' . iconv('utf-8','gbk',  $v["pRealFreezenAmt"]). '"';

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
	/*
	public function trade_delete()
	{
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("ips_register_subject")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['pBidNo'];	
				}
				if($info) $info = implode(",",$info);
				$list = M("ips_register_subject")->where ( $condition )->delete();
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
	}
	*/
	public function trade_view()
	{
		$id = intval($_REQUEST['id']);
		$ips_info = M("ips_register_subject")->where("id=".$id)->find();
		if(!$ips_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		//$ips_info_items = M("ips_create_new_acct")->where(" id=".$ips_info['id'])->findAll();
		
		$ips_info["status"] =  l("P_T_STATUS_".$ips_info["status"]);

		$ips_info["deal_name"] = M("deal")->where(" id=".$ips_info["id"])->getField("name");
		
		if($ips_info["pTrdCycleType"])
		{
			$ips_info["pTrdCycleType"] =  l("P_TRD_CYCLE_TYPE_".$ips_info["pTrdCycleType"]);
		}
		
		if($ips_info["pRepayMode"])
		{
			$ips_info["pRepayMode"] =  l("P_REPAY_MODE_".$ips_info["pRepayMode"]);
		}
		
		if($ips_info["pOperationType"])
			$ips_info["pOperationType"] = l("P_OPERACTION_TYPE_".$ips_info["pOperationType"]);

		$ips_info["pAcctType"] = l("P_ACCT_TYPE_".$ips_info["pAcctType"]);
		
		if(intval($ips_info["pBidStatus"])>0)
			$ips_info["pBidStatus"] = l("P_BID_STATUS_".$ips_info["pBidStatus"]);
		else
		{
			$ips_info["pBidStatus"] = "";
		}
		
		$ips_info["is_callback"] = l("IPS_IS_CALLBACK_".$ips_info["is_callback"]);
		
		$this->assign("ips_info",$ips_info);
		
		$this->display();
	}
	public function creditor()
	{
		$where = "  pErrCode ='MG00000F'  ";
		
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
		
		//$name=$this->getActionName();
		$model = D ("ips_register_creditor");
		if (! empty ( $model )) {
			$this->_list ( $model, $where );
		}
		
		$this->display ();
	}
	/*
	public function creditor_delete()
	{
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("ips_register_creditor")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['pContractNo'];	
				}
				if($info) $info = implode(",",$info);
				$list = M("ips_register_creditor")->where ( $condition )->delete();
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
	
	public function creditor_view()
	{
		$id = intval($_REQUEST['id']);
		$ips_info = M("ips_register_creditor")->where("id=".$id)->find();
		if(!$ips_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		//$ips_info_items = M("ips_create_new_acct")->where(" id=".$ips_info['id'])->findAll();
		if($ips_info["pAcctType"] == 0)
		{
			$ips_info["user_name"] = M("deal_agency")->where(" id=".$ips_info["user_id"])->getField("name");
		}
		else if($ips_info["pAcctType"] == 1)
		{
			$ips_info["user_name"] = M("user")->where(" id=".$ips_info["user_id"])->getField("user_name");
			;
		}
		if($ips_info["pRegType"])
		{
			$ips_info["pRegType"] =  l("P_REG_TYPE_".$ips_info["pRegType"]);
		}
		
		$ips_info["deal_name"] = M("deal")->where(" id=".$ips_info["deal_id"])->getField("name");
		
		if($ips_info["pAcctType"])
		{
			$ips_info["pAcctType"] =  l("P_ACCT_TYPE_".$ips_info["pAcctType"]);
		}
		if($ips_info["pBusiType"]==1)
		{
			$ips_info["pBusiType"] =  "投标";
		}
		else
		{
			$ips_info["pBusiType"] = "";
		}
		if(isset($ips_info["pStatus"]))
		{
			$ips_info["pStatus"] =  l("P_CREDITOR_STATUS_".intval($ips_info["pStatus"]));
		}

		$ips_info['is_callback'] = l("IPS_PASS_".$ips_info["is_callback"]);

		$this->assign("ips_info",$ips_info);
		
		$this->display();
	}
	public function creditor_export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		

		$where = "  pErrCode ='MG00000F'  ";
		
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

		
		$list = M("ips_register_creditor")
				->where($where)
				->limit($limit)->findAll();
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'creditor_export_csv'), $page+1);
			
			$list_value_old = array('id'=>'""', 'deal_name'=>'""', 'user_name'=>'""','pMerCode'=>'""', 'pMerBillNo'=>'""', 'pMerDate'=>'""','pBidNo'=>'""','pContractNo'=>'""', 'pRegType'=>'""', 'pAuthNo'=>'""', 'pAuthAmt'=>'""','pTrdAmt'=>'""',  'pFee'=>'""','pAcctType'=>'""','pIdentNo'=>'""', 'pRealName'=>'""', 'pAccount'=>'""', 'pUse'=>'""', 'pMemo1'=>'""','pMemo2'=>'""', 'pMemo3'=>'""', 'pAccountDealNo'=>'""','pBidDealNo'=>'""', 'pBusiType'=>'""', 'pTransferAmt'=>'""','pStatus'=>'""', 'pP2PBillNo'=>'""', 'pIpsTime'=>'""');
	    	
			if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,贷款名称,会员名称,平台账号,商户开户流水号,商户日期,标的号,合同号,登记方式,授权号,债券面额,交易金额,投资人手续费,账户类型,证件号码,姓名,投资人账户,借款用途,备注1,备注2,备注3,投资人编号,标的编号,业务类型,实际冻结金额,债权人状态,IPS P2P订单号,IPS处理时间");	    		    	
		    	$content = $content . "\n";
	    	}

			
			foreach($list as $k=> $v)
			{
			
				$list_value = $list_value_old;
				
				if($v["pAcctType"] == 0)
				{
					$list_value["user_name"] = M("deal_agency")->where(" id=".$v["user_id"])->getField("name");
				}
				else if($v["pAcctType"] == 1)
				{
					$list_value["user_name"] = M("user")->where(" id=".$v["user_id"])->getField("user_name");
					
				}
				$list_value["user_name"] = '"' . iconv('utf-8','gbk', $list_value["user_name"]). '"';
				
				$list_value["id"] =  '"' . iconv('utf-8','gbk', $v["id"]). '"';
				
				$list_value["pRegType"] =  '"' . iconv('utf-8','gbk', l("P_REG_TYPE_".$v["pRegType"])). '"';
				
				$list_value["deal_name"] = M("deal")->where(" id=".$v["deal_id"])->getField("name");
				
				$list_value["pAcctType"] =  '"' . iconv('utf-8','gbk',  l("P_ACCT_TYPE_".$v["pAcctType"])). '"';
				
				$list_value["pStatus"] = '"' . iconv('utf-8','gbk', l("P_CREDITOR_STATUS_".$v["pStatus"])) . '"';
				
				
				$list_value["pMerCode"] = '"' . iconv('utf-8','gbk', $v["pMerCode"]) . '"';
				
				$list_value["pMerBillNo"] = '"' . iconv('utf-8','gbk', $v["pMerBillNo"]) . '"';
				
				$list_value["pBidNo"] =  '"' . iconv('utf-8','gbk', $v["pBidNo"]) . '"';
				
				$list_value["pMerDate"] = '"' . iconv('utf-8','gbk', $v["pMerDate"]) . '"';
				
				$list_value["pContractNo"] = '"' . iconv('utf-8','gbk', $v["pContractNo"]) . '"';

				$list_value["pAuthNo"] =  '"' . iconv('utf-8','gbk',  $v["pAuthNo"]). '"';
				
				$list_value["pAuthAmt"] =  '"' . iconv('utf-8','gbk', $v["pAuthAmt"]). '"';
				
				$list_value["pTrdAmt"] =  '"' . iconv('utf-8','gbk',  $v["pTrdAmt"]). '"';
				
				$list_value["pFee"] =  '"' . iconv('utf-8','gbk',  $v["pFee"]). '"';
				
				$list_value["pIdentNo"] =  '"' . iconv('utf-8','gbk', $v["pIdentNo"]). '"';
				
				$list_value["pRealName"] =  '"' . iconv('utf-8','gbk',  $v["pRealName"]). '"';
				
				$list_value["pAccount"] =  '"' . iconv('utf-8','gbk', $v["pAccount"]). '"';
				
				$list_value["pUse"] =  '"' . iconv('utf-8','gbk',  $v["pUse"]). '"';
				
				$list_value["pMemo1"] =  '"' . iconv('utf-8','gbk',  $v["pMemo1"]). '"';
				
				$list_value["pMemo2"] =  '"' . iconv('utf-8','gbk',  $v["pMemo2"]). '"';
				
				$list_value["pMemo3"] =  '"' . iconv('utf-8','gbk',  $v["pMemo3"]). '"';
				
				$list_value["pAccountDealNo"] =  '"' . iconv('utf-8','gbk',  $v["pAccountDealNo"]). '"';
				
				$list_value["pBidDealNo"] =  '"' . iconv('utf-8','gbk',  $v["pBidDealNo"]). '"';
				
				$list_value["pBusiType"] =  '"' . iconv('utf-8','gbk', l("P_BUSI_TYPE_".$v["pBusiType"])). '"';
				
				$list_value["pTransferAmt"] =  '"' . iconv('utf-8','gbk',  $v["pTransferAmt"]). '"';
				
				$list_value["pP2PBillNo"] =  '"' . iconv('utf-8','gbk',  $v["pP2PBillNo"]). '"';
				
				$list_value["pIpsTime"] =  '"' . iconv('utf-8','gbk',  $v["pIpsTime"]). '"';
				
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
	public function guarantor()
	{
		$where = " pErrCode ='MG00000F' ";
		//定义条件
		
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
		if(isset($_REQUEST['pAcctType'])&&intval($_REQUEST['pAcctType'])>=0)
			$where.=" and pAcctType = '".intval($_REQUEST['pAcctType'])."'";
			
		if(isset($_REQUEST['pFromIdentNo'])&&strim($_REQUEST['pFromIdentNo'])!='')
		{		
			$where.=" and pFromIdentNo like '%".strim($_REQUEST['pFromIdentNo'])."%'";
		}

		if(isset($_REQUEST['pAccountName'])&&strim($_REQUEST['pAccountName'])!='')
		{		
			$where.=" and pAccountName like '%".strim($_REQUEST['pAccountName'])."%'";
		}
		
		if(isset($_REQUEST['pAccount'])&&strim($_REQUEST['pAccount'])!='')
			$where.=" and pAccount = '".intval($_REQUEST['pAccount'])."'";
				
		if(isset($_REQUEST['pStatus'])&&intval(strim($_REQUEST['pStatus']))>=0)
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
		
		$model = D ("ips_register_guarantor");
		if (! empty ( $model )) {
			$this->_list ( $model, $where );
		}
		
		$this->display ();
	}
	/*
	public function guarantor_delete()
	{
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("ips_register_guarantor")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M("ips_register_guarantor")->where ( $condition )->delete();
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
	}
	*/
	public function guarantor_view()
	{
		$id = intval($_REQUEST['id']);
		$ips_info = M("ips_register_guarantor")->where("id=".$id)->find();
		if(!$ips_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		//$ips_info_items = M("ips_create_new_acct")->where(" id=".$ips_info['id'])->findAll();

		
		$ips_info["deal_name"] = M("deal")->where(" id=".$ips_info["deal_id"])->getField("name");
		
		if($ips_info["pAcctType"] == 0)
		{
			$ips_info["user_name"] = M("deal_agency")->where(" id=".$ips_info["agency_id"])->getField("name");
		}
		else if($ips_info["pAcctType"] == 1)
		{
			$ips_info["user_name"] = M("user")->where(" id=".$ips_info["agency_id"])->getField("user_name");
		}
		if($ips_info["pStatus"])
		{
			$ips_info["pStatus"] =  l("P_CREDITOR_STATUS_".$ips_info["pStatus"]);
		}
		if($ips_info["pAcctType"])
		{
			$ips_info["pAcctType"] =  l("P_ACCT_TYPE_".$ips_info["pAcctType"]);
		}
		
		$ips_info['is_callback'] = l("IPS_PASS_".$ips_info["is_callback"]);

		$this->assign("ips_info",$ips_info);
		
		$this->display();
	}
	public function guarantor_export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		

		$where = " pErrCode ='MG00000F' ";
		//定义条件
		
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
		if(isset($_REQUEST['pAcctType'])&&intval($_REQUEST['pAcctType'])>=0)
			$where.=" and pAcctType = '".intval($_REQUEST['pAcctType'])."'";
			
		if(isset($_REQUEST['pFromIdentNo'])&&strim($_REQUEST['pFromIdentNo'])!='')
		{		
			$where.=" and pFromIdentNo like '%".strim($_REQUEST['pFromIdentNo'])."%'";
		}

		if(isset($_REQUEST['pAccountName'])&&strim($_REQUEST['pAccountName'])!='')
		{		
			$where.=" and pAccountName like '%".strim($_REQUEST['pAccountName'])."%'";
		}
		
		if(isset($_REQUEST['pAccount'])&&strim($_REQUEST['pAccount'])!='')
			$where.=" and pAccount = '".intval($_REQUEST['pAccount'])."'";
				
		if(isset($_REQUEST['pStatus'])&&intval(strim($_REQUEST['pStatus']))>=0)
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
		
		$list = M("ips_register_guarantor")
				->where($where)
				->limit($limit)->findAll();
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'guarantor_export_csv'), $page+1);
			
			$list_value_old = array('id'=>'""', 'deal_name'=>'""', 'user_name'=>'""','pMerCode'=>'""', 'pMerBillNo'=>'""', 'pMerDate'=>'""','pBidNo'=>'""','pAmount'=>'""', 'pMarginAmt'=>'""', 'pProFitAmt'=>'""', 'pAcctType'=>'""','pFromIdentNo'=>'""',  'pAccountName'=>'""','pAccount'=>'""','pMemo1'=>'""', 'pMemo2'=>'""', 'pMemo3'=>'""', 'pP2PBillNo'=>'""', 'pRealFreezeAmt'=>'""','pCompenAmt'=>'""', 'pIpsTime'=>'""', 'pStatus'=>'""');
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,贷款名称,用户名,平台账号,商户开户流水号,商户日期,标的号,担保金额,担保保证金,担保收益,担保方类型,担保方证件号码,担保方账户姓名,担保方账户,备注1,备注2,备注3,担保方编号,实际冻结金额,已代偿金额,IPS处理时间,担保状态");	    		    	
		    	$content = $content . "\n";
	    	}

			
			foreach($list as $k=> $v)
			{
			
				$list_value = $list_value_old;
				
				if($v["pAcctType"] == 0)
				{
					$list_value["user_name"] = M("deal_agency")->where(" id=".$v["agency_id"])->getField("name");
				}
				else if($v["pAcctType"] == 1)
				{
					$list_value["user_name"] = M("user")->where(" id=".$v["agency_id"])->getField("user_name");
					
				}
				$list_value["user_name"] = '"' . iconv('utf-8','gbk', $list_value["user_name"]). '"';
				$list_value["id"] =  '"' . iconv('utf-8','gbk', $v["id"]). '"';
				
				$list_value["deal_name"] = M("deal")->where(" id=".$v["deal_id"])->getField("name");
				
				$list_value["pAcctType"] =  '"' . iconv('utf-8','gbk',  l("P_ACCT_TYPE_".$v["pAcctType"])). '"';
				
				$list_value["pStatus"] = '"' . iconv('utf-8','gbk', l("P_CREDITOR_STATUS_".$v["pStatus"])) . '"';
				
				$list_value["pMerCode"] = '"' . iconv('utf-8','gbk', $v["pMerCode"]) . '"';
				
				$list_value["pMerBillNo"] = '"' . iconv('utf-8','gbk', $v["pMerBillNo"]) . '"';
				
				$list_value["pBidNo"] =  '"' . iconv('utf-8','gbk', $v["pBidNo"]) . '"';
				
				$list_value["pMerDate"] = '"' . iconv('utf-8','gbk', $v["pMerDate"]) . '"';
				
				$list_value["pAmount"] = '"' . iconv('utf-8','gbk', $v["pAmount"]) . '"';

				$list_value["pAuthNo"] =  '"' . iconv('utf-8','gbk',  $v["pAuthNo"]). '"';
				
				$list_value["pMarginAmt"] =  '"' . iconv('utf-8','gbk', $v["pMarginAmt"]). '"';
				
				$list_value["pProFitAmt"] =  '"' . iconv('utf-8','gbk',  $v["pProFitAmt"]). '"';
				
				$list_value["pFromIdentNo"] =  '"' . iconv('utf-8','gbk',  $v["pFromIdentNo"]). '"';
				
				$list_value["pAccountName"] =  '"' . iconv('utf-8','gbk', $v["pAccountName"]). '"';
	
				$list_value["pAccount"] =  '"' . iconv('utf-8','gbk', $v["pAccount"]). '"';
				
				$list_value["pMemo1"] =  '"' . iconv('utf-8','gbk',  $v["pMemo1"]). '"';
				
				$list_value["pMemo2"] =  '"' . iconv('utf-8','gbk',  $v["pMemo2"]). '"';
				
				$list_value["pMemo3"] =  '"' . iconv('utf-8','gbk',  $v["pMemo3"]). '"';
				
				$list_value["pP2PBillNo"] =  '"' . iconv('utf-8','gbk',  $v["pP2PBillNo"]). '"';
				
				$list_value["pRealFreezeAmt"] =  '"' . iconv('utf-8','gbk',  $v["pRealFreezeAmt"]). '"';
				
				$list_value["pCompenAmt"] =  '"' . iconv('utf-8','gbk', $v["pCompenAmt"]). '"';
				
				$list_value["pIpsTime"] =  '"' . iconv('utf-8','gbk',  $v["pIpsTime"]). '"';
				
				
				
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
	
	public function recharge()
	{
		$where = " pErrCode ='MG00000F' ";
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
		
		$model = D ("ips_do_dp_trade");
		if (! empty ( $model )) {
			$this->_list ( $model, $where );
		}
		$this->display ();
	}
	
	public function recharge_view()
	{
		$id = intval($_REQUEST['id']);
		$ips_info = M("ips_do_dp_trade")->where("id=".$id)->find();
		if(!$ips_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		//$ips_info_items = M("ips_create_new_acct")->where(" id=".$ips_info['id'])->findAll();
		
		if($ips_info["user_type"] == 0)
		{
			$ips_info["user_name"] = M("user")->where(" id=".$ips_info["user_id"])->getField("user_name");
		}
		else if($ips_info["user_type"] == 1)
		{
			$ips_info["user_name"] = M("deal_agency")->where(" id=".$ips_info["user_id"])->getField("name");
		}

		$ips_info["user_type"] = l("P_USER_TYPE_".$ips_info["user_type"]);
		
		if($ips_info["pAcctType"])
		{
			$ips_info["pAcctType"] =  l("P_ACCT_TYPE_".$ips_info["pAcctType"]);
		}
		
		if($ips_info["pChannelType"])
		{
			$ips_info["pChannelType"] =  l("P_CHANNEL_TYPE_".$ips_info["pChannelType"]);
		}

		if($ips_info["pIpsFeeType"])
		{
			$ips_info["pIpsFeeType"] =  l("P_IPS_FEE_TYPE_".$ips_info["pIpsFeeType"]);
		}
		
		
		$this->assign("ips_info",$ips_info);
		
		$this->display();
	}
	/*
	public function recharge_delete()
	{
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("ips_do_dp_trade")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M("ips_do_dp_trade")->where ( $condition )->delete();
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
	
	public function recharge_export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		

		$where = " pErrCode ='MG00000F' ";
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
		
		$list = M("ips_do_dp_trade")
				->where($where)
				->limit($limit)->findAll();
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'recharge_export_csv'), $page+1);
			
			$list_value_old = array('id'=>'""',  'user_name'=>'""','user_type'=>'""','pMerCode'=>'""', 'pMerBillNo'=>'""', 'pAcctType'=>'""','pIdentNo'=>'""',  'pRealName'=>'""','pIpsAcctNo'=>'""','pTrdDate'=>'""', 'pTrdAmt'=>'""', 'pChannelType'=>'""', 'pTrdBnkCode'=>'""', 'pMerFee'=>'""','pIpsFeeType'=>'""', 'pMemo1'=>'""', 'pMemo2'=>'""', 'pMemo3'=>'""','pIpsBillNo'=>'""');
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,用户名,用户类型,平台账号,商户充值订单号,账户类型,证件号码,姓名,IPS托管账户号,充值日期,充值金额,充值渠道种类,充值银行,平台手续费,手续费支付方,备注1,备注2,备注3,IPS充值订单号");	    		    	
		    	$content = $content . "\n";
	    	}

			
			foreach($list as $k=> $v)
			{
				$list_value = $list_value_old;
				
				if($v["user_type"] == 0)
				{
					$list_value["user_name"] = M("user")->where(" id=".$v["user_id"])->getField("user_name");
				}
				else if($v["user_type"] == 1)
				{
					$list_value["user_name"] =  M("deal_agency")->where(" id=".$v["user_id"])->getField("name");
				}
				$list_value["user_name"] = '"' . iconv('utf-8','gbk', $list_value["user_name"]). '"';
				
				$list_value["id"] =  '"' . iconv('utf-8','gbk', $v["id"]). '"';
				
				$list_value["user_type"] = '"' . iconv('utf-8','gbk',  l("P_USER_TYPE_".$v["user_type"])). '"';

				$list_value["pMerCode"] = '"' . iconv('utf-8','gbk', $v["pMerCode"]) . '"';
				
				$list_value["pMerBillNo"] = '"' . iconv('utf-8','gbk', $v["pMerBillNo"]) . '"';
				
				$list_value["pAcctType"] =  '"' . iconv('utf-8','gbk',  l("P_ACCT_TYPE_".$v["pAcctType"])). '"';
				
				$list_value["pIdentNo"] = '"' . iconv('utf-8','gbk', $v["pIdentNo"]) . '"';
				
				$list_value["pRealName"] = '"' . iconv('utf-8','gbk', $v["pRealName"]) . '"';
				
				$list_value["pIpsAcctNo"] = '"' . iconv('utf-8','gbk', $v["pIpsAcctNo"]) . '"';
				
				$list_value["pTrdDate"] =  '"' . iconv('utf-8','gbk', $v["pTrdDate"]) . '"';
				
				$list_value["pTrdAmt"] = '"' . iconv('utf-8','gbk', $v["pTrdAmt"]) . '"';
				
				$list_value["pChannelType"] = '"' . iconv('utf-8','gbk', l("P_CHANNEL_TYPE_".$v["pChannelType"])) . '"';

				$list_value["pTrdBnkCode"] =  '"' . iconv('utf-8','gbk',  $v["pTrdBnkCode"]). '"';
				
				$list_value["pMerFee"] =  '"' . iconv('utf-8','gbk', $v["pMerFee"]). '"';
				
				$list_value["pIpsFeeType"] = '"' . iconv('utf-8','gbk', l("P_IPS_FEE_TYPE_".$v["pIpsFeeType"])) . '"';
				
				$list_value["pMemo1"] =  '"' . iconv('utf-8','gbk',  $v["pMemo1"]). '"';
				
				$list_value["pMemo2"] =  '"' . iconv('utf-8','gbk',  $v["pMemo2"]). '"';
				
				$list_value["pMemo3"] =  '"' . iconv('utf-8','gbk',  $v["pMemo3"]). '"';
				
				$list_value["pIpsBillNo"] =  '"' . iconv('utf-8','gbk',  $v["pIpsBillNo"]). '"';

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
	public function transfer()
	{
		$where = " pErrCode ='MG00000F' ";
		
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
		//$name=$this->getActionName();
		
		$model = D ("ips_do_dw_trade");
		if (! empty ( $model )) {
			$this->_list ( $model, $where );
		}
		$this->display ();
	}
	
	public function transfer_view()
	{
		$id = intval($_REQUEST['id']);
		$ips_info = M("ips_do_dw_trade")->where("id=".$id)->find();
		if(!$ips_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		//$ips_info_items = M("ips_create_new_acct")->where(" id=".$ips_info['id'])->findAll();
		
		if($ips_info["user_type"] == 0)
		{
			$ips_info["user_name"] = M("user")->where(" id=".$ips_info["user_id"])->getField("user_name");
		}
		else if($ips_info["user_type"] == 1)
		{
			$ips_info["user_name"] = M("deal_agency")->where(" id=".$ips_info["user_id"])->getField("name");
		}

		$ips_info["user_type"] = l("P_USER_TYPE_".$ips_info["user_type"]);
		
		if($ips_info["pAcctType"])
		{
			$ips_info["pAcctType"] =  l("P_ACCT_TYPE_".$ips_info["pAcctType"]);
		}
		
		if($ips_info["pOutType"])
		{
			$ips_info["pOutType"] =  l("P_OUT_TYPE_".$ips_info["pOutType"]);
		}

		if($ips_info["pIpsFeeType"])
		{
			$ips_info["pIpsFeeType"] =  l("P_IPS_FEE_TYPE_".$ips_info["pIpsFeeType"]);
		}
		
		if($ips_info["is_callback"])
		{
			$ips_info["is_callback"] =  l("IPS_IS_CALLBACK_".$ips_info["is_callback"]);
		}
		
		$this->assign("ips_info",$ips_info);
		
		$this->display();
	}
	public function transfer_export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		

		$where = " pErrCode ='MG00000F' ";
		
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
		$list = M("ips_do_dw_trade")
				->where($where)
				->limit($limit)->findAll();
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'transfer_export_csv'), $page+1);
			
			$list_value_old = array('id'=>'""',  'user_name'=>'""','user_type'=>'""','pMerCode'=>'""', 'pMerBillNo'=>'""', 'pAcctType'=>'""','pOutType'=>'""','pBidNo'=>'""','pContractNo'=>'""','pDwTo'=>'""','pIdentNo'=>'""',  'pRealName'=>'""','pIpsAcctNo'=>'""','pDwDate'=>'""', 'pTrdAmt'=>'""', 'pMerFee'=> '""','pIpsFeeType'=>'""','pIpsBillNo'=>'""');
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,用户名,用户类型,平台账号,商户提现订单号,账户类型,提现模式,标号,合同号,提现去向,证件号码,姓名,IPS账户号,提现日期,提现金额,平台手续费,手续费支付方,IPS订单号");	    		    	
		    	$content = $content . "\n";
	    	}

			foreach($list as $k=> $v)
			{
				$list_value = $list_value_old;
				
				if($v["user_type"] == 0)
				{
					$list_value["user_name"] = M("user")->where(" id=".$v["user_id"])->getField("user_name");
				}
				else if($v["user_type"] == 1)
				{
					$list_value["user_name"] =  M("deal_agency")->where(" id=".$v["user_id"])->getField("name");
				}
				$list_value["user_name"] = '"' . iconv('utf-8','gbk', $list_value["user_name"]). '"';
				
				$list_value["id"] =  '"' . iconv('utf-8','gbk', $v["id"]). '"';
				
				$list_value["user_type"] = '"' . iconv('utf-8','gbk',  l("P_USER_TYPE_".$v["user_type"])). '"';

				$list_value["pMerCode"] = '"' . iconv('utf-8','gbk', $v["pMerCode"]) . '"';
				
				$list_value["pMerBillNo"] = '"' . iconv('utf-8','gbk', $v["pMerBillNo"]) . '"';
				
				$list_value["pAcctType"] =  '"' . iconv('utf-8','gbk',  l("P_ACCT_TYPE_".$v["pAcctType"])). '"';
				
				$list_value["pOutType"] =  '"' . iconv('utf-8','gbk',  l("P_OUT_TYPE_".$v["pOutType"])). '"';
				
				$list_value["pBidNo"] = '"' . iconv('utf-8','gbk', $v["pBidNo"]) . '"';
				
				$list_value["pContractNo"] = '"' . iconv('utf-8','gbk', $v["pContractNo"]) . '"';
				
				$list_value["pDwTo"] = '"' . iconv('utf-8','gbk', $v["pDwTo"]) . '"';				
				
				$list_value["pIdentNo"] = '"' . iconv('utf-8','gbk', $v["pIdentNo"]) . '"';
				
				$list_value["pRealName"] = '"' . iconv('utf-8','gbk', $v["pRealName"]) . '"';
				
				$list_value["pIpsAcctNo"] = '"' . iconv('utf-8','gbk', $v["pIpsAcctNo"]) . '"';
				
				$list_value["pDwDate"] =  '"' . iconv('utf-8','gbk', $v["pDwDate"]) . '"';
				
				$list_value["pMerFee"] = '"' . iconv('utf-8','gbk', $v["pMerFee"]) . '"';
				
				$list_value["pTrdAmt"] = '"' . iconv('utf-8','gbk', $v["pTrdAmt"]) . '"';
				
				$list_value["pIpsFeeType"] = '"' . iconv('utf-8','gbk', l("P_IPS_FEE_TYPE_".$v["pIpsFeeType"])) . '"';

				$list_value["pIpsBillNo"] =  '"' . iconv('utf-8','gbk',  $v["pIpsBillNo"]). '"';
				

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