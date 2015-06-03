<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class DirectoryAction extends CommonAction{
	public function Directory_index()
	{
	
		$list1=M('Role_nav')->order('sort asc')->select();
		$list2=M('Role_group')->order('nav_id asc,sort ')->select();
		$list3=M('Role_module')->select();
		$list4=M('Role_node')->order('group_id')->select();
		
		foreach($list1 as $k=>$v){
			
			$group=M('Role_group')->where('nav_id='.$v['id'])->select();
		//echo	M('Role_group')->getlastsql();
			$arr[$v['name']]=$group;
			foreach($arr[$v['name']] as $kk=>$vv){
			
			$list=M('Role_node')->where('group_id ='.$vv['id'])->select();
			array_unshift($list,$arr[$v['name']][$kk]['name']);
			$arr[$v['name']][$kk]['name']=$list;
			
			}
		
		
		}
	
		//print_r($arr);
		
		
		
	
	
		echo '<p style="background:#aaFa00">首页目录role_nav表</p>';
		echo '<a href="?m=Directory&a=add&type=Role_nav">新增</a>';
		echo '<hr/>';
		$list=M('Role_nav')->order('sort asc')->select();
		echo '<table border="1">';
		echo '<tr><th>ID</th><th>名</th><th>排序</th></tr>';
		foreach ($list as $k=>$v){
		echo "<tr><td>".$v['id']."</td><td>".$v['name']."</td><td>".$v['sort']."</td></tr>";
		
		}
		echo '</table>';
			
		echo '<p style="background:#ddFa00">组别role_group表</p>';
		echo '<a href="?m=Directory&a=add&type=Role_group">新增</a>';
		echo '<hr/>';
		$list=M('Role_group')->order('nav_id asc,sort ')->select();
		echo '<table border="1">';
		echo '<tr><th>ID</th><th>名</th><th>排序</th><th>nav_id</th></tr>';
		foreach ($list as $k=>$v){
		echo "<tr><td>".$v['id']."</td><td>".$v['name']."</td><td>".$v['sort']."</td><td>".$v['nav_id']."</td></tr>";
		
		}
		echo '</table>';
		
		echo '<p style="background:#dddd00">类名Role_module</p>';
		echo '<a href="?m=Directory&a=add&type=Role_module">新增</a>';
		echo '<hr/>';
		$list=M('Role_module')->select();
		echo '<table border="1">';
		echo '<tr><th>ID</th><th>module</th><th>名</th></tr>';
		foreach ($list as $k=>$v){
		echo "<tr><td>".$v['id']."</td><td>".$v['module']."</td><td>".$v['name']."</td></tr>";
		
		}
		
		echo '</table>';
		
		echo '<p style="background:#ddffff">动作Role_node</p>';
		echo '<a href="?m=Directory&a=add&type=Role_node">新增</a>';
		echo '<hr/>';
		$list=M('Role_node')->order('group_id')->select();
		echo '<table border="1">';
		echo '<tr><th>ID</th><th>名</th><th>action_name</th><th>group_id</th><th>module_id</th></tr>';
		foreach ($list as $k=>$v){
		echo "<tr><td>".$v['id']."</td><td>".$v['name']."</td><td>".$v['action']."</td><td>".$v['group_id']."</td><td>".$v['module_id']."</td></tr>";
		
		}
		echo '</table>';
		
	}
	public function add()
	{
		$type=$_GET['type'];
		$this->assign('type',$type);
		$this->display();
		
	
	}
	public function doadd()
	{
		$type=trim($_POST['type']);
		$module=M($type);
		$data['is_delete']=0;	
		$data['is_effect']=1;
		if($type=='Role_nav'){
		$data['name']=trim($_POST['name']);
		$data['sort']=trim($_POST['sort']);
		$module->add($data);
		}
		if($type=='Role_group'){
		$data['name']=trim($_POST['name']);
		$data['nav_id']=trim($_POST['nav_id']);
		$data['sort']=trim($_POST['sort']);
		$module->add($data);
		}
		if($type=='Role_module'){
		$data['name']=trim($_POST['name']);
		$data['module']=trim($_POST['module']);
		$module->add($data);
		}
		if($type=='Role_node'){
		$data['name']=trim($_POST['name']);
		$data['group_id']=trim($_POST['group_id']);
		$data['module_id']=trim($_POST['module_id']);
		$data['action']=trim($_POST['action']);
		$module->add($data);
		}
	$this->success('插入成功');
	
	}
}
?>