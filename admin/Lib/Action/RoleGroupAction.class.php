<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class RoleGroupAction extends CommonAction{
	public function index()
	{
		$condition['is_delete'] = 0;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function add()
	{
		
		 $nav_list = M("Role_nav")->where("is_effect=1")->findAll();
		
		$this->assign("nav_list",$nav_list);
		parent::add();
	}
	public function del()
	{
		 if($_REQUEST['id']){
	  $id=$_REQUEST['id'];
      $model=M("PfcfVirtualCurrency");
	  $model->delete($id);
	  $this->success("删除成功");
      } 
		echo 123;exit;
	}
	public function insert()
	{
		$data['name']=$_REQUEST['name'];
		$data['nav_id']=$_REQUEST['nav_id'];
        $data['is_delete ']=0;
		$data['is_effect']=$_REQUEST['is_effect'];
		$data['sort']=$_REQUEST['sort'];
		
		$role_id=M('role_group')->add($data);
		
		if($role_id){
			
			
		    save_log($log_info.L("INSERT_SUCCESS"),1);
			$this->success(L("INSERT_SUCCESS"));
			
		}else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
		
	
	}
	
}
?>