<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class CreateRelevanceAction extends CommonAction{
	public function index()
	{	
		$user_name  = trim($_REQUEST['user_name']);
		
		
		if (isset ( $_REQUEST ['_order'] )) {
			$sorder = $_REQUEST ['_order'];
		}
		else{
			$sorder = "id";
		}
		switch($sorder){
			case "user_name":
				$order =$sorder;
				break;
			case "email ":
				$order ="email";
				break;
			case "status":
				$order ="pid";
				break;
			case "humans":
				$order ="pid";
				break;
			case "referer_memo":
				$order ="referer_memo";
				break;
			default :
				$order =$sorder;
				break;
		}
		
		
		
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		}
		else{
			$sort = "ASC";
		}
		
		
		
		
		$condition = "1=1";
		
		if($user_name!=="" && $user_name!=0)
		$condition.=" and user_name like '%".$user_name."%' ";
		
		$sql_count = "SELECT count(*) FROM ".DB_PREFIX."user u  WHERE  $condition ";
		$count = $GLOBALS['db']->getOne($sql_count);
	
		if (! empty ( $_REQUEST ['listRows'] )) {
			$listRows = $_REQUEST ['listRows'];
		} else {
			$listRows = '';
		}
		
		$p = new Page ( $count, $listRows );
		if($count>0){
			$sql = "SELECT u.id,u.user_name,u.email,u.pid,referer_memo FROM ".DB_PREFIX."user u WHERE  $condition  ORDER BY $order $sort LIMIT ".($p->firstRow . ',' . $p->listRows);
		
			$list = $GLOBALS['db']->getAll($sql);
			foreach($list as $k=>$v){
				if($list[$k]['pid'] == 0){
					$list[$k]['status']="未关联";
				}else{
					$list[$k]['status']="已关联";
				}
				$list[$k]['humans'] = get_user_name($list[$k]['pid']);  
			}
			$this->assign("list",$list);
		}
	
		$page = $p->show();
		
		$sortImg = $sort; //排序图标
		$sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
		$sort = $sort == 'desc' ? 1 : 0; //排序方式
		
		$this->assign ( 'sort', $sort );
		$this->assign ( "page", $page );
		$this->display();
	}
	
	
	
	public function edit()
	{
		$user_id = intval($_REQUEST['id']);
		$sql = "SELECT id,user_name,pid,referer_memo FROM ".DB_PREFIX."user WHERE id= ".$user_id ;
		$list = $GLOBALS['db']->getRow($sql);
		$rel_user_name = $GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id =".$list['pid']);
		$list['rel_user_name'] = $rel_user_name;
		$this->assign("list",$list);
		$this->display();
	}
	

	public function update()
	{	
		$id=intval($_REQUEST['id']);
		$referer_memo = strim($_REQUEST['referer_memo']);
		$rel_user_name = strim($_REQUEST['rel_user_name']);
		$pid = M("User")->where("user_name='".$rel_user_name."'")->getField("id");
		
		if($rel_user_name=="")
		{
			$this->error(L("推荐人不能为空"));
		}
		if($id == $pid)
		{
			$this->error(L("推荐人不能是自己"));
		}
		if(isset($pid))
		{
			$data = array();
			$data['pid'] = $pid;
			$data['referer_memo'] = $referer_memo;
			
			$list = M('User')->where('id='.$id)->save($data);
			
			//成功提示
			save_log($id.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		}else{
			$this->error(L("RELEVANCE_USER_NO"));
		}
	}
	
	//取消关联
	public function del_referrals()
	{
		$id=intval($_REQUEST['id']);
		$pid = M("User")->where("id=".$id)->getField("pid");
		if($pid != "" && $pid !=0)
		{
			$data = array();
			$data['pid'] = "";
			$data['referer_memo'] = "";
			$list = M('User')->where('id='.$id)->save($data);
			save_log($id.L("UPDATE_SUCCESS"),1);
			$this->success(L("UPDATE_SUCCESS"));
		}else{
			$this->error(L("无关联推广人"));
		}
		
	}
		
}
?>