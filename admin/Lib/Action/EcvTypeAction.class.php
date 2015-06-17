<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class EcvTypeAction extends CommonAction{
	public function index()
	{
		parent::index();
	}
	public function add()
	{
		$this->display();
	}
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("VOUCHER_NAME_EMPTY_TIP"));
		}	
		if(doubleval($data['money'])<=0)
		{
			$this->error(L("VOUCHER_MONEY_ERROR_TIP"));
		}	
                
                $data['user_type'] = implode("|",$data['user_type']);//1为新用户，2为老用户
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
                //var_dump($vo);exit;
                $vo['user_type'] = explode("|", $vo['user_type']); 
		$vo['begin_time']=$vo['begin_time']?date("Y-m-d H:i:s",$vo['begin_time']):'没有限制';
		$vo['end_time']=$vo['end_time']?date("Y-m-d H:i:s",$vo['end_time']):'没有限制';
		$this->assign ( 'vo', $vo );
		$this->display ();
	}
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
				
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("VOUCHER_NAME_EMPTY_TIP"));
		}	
		if(doubleval($data['money'])<=0)
		{
			$this->error(L("VOUCHER_MONEY_ERROR_TIP"));
		}	
	
                $data['user_type'] = implode("|",$data['user_type']);//1为新用户，2为老用户
		$data['begin_time'] = trim($data['begin_time'])==''?0:to_timespan($data['begin_time']);
		$data['end_time'] = trim($data['end_time'])==''?0:to_timespan($data['end_time']);
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			M("Ecv")->where("ecv_type_id=".$data['id'])->setField("use_limit",$data['use_limit']);  //同步可用次数
			M("Ecv")->where("ecv_type_id=".$data['id'])->setField("begin_time",$data['begin_time']);
			M("Ecv")->where("ecv_type_id=".$data['id'])->setField("end_time",$data['end_time']);
			M("Ecv")->where("ecv_type_id=".$data['id'])->setField("money",$data['money']);
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				if(M("Ecv")->where(array ('ecv_type_id' => array ('in', explode ( ',', $id ) ) ))->count()>0)
				{
					$this->error(l("VOUCHER_EXIST"),$ajax);
				}
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
		
				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
        	public function send_list()
	{
		$group_list = M("UserGroup")->findAll();
		$this->assign("group_list",$group_list);
		
		//定义条件
		$map[DB_PREFIX.'user.is_delete'] = 0;

		if(intval($_REQUEST['group_id'])>0)
		{
			$map[DB_PREFIX.'user.group_id'] = intval($_REQUEST['group_id']);
		}
		
		if(trim($_REQUEST['user_name'])!='')
		{
			$map[DB_PREFIX.'user.user_name'] = array('like','%'.trim($_REQUEST['user_name']).'%');
		}
		if(trim($_REQUEST['email'])!='')
		{
			$map[DB_PREFIX.'user.email'] = array('like','%'.trim($_REQUEST['email']).'%');
		}
		if(trim($_REQUEST['mobile'])!='')
		{
			$map[DB_PREFIX.'user.mobile'] = array('like','%'.trim($_REQUEST['mobile']).'%');
		}
		if(trim($_REQUEST['pid_name'])!='')
		{
			$pid = M("User")->where("user_name='".trim($_REQUEST['pid_name'])."'")->getField("id");
			$map[DB_PREFIX.'user.pid'] = $pid;
		}
		
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D (User);
		if (! empty ( $model )) {
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

				$voList = $model->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
				//var_dump($voList);exit;
				$ecv_moduel=D('ecv');
				$ecv_list=$ecv_moduel->join(DB_PREFIX.'ecv_type ON '.DB_PREFIX.'ecv_type.id = '.DB_PREFIX.'ecv.ecv_type_id')->select();
				
				
				//var_dump($ecv_type_list);exit;
				//var_dump($ecv_list);exit;
				foreach($voList as $k=>$v){
				
					foreach($ecv_list as $kk=>$vv){
						if($vv['user_id']==$v['id']){
                                                $end_time = date('Y-m-d H:i:s',$vv['end_time']);//到期时间
                                                $use_time = date('Y-m-d H:i:s',$vv['receive_time']);//领取代金券时间
                                                $last_time = date('Y-m-d H:i:s',$vv['last_time']);//使用到期时间
						$use=$vv['used_yn']?'已用 时间:'.$last_time:'未用';
                                                $used =$vv['receive']?'已领取 时间:'.$use_time:'未领取';
							$voList[$k]['ecvs'].=trim('类型：'.$vv['name'].',面额:'.substr($vv['money'],0,-5).'元'.'【'.$used.'】【'.$use.'】 到期时间：'.$end_time.'<br />');
						}
						
					}
				
				}
				//var_dump($voList[0]['ecvs'][0]);exit;
	//			echo $model->getlastsql();
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
				
				
				$this->assign ( 'list', $voList );
				$this->assign ( 'sort', $sort );
				$this->assign ( 'order', $order );
				$this->assign ( 'sortImg', $sortImg );
				$this->assign ( 'sortType', $sortAlt );
				$this->assign ( "page", $page );
				$this->assign ( "nowPage",$p->nowPage);
			}
		
		}
		
		$this->display ();
	}
    


	public function send_list_ecv()
	{
		$group_list = M("UserGroup")->findAll();
		
		
		$count = M("Ecv")->count ( 'id' );
		
			if ($count > 0) {
				//创建分页对象
				if (! empty ( $_REQUEST ['listRows'] )) {
					$listRows = $_REQUEST ['listRows'];
				} else {
					$listRows = '';
				}
			}
				$p = new Page ( $count, $listRows );
				
		$ecv_list = M("Ecv")->limit($p->firstRow . ',' . $p->listRows)->findAll();
		

				
					foreach($ecv_list as $kk=>$vv){
					                            $ecv_list[$kk]['user_id'] = M("User")->where("id=".$vv['user_id'])->getField("user_name");
												$ecv_list[$kk]['ecv_type_id'] = M("Ecv_type")->where("id=".$vv['ecv_type_id'])->getField("name");
                                                $ecv_list[$kk]['end_time'] = date('Y-m-d H:i:s',$vv['end_time']);//到期时间
                                                $ecv_list[$kk]['receive_time'] = date('Y-m-d H:i:s',$vv['receive_time']);//领取代金券时间
                                                $ecv_list[$kk]['last_time'] = date('Y-m-d H:i:s',$vv['last_time']);//使用到期时间
												if($vv['used_yn']==1 || $vv['used_yn']==2){
													$ecv_list[$kk]['used_yn']='以用';
												}else{
													
													$ecv_list[$kk]['used_yn']='未用';
												}
					
						
						
						
					}
				
			$page = $p->show ();
	
			$this->assign ( 'ecv_list', $ecv_list );
			$this->assign("group_list",$group_list);
		    $this->assign("page",$page);
		
		$this->display ();
	}    
	
	public function send_list_ecv_edit()
	{
		$id = intval($_REQUEST ['id']);
			//print_r($id);exit;
			  $user_id= M("Ecv")->where("id=".$id)->getField("user_id");
		      $user_name = M("User")->where("id=".$user_id)->getField("user_name");
			  $ecv_id= M("Ecv")->where("id=".$id)->getField("ecv_type_id");
			  $ecv_type__name = M("Ecv_type")->where("id=".$ecv_id)->getField("name");
			  $used_yn= M("Ecv")->where("id=".$id)->getField("used_yn");
			
			
			
			 
			
		   
		    $this->assign("ecv_type__name",$ecv_type__name);
			$this->assign("user_name",$user_name);
			$this->assign("used_yn",$used_yn);
		
	        $this->assign("id",$id);
			
		    $this->display ();
		
		
		
		
		
		
	}
	public function send_list_ecv_search()
	{   	if(intval($_REQUEST['id'])>0)
		{	 $id = intval($_REQUEST ['id']);
	        $ecv_list= M("Ecv")->where("id=".$id)->findAll();
		
			
		
		}
		
		if(trim($_REQUEST['user_name'])!='')
	    { 
		$user_name  = trim($_REQUEST['user_name']);
		$user_id = M("User")->where("user_name='$user_name'")->getField("id");

			 $ecv_list= M("Ecv")->where("user_id=".$user_id)->findAll();
			
		}
			if(intval($_REQUEST['used_yn'])==1)
		{	 $used_yn = intval($_REQUEST ['used_yn']);
	        $ecv_list= M("Ecv")->where("used_yn=".$used_yn)->findAll();
		
		
		}
		if(intval($_REQUEST['used_yn'])==0 && trim($_REQUEST['user_name'])=='' && !intval($_REQUEST['id']))
		{	 $used_yn = intval($_REQUEST ['used_yn']);
	        $ecv_list= M("Ecv")->where("used_yn=".$used_yn)->findAll();
		
	
			
		}
	
			
			  $group_list = M("UserGroup")->findAll();
	
		

				
					foreach($ecv_list as $kk=>$vv){
					                            $ecv_list[$kk]['user_id'] = M("User")->where("id=".$vv['user_id'])->getField("user_name");
												$ecv_list[$kk]['ecv_type_id'] = M("Ecv_type")->where("id=".$vv['ecv_type_id'])->getField("name");
                                                $ecv_list[$kk]['end_time'] = date('Y-m-d H:i:s',$vv['end_time']);//到期时间
                                                $ecv_list[$kk]['receive_time'] = date('Y-m-d H:i:s',$vv['receive_time']);//领取代金券时间
                                                $ecv_list[$kk]['last_time'] = date('Y-m-d H:i:s',$vv['last_time']);//使用到期时间
												if($vv['used_yn']==1 || $vv['used_yn']==2){
													$ecv_list[$kk]['used_yn']='以用';
												}else{
													
													$ecv_list[$kk]['used_yn']='未用';
												}
					
						
						
						
					}
				
			
	
			$this->assign ( 'ecv_list', $ecv_list );
			$this->assign("group_list",$group_list);
		
		
	
		 $this->display ('send_list_ecv');
		
		
		
		
		
		
	}
		public function send_list_ecv_update()
	{
		if($_POST){
			$id=$_POST['id'];
			$data['used_yn']=$_POST['used_yn'];
			}
	
			
		$one = M("Ecv")->where("id='$id'")->save($data);
		
	
	if($one==1){
		
	
		//操作成功跳转;
		$this->success(L("UPDATE_SUCCESS"));
		
		
		
		}else{
	
	$this->error(L("UPDATE_FAILED"));
	}
		 
		
		
	}

		public function send_list_ecv_delete()
	{  //删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
			$one=M("Ecv")->delete("$id"); 
				if($one){
					$this->success ("删除成功",$ajax);
		} else {
				$this->error ("删除失败",$ajax);
		}		
		}
	}
	public function send()
	{
		$id = intval($_REQUEST['id']);
		$ecv_type = M("EcvType")->getById($id);
		if(!$ecv_type)
		{
			$this->error(l("INVALID_ECV_TYPE"));
		}
		$user_group = M("UserGroup")->findAll();
		$this->assign("user_group",$user_group);
		$this->assign("ecv_type",$ecv_type);
		$this->display();
	}	
	
	public function doSend()
	{
		require_once APP_ROOT_PATH."system/libs/voucher.php";
		$ecv_type_id = intval($_REQUEST['ecv_type_id']);
		$need_password = intval($_REQUEST['need_password']);
		$send_type = intval($_REQUEST['send_type']);
		$user_group = intval($_REQUEST['user_group']);
		$user_ids = trim($_REQUEST['user_id']);
		$gen_count = intval($_REQUEST['gen_count']);
		$page = intval($_REQUEST['page'])==0?1:intval($_REQUEST['page']);  //大数据量时的载入的页数
		$page_size = app_conf("BATCH_PAGE_SIZE"); //每次运行的次数， 开发时可根据实际环境改变此大小
		$page_limit = ($page-1)*$page_size.",".$page_size;
		switch($send_type)
		{
			case 0:
				//按会员组
				$user_list = M("User")->where("group_id=".$user_group)->order("id asc")->limit($page_limit)->findAll();
				if($user_list)
				{
					foreach($user_list as $v)
					{
					 if(M("DealLoad")->where("user_id=".$v['id'])->findAll())
					 {
						send_voucher($ecv_type_id,$v['id'],$need_password);
					 }
					}
					
					$this->assign("jumpUrl",u("EcvType/doSend",array("ecv_type_id"=>$ecv_type_id,'need_password'=>$need_password,'send_type'=>$send_type,'user_group'=>$user_group,'user_id'=>$user_ids,'gen_count'=>$gen_count,'page'=>($page+1))));
					$msg = sprintf(l("SEND_VOUCHER_PAGE_SUCCESS"),($page-1)*$page_size,$page*$page_size);
					$this->success($msg);
				}
				else
				{
					save_log("ID".$ecv_type_id.l("VOUCHER_SEND_SUCCESS"),1);
					$this->assign("jumpUrl",u("EcvType/index"));
					$this->success(l("VOUCHER_SEND_SUCCESS"));
				}
				break;
			case 1:
				//按会员ID
				$user_list = M("User")->where("id in(".$user_ids.")")->order("id asc")->limit($page_limit)->findAll();
				if($user_list)
				{
					foreach($user_list as $v)
					{
						send_voucher($ecv_type_id,$v['id'],$need_password);
					}
					$this->assign("jumpUrl",u("EcvType/doSend",array("ecv_type_id"=>$ecv_type_id,'need_password'=>$need_password,'send_type'=>$send_type,'user_group'=>$user_group,'user_id'=>$user_ids,'gen_count'=>$gen_count,'page'=>($page+1))));
					$msg = sprintf(l("SEND_VOUCHER_PAGE_SUCCESS"),($page-1)*$page_size,$page*$page_size);
					$this->success($msg);
				}
				else
				{
					save_log("ID".$ecv_type_id.l("VOUCHER_SEND_SUCCESS"),1);
					$this->assign("jumpUrl",u("EcvType/index"));
					$this->success(l("VOUCHER_SEND_SUCCESS"));
				}
				break;
			case 2:
				//线下
				for($i=0;$i<$page_size;$i++)
				{					
					if(($page-1)*$page_size+$i==$gen_count)
					{
						save_log("ID".$ecv_type_id.l("VOUCHER_SEND_SUCCESS"),1);
						$this->assign("jumpUrl",u("EcvType/index"));
						$this->success(l("VOUCHER_SEND_SUCCESS"));
						break;
					}
					send_voucher($ecv_type_id,0,$need_password);	
				}
				$this->assign("jumpUrl",u("EcvType/doSend",array("ecv_type_id"=>$ecv_type_id,'need_password'=>$need_password,'send_type'=>$send_type,'user_group'=>$user_group,'user_id'=>$user_ids,'gen_count'=>$gen_count,'page'=>($page+1))));
				$msg = sprintf(l("SEND_VOUCHER_PAGE_SUCCESS"),($page-1)*$page_size,$page*$page_size);
				$this->success($msg);
				break;
		}
		
	}
        
        public function doSend_one()   //按提交内容不同执行发送代金券，
	{
		if($_POST)
		{
		$ecv_id=$_POST['ecv_id'];
		}
		include('app/Lib/common.php');
		$user_id = intval($_REQUEST['id']);
		$voucher_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where `id` = ".$ecv_id);
		//var_dump($voucher_info);exit;
		if(!empty($voucher_info))
			{
			//或者是没有时间限制
				if($voucher_info['end_time']>time()||$voucher_info['end_time']==0){
				//echo 1;exit;
					require_once APP_ROOT_PATH."system/libs/voucher.php";   
					$rs = send_voucher($voucher_info['id'],$user_id,1);   //返回ID
					if($rs){
					
					//发送站内信
					//send_voucher(代金券ID,用户ID,'是否需要密码')
					$voucher_info['end_time']=$voucher_info['end_time']?date("Y-m-d H:i:s",$voucher_info['end_time']):'没有限制';
					$title="获得代金券";
					$content="恭喜你,获得代金券".$voucher_info['name']."到期时间为:".$voucher_info['end_time'];
					
					 send_user_msg($title,$content,0,$user_id,time(),0,true,true);
					
					$msg = sprintf(l("SEND_VOUCHER_PAGE_SUCCESS"));
					$this->success($msg);
					}
				}
				else{
				$this->error ('改代金券已经失效');
				}
			}
		
		
	}
	
        public function doSend_arr(){
               
//          $user_id_arr = $_REQUEST['user_id_arr'];//获取array的user_id
            $ecv_id  = intval($_REQUEST['ecv_id']);//获取代金券id
            $receive = intval($_REQUEST['receive']);//获取是否帮老客户自动领取 1为已领取 0为未领取
            $old_user = intval($_REQUEST['lao']);//获取发给老用户命令
            
            if($old_user!=1){
                $this->error("没有用户ID");
            }else{
                
                $id_old= $GLOBALS['db']->getAll("select `user_id` from ".DB_PREFIX."deal_load group by user_id" );
                $user_id_arr = $id_old;
            }
            $voucher_info=$GLOBALS['db']->getRow("select * from ".DB_PREFIX."ecv_type where `id` = ".$ecv_id);
            
            if(empty($voucher_info)){
                $this->error("没有该代金券");
            }
            echo "请稍候，数据处理中。。。。。";
            include('app/Lib/common.php');
            if($voucher_info['end_time']>time()||$voucher_info['end_time']==0){

                $ecv_data['use_limit'] = $voucher_info['use_limit'];
                $ecv_data['begin_time'] = $voucher_info['begin_time'];
                $ecv_data['end_time'] = $voucher_info['end_time'];
                $ecv_data['money'] = $voucher_info['money'];
                $ecv_data['receive'] = $receive;
                $ecv_data['ecv_type_id'] = $ecv_id;
                $ecv_data['password'] = rand(10000000,99999999);
                $ecv_data['sn'] = uniqid();
                if($ecv_data['receive']){
                    $ecv_data['receive_time'] = time();
                    $ecv_data['last_time'] = time()+$voucher_info['time_limitd']*24*3600;
                }
                $insert_id = array();
                $error_id =array();
                foreach ($user_id_arr as $key => $value) {
                    $ecv = M('ecv')->where("user_id ='".$value['user_id']."' and ecv_type_id ='".$ecv_id."'")->count();
                    if($ecv>0){
                        $error_id[$key] = $value['user_id'];
                    }else{
                        $ecv_data['user_id'] = $value['user_id'];
                        $GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$ecv_data,'INSERT');//autoExecute(表,$字段,'插入动作','条件','执行模式');
                        $insert_id[$key] = $GLOBALS['db']->insert_id();
                        $voucher_info['end_time']=$voucher_info['end_time']?date("Y-m-d H:i:s",$voucher_info['end_time']):'没有限制';
                        $title="获得代金券";
                        $content="恭喜你,获得代金券".$voucher_info['name']."到期时间为:".$voucher_info['end_time'];
                        send_user_msg($title,$content,0,$ecv_data['user_id'],time(),0,true,true);
                    }
                }
                $id_num = count($insert_id);
                $error_num = count($error_id);
                if($id_num){//成功发送红包的数量
                    $GLOBALS['db']->query("update ".DB_PREFIX."ecv_type set gen_count = gen_count + ".$id_num." where id = ".$ecv_id);
                    
                }        
                echo "成功发送了【".$voucher_info['name'] ."】代金券给".$id_num."个用户\n 发送失败有".$error_num."用户：";
                var_dump($error_id);
            }
        }
	
	
}
?>