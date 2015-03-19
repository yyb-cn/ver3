<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class DealAgencyAction extends CommonAction{
	public function index()
	{
		
		//列表过滤器，生成查询Map对象
		$map = $this->_search ();
		if(strim($_REQUEST['name'])!=""){
			$map['name'] =  array("like","%".strim($_REQUEST['name'])."%");
		}
		
		if(strim($_REQUEST['mobile'])!=""){
			$map['mobile'] =  array("eq",strim($_REQUEST['mobile']));
		}
		
		if(strim($_REQUEST['email'])!=""){
			$map['email'] =  array("eq",strim($_REQUEST['email']));
		}
		
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		/*$list = $this->get("list");
		
		$result = array();
		$row = 0;
		foreach($list as $k=>$v)
		{
			$v['level'] = -1;
			$v['name'] = $v['name'];
			$result[$row] = $v;
			$row++;
			$sub_cate = M(MODULE_NAME)->where(array("id"=>array("in",D(MODULE_NAME)->getChildIds($v['id'])),'is_delete'=>0))->findAll();
			$sub_cate = D(MODULE_NAME)->toFormatTree($sub_cate,'name');
			foreach($sub_cate as $kk=>$vv)
			{
				$vv['name']	=	$vv['title_show'];
				$result[$row] = $vv;
				$row++;
			}
		}
		//dump($result);exit;
		$this->assign("list",$result);*/
		
		$this->display ();
		return;
	}
	
	
	public function add()
	{
		$this->display();
	}
	
	public function insert() {
		B('FilterString');
		if(strim($_REQUEST['password'])!=""){
			if(strim($_REQUEST['password'])!=strim($_REQUEST['cfgpassword'])){
				$this->error("确认密码错误");
			}
		}
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("DEALAGENCY_NAME_EMPTY_TIP"));
		}	
		if($data['password']!=""){
			$data['password'] = md5($data['password']);
		}
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
		$this->assign ( 'vo', $vo );		
		
		$this->display ();
	}

	
    public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		save_log($log_info.l("SORT_SUCCESS"),1);
		
		$this->success(l("SORT_SUCCESS"),1);
	}
	
	public function update() {
		B('FilterString');
		if(strim($_REQUEST['password'])!=""){
			if(strim($_REQUEST['password'])!=strim($_REQUEST['cfgpassword'])){
				$this->error("确认密码错误");
			}
		}
		$data = M(MODULE_NAME)->create ();
		if(!check_empty($data['name']))
		{
			$this->error(L("DEALAGENCY_NAME_EMPTY_TIP"));
		}	
		
		if($data['password']!=""){
			$data['password'] = md5($data['password']);
		}
		else{
			unset($data['password']);
		}
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
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
	
	function view_info(){
		$agency_id = intval($_REQUEST['id']);
		$deal_agency = M("DealAgency")->getById($agency_id);
		$old_imgdata_str = unserialize($deal_agency['view_info']);
		$this->assign("deal_agency",$deal_agency);
		$this->assign("old_imgdata_str",$old_imgdata_str);
		$this->display();
	}
	
	function modify_view_info(){
		
		if(intval($_REQUEST['id'])==0){
			$this->error("机构不存在！");
			exit();
		}
		
		$view_down_data = array();
		foreach($_FILES['img_data']['name'] as $k=>$v){
			$file = pathinfo($v);
			
			if($file['error'] == 0){
				if(!file_exists(APP_ROOT_PATH."/public/gview_info"))
					@mkdir(APP_ROOT_PATH."/public/gview_info",0777);
			
				$time = to_date(TIME_UTC,"Ym");
				if(!file_exists(APP_ROOT_PATH."/public/gview_info/".$time))
					@mkdir(APP_ROOT_PATH."/public/gview_info/".$time,0777);
			
				$file_name = md5(TIME_UTC.$_REQUEST['id'].$v.$k).".".$file['extension'];
				
				move_uploaded_file($_FILES['img_data']['tmp_name'][$k],APP_ROOT_PATH."/public/gview_info/".$time."/".$file_name);
				
				if(file_exists(APP_ROOT_PATH."/public/gview_info/".$time."/".$file_name)){
					$view_down_data[$k]['img'] = "./public/gview_info/".$time."/".$file_name;
					$view_down_data[$k]['name'] = strim($_REQUEST['file_name'][$k]);
				}
			
			}
			
		}
		
		$new_view_info_arr= array();
		$old_view_info = M("DealAgency")->where("id=".intval($_REQUEST['id']))->getField("view_info");
		if($old_view_info !=""){
			$old_view_info_arr = unserialize($old_view_info);
			
			foreach($old_view_info_arr as $k=>$v){
				$new_view_info_arr[$k] = $v;
			}
		}
		
		foreach($view_down_data as $k=>$v){
			$new_view_info_arr[] = $v;
		}
	
		
		$data['view_info'] = serialize($new_view_info_arr);
		
	
		if($GLOBALS['db']->autoExecute(DB_PREFIX."deal_agency",$data,"UPDATE","id=".$_REQUEST['id'])){
			$this->success("上传资料成功！");
		}
		else{
			$this->error("上传资料失败！");
		}
	
	}
	
	function view_info_del_img(){
		if(intval($_REQUEST['id'])==0){
			$this->error("机构不存在！");
			exit();
		}
		
		if(strim($_REQUEST['src'])==""){
			$this->error("删除的文件不存在！");
			exit();
		}
		
		$old_view_info = M("DealAgency")->where("id=".intval($_REQUEST['id']))->getField("view_info");
		if($old_view_info !=""){
			$old_view_info_arr = unserialize($old_view_info);
			foreach($old_view_info_arr as $k=>$v){
				if($v['img'] == strim($_REQUEST['src'])){
					@unlink(APP_ROOT_PATH.$v['img']);
					unset($old_view_info_arr[$k]);
				}
			}
		}
		$data['view_info'] = serialize($old_view_info_arr);
		
		if($GLOBALS['db']->autoExecute(DB_PREFIX."deal_agency",$data,"UPDATE","id=".$_REQUEST['id'])){
			$this->success("删除成功！");
		}
		else{
			$this->error("删除失败！");
		}
	}
}
?>