<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

class IpsRelationAction extends CommonAction{
	public function repayment()
	{
		$condition = " where ips.pErrCode = 'MG00000F'";
		
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
	public function repayment_delete() {
		//删除指定记录
		
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("ips_repayment_new_trade")->where($condition)->findAll();
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
					$result = M("ips_repayment_new_trade_detail")->where(" pid = ".$data["id"])->count();
					if($result>0)
					{
						save_log($info.l("DELETE_FAILED"),0);
						$this->error (l("INVALID_DELETE"),$ajax);
						//return;
					}
				}
				if($info) $info = implode(",",$info);
				$list = M("ips_repayment_new_trade")->where ( $condition )->delete();
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
		$sql = "select ips.*,ips.id as mid,d.`name` as deal_name,u.user_name, dr.* from ".DB_PREFIX."ips_repayment_new_trade as ips
left join ".DB_PREFIX."deal d on d.id = ips.deal_id
left join ".DB_PREFIX."deal_repay dr on dr.id = ips.deal_repay_id 
left join ".DB_PREFIX."user u on u.id = d.user_id where ips.id =".$id;
		
		$ips_info = $GLOBALS['db']->getRow($sql);
		
		if(!$ips_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		
		//
	
		$ips_info["pRepayType"] = l("P_REPAY_TYPE_".$ips_info["pRepayType"]);
		$ips_info["is_callback"] =  l("IPS_IS_CALLBACK_".$ips_info["is_callback"]);
		if($ips_info["status"]!="")
		{
			$ips_info["status"] =  l("REPAY_STATUS_".$ips_info["status"]);
		}
		if($ips_info["has_repay"]!="")
		{
			$ips_info["has_repay"] =  l("HAS_REPAY_".$ips_info["has_repay"]);
		}
		
		$this->assign("ips_info",$ips_info);
		
		$this->display();
	}

	public function repayment_export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		$condition = " ips.pErrCode = 'MG00000F'";

		$sql = "select ips.*,ips.id as mid,d.`name` as deal_name,u.user_name, dr.* from ".DB_PREFIX."ips_repayment_new_trade as ips
left join ".DB_PREFIX."deal d on d.id = ips.deal_id
left join ".DB_PREFIX."deal_repay dr on dr.id = ips.deal_repay_id 
left join ".DB_PREFIX."user u on u.id = d.user_id ";

		$where = " 1=1 ";
		//定义条件
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
		 
		$list = $GLOBALS['db']->getAll($sql." where ".$condition." limit ".$limit);
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'repayment_export_csv'), $page+1);
			
			$list_value_old = array(
				'id'=>'""', 
				'deal_name'=>'""', 
				'pMerCode'=>'""',
				'pMerBillNo'=>'""', 
				'pBidNo'=>'""', 
				'pRepaymentDate'=>'""',
				'pRepayType'=>'""',
				'pIpsAuthNo'=>'""', 
				'pOutAcctNo'=>'""', 
				'pOutAmt'=>'""', 
				'pOutFee'=>'""', 
				'pMessage'=>'""', 
				'pMemo1'=>'""',
				'pMemo2'=>'""',
				'pMemo3'=>'""', 
				'pIpsBillNo'=>'""', 
				'pOutIpsFee'=>'""', 
				'pIpsDate'=>'""', 
				'user_name'=>'""',
				'repay_money'=>'""', 
				'manage_money'=>'""', 
				'impose_money'=>'""',
				'repay_time'=>'""',
				'true_repay_time'=>'""',
				'status' => '""',
				'l_key' => '""',
				'has_repay'=>'""',
				'mange_impose_money'=>'""'
			);
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,贷款名称,平台账号,商户开户流水号,标的号,还款日期,还款类型,授权号,转出方IPS账号,转出金额,转出方总手续费,转入结果说明,备注1,备注2,备注3,IPS还款订单号,收取转出方手续费,IPS受理日期,用户名,还款金额,管理费,罚息,还的是第几期,还款时间,还款状态,还款期号,订单状态,逾期管理费");	    		    	
		    	$content = $content . "\n";
	    	}

			
			foreach($list as $k=> $v)
			{
				$list_value = $list_value_old;
				
				$list_value["id"] = '"' . iconv('utf-8','gbk', $v['mid']) . '"';
				
				$list_value["deal_name"] = '"' . iconv('utf-8','gbk',  $v["deal_name"]). '"';
				
				$list_value["pMerCode"] =  '"' . iconv('utf-8','gbk',  $v["pMerCode"]). '"';
				
				$list_value["pMerBillNo"] =  '"' . iconv('utf-8','gbk', $v["pMerBillNo"]). '"';
				
				$list_value["pBidNo"] =  '"' . iconv('utf-8','gbk', $v["pBidNo"]). '"';
				
				$list_value["pRepaymentDate"] =  '"' . iconv('utf-8','gbk', $v["pRepaymentDate"]). '"';
				
				$list_value["pRepayType"] =  '"' . iconv('utf-8','gbk', l("P_REPAY_TYPE_".$v["pRepayType"])). '"';
				
				$list_value["pIpsAuthNo"] =  '"' . iconv('utf-8','gbk', $v["pIpsAuthNo"]). '"';
				
				$list_value["pOutAcctNo"] =  '"' . iconv('utf-8','gbk', $v["pOutAcctNo"]). '"';
				
				$list_value["pOutAmt"] =  '"' . iconv('utf-8','gbk', $v["pOutAmt"]). '"';
				
				$list_value["pOutFee"] =  '"' . iconv('utf-8','gbk', $v["pOutFee"]). '"';
				
				$list_value["pMessage"] =  '"' . iconv('utf-8','gbk', $v["pMessage"]). '"';
				
				$list_value["pMemo1"] =  '"' . iconv('utf-8','gbk', $v["pMemo1"]).'"';
				
				$list_value["pMemo2"] =  '"' . iconv('utf-8','gbk', $v["pMemo2"]). '"';
				
				$list_value["pMemo3"] =  '"' . iconv('utf-8','gbk', $v["pMemo3"]). '"';
				
				$list_value["pIpsBillNo"] =  '"' . iconv('utf-8','gbk', $v["pIpsBillNo"]). '"';
				
				$list_value["pOutIpsFee"] =  '"' . iconv('utf-8','gbk', $v["pOutIpsFee"]). '"';
				
				$list_value["pIpsDate"] =  '"' . iconv('utf-8','gbk', $v["pIpsDate"]). '"';
				
				$list_value["user_name"] =  '"' . iconv('utf-8','gbk', $v["user_name"]). '"';
				
				$list_value["repay_money"] =  '"' . iconv('utf-8','gbk', $v["repay_money"]). '"';
				
				$list_value["manage_money"] =  '"' . iconv('utf-8','gbk', $v["manage_money"]). '"';
				
				$list_value["impose_money"] =  '"' . iconv('utf-8','gbk', $v["impose_money"]). '"';
				
				$list_value["repay_time"] =  '"' . iconv('utf-8','gbk', $v["repay_time"]). '"';
				
				$list_value["true_repay_time"] =  '"' . iconv('utf-8','gbk', $v["true_repay_time"]). '"';
				
				if($v["status"]!="")
				{
					$list_value["status"] =  '"' . iconv('utf-8','gbk', l("REPAY_STATUS_".$v["status"])). '"';
				}
				else
				{
					$list_value["status"] = "";
				}
				
				
				$list_value["l_key"] =  '"' . iconv('utf-8','gbk', $v["l_key"]). '"';
				
				if($v["has_repay"]!="")
				{
					$list_value["has_repay"] =  '"' . iconv('utf-8','gbk', l("HAS_REPAY_".$v["has_repay"])). '"';
				}
				else
				{
					$list_value["has_repay"] = "";
				}
				
				$list_value["mange_impose_money"] =  '"' . iconv('utf-8','gbk', $v["mange_impose_money"]). '"';

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

	public function deal_list()
	{
		$condition = " where 1=1 ";
		
		$sql = "select d.*,r.*,d.id as mid ,u.user_name,tu.user_name as t_user_name from ".DB_PREFIX."ips_repayment_new_trade_detail as d
left JOIN ".DB_PREFIX."deal_load_repay r on r.id = d.deal_load_repay_id
left join ".DB_PREFIX."user u on u.id = r.user_id
left join ".DB_PREFIX."user tu on tu.id = r.t_user_id ";
		
		$count_sql = "select count(*) from ".DB_PREFIX."ips_repayment_new_trade_detail as d
left JOIN ".DB_PREFIX."deal_load_repay r on r.id = d.deal_load_repay_id
left join ".DB_PREFIX."user u on u.id = r.user_id
left join ".DB_PREFIX."user tu on tu.id = r.t_user_id ";
		
		if(isset($_REQUEST['id'])&&intval(strim($_REQUEST['id']))>0)
		{		
			$condition .= " and d.pid = ".intval(strim($_REQUEST['id']));
			$this->assign ( "id", intval(strim($_REQUEST['id'])) );
		}
		else
		{
			$this->error (l("INVALID_OPERATION"),$ajax);
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
	public function deal_delete() {
		//删除指定记录
		
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("ips_repayment_new_trade_detail")->where($condition)->findAll();
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M("ips_repayment_new_trade_detail")->where ( $condition )->delete();
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
	public function deal_view()
	{
		$id = intval($_REQUEST['id']);
		if(!$id)
		{
			$this->error(l("INVALID_ORDER"));
			return;
		}
		$sql = "select d.*,d.id as mid,r.*,u.user_name,tu.user_name as t_user_name from ".DB_PREFIX."ips_repayment_new_trade_detail as d left JOIN ".DB_PREFIX."deal_load_repay r on r.id = d.deal_load_repay_id
left join ".DB_PREFIX."user u on u.id = r.user_id
left join ".DB_PREFIX."user tu on tu.id = r.t_user_id where d.id =".$id;
		
		$ips_info = $GLOBALS['db']->getRow($sql);
		
		if(!$ips_info)
		{
			$this->error(l("INVALID_ORDER"));
		}
		if(strim($ips_info['pStatus'])!='')
		{
			$ips_info["pStatus"]= l("P_RELATION_STATUS_".strim($ips_info['pStatus']));
		}
		if(strim($ips_info['status'])!='')
		{
			$ips_info["status"]= l("REPAY_STATUS_".strim($ips_info['status']));
		}
		if(strim($ips_info['has_repay'])!='')
		{
			$ips_info["has_repay"] = l("HAS_REPAY_".strim($ips_info['has_repay']));
		}
		
		if(strim($ips_info['is_site_repay'])!='')
		{
			$ips_info["is_site_repay"]= l("IS_SITE_REPAY_".strim($ips_info['is_site_repay']));
		}
		
		if(strim($ips_info['deal_id']))
		{
			$ips_info["deal_name"]= M("deal")->where(" id=".strim($ips_info['deal_id']))->getField("name");
		}
		
		$this->assign("ips_info",$ips_info);
		
		$this->display();
	}
	
	public function deal_export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		$condition = " where 1=1 ";
		
		$sql = "select d.*,d.id as mid,r.*,u.user_name,tu.user_name as t_user_name from ".DB_PREFIX."ips_repayment_new_trade_detail as d
left JOIN ".DB_PREFIX."deal_load_repay r on r.id = d.deal_load_repay_id
left join ".DB_PREFIX."user u on u.id = r.user_id
left join ".DB_PREFIX."user tu on tu.id = r.t_user_id";
		
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
		
		$list = $GLOBALS['db']->getAll($sql.$condition." limit ".$limit);	
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'deal_export_csv'), $page+1);
			
			$list_value = array('id'=>'""', 'pCreMerBillNo'=>'""', 'pInAcctNo'=>'""','pInFee'=>'""', 'pOutInfoFee'=>'""', 'pInAmt'=>'""','pStatus'=>'""','pMessage'=>'""','impose_money'=>'""', 'repay_manage_impose_money'=>'""', 'deal_name'=>'""', 'self_money'=>'""','repay_money'=>'""',  'manage_money'=>'""','repay_time'=>'""','true_repay_time'=>'""', 'status'=>'""', 'is_site_repay'=>'""', 'l_key'=>'""', 'u_key'=>'""', 'has_repay'=>'""','repay_manage_money'=>'""','user_name'=>'""', 't_user_name'=>'""');
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,登记债权人时提交的订单号,转入方,转入方手续费,转出方手续费,转入金额,转入状态,转入结果说明,罚息,接入者均摊下来的逾期管理费,贷款名称,本金,还款金额,管理费,还款日,还款时间,还款状态,付款方式,还款期号,还款顺序,订单状态,从借款者均摊下来的管理费,投标人,承接者");	    		    	
		    	$content = $content . "\n";
	    	}

			
			foreach($list as $k=> $v)
			{
				$list_value["pInAmt"] =  '"' . iconv('utf-8','gbk', $v["pInAmt"]). '"';
				
				$list_value["id"] =  '"' . iconv('utf-8','gbk', $v["id"]). '"';
				
				$list_value["pCreMerBillNo"] =  '"' . iconv('utf-8','gbk', $v["pCreMerBillNo"]). '"';
				
				$list_value["pInAcctNo"] =  '"' . iconv('utf-8','gbk', $v["pInAcctNo"]). '"';
				
				$list_value["pInFee"] = '"' . iconv('utf-8','gbk', $v["pInFee"]) . '"';
				
				$list_value["pOutInfoFee"] = '"' . iconv('utf-8','gbk', $v["pOutInfoFee"]) . '"';
				
				if(strim($v['pStatus'])!="")
				{
					$list_value["pStatus"] =  '"' . iconv('utf-8','gbk', l("P_RELATION_STATUS_".strim($v['pStatus']))) . '"';
				}
				else
				{
					$list_value["pStatus"] = '""';
				}
				
				$list_value["pMessage"] = '"' . iconv('utf-8','gbk', $v["pMessage"]) . '"';
				
				$list_value["impose_money"] = '"' . iconv('utf-8','gbk', $v["impose_money"]) . '"';
				
				$list_value["repay_manage_impose_money"] = '"' . iconv('utf-8','gbk', $v["repay_manage_impose_money"]) . '"';
				
				$list_value["deal_name"] =  '"' . iconv('utf-8','gbk',  M("deal")->where(" id=".strim($v['deal_id']))->getField("name")). '"';
				
				$list_value["self_money"] =  '"' . iconv('utf-8','gbk', $v["self_money"]). '"';
				
				$list_value["repay_money"] =  '"' . iconv('utf-8','gbk',  $v["repay_money"]). '"';
				
				$list_value["manage_money"] =  '"' . iconv('utf-8','gbk',  $v["manage_money"]). '"';
				
				$list_value["repay_time"] =  '"' . iconv('utf-8','gbk',  $v["repay_time"]). '"';
				
				$list_value["true_repay_time"] =  '"' . iconv('utf-8','gbk', $v["true_repay_time"]). '"';
				
				if(strim($v['status'])!="")
				{
					$list_value["status"] =  '"' . iconv('utf-8','gbk', l("REPAY_STATUS_".strim($v['status']))) . '"';
				}
				else
				{
					$list_value["status"] = '""';
				}
				
				if(strim($v['is_site_repay'])!="")
				{
					$list_value["is_site_repay"] =  '"' . iconv('utf-8','gbk', l("IS_SITE_REPAY_".strim($v['is_site_repay']))) . '"';
				}
				else
				{
					$list_value["is_site_repay"] = '""';
				}
				
				$list_value["l_key"] =  '"' . iconv('utf-8','gbk',  $v["l_key"]). '"';
				
				$list_value["u_key"] =  '"' . iconv('utf-8','gbk',  $v["u_key"]). '"';
				
				//$list_value["repay_id"] =  '"' . iconv('utf-8','gbk',  $v["repay_id"]). '"';
				
				if(strim($v['has_repay'])!="")
				{
					$list_value["has_repay"] =  '"' . iconv('utf-8','gbk', l("HAS_REPAY_".strim($v['has_repay']))) . '"';
				}
				else
				{
					$list_value["has_repay"] = '""';
				}
				$list_value["repay_manage_money"] =  '"' . iconv('utf-8','gbk',  $v["repay_manage_money"]). '"';
				
				$list_value["user_name"] =  '"' . iconv('utf-8','gbk',  $v["user_name"]). '"';
				
				$list_value["t_user_name"] =  '"' . iconv('utf-8','gbk',  $v["t_user_name"]). '"';
				
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