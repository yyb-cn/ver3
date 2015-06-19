<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class ArticleAction extends CommonAction{
	public function index()
	{
		if(trim($_REQUEST['title'])!='')
		{
			$condition['title'] = array('like','%'.trim($_REQUEST['title']).'%');			
		}
		$condition['is_delete'] = 0;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function trash()
	{
		$condition['is_delete'] = 1;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function add()
	{
		$cate_tree = M("ArticleCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("ArticleCate")->toFormatTree($cate_tree);
		$this->assign("cate_tree",$cate_tree);
		$this->assign("new_sort", M("Article")->where("is_delete=0")->max("sort")+1);
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		$cate_tree = M("ArticleCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("ArticleCate")->toFormatTree($cate_tree);
		$this->assign("cate_tree",$cate_tree);
		$this->display ();
	}
	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['title'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 1 );
				if ($list!==false) {
					save_log($info.l("DELETE_SUCCESS"),1);
					clear_auto_cache("get_help_cache");
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}		
	}
	
	public function restore() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['title'];						
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
				if ($list!==false) {
					save_log($info.l("RESTORE_SUCCESS"),1);
					clear_auto_cache("get_help_cache");
					$this->success (l("RESTORE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("RESTORE_FAILED"),0);
					$this->error (l("RESTORE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
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
					$info[] = $data['title'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
				//删除相关预览图
//				foreach($rel_data as $data)
//				{
//					@unlink(get_real_path().$data['preview']);
//				}			
				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					clear_auto_cache("get_help_cache");
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['title']))
		{
			$this->error(L("ARTICLE_TITLE_EMPTY_TIP"));
		}	
		if(!check_empty($data['content'])&&$data['rel_url']=='')
		{
			$this->error(L("ARTICLE_CONTENT_EMPTY_TIP"));
		}			
		if($data['cate_id']==0)
		{
			$this->error(L("ARTICLE_CATE_EMPTY_TIP"));
		}
		// 更新数据
		$log_info = $data['title'];
		$data['create_time'] = TIME_UTC;
		$data['update_time'] = TIME_UTC;
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			clear_auto_cache("get_help_cache");
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
//		if($_FILES['preview']['name']!='')
//		{
//			$result = $this->uploadImage();
//			if($result['status']==0)
//			{
//				$this->error($result['info'],$ajax);
//			}
//			//删除图片
//			@unlink(get_real_path().M("Article")->where("id=".$data['id'])->getField("preview"));
//			$data['preview'] = $result['data'][0]['bigrecpath'].$result['data'][0]['savename'];
//		}
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("title");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['title']))
		{
			$this->error(L("ARTICLE_TITLE_EMPTY_TIP"));
		}	
		if(!check_empty($data['content'])&&$data['rel_url']=='')
		{
			$this->error(L("ARTICLE_CONTENT_EMPTY_TIP"));
		}			
		if($data['cate_id']==0)
		{
			$this->error(L("ARTICLE_CATE_EMPTY_TIP"));
		}
		// 更新数据
		$data['update_time'] = TIME_UTC;
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			clear_auto_cache("get_help_cache");
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M("Article")->where("id=".$id)->getField("title");
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M("Article")->where("id=".$id)->setField("sort",$sort);
		save_log($log_info.l("SORT_SUCCESS"),1);
		clear_auto_cache("get_help_cache");
		$this->success(l("SORT_SUCCESS"),1);
	}
	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("title");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);	
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		clear_auto_cache("get_help_cache");
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;	
	}
	//首页幻灯片管理
	//图片列表
    public function img_list()
	{
	 $img_list_nav=M("ImgListNav");
	// var_dump($img_list_nav);exit;
	  $img_data=$img_list_nav->select();
	  // var_dump($img_data);exit;
		foreach($img_data as $k=>$v){
		$img_data[$k]['url']=$v['url'].'?rand='.rand(1111,9999);
		}
		
	  $this->assign("img_data",$img_data);
	  $this->display();
	}
	
	
	//图片上传
		public function img_add()
	{
	
		 $img_list_nav=M("ImgListNav");
		 $c=$img_list_nav->select();  
		 $date=date("Y-m-d",time());
       // $key = array_search(max($c),$c); 
	   // echo $date;exit;
		//查询有多少图片
		//图片上传
		if($_FILES['file']['name']!=''){
				$File = $this -> uploadfile($ARG=array(
				'File'     => array('name'=>$_FILES['file']['name'],'tmp_name' => $_FILES['file']['tmp_name']),
				'Dir'=>'app/Tpl/blue/images/',
				'newname'=> 'top_'.$date.($_FILES['file']['name'])
				));
				$img=$File['uploadfile'];//路径
				$name=$File['newname'];//图片名
			}
		
			if($_POST){
			
			$data['name']=$name;
			$data['url']=$img;
			$data['nav_url']=$_POST['nav_url'];
			$data['target']=$_POST['target'];
			
			$a=$img_list_nav->add($data);
		 if($a){
			header("location:?m=Article&a=img_list");
		 }
		 else{
		 die('上传失败');
		 }
			}
		
		$this->display();
	 
   }
 
 //图片修改
   public function img_edit()
	{
	if($_GET['id']){
		$img_list_nav=M("ImgListNav");
		$one=$img_list_nav->find($_GET['id']);
		$tmp = explode('.',$one['name']);
		$name = $tmp[0];
		$this->assign("img",$one);
		$this->display();
	}
	if($_POST){
			$img_list_nav=M("ImgListNav");
			$id=$_POST['id'];
			$one=$img_list_nav->find($_POST['id']);
			$tmp = explode('.',$one['name']);
			$name = $tmp[0];
			rename($one['url'],	'app/Tpl/blue/images/'.$_POST['name']);
			$data['url']='app/Tpl/blue/images/'.$_POST['name'];
			$data['nav_url']=$_POST['nav_url'];
			$data['target']=$_POST['target'];
			$data['name']=$_POST['name'];
			if($_FILES['file']['name']!=''){
				$File = $this -> uploadfile($ARG=array(
				'File'     => array('name'=>$_FILES['file']['name'],'tmp_name' => $_FILES['file']['tmp_name']),
				'Dir'=>'app/Tpl/blue/images/',
				'newname'=> $name
				));
				$img=$File['uploadfile'];//路径
				$name=$File['newname'];//图片名
				
			}
			$a=$img_list_nav->where(array('id'=>$id))->save($data);
		
			header("location:?m=Article&a=img_list");
		
		
			}
	
   }
   //删除图片
	public function img_del()
	{
     if(!empty($_GET['id'])){
      $img_list=M("ImgListNav");
	  $img_list->delete($_GET['id']);
     }
	 	$this->success(l("操作成功"));
    }
	
	public  function uploadfile($ARG=array(
	'File'  => array(),
	'Dir'   => '',
	'newname'=> '')){
		//默认目录
		$dir = "upload/";
		//文件原始名称
		$oldname = $ARG['File']['name'];
		//文件类型
		$tmp = explode('.',$oldname);
		$filetype = $tmp[1];
		//重命名(文件新名称)
		$newname = $ARG['newname'].".".$filetype;
		//上传目录处理
		if(!isset($ARG['Dir']) || $ARG['Dir']==''){
			$dir .= '';
		}else{
			$dir = $ARG['Dir'];
		}
		if(file_exists($dir)){
			$uploaddir = $dir;
		}
		
		//上传
		$uploadfile = $uploaddir.$newname;
		//echo $ARG['File']['tmp_name'];
		if(is_uploaded_file($ARG['File']['tmp_name'])){
			if(move_uploaded_file($ARG['File']['tmp_name'],$uploadfile)){
			
			}
			else{
			
			}
		}
		//返回数据，便于操作。
		$File  = array();
		$File['oldname']    =  $oldname;
		$File['newname']  =  $newname;
		$File['uploadfile']  =  $uploadfile;
		return $File;
	}
	
	public  function zhixing(){
	// echo "关闭";exit;
	set_time_limit(0);
	$user_money=M("UserMoneyLog");
	$a=0;
    $op=M("UserLog")->findAll();
   foreach($op as $k=>$v){
     $a++;
     $money_log_info['create_time']=$v['log_time'];
	 $money_log_info['create_time_ymd']=date("Y-m-d",$v['log_time']);
     $money_log_info['money']=$v['money'];
     $money_log_info['memo'] = $v['log_info'];
     $money_log_info['user_id'] = $v['user_id'];
     $money_log_info['pfcfb'] = $v['pfcfb'];
	 $money_log_info['lock_money'] =$v['lock_money'];
     $money_log_info['account_money'] =0;
     $money_log_info['unjh_pfcfb'] = $v['unjh_pfcfb'];
     $money_log_info['type'] = 27;

    if(!$user_money->add($money_log_info))
	 {
         echo "第".$a."行，失败";exit;
	 }
    }
   echo "执行了".$a."行成功";exit;
	}
	public  function yue(){
		set_time_limit(0);
	// DELETE FROM Person WHERE LastName = 'Wilson' 
    $GLOBALS['db']->query("UPDATE fanwe_user_money_log SET account_money =0 ");
	// echo "NICE";exit;	
	
	$user_money=M("UserMoneyLog");
	$user_log=M("UserLog")->findAll();
	foreach($user_log as $kw=>$vaaa){
	$nm_money=0;
	 $name_money=$user_money->where("user_id=".$vaaa['id'])->findAll();
	  foreach($name_money as $kwv=>$vaaav){  
	    $nm_money=$nm_money+$vaaav['money'];
	    $user_money->id=$vaaav['id'];
		$user_money->account_money=$nm_money;
		$user_money->save();
	  }
	}
	echo "OK";
	exit;	
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
}
?>