<?php

class AwardAction extends CommonAction{
		public function index()
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
				$award_moduel=D('lottery');
				$award_list=$award_moduel->select();
				foreach($voList as $k=>$v){
				
					foreach($award_list as $kk=>$vv){
						if($vv['uid']==$v['id']){
							$voList[$k]['award_number']=$vv['draw_sec'];
						}
						
					}
				
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
	
/*抽奖记录*/	
	function award_log(){
		$group_list = M("UserGroup")->findAll();
		$this->assign("group_list",$group_list);
		$condition=1;
		if(trim($_REQUEST['user_name'])!='')
		{
			$condition = "and u.user_name like"."'%".trim($_REQUEST['user_name'])."%'";
		}
		if(trim($_REQUEST['huodong_id'])!='')
		{
			$condition = " a.huodong_id =".$_REQUEST['huodong_id'];
		}
		if(trim($_REQUEST['user_name'])!='')
		{
			$condition = " u.user_name like"."'%".trim($_REQUEST['user_name'])."%'";
		}
		if(trim($_REQUEST['group_id']!=''))
			{
				if($_REQUEST['group_id']==0){
				$condition .='';
				}
			else{
			$condition .= "  and   u.group_id ="."'".$_REQUEST['group_id']."'";
			}
		}
		//排序
		if(trim($_REQUEST['_sort'])==0){
			$sort='desc';
		
		}
		elseif(trim($_REQUEST['_sort'])==1){
			$sort='asc';
		
		}
		if(trim($_REQUEST['_order'])=='')
		{
			$order=	"order  by  a.log_time  ".$sort;
		}
		if(trim($_REQUEST['_order'])=='user_name')
		{
			$order=	"order  by  u.user_name  ".$sort;
		}
		if(trim($_REQUEST['_order'])=='group_id')
		{
			$order=	"order  by  u.group_id  ".$sort;
		}
		if(trim($_REQUEST['_order'])=='log_time')
		{
			$order=	"order  by  a.log_time  ".$sort;
		}
		
		$module=m('award_log');		
		import('ORG.Util.Page');// 导入分页类
		$count  = $module->query( "select  count(*) as count from ".DB_PREFIX."award_log a left join ".DB_PREFIX."user as u on a.user_id = u.id   where ".$condition);
		//抽奖 总额
		if($_REQUEST['huodong_id']==2){
		
			$all=m('award_log')->where(array('huodong_id'=>2))->select();
			
		}
		$totle_money=0;
		foreach($all as $k=>$v){
		
			$totle_money+=$v['prize_name'];
		}
		$this->assign('totle_money',$totle_money);
		$count=$count[0]['count'];
		// 查询满足要求的总记录数
		$per_page=$_REQUEST['per_page']?$_REQUEST['per_page']:30;
			
		$Page   = new Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
		$show   = $Page->show();// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		$sql = "select u.user_name,u.group_id,u.real_name,p.name as prize_name,a.*  from ".DB_PREFIX."award_log a left join ".DB_PREFIX."user as u on a.user_id = u.id  left join ".DB_PREFIX."prize as p on p.id = a.prize_id  where ".$condition .' '. $order . ' limit '.$Page->firstRow.','.$Page->listRows ;
		$list = $GLOBALS['db']->getAll($sql);
		$this->assign('list',$list);
		$this->display ();
	}
	
	//抽奖记录的增加
	function award_add(){

		if($_POST){
			
			$name=trim($_POST['user_id']);
			$one=D(user)->where(array('user_name'=>$name))->find();
			if($id=$one['id'])
			{
				$data['user_id']=$id;
			}
			else{
				$this->error('用户名不存在');
			}
			$data['log_time']=get_gmtime();
			$data['prize_id']=$_POST['prize_id'];
			$data['huodong_id']=$_POST['huodong_id'];	
			$one=D(award_log)->add($data);
			if($one){
				$this->success(L("success"));
			}
		}
	$this->assign('one',$one);
	$this->display ('award_add');
	}
	//删除中奖记录
	function virtual_del(){
	  // print_r($_GET['id']);exit;
	$id=$_GET['id'];
	if($id){
	   
	  $award=M("AwardLog");
	  // print_r($award);exit;
      $award->delete($id); 
	  $this->success(L("success"));
	  
	// echo 1;exit;
	
	}
}
	//这个是添加一次抽奖机会
	function send(){
		$user_id=$_REQUEST['id'];
		
		$gg=D(lottery)->where(array('uid'=>$user_id))->find();
		if($gg){
		$one=D(lottery)->where(array('uid'=>$user_id))->setInc('draw_sec');
		}
		else{
		$one=D(lottery)->add(array('uid'=>$user_id,'draw_sec'=>1));
		}
		if($one)
		$this->success(L("增加一次抽奖机会"));
	}
	//这个是奖品的目录
	function prize(){
		$hd_id=intval($_GET['hd_id']);
		if($hd_id)$data=array('huodong_id'=>$hd_id);
		$list=D(prize)->where($data)->select();
		foreach($list as $k=>$v){
			$hd=D(huodong)->where(array('id'=>$v['huodong_id']))->find();
			$list[$k]['huodong_name']=$hd['name'];
		}
		$huodong_list=D(huodong)->select();
		$this->assign('huodong_list',$huodong_list);
		$this->assign('list',$list);
		$this->display ();
	}
	
	function prize_add(){
	$huodong=D(huodong)->select();
	//var_dump($huodong);exit;
	$this->assign('huodong',$huodong);
			if($id=$_GET['id']){
		$one=D(prize)->where(array('id'=>$id))->find();
			}
		if($_POST){
			$data['name']=$_POST['name'];
			$data['probability']=$_POST['probability'];
			$data['max']=intval($_POST['max']);
			$data['huodong_id']=intval($_POST['huodong_id']);
		if($_POST['id']){
		
			$one=D(prize)->where(array('id'=>$_POST['id']))->save($data);
		}
		else{	
			$one=D(prize)->add($data);
			}
			if($one)
			$this->success(L("success"));
	}
	$this->assign('one',$one);
	$this->display ('prize_add');
		
	}
	
	function prize_del(){
		$id=intval($_GET['id']);
		if(D(prize)->where(array(id=>$id))->delete())
		$this->success(L("success"));
	}
	
	
	
	
	function huodong(){
		$list=D(huodong)->select();
		//var_dump($list);exit;
		$this->assign('list',$list);
		$this->display ();
	}
	function huodong_add(){
	if($id=$_GET['id']){
	$one=D(huodong)->where(array('id'=>$id))->find();
	}
	if($_POST){
			$data['endtime']=strtotime($_POST['end_time']);
			$data['name']=$_POST['name'];
			$data['content']=$_POST['content'];
		if($_POST['id']){
			$one=D(huodong)->where(array('id'=>$_POST['id']))->save($data);
		}
		else{	
			$one=D(huodong)->add($data);
			}
			if($one)
			$this->success(L("success"));
	}
	$this->assign('one',$one);
	$this->display ('huodong_add');
	}
	function huodong_del(){
		$id=intval($_GET['id']);
		if(D(huodong)->where(array(id=>$id))->delete())
		$this->success(L("success"));
	}
	
}
?>