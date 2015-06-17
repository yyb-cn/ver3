<?php

class Deal_listAction extends CommonAction{
	public function index()
	{
		// $detect=new Mobile_Detect(); 
		// $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
		//echo 123;exit;
		$condition=' d.deal_status in(1,2,4,5) ';
		$group_list = M("UserGroup")->findAll();
		$this->assign("group_list",$group_list);
		if($_REQUEST['start_time']!=''){
			$_REQUEST['start_time']=strtotime($_REQUEST['start_time']);
			$condition .= " and  dl.create_time  >"."'".$_REQUEST['start_time']."'";
		}
		if(trim($_REQUEST['pid_name'])!=''){
	       $pid=$GLOBALS['db']->getOne("select id from ".DB_PREFIX."user where user_name= '".$_REQUEST['pid_name']."'");
		   if(!$pid){
			die('推荐人不存在');
		   }
			$condition .= " and  u.pid  ="."'".$pid."'";
		
		}
			if($_REQUEST['deal_load_check_yn']!=''){
			
			$a=($_REQUEST['deal_load_check_yn']==2)?0:$_REQUEST['deal_load_check_yn'];
			$condition .="  and dl.deal_load_check_yn=" .$a;

		}
		
		if($_REQUEST['end_time']!=''){
			$_REQUEST['end_time']=strtotime($_REQUEST['end_time']);
			$condition .= " and  dl.create_time  <"."'".$_REQUEST['end_time']."'";
		}
		if($_REQUEST['gmtime_id']>0){
		if($_REQUEST['gmtime_id']<30){
		    $tmie=get_gmtime();
			$time=get_gmtime()-$_REQUEST['gmtime_id']*86400;
			$condition .= " and  dl.create_time  >"."'".$time."'";
			$condition .= " and  dl.create_time  <"."'".$tmie."'";	
		  }
		if($_REQUEST['gmtime_id']==30){
           $datevip=date('Y-m-01', strtotime(date("Y-m-d")));
            $datebei=date('Y-m-d', strtotime("$datevip +1 month -1 day"));
			$op=strtotime($datevip);
			$po=strtotime($datebei);
			$condition .= " and  dl.create_time  >"."'".$op."'";
			$condition .= " and  dl.create_time  <"."'".$po."'";	
		}
		  // echo $condition;exit;
		  
		}
		

		// echo $ass;exit;
		
		
		
		if(trim($_REQUEST['user_name'])!='')
		{
			
			$condition .= " and  dl.user_name like"."'%".trim($_REQUEST['user_name'])."%'";
		}
		
		if(trim($_REQUEST['name'])!='')
		{
			
			$condition .= "  and   d.name like"."'%".trim($_REQUEST['name'])."%'";
		}
		if(trim($_REQUEST['deal_load_id']!=''))
		{
			$condition .= "  and   dl.id ="."'".trim($_REQUEST['deal_load_id'])."'";
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
		else
		{
			//echo $condition;exit;
			$condition .= "  and   u.group_id = 1";
		}
		if(trim($_REQUEST['_sort'])==0){
			$sort='desc';
		
		}
		elseif(trim($_REQUEST['_sort'])==1){
			$sort='asc';
		
		}
		if(trim($_REQUEST['_order'])=='')
		{
		
			$order=	"order  by deal_time desc";
			
		}
		if(trim($_REQUEST['_order'])=='name')
		{
			$order=	"order  by  d.name  ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='deal_load_id')
		{
			$order=	"order  by deal_load_id ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='real_name')
		{
			$order=	"order  by u.real_name ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='user_name')
		{
			$order=	"order  by dl.user_name ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='deal_time')
		{
			$order=	"order  by deal_time ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='u_load_money')
		{
			$order=	"order  by u_load_money ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='repay_time')
		{
			$order=	"order  by d.repay_time ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='deal_load_check_yn')
		{
			$order=	"order  by dl.deal_load_check_yn ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='group_name')
		{
			$order=	"order  by group_name ".$sort;
		}
		elseif(trim($_REQUEST['_order'])=='mobile')
		{
			$order=	"order  by u.mobile ".$sort;
		}
		
		$module=m('deal');		
		import('ORG.Util.Page');// 导入分页类
		$count  = $module->query( "select  count(*) as count from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id  left join ".DB_PREFIX."user_group as g on u.group_id = g.id  where ".$condition);
		$count=$count[0]['count'];
		// 查询满足要求的总记录数
		$per_page=$_REQUEST['per_page']?$_REQUEST['per_page']:30;
			if($deviceType!='computer'){
				$per_page = 10;
			}
		$Page   = new Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
		$show   = $Page->show();// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		
    	$sql = "select u.real_name,u.mobile,u.pid,g.name as group_name, d.name,d.repay_start_time,d.last_repay_time,d.rate,d.repay_time,d.repay_time_type,d.id as deal_id,dl.user_name,dl.user_id,dl.money as u_load_money,dl.id as deal_load_id,dl.create_time as deal_time , dl.deal_load_check_yn,dl.virtual_money from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id  left join ".DB_PREFIX."user_group as g on u.group_id = g.id  where ".$condition .' '. $order . ' limit '.$Page->firstRow.','.$Page->listRows ;
		/*
		d   是  deal
		dl  是  deal_load
		u   是  user
		g   是  user_group
		*/
		$sql_no_limit = "select d.name,d.rate,d.repay_time,d.repay_time_type, dl.money as u_load_money,dl.virtual_money  from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id  left join ".DB_PREFIX."user_group as g on u.group_id = g.id  where ".$condition ;
	
		$list_no_limit = $GLOBALS['db']->getAll($sql_no_limit);
		foreach($list_no_limit as $k=>$v)
		{
			$total_no_limit+=$v['u_load_money'];
			if($v['repay_time_type']==1){ //1表示月0表示日
			$list_no_limit[$k]['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/12)*$v['repay_time']*0.01,2);
			//计算利率
			}
			else{
			$list_no_limit[$k]['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/365)*$v['repay_time']*0.01,2);
			}
			$total_rate_money_nolimit+=$list_no_limit[$k]['get_money'];//当页累计效益
		}
		$this->assign('total_rate_money_nolimit',$total_rate_money_nolimit);
		$list = $GLOBALS['db']->getAll($sql);
		// echo $sql_no_limit;exit;
		//deal_load_check_yn
		foreach($list as $k=>$v)
		{
			$total_limit+=$v['u_load_money'];//当页累计成交金额
			if($v['repay_time_type']==1){ //1表示月0表示日
			$list[$k]['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/12)*$v['repay_time']*0.01,2);
			//计算利率
			}
			else{
			$list[$k]['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/365)*$v['repay_time']*0.01,2);
			}
			$total_rate_money+=$list[$k]['get_money'];//当页累计效益
			$list[$k]['urlencode_name']=str_replace('+','%2b',$v['name']);
		}
		
			$this->assign('total_rate_money',$total_rate_money);
		
		$total_limit=number_format($total_limit);
		$this->assign('total_limit',$total_limit);
		$total_no_limit=number_format($total_no_limit);
		$this->assign('total_no_limit',$total_no_limit);
		// echo $sql;exit;
		$this->assign('list',$list);
		// if($deviceType!='computer'){	
		// echo 123;exit;
			// $show = $Page->pre_nex ();
			// $this->assign ( "page", $show );
			// $this->display('index_mobile');
				
			// }
			// else{
			// }		
		$this->display('index');
		
		
	}
	public function check(){
		
		$id=trim($_REQUEST['id']);
		
		$GLOBALS['db']->query("update ".DB_PREFIX."deal_load set deal_load_check_yn = 1 where id = ".$id);
		
		redirect('?m=Deal_list&a=index');
	}
	public function send()
	{
		echo  'heihei，还没做好';
	}
	
	
	//首次投资列表奖励
	public function first_load(){
		$condition='l.money=40 ';
		$group_list = M("UserGroup")->findAll();
		$this->assign("group_list",$group_list);
		$this->assign("sort",1);
		if(trim($_REQUEST['user_name'])!='')
		{
			$condition .= " and  u.user_name like"."'%".trim($_REQUEST['user_name'])."%'";
		}
		if(trim($_REQUEST['_sort'])==0){
			$sort='desc';
		}
		elseif(trim($_REQUEST['_sort'])==1){
			$sort='asc';
		}
		if(trim($_REQUEST['_order'])=='')
		{
			$order=	"order  by l.id desc";
		}
		if(trim($_REQUEST['_order'])=='user_name')
		{
			$order=	"order  by u.user_name desc";
		}
		if(trim($_REQUEST['_order'])=='log_info')
		{
			$order=	"order  by l.log_info desc";
		}
		if(trim($_REQUEST['_order'])=='adm_name')
		{
			$order=	"order  by a.adm_name desc";
		}
		if(trim($_REQUEST['_order'])=='log_time')
		{
			$order=	"order  by l.log_time desc";
		}
		$module=m('user_log');		
		import('ORG.Util.Page');// 导入分页类
		$count  = $module->query( "select  count(*) as count from ".DB_PREFIX."user_log l left join ".DB_PREFIX."user as u on u.id = l.user_id LEFT JOIN ".DB_PREFIX."admin a ON a.id=l.log_admin_id   where ".$condition);
		$count=$count[0]['count'];
		// 查询满足要求的总记录数
		$per_page=$_REQUEST['per_page']?$_REQUEST['per_page']:30;
	
		$Page   = new Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
		$show   = $Page->show();// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		
    	$sql = "select u.user_name,l.*,a.adm_name  from ".DB_PREFIX."user_log l left join ".DB_PREFIX."user as u on u.id = l.user_id LEFT JOIN ".DB_PREFIX."admin a ON a.id=l.log_admin_id    where ".$condition .' '. $order . ' limit '.$Page->firstRow.','.$Page->listRows ;
		
	
		$list = $GLOBALS['db']->getAll($sql);
		$this->assign('list',$list);
		$this->display('first_load');
	
	}
	
	public function daochu($page = 1)
	{
		$id = $_REQUEST ['id'];
		
		//where(array ('user_id' => array ('in', explode ( ',', $id ) ) ));
		$condition ="dl.id in ($id) " ;
		$order="order by dl.id desc";
		
		
		
		$sql = "select u.real_name,u.pid,u.mobile,g.name as group_name, d.name,d.rate,d.repay_time,d.repay_time_type,d.id as deal_id,dl.user_name,dl.user_id,dl.money as u_load_money,dl.id as deal_load_id,dl.virtual_money as virtual_money ,dl.create_time as deal_time , dl.deal_load_check_yn,dl.virtual_money,b.bankcard ,b.bankzone from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_load as dl on d.id = dl.deal_id LEFT JOIN ".DB_PREFIX."user u ON u.id=dl.user_id  left join ".DB_PREFIX."user_group as g on u.group_id = g.id left join ".DB_PREFIX."user_bank as b on u.id = b.user_id  where ".$condition .' '. $order ;
		
		$list = $GLOBALS['db']->getAll($sql);
		foreach($list as $k=>$v)
		{
		
		$v['pid_name']= $this->get_user_name_nolink($v['pid']);
		if($v['repay_time_type']==1){ //1表示月0表示日
			$v['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/12)*$v['repay_time']*0.01,2);
			//计算利率
			}
			else{
			$v['get_money']=number_format((($v['u_load_money']+$v['virtual_money'])*$v['rate']/365)*$v['repay_time']*0.01,2);
			}
		$v['repay_time_type']=$v['repay_time_type']?'月':'日';
		$arr[0]=array('编号','投资人','真实姓名','电话号码','组别','推荐人','项目名称','交易金额','代金劵','利率','收益','期限','交易时间','银行账号','开户行');
		$arr[$k+1]=array($v['deal_load_id'],$v['user_name'],$v['real_name'],$v['mobile'],$v['group_name'],$v['pid_name'],$v['name'],$v['u_load_money'],$v['virtual_money'],$v['rate'],$v['get_money'],$v['repay_time'].$v['repay_time_type'],to_date($v['deal_time'],'Y-m-d H:i:s'),$v['bankcard'],$v['bankzone']);
		}
		
		$this->outputXlsHeader($arr,'交易列表'.time());
		
		
	}
	
	public function outputXlsHeader($data,$file_name = 'export')
{
 header('Content-Type: text/xls'); 
 header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );
 $str = mb_convert_encoding($file_name, 'gbk', 'utf-8');   
 header('Content-Disposition: attachment;filename="' .$str . '.xls"');      
 header('Cache-Control:must-revalidate,post-check=0,pre-check=0');        
 header('Expires:0');         
 header('Pragma:public');
 
 $table_data = '<table border="1">'; 
 foreach ($data as $line)         
 {
  $table_data .= '<tr>';
  foreach ($line as $key => &$item)
  {
   $item = mb_convert_encoding($item, 'gbk', 'utf-8'); 
   $table_data .= '<td style="vnd.ms-excel.numberformat:@">' . $item . '</td>';
  }
  $table_data .= '</tr>';
 }
 $table_data .='</table>';
 echo $table_data;    
 die();
}	
	
public function get_user_name_nolink($user_id)
{
	$user_name =  M("User")->where("id=".$user_id." and is_delete = 0")->getField("user_name");
	
	if(!$user_name)
	return '';
	else
	return $user_name;
}	

		
}
?>