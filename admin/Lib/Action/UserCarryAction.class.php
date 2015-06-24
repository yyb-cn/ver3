<?php

class UserCarryAction extends CommonAction{

    //提现申请列表
	public function index(){
		
		$this->getlist(trim($_REQUEST['status']));
	}
	public function wait(){
		$this->getlist("0");
	}
	public function waitpay(){
		
		$this->getlist("3");
	}
	public function success(){
		$this->getlist("1");
	}
	public function failed(){
		$this->getlist("2");
	}
	public function reback(){
		$this->getlist("4");
	}
	
	
	private function getlist($status=''){
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$map['user_id'] = D("User")->where("user_name='".trim($_REQUEST['user_name'])."'")->getField('id');
			
		}
		
		if($status!='')
		{
			$map['status'] = intval($status);
		}
		
		//申请时间
			$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		    $end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		
		if($begin_time > 0 || $end_time > 0){
			if($end_time==0)
			{
				$map[DB_PREFIX.'user_carry.create_time'] = array('egt',$begin_time);
			}
			else
				$map[DB_PREFIX.'user_carry.create_time']= array("between",array($begin_time,$end_time));
		}
		 
		// 处理时间
		    $begin_time_1  = trim($_REQUEST['begin_time_1'])==''?0:to_timespan($_REQUEST['begin_time_1']);
		    $end_time_1  = trim($_REQUEST['end_time_1'])==''?0:to_timespan($_REQUEST['end_time_1']);
		if($begin_time_1 > 0 || $end_time_1 > 0){
			if($end_time_1==0)
			{
				$map[DB_PREFIX.'user_carry.update_time'] = array('egt',$begin_time_1);
			}
			else
				$map[DB_PREFIX.'user_carry.update_time']= array("between",array($begin_time_1,$end_time_1));
		}
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$model = D ("UserCarry");
		// if (! empty ( $model )) {
		//	 $this->_list ( $model, $map );
		// }
	if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		} else {
			$order = ! empty ( $sortBy ) ? $sortBy : $model->getPk ();
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		//取得满足条件的记录数
		$count = $model->where ( $map )->count ( 'id' );
	
		if ($count > 0) {
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = '';
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据

				$more_money=0;	
		$all_list=$model->where ( $map )->	findAll ( );
	foreach($all_list as $k=>$v){
				$more_money+=$v['money']+$v['pfcfb'];
			}
				$this->assign ( 'more_money', $more_money );
			
			//分页查询数据
        $all_money=0;
			$voList = $model->where ( $map )->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
			foreach($voList as $k=>$v){
				$voList[$k]['totle_money']=$v['money']+$v['pfcfb'];
				$all_money+=$v['money']+$v['pfcfb'];
			}
			
			$ssall_money=0;
		$slist=$model->	findAll ( );
			foreach($slist as $k=>$v){
				$ssall_money+=$v['money']+$v['pfcfb'];
			}
		
			
			
			
			
			$voList = $model->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
   foreach($voList as $k=>$v){
				$voList[$k]['totle_money']=$v['money']+$v['pfcfb']+$v['fee'];
			}
			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
			}
			//分页显示

			$page = $p->show ();
			//列表排序显示
			$sortImg = $sort; //排序图标
			$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
			$sort = $sort == 'desc' ? 1 : 0; //排序方式
			//模板赋值显示
		    $this->assign ( 'ssall_money', $ssall_money );//当页总额
			$this->assign ( 'all_money', $all_money );//当页总额
			$this->assign ( 'list', $voList );
			$this->assign ( 'sort', $sort );
			$this->assign ( 'order', $order );
			$this->assign ( 'sortImg', $sortImg );
			$this->assign ( 'sortType', $sortAlt );
			$this->assign ( "page", $page );
			$this->assign ( "nowPage",$p->nowPage);
		}
		$this->display ("index");
	}
	//提现申请列表
	public function edit(){
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$vo['region_lv1_name'] = M("DeliveryRegion")->where("id=".$vo['region_lv1'])->getField("name");
		$vo['region_lv2_name'] = M("DeliveryRegion")->where("id=".$vo['region_lv2'])->getField("name");
		$vo['region_lv3_name'] = M("DeliveryRegion")->where("id=".$vo['region_lv3'])->getField("name");
		$vo['region_lv4_name'] = M("DeliveryRegion")->where("id=".$vo['region_lv4'])->getField("name");
		$vo['bank_name'] =  M("bank")->where("id=".$vo['bank_id'])->getField("name");
		
		$this->assign("vo",$vo);
		$this->display ();
	}
	
	public function update(){
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		switch($data['status']){
			case 0:
				$action = 'wait';
				break;
			case 1:
				$action = 'success';
				break;
			case 2:
				$action = 'failed';
				break;
			case 3:
				$action = 'waitpay';
				break;
			case 4:
				$action = 'reback';
				break;
			default :
				$action = 'index';
				break;
		}
	
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		
		if ($list > 0) {
			$sdata['update_time'] = TIME_UTC;
			$sdata['id'] = $data['id'];
			M(MODULE_NAME)->save ($sdata);
			//成功提示
			$vo = M(MODULE_NAME)->where("id=".$data['id'])->find();
			$user_id = $vo['user_id'];
			$user_info = M("User")->where("id=".$user_id)->find();
			require_once APP_ROOT_PATH."/system/libs/user.php";
			if($data['status']==1){
				//提现
				modify_account(array("lock_money"=>-$vo['money']),$vo['user_id'],"提现成功",8);
				modify_account(array("lock_money"=>-$vo['fee']),$vo['user_id'],"提现成功",9);
				$content = "您于".to_date($vo['create_time'],"Y年m月d日 H:i:s")."提交的".format_price($vo['money'])."提现申请汇款成功，请查看您的资金记录。";
				
				
				$group_arr = array(0,$user_id);
				sort($group_arr);
				$group_arr[] =  6;
				
				$msg_data['content'] = $content;
				$msg_data['to_user_id'] = $user_id;
				$msg_data['create_time'] = TIME_UTC;
				$msg_data['type'] = 0;
				$msg_data['group_key'] = implode("_",$group_arr);
				$msg_data['is_notice'] = 6;
				
				$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
				$id = $GLOBALS['db']->insert_id();
				$GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);
				
				//短信通知
				if(app_conf("SMS_ON")==1)
				{
					$tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_CARYY_SUCCESS_SMS'");				
					$tmpl_content = $tmpl['content'];
									
					$notice['user_name'] = $user_info["user_name"];
					$notice['carry_money'] = $vo['money'];
					$notice['site_name'] = app_conf("SHOP_TITLE");
					
					$GLOBALS['tmpl']->assign("notice",$notice);
					
					$msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);
					
					$msg_data['dest'] = $user_info['mobile'];
					$msg_data['send_type'] = 0;
					$msg_data['title'] = "提现成功短信提醒";
					$msg_data['content'] = addslashes($msg);;
					$msg_data['send_time'] = 0;
					$msg_data['is_send'] = 0;
					$msg_data['create_time'] = TIME_UTC;
					$msg_data['user_id'] = $user_info['id'];
					$msg_data['is_html'] = $tmpl['is_html'];
					$GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
				}
			}
			elseif($data['status']==2){
				//驳回
				modify_account(array("money"=>$vo['money'],"lock_money"=>-$vo['money']),$vo['user_id'],"提现失败",8);
				modify_account(array("money"=>$vo['fee'],"lock_money"=>-$vo['fee']),$vo['user_id'],"提现失败",9);
				$content = "您于".to_date($vo['create_time'],"Y年m月d日 H:i:s")."提交的".format_price($vo['money'])."提现申请被我们驳回，驳回原因\"".$data['msg']."\"";
				
				$group_arr = array(0,$user_id);
				sort($group_arr);
				$group_arr[] =  7;
				
				$msg_data['content'] = $content;
				$msg_data['to_user_id'] = $user_id;
				$msg_data['create_time'] = TIME_UTC;
				$msg_data['type'] = 0;
				$msg_data['group_key'] = implode("_",$group_arr);
				$msg_data['is_notice'] = 7;
				
				$GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
				$id = $GLOBALS['db']->insert_id();
				$GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);
			}
			save_log("编号为".$data['id']."的提现申请".L("UPDATE_SUCCESS"),1);
			//开始验证有效性
			$this->assign("jumpUrl",u(MODULE_NAME."/".$action));
			parent::success(L("UPDATE_SUCCESS"));
		}else {
			//错误提示
			$DBerr = M()->getDbError();
			save_log("编号为".$data['id']."的提现申请".L("UPDATE_FAILED").$DBerr,0);
			$this->error(L("UPDATE_FAILED").$DBerr,0);
		}
	}
	//批量编辑取现
        public function edit_arr(){
            $array_id = $_REQUEST['id'];
			
                $id_arr = explode ( ',', $array_id );
                foreach ($id_arr as $key => $value) {
                    $condition['id'] = $value;
                   
                    $result[$key] = M(MODULE_NAME)->where($condition)->find();
                    if($result[$key]==''){
                         continue; 
                    }

                    $realname = M("User")->where("id=".$result[$key]['user_id'])->getField("real_name");//判断用户开户名是否与注册认证真实姓名一致
                    
                    if($realname!=$result[$key]['real_name'])
                    {
                        $result[$key]['red_name'] = 1;
                        $result[$key]['user_real_name'] = $realname;
                    }else{
                        $result[$key]['red_name'] = 0;
                    }
                    
                    $result[$key]['region_lv1_name'] = M("DeliveryRegion")->where("id=".$result[$key]['region_lv1'])->getField("name");
                    $result[$key]['region_lv2_name'] = M("DeliveryRegion")->where("id=".$result[$key]['region_lv2'])->getField("name");
                    $result[$key]['region_lv3_name'] = M("DeliveryRegion")->where("id=".$result[$key]['region_lv3'])->getField("name");
                    $result[$key]['region_lv4_name'] = M("DeliveryRegion")->where("id=".$result[$key]['region_lv4'])->getField("name");
                    $result[$key]['bank_name'] =  M("bank")->where("id=".$result[$key]['bank_id'])->getField("name");
                    
                }
            $result = array_filter($result);
            $num = count($result);
            $this->assign("idnum",$num);
            $this->assign("result",$result);
            $this->assign("array_id",$array_id);
            $this->display ();
        }
        //批量处理取现
        public function update_arr(){
                B('FilterString');
                $result = $_REQUEST;
		//开始验证有效性
                foreach ($result['id'] as $key => $value) {
                    $data['id'] = $value;
                    $data['status'] = $result['status'];
                    $data['desc'] = $result['desc'];
                    // 更新数据
                    switch($data['status']){
                            case 0:
                                    $action = 'wait';
                                    break;
                            case 1:
                                    $action = 'success';
                                    break;
                            case 2:
                                    $action = 'failed';
                                    break;
                            case 3:
                                    $action = 'waitpay';
                                    break;
                            case 4:
                                    $action = 'reback';
                                    break;
                            default :
                                    $action = 'index';
                                    break;
                    }
          
                    // 更新数据
                    $list=M(MODULE_NAME)->save ($data);

                    if ($list > 0) {
                            $sdata['update_time'] = TIME_UTC;
                            $sdata['id'] = $data['id'];
                            M(MODULE_NAME)->save ($sdata);
                            //成功提示
                            $vo = M(MODULE_NAME)->where("id=".$data['id'])->find();
                            $user_id = $vo['user_id'];
                            $user_info = M("User")->where("id=".$user_id)->find();
                            require_once APP_ROOT_PATH."/system/libs/user.php";
                            if($data['status']==1){
                                    //提现
                                    modify_account(array("lock_money"=>-$vo['money']),$vo['user_id'],"提现成功",8);
                                    modify_account(array("lock_money"=>-$vo['fee']),$vo['user_id'],"提现成功",9);
                                    $content = "您于".to_date($vo['create_time'],"Y年m月d日 H:i:s")."提交的".format_price($vo['money'])."提现申请汇款成功，请查看您的资金记录。";


                                    $group_arr = array(0,$user_id);
                                    sort($group_arr);
                                    $group_arr[] =  6;

                                    $msg_data['content'] = $content;
                                    $msg_data['to_user_id'] = $user_id;
                                    $msg_data['create_time'] = TIME_UTC;
                                    $msg_data['type'] = 0;
                                    $msg_data['group_key'] = implode("_",$group_arr);
                                    $msg_data['is_notice'] = 6;

                                    $GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
                                    $id = $GLOBALS['db']->insert_id();
                                    $GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);

                                    //短信通知
                                    if(app_conf("SMS_ON")==1)
                                    {
                                            $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = 'TPL_CARYY_SUCCESS_SMS'");				
                                            $tmpl_content = $tmpl['content'];

                                            $notice['user_name'] = $user_info["user_name"];
                                            $notice['carry_money'] = $vo['money'];
                                            $notice['site_name'] = app_conf("SHOP_TITLE");

                                            $GLOBALS['tmpl']->assign("notice",$notice);

                                            $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

                                            $msg_data['dest'] = $user_info['mobile'];
                                            $msg_data['send_type'] = 0;
                                            $msg_data['title'] = "提现成功短信提醒";
                                            $msg_data['content'] = addslashes($msg);;
                                            $msg_data['send_time'] = 0;
                                            $msg_data['is_send'] = 0;
                                            $msg_data['create_time'] = TIME_UTC;
                                            $msg_data['user_id'] = $user_info['id'];
                                            $msg_data['is_html'] = $tmpl['is_html'];
                                            $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data); //插入				
                                    }
                            }
                            elseif($data['status']==2){
							
                                    //驳回
                                    modify_account(array("money"=>$vo['money'],"lock_money"=>-$vo['money']),$vo['user_id'],"提现失败",8);
                                    modify_account(array("money"=>$vo['fee'],"lock_money"=>-$vo['fee']),$vo['user_id'],"提现失败",9);
                                    $content = "您于".to_date($vo['create_time'],"Y年m月d日 H:i:s")."提交的".format_price($vo['money'])."提现申请被我们驳回，驳回原因\"".$data['msg']."\"";

                                    $group_arr = array(0,$user_id);
                                    sort($group_arr);
                                    $group_arr[] =  7;

                                    $msg_data['content'] = $content;
                                    $msg_data['to_user_id'] = $user_id;
                                    $msg_data['create_time'] = TIME_UTC;
                                    $msg_data['type'] = 0;
                                    $msg_data['group_key'] = implode("_",$group_arr);
                                    $msg_data['is_notice'] = 7;

                                    $GLOBALS['db']->autoExecute(DB_PREFIX."msg_box",$msg_data);
                                    $id = $GLOBALS['db']->insert_id();
                                    $GLOBALS['db']->query("update ".DB_PREFIX."msg_box set group_key = '".$msg_data['group_key']."_".$id."' where id = ".$id);
                            }
                            save_log("编号为".$data['id']."的提现申请".L("UPDATE_SUCCESS"),1);
                            //开始验证有效性
                            // $this->assign("jumpUrl",u(MODULE_NAME."/".$action));
                            // parent::success(L("UPDATE_SUCCESS"));
                    }else {
                            //错误提示
                            $DBerr = M()->getDbError();
                            save_log("编号为".$data['id']."的提现申请".L("UPDATE_FAILED").$DBerr,0);
                            $this->error(L("UPDATE_FAILED").$DBerr,0);
                    }
                }
                            $this->assign("jumpUrl",u(MODULE_NAME."/".$action));
                            parent::success(L("UPDATE_SUCCESS")); 
        }
        
	public function config(){
		$list = M()->query("SELECT * FROM ".DB_PREFIX."user_carry_config ORDER BY id ASC");
		$this->assign("list",$list);
		$this->display();
	}
	
	public function saveconfig(){
		$config = $_POST['config'];
		$has_ids = null;
		foreach($config['id'] as $k=>$v){
			if(intval($v) > 0){
				$has_ids[] = $v;
			}
		}
		M()->query("DELETE FROM ".DB_PREFIX."user_carry_config WHERE id not in (".implode(",",$has_ids).")");
		
		foreach($config['id'] as $k=>$v){
			if(intval($v) > 0){
				$config_data =array();
				$config_data['id'] = $v;
				$config_data['name'] = trim($config['name'][$k]);
				$config_data['min_price'] = floatval($config['min_price'][$k]);
				$config_data['max_price'] = floatval($config['max_price'][$k]);
				$config_data['fee'] = floatval($config['fee'][$k]);
				$config_data['fee_type'] = intval($config['fee_type'][$k]);
				M("UserCarryConfig")->save($config_data);
			}
		}
		
		$aconfig = $_POST['aconfig'];
		foreach($aconfig['name'] as $k=>$v){
			if(trim($v)!=""){
				$config_data =array();
				$config_data['name'] = trim($v);
				$config_data['min_price'] = floatval($aconfig['min_price'][$k]);
				$config_data['max_price'] = floatval($aconfig['max_price'][$k]);
				$config_data['fee'] = floatval($aconfig['fee'][$k]);
				$config_data['fee_type'] = intval($aconfig['fee_type'][$k]);
				M("UserCarryConfig")->add($config_data);
			}
		}
		rm_auto_cache("user_carry_config");
		parent::success(L("UPDATE_SUCCESS"));
	}
	
	public function export_csv($page = 1)
	{
		set_time_limit(0);
		$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$map['user_id'] = D("User")->where("user_name='".trim($_REQUEST['user_name'])."'")->getField('id');
		}
		
		if(trim($_REQUEST['status_type'])=="index"){
			if(trim($_REQUEST['status'])!='')
			{
				$map['status'] = intval($_REQUEST['status']);
			}
		}
		else{
			$status_type = trim($_REQUEST['status_type']);
			switch($status_type){
				case "wait":
					$map['status'] = 0;
					break;
				case "waitpay":
					$map['status'] = 3;
					break;
				case "success":
					$map['status'] = 1;
					break;
				case "failed":
					$map['status'] = 2;
					break;
				case "reback":
					$map['status'] = 4;
					break;
			}
		}
		
		$list = M("UserCarry")
				->where($map)
				->join(DB_PREFIX.'user ON '.DB_PREFIX.'user.id = '.DB_PREFIX.'user_carry.user_id')
				->join(DB_PREFIX.'delivery_region lv1 ON lv1.id = '.DB_PREFIX.'user_carry.region_lv1')
				->join(DB_PREFIX.'delivery_region lv2 ON lv2.id = '.DB_PREFIX.'user_carry.region_lv2')
				->join(DB_PREFIX.'delivery_region lv3 ON lv3.id = '.DB_PREFIX.'user_carry.region_lv3')
				->join(DB_PREFIX.'delivery_region lv4 ON lv4.id = '.DB_PREFIX.'user_carry.region_lv4')
				->join(DB_PREFIX.'bank ON '.DB_PREFIX.'bank.id = '.DB_PREFIX.'user_carry.bank_id')
				->field(
						DB_PREFIX.'user_carry.id,' .
						DB_PREFIX.'user.user_name,' .
						DB_PREFIX.'user_carry.real_name,' .
						DB_PREFIX.'user_carry.bankzone,' .
						DB_PREFIX.'user_carry.bankcard,' .
						DB_PREFIX.'user_carry.money,' .
						DB_PREFIX.'user_carry.fee,' .
						DB_PREFIX.'user_carry.create_time,' .
						DB_PREFIX.'user_carry.update_time,' .
						DB_PREFIX.'user_carry.status,' .
						DB_PREFIX.'user_carry.desc,' .
						'lv1.name as lv1_name,' .
						'lv2.name as lv2_name,' .
						'lv3.name as lv3_name,' .
						'lv4.name as lv4_name,' .
						DB_PREFIX.'bank.name as bank_name,' .
						'lv4.name as lv4_name'
						)
				->limit($limit)->findAll();


		if($list)
		{
			register_shutdown_function(array(&$this, 'export_csv'), $page+1);
			
			$carry_value = array('id'=>'""','user_name'=>'""','money'=>'""','fee'=>'""','bank_name'=>'""','region'=>'""','bankzone'=>'""','real_name'=>'""','bankcard'=>'""','create_time'=>'""','status'=>'""','update_time'=>'""','desc'=>'""');
			if($page == 1){
		    	$content = iconv("utf-8","gbk","编号,会员,提现,手续费,开户行,开户行所在地,开户行网点,姓名,银行卡卡号,申请时间,处理结果,处理时间,操作备注");
		    	$content = $content . "\n";
			}
	    	
	    	foreach($list as $k=>$v)
			{	
				$carry_value = array();
				$carry_value['id'] = iconv('utf-8','gbk','"' . $v['id'] . '"');
				$carry_value['user_name'] = iconv('utf-8','gbk','"' . $v['user_name'] . '"');
				$carry_value['money'] = iconv('utf-8','gbk','"' .  number_format($v['money'],2) . '"');
				$carry_value['fee'] = iconv('utf-8','gbk','"' . number_format($v['fee'],2) . '"');
				$carry_value['bank_name'] = iconv('utf-8','gbk','"' . $v['bank_name'] . '"');
				$carry_value['region'] = iconv('utf-8','gbk','"' . $v['lv1_name'] . ' '.$v['lv1_name'] .' '.$v['lv2_name'] .' '.$v['lv3_name'] .' '.$v['lv4_name'] . '"');
				$carry_value['bankzone'] = iconv('utf-8','gbk','"' . $v['bankzone'] . '"');
				$carry_value['real_name'] = iconv('utf-8','gbk','"' . $v['real_name'] . '"');
				$carry_value['bankcard'] = iconv('utf-8','gbk','"' . $v['bankcard'] . '"');
				$carry_value['bankcard'] =$carry_value['bankcard']."'";
				$carry_value['create_time'] = iconv('utf-8','gbk','"' . to_date($v['create_time']) . '"');
				if($v['status']==0){
					$status_name ="未处理";
				}
				if($v['status']==1){
					$status_name ="申请通过";
				}
				if($v['status']==1){
					$status_name ="申请驳回";
				}
				$carry_value['status'] = iconv('utf-8','gbk','"' . $status_name . '"');
				$carry_value['update_time'] = iconv('utf-8','gbk','"' . to_date($v['update_time']) . '"');
				$carry_value['desc'] = iconv('utf-8','gbk','"' . $v['desc'] . '"');
			
				$content .= implode(",", $carry_value) . "\n";
			}	
			
			
			header("Content-Disposition: attachment; filename=carry_list.csv");
	    	echo $content;  
		}
		else
		{
			if($page==1)
			$this->error(L("NO_RESULT"));
		}
		
	}
	
	
	  public function daochu()
	{
		 $id = $_REQUEST ['id'];
		 //格式的输入
		$geshi = $_REQUEST ['geshi'];
	
		//where(array ('user_id' => array ('in', explode ( ',', $id ) ) ));
		$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
		
		$vo = M(MODULE_NAME)->where($condition)->select();
		
		foreach($vo as $k=>$v)
		{
		
		$v['region_lv1_name'] = M("DeliveryRegion")->where("id=".$v['region_lv1'])->getField("name");
		$v['region_lv2_name'] = M("DeliveryRegion")->where("id=".$v['region_lv2'])->getField("name");
		$v['region_lv3_name'] = M("DeliveryRegion")->where("id=".$v['region_lv3'])->getField("name");
		$v['region_lv4_name'] = M("DeliveryRegion")->where("id=".$v['region_lv4'])->getField("name");
		$v['phone']=M("User")->where("id=".$v['user_id'])->getField("mobile");
		$v['user_name']=M("User")->where("id=".$v['user_id'])->getField("user_name");
		$v['bank_name'] =  M("bank")->where("id=".$v['bank_id'])->getField("name");
		$arr[0]=array('序号','银行','地区(省)','地区(市/区)','支行名','开户名','卡号','金额','电话号码','操作备注','申请时间','处理时间','备注');
		$all_money=$v['pfcfb']+$v['money'];
		$arr[$k+1]=array($k+1,$v['bank_name'],$v['region_lv2_name'],$v['region_lv3_name'],$v['bankzone'],$v['real_name'],$v['bankcard'],$all_money,$v['phone'],$v['desc'],to_date($v['create_time'],'Y-m-d'),to_date($v['update_time'],'Y-m-d'),$v['user_name']);
		}
		if($geshi=='utf-8' || $geshi=='gbk'){
		
	//var_dump($arr);exit;
		
		$this->outputXlsHeader($arr,'提现名单'.time(),$geshi);
		}else{
			
		$this->error(L("输入的格式不支持"));
			
		}
		
	}
	
	
	public function outputXlsHeader($data,$file_name = 'export',$geshi)
{
 header('Content-Type: text/xls'); 
 header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );
 $str = mb_convert_encoding($file_name, 'gbk', $geshi);   
 header('Content-Disposition: attachment;filename="' .$str . '.xls"');      
 header('Cache-Control:must-revalidate,post-check=0,pre-check=0');        
 header('Expires:0');         
 header('Pragma:public');
 
 $table_data = '<table border="1">'; 
 foreach ($data as $line)         
 {
  $table_data .= '<tr>';
  foreach ($line as $key => &$item)
  {
  $item = mb_convert_encoding($item, 'gbk', $geshi); 
  //根据身份证号码进行导出格式输出
  if(strlen($item)>=15){
   $table_data .= '<td style="vnd.ms-excel.numberformat:@">' . $item . '</td>';
   }else{
	   
	   $table_data .= '<td>' . $item . '</td>'; 
   }
  }
  $table_data .= '</tr>';
 }
 $table_data .='</table>';
 echo $table_data;    
 die();
}

	
	public function delete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				
				
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
		
				if ($list!==false) {					
					save_log(l("FOREVER_DELETE_SUCCESS"),1);
					parent::success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log(l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}	
}
?>