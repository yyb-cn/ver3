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
	public function edit()
	{
		$id=$_REQUEST['id'];
		$vo = M("Role_group")->where("id=".$id)->find();
		$this->assign("vo",$vo);
		 $nav_list = M("Role_nav")->where("is_effect=1")->findAll();
		
		$this->assign("nav_list",$nav_list);
	
		parent::edit();
	}
	

		public function update(){
		$id = intval($_REQUEST ['id']);
		
		$data['name']=$_REQUEST['name'];
		$data['nav_id']=$_REQUEST['nav_id'];
        $data['is_delete ']=0;
		$data['is_effect']=$_REQUEST['is_effect'];
		$data['sort']=$_REQUEST['sort'];
	
			
		$one = M("Role_group")->where("id='$id'")->save($data);
		
	
	if($one==1){
		
	
		//操作成功跳转;
		$this->success(L("UPDATE_SUCCESS"));
		
		
		
		}else{
	
	$this->error(L("UPDATE_FAILED"));
	}
			}
		public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M("Role_group")->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['user_name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M("Role_group")->where ( $condition )->setField ( 'is_delete', 1 );
				if ($list!==false) {
					//把信息屏蔽
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