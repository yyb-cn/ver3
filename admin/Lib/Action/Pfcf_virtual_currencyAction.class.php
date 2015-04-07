<?php




class Pfcf_virtual_currencyAction extends CommonAction{

 public function  index()
   {
		if(trim($_REQUEST['user_name'])!='')
		{
			$map['user_name'] = array('like','%'.trim($_REQUEST['user_name']).'%');		
		}
  	$model=M("PfcfVirtualCurrency");
	if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$this->_list($model,$map);	
		$this->display ();
		return;
	
	}
	
  public function  add()
   {  
      	$this->display ();
		return;
   }
  public function  on_add()
   {  
    $PfcfVirtualCurrency=M("PfcfVirtualCurrency");
	$user_name=$_REQUEST['user_name'];
	$User=M('User');
	$select_user=$User->where("user_name='$user_name'")->find();
  if($select_user){
	$PfcfVirtualCurrency->create();
	$PfcfVirtualCurrency->user_id=$select_user['id'];
	$PfcfVirtualCurrency->create_time=get_gmtime();
    $PfcfVirtualCurrency->add();
	// echo $PfcfVirtualCurrency->getLastSql() ;exit;
	$this->success("添加成功");
	}else{
	$this->error("用户名不存在");
	}
   }	

 public function  del()
   {
	  if($_REQUEST['id']){
	  $id=$_REQUEST['id'];
      $model=M("PfcfVirtualCurrency");
	  $model->delete($id);
	  $this->success("删除成功");
      } 
	}
	
	
	
	
	
}
?>