<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------

class IpsTransferAction extends CommonAction{
	public function index()
	{
		$condition = " and t.pErrCode = 'MG00000F' ";
		
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
		
		/**
			id
			deal_id
			ref_data		
			pMerCode
			pMerBillNo
			pBidNo
			pDate
			pTransferType
			pTransferMode
			pErrCode
			pErrMsg
			pIpsBillNo
			pIpsTime
			is_callback
			pMemo1
			pMemo2
			pMemo3
			name
			user_name
			t_user_name
		**/
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
		
		$sql = "select t.*,d.`name`,u.user_name,tu.user_name as t_user_name from ".DB_PREFIX."ips_transfer as t
LEFT JOIN ".DB_PREFIX."deal as d on d.id = t.deal_id
LEFT JOIN ".DB_PREFIX."deal_load_transfer as dlt on dlt.id = t.ref_data
LEFT JOIN ".DB_PREFIX."user as u on u.id = d.user_id
left join ".DB_PREFIX."user tu on tu.id = dlt.t_user_id
where t.pTransferType = 4 and t.id =".$id;
		
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
		
		$sql = "select t.*,d.`name`,u.user_name,tu.user_name as t_user_name from ".DB_PREFIX."ips_transfer as t
LEFT JOIN ".DB_PREFIX."deal as d on d.id = t.deal_id
LEFT JOIN ".DB_PREFIX."deal_load_transfer as dlt on dlt.id = t.ref_data
LEFT JOIN ".DB_PREFIX."user as u on u.id = d.user_id
left join ".DB_PREFIX."user tu on tu.id = dlt.t_user_id
where t.pTransferType = 4 ";

		$condition = " and t.pErrCode = 'MG00000F' ";
		
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
				'user_name'=>'""',
				't_user_name'=>'""'
			);
			
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,贷款名称,还款日期,平台账号,商户开户流水号,标的号,商户日期,转账类型,转账方式,备注1,备注2,备注3,IPS订单号,IPS处理时间,债券人,承接人");	    		    	
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
				
				$list_value["t_user_name"] =  '"' . iconv('utf-8','gbk', $v["t_user_name"]). '"';
				
				$list_value["name"] =  '"' . iconv('utf-8','gbk', $v["name"]). '"';
				
				$list_value["user_name"] =  '"' . iconv('utf-8','gbk', $v["user_name"]). '"';
				
				$list_value["pIpsBillNo"] =  '"' . iconv('utf-8','gbk', $v["pIpsBillNo"]). '"';


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
			
		$condition = " ";
		
