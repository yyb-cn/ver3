<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class IncreaseAction extends CommonAction
{
public function index()
	{
		$conf_res = M("Ease")->where("is_effect = 1 and is_conf = 1")->order("group_id asc,sort asc")->findAll();
		foreach($conf_res as $k=>$v)
		{
			$v['value'] = htmlspecialchars($v['value']);
			if($v['name']=='TEMPLATE')
			{
				
				//输出现有模板文件夹
				$directory = APP_ROOT_PATH."app/Tpl/";
				$dir = @opendir($directory);
			    $tmpls     = array();
			
			    while (false !== ($file = @readdir($dir)))
			    {
			    	if($file!='.'&&$file!='..')
			        $tmpls[] = $file;
			    }
			    @closedir($dir);
				//end
				
				$v['input_type'] = 1;
				$v['value_scope'] = $tmpls;
			}
			elseif($v['name']=='SHOP_LANG')
			{
				//输出现有语言包文件夹
				$directory = APP_ROOT_PATH."app/Lang/";
				$dir = @opendir($directory);
			    $tmpls     = array();
			
			    while (false !== ($file = @readdir($dir)))
			    {
			    	if($file!='.'&&$file!='..')
			        $tmpls[] = $file;
			    }
			    @closedir($dir);
				//end
				
				$v['input_type'] = 1;
				$v['value_scope'] = $tmpls;
			}
			else
			$v['value_scope'] = explode(",",$v['value_scope']);
			$conf[$v['group_id']][] = $v;
		}
		$this->assign("conf",$conf);
		
		$this->display();
	}
	
	
		//加息开启关闭编辑修改;
		public function updata(){
			$id=$_POST['id'];
			$data['value']=$_POST['value'];
		 
	
			
		$one = M("Ease")->where("id=$id")->save($data);
		

	if($one==1){
		
	
		//操作成功跳转;
		$this->success(L("UPDATE_SUCCESS"));
		
		
		
		}
	
	$this->error(L("UPDATE_FAILED"));
	
			}
	
	
	
	
		//加息编辑修改;
		public function update(){
			
			if($_POST){
			$id=$_POST['id'];
		    $data['create_time']=strtotime($_POST['create_time']);
			$data['create_source']=$_POST['create_source'];
			$data['yields']=$_POST['yields'];
			$data['is_used']=$_POST['is_used'];
			$data['user_name']=$_POST['user_name'];
			$data['used_time']=strtotime($_POST['used_time']);
			$data['target_name']=$_POST['target_name'];
			$data['adm_name']=$_POST['adm_name'];
			$data['expires_time']=strtotime($_POST['expires_time']);
			}
	
			
		$one = M("User_increase")->where("id='$id'")->save($data);
		
	
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
			
				$list = M("User_increase")->where ( $id )->delete ( );
				
				if($list){
					save_log($info.l("DELETE_SUCCESS"),1);
					$this->success (l("DELETE_SUCCESS"),$ajax);
				}
		}		
	}
	
	

		//加息详细列表
		public function details(){
			
		
		$group_list = M("User_increase")->findAll();
		foreach ($group_list as $key => $val ) {
			
			
			
			}
			//加息劵生成来源的操作
			$user_id=$val['create_source'];
            $this->assign("create_source",$create_source);
			
			//加息劵收益率的操作
			//$user_id=$val['yields'];
          //  $this->assign("yields",$yields);
		  
			//拥有加息劵会员的操作
			$user_id=$val['user_id'];
            $this->assign("user_id",$user_id);
			
		    //加息劵是否使用的操作
			$user_id=$val['is_used'];
            $this->assign("is_used",$is_used);
			
			  //加息劵使用到标的操作
			$target_id=$val['target_id'];
            $this->assign("target_id",$target_id);
			
			 //加息劵添加的管理员操作
			$admin_id=$val['admin_id'];
            $this->assign("admin_id",$admin_id);
			
		//查询加息表
		
		
$model = D ('User_increase');
		//传递到common的_list方法中；
		
		
			if($_POST){
				
				//使用了加息劵的搜索
				if($_POST['is_used']==1){
					$map="is_used=1";
					if (! empty ( $model )) {
			       $this->_list ( $model, $map );
		}
				$is_used=$_POST['is_used'];
				 $this->assign("is_used",$is_used);
				}
				
						//没使用加息劵的搜索
				if($_POST['is_used']==2){
					$map="is_used=0";
					if (! empty ( $model )) {
			       $this->_list ( $model, $map );
		}
					$is_used=$_POST['is_used'];
				 $this->assign("is_used",$is_used);
				}
				//当2个时间都没有值时在进行查询；
				if($_POST['begin_time']||$_POST['end_time']){
					
				
							//按两个时间来进行搜索
				if($_POST['begin_time']&&$_POST['end_time']){
					//前
					
			       		$begin_time=strtotime($_POST['begin_time']);
					//后	
						$end_time=strtotime($_POST['end_time']);
					
				    $map="create_time>".$begin_time  .' '.'and'.' '. "create_time<".$end_time;
				
					if (! empty ( $model )) {
			       $this->_list ( $model, $map );
		}
		
	
				}
							//按前面时间来进行搜索
				if($_POST['begin_time']){
					//前
			       		$begin_time=strtotime($_POST['begin_time']);
				
					
				    $map="create_time>".$begin_time;
					
					if (! empty ( $model )) {
			       $this->_list ( $model, $map );
		}
		
	
				}
					//按后面时间来进行搜索
				if($_POST['end_time']){
					
					//后	
						$end_time=strtotime($_POST['end_time']);
					
				    $map="create_time<".$end_time;
					
					if (! empty ( $model )) {
			       $this->_list ( $model, $map );
		}
		
	
				}
				
				
				
				}else{
					
					
							//所有代金劵的搜索
				if($_POST['is_used']==0){
				    $map='';
					if (! empty ( $model )) {
			       $this->_list ( $model, $map );
		}            //传值进行判断选中状态
					$is_used=$_POST['is_used'];
				 $this->assign("is_used",$is_used);
				}
					
					
					}
				
				
				
				
						//会员名称的搜索
				if($_POST['user_name']){
					$date=$_POST['user_name'];
					//查询会员名称的ID"id=".$user_id."  
					 
					 $dest_array=M("User")->where()->select();
					 foreach($dest_array as $k=>$v){
						 
						
					
						 if($v['user_name']=$date){
							 
							 $map="user_id=".$v['id'];
							 
							
							 
							 	if (! empty ( $model )) {
			       $this->_list ( $model, $map );
		}
							 
							 }
						 
						 
						 }
				
				   
					
					
				
		
		
					
				}
			
				
				
				
				
				
				
				
				}else{
			
		if (! empty ( $model )) {
				$map="";
			$this->_list ( $model, $map );
		}
		}
		$this->display ();
		
         }
		 
		 
	
	

	//加息详细列表
		public function edit(){
			$id = intval($_REQUEST ['id']);
			//print_r($id);exit;
			 $date = M("User_increase")->where("id=".$id)->find();
			 $expires_time=date('Y-m-d H:i:s',$date['expires_time']);
		     $create_time=date('Y-m-d H:i:s',$date['create_time']); 
			 $create_source=$date['create_source'];
			 $yields=$date['yields'];
			 $is_used=$date['is_used'];
			 $user_id=$date['user_id'];
			 $user_name = M("User")->where("id=".$user_id)->getField("user_name");
			 $used_time=date('Y-m-d H:i:s',$date['used_time']);
			 $target_id=$date['target_id'];
			 $target_name = M("Deal")->where("id=".$target_id)->getField("name");
			 $admin_id=$date['admin_id'];
			 $adm_name = M("Admin")->where("id=".$admin_id)->getField("adm_name");
			 $admin_id=$date['expires_time'];
			 
			 
			$this->assign("id",$id);
			$this->assign("expires_time",$expires_time);
			$this->assign("adm_name",$adm_name); 
			$this->assign("target_name",$target_name); 
			$this->assign("used_time",$used_time);
			$this->assign("user_name",$user_name);
			$this->assign("is_used",$is_used);
			$this->assign("yields",$yields);
			$this->assign("create_source",$create_source); 
			$this->assign("create_time",$create_time);
	        $this->assign("date",$date);
			
			$this->display ();
			
	
			}
			
			//导出文件操作;	
	public function export_csv($page = 1)
	{   

	
	 //设置程序执行时间的函数  
		set_time_limit(0);
		//查询的条数
	$limit = (($page - 1)*intval(app_conf("BATCH_PAGE_SIZE"))).",".(intval(app_conf("BATCH_PAGE_SIZE")));
	if($_REQUEST['is_used']==0){
		
		$list = M("user_increase")->where()->limit($limit)->findAll();
		
		}else{
		
      $map = $_REQUEST['is_used'];
	  
		$list = M("user_increase")->where("is_used=".$map)->limit($limit)->findAll();
			}	
				
	if($list)
		{
			register_shutdown_function(array(&$this, 'export_csv'), $page+1);
			
					$user_value = array('id'=>'""','create_time'=>'""','create_source'=>'""','yields'=>'""','is_used'=>'""','user_id'=>'""','used_time'=>'""','target_id'=>'""','admin_id'=>'""','expires_time'=>'""');
			
			if($page == 1)
	    	$content = iconv("utf-8","gbk","编号,生成时间,生成来源,收益率,是否使用,拥有加息劵会员,加息劵使用时间,使用到标的名称,添加加息的管理员,加息劵到期时间");
	    	
	    	
	    	
			
	   
	      
	    	if($page==1) 	
	    	$content = $content . "\n";
	    
	    	foreach($list as $k=>$v)
			{	
				$user_value = array();
				$user_value['id'] = iconv('utf-8','gbk','"' . $v['id'] . '"');
				$user_value['create_time'] = iconv('utf-8','gbk','"' . $v['create_time'] . '"');
				$user_value['create_source'] = iconv('utf-8','gbk','"' . $v['create_source'] . '"');
				$user_value['yields'] = iconv('utf-8','gbk','"' . $v['yields'] . '"');
				$user_value['is_used'] = iconv('utf-8','gbk','"' . $v['is_used'] . '"');
				$user_value['user_id'] = iconv('utf-8','gbk','"' . $v['user_id'] . '"');
				$user_value['used_time'] = iconv('utf-8','gbk','"' . $v['used_time'] . '"');
				$user_value['target_id'] = iconv('utf-8','gbk','"' . $v['target_id'] . '"');
				$user_value['admin_id'] = iconv('utf-8','gbk','"' . $v['admin_id'] . '"');
				$user_value['expires_time'] = iconv('utf-8','gbk','"' . $v['expires_time'] . '"');
				
				
                   //开始获取扩展字段(拥有加息劵的会员)
	    	         $extend_fields =M("User")->where("id=".$user_value['user_id'])->findAll();
					 	foreach($extend_fields as $k=>$vv){	
						
						
						
						}
						//开始获取扩展字段(使用到的标)
				$extend_fieldsval = M("Deal")->where("id=".$user_value['target_id'])->findAll();
			             foreach($extend_fieldsval as $k=>$vvv){	
						
						
						
						           }
							//开始获取扩展字段(管理员添加的管理员名称)
				$extend_admin = M("Admin")->where("id=".$user_value['admin_id'])->findAll();
			             foreach($extend_admin as $k=>$vvvv){	
						
						
						
						           }
						
					
					 if($user_value['is_used']==1){
						 
						 $user_value['is_used']="使用了";
						 
						 }else{
							 
							 $user_value['is_used']="没使用";
							 
							 }
							 //重新赋值;
					 $user_value['used_time']=$us;
					 $user_value['expires_time'] =$ex;
					 $user_value['create_time']=$cr;
					 $user_value['used_time'] =date('Y-m-d H:i:s',$us);
					 $user_value['expires_time'] =date('Y-m-d H:i:s',$ex);
				     $user_value['create_time'] =date('Y-m-d H:i:s',$cr);
					 $user_value['admin_id'] = iconv('utf-8','gbk','"' . $vvvv['adm_name'] . '"');
					 $user_value['target_id'] = iconv('utf-8','gbk','"' . $vvv['name'] . '"');	
					 $user_value['is_used'] = iconv('utf-8','gbk','"' . $user_value['is_used'] . '"');	 
					 $user_value['user_id'] = iconv('utf-8','gbk','"' . $vv['user_name'] . '"');
		
				$content .= implode(",", $user_value) . "\n";
			}	
	          
			
			header("Content-Disposition: attachment; filename=user_increase.csv");
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