		if(isset($_REQUEST['id'])&&intval(strim($_REQUEST['id']))>0)
		{		
			
			//$condition .= " and t.pid = ".intval(strim($_REQUEST['id']));
			$this->assign ( "id", intval(strim($_REQUEST['id'])) );
		}
		else
		{
			$this->error (l("INVALID_OPERATION"),$ajax);
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
		
		$sql = "select dlr.*,u.user_name,tu.user_name as t_user_name from ".DB_PREFIX."deal_load_repay as dlr LEFT JOIN ".DB_PREFIX."user as u on u.id = dlr.user_id left join ".DB_PREFIX."user as tu on tu.id = dlr.t_user_id where dlr.load_id =".intval($load_info['load_id']) . " and dlr.user_id =".intval($load_info['user_id']) . " and dlr.deal_id = ".$load_info["deal_id"];
		
		$count_sql = "select count(*) from ".DB_PREFIX."deal_load_repay as dlr LEFT JOIN ".DB_PREFIX."user as u on u.id = dlr.user_id left join ".DB_PREFIX."user as tu on tu.id = dlr.t_user_id where dlr.load_id =".intval($load_info['load_id']) . " and dlr.user_id =".intval($load_info['user_id']) . " and dlr.deal_id = ".$load_info["deal_id"];
		
		/*
			id
			deal_id					//借款
			user_id					//投标人
			self_money				//本金
			repay_money				//还款金额
			manage_money			//管理费
			impose_money			//罚息
			repay_time				//还款日
			true_repay_time			//实际还款时间
			status					//0提前 1准时 2逾期 3严重逾期  
			is_site_repay			//0自付 1网站垫付	2担保机构垫付
			l_key					//还的是第几期
			u_key					//还的是第几个投标人
			repay_id				//还款计划ID
			load_id					//投标记录ID
			has_repay				//0未收到还款  1已收到还款
			t_user_id				//
			repay_manage_money		//从借款者均摊下来的管理费
			repay_manage_impose_money		//接入者均摊下来的逾期管理费
			user_name
			t_user_name
		*/
		
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
	public function relation_export_csv($page = 1)
	{
		set_time_limit(0);
		
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		$condition = " ";
		
		if(isset($_REQUEST['id'])&&intval(strim($_REQUEST['id']))>0)
		{		
			//$condition .= " and t.pid = ".intval(strim($_REQUEST['id']));
			$this->assign ( "id", intval(strim($_REQUEST['id'])) );
		}
		else
		{
			$this->error (l("INVALID_OPERATION"),$ajax);
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
		
		$sql = "select dlr.*,u.user_name,tu.user_name as t_user_name from ".DB_PREFIX."deal_load_repay as dlr LEFT JOIN ".DB_PREFIX."user as u on u.id = dlr.user_id left join ".DB_PREFIX."user as tu on tu.id = dlr.t_user_id where dlr.load_id =".intval($load_info['load_id']) . " and dlr.user_id =".intval($load_info['user_id']) . " and dlr.deal_id = ".$load_info["deal_id"];
		
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
		
		//print_r($sql.$condition." limit ".$limit);die;
		
		$list = $GLOBALS['db']->getAll($sql.$condition." limit ".$limit);
		
		if($list)
		{
			register_shutdown_function(array(&$this, 'relation_export_csv'), $page+1);
			
			$list_value = array('id'=>'""', 'deal_name'=>'""', 'self_money'=>'""','repay_money'=>'""', 'manage_money'=>'""', 'impose_money'=>'""','repay_time'=>'""','true_repay_time'=>'""','status'=>'""', 'is_site_repay'=>'""', 'l_key'=>'""', 'u_key'=>'""','has_repay'=>'""',  'repay_manage_money'=>'""','repay_manage_impose_money'=>'""','user_name'=>'""', 't_user_name'=>'""');
	    	if($page == 1)
	    	{
		    	$content = iconv("utf-8","gbk","编号,借款名称,本金,还款金额,管理费,罚息,还款日,实际还款时间,还款状态,付款方式,还款期号,还款顺序,订单状态,从借款者均摊下来的管理费,接入者均摊下来的逾期管理费,投标人,承接人");	    		    	
		    	$content = $content . "\n";
	    	}
			
			foreach($list as $k=> $v)
			{
				/*
					id
					deal_id					//借款
					user_id					//投标人
					self_money				//本金
					repay_money				//还款金额
					manage_money			//管理费
					impose_money			//罚息
					repay_time				//还款日
					true_repay_time			//实际还款时间
					status					//0提前 1准时 2逾期 3严重逾期  
					is_site_repay			//0自付 1网站垫付	2担保机构垫付
					l_key					//还的是第几期
					u_key					//还的是第几个投标人
					repay_id				//还款计划ID
					load_id					//投标记录ID
					has_repay				//0未收到还款  1已收到还款
					t_user_id				//
					repay_manage_money		//从借款者均摊下来的管理费
					repay_manage_impose_money		//接入者均摊下来的逾期管理费
					user_name
					t_user_name
				*/
				$list_value["id"] =  '"' . iconv('utf-8','gbk', $v["id"]). '"';
				
				$list_value["deal_name"] =  '"' . iconv('utf-8','gbk',  M("deal")->where(" id=".strim($v['deal_id']))->getField("name")). '"';
				
				$list_value["self_money"] =  '"' . iconv('utf-8','gbk', $v["self_money"]). '"';
				
				$list_value["repay_money"] =  '"' . iconv('utf-8','gbk', $v["repay_money"]). '"';
				
				$list_value["manage_money"] =  '"' . iconv('utf-8','gbk', $v["manage_money"]). '"';
				
				$list_value["impose_money"] =  '"' . iconv('utf-8','gbk', $v["impose_money"]). '"';
				
				$list_value["repay_time"] =  '"' . iconv('utf-8','gbk', $v["repay_time"]). '"';
				
				$list_value["true_repay_time"] =  '"' . iconv('utf-8','gbk', $v["true_repay_time"]). '"';
				
				if($v['status']!='')
				{
					$list_value["status"] =  '"' . iconv('utf-8','gbk', l("REPAY_STATUS_".strim($v['status']))). '"';		
				}
				else
				{
					$list_value["status"] = "";
				}
				
				if($v['is_site_repay']!='')
				{
					$list_value["is_site_repay"] =  '"' . iconv('utf-8','gbk', l("IS_SITE_REPAY_".strim($v['is_site_repay']))). '"';		
				}
				else
				{
					$list_value["is_site_repay"] = "";
				}
				
				$list_value["l_key"] =  '"' . iconv('utf-8','gbk', $v["l_key"]). '"';
				
				$list_value["u_key"] =  '"' . iconv('utf-8','gbk', $v["u_key"]). '"';
				
				if($v['has_repay']!='')
				{
					$list_value["has_repay"] =  '"' . iconv('utf-8','gbk', l("HAS_REPAY_".strim($v['has_repay']))). '"';		
				}
				else
				{
					$list_value["has_repay"] = "";
				}
				
				$list_value["repay_manage_money"] =  '"' . iconv('utf-8','gbk', $v["repay_manage_money"]). '"';
				
				$list_value["repay_manage_impose_money"] =  '"' . iconv('utf-8','gbk', $v["repay_manage_impose_money"]). '"';
				
				$list_value["user_name"] =  '"' . iconv('utf-8','gbk', $v["user_name"]). '"';
				
				$list_value["t_user_name"] =  '"' . iconv('utf-8','gbk', $v["t_user_name"]). '"';
				
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