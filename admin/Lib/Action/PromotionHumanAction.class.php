<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class PromotionHumanAction extends CommonAction{
	public function index()
	{	
		$user_name  = trim($_REQUEST['user_name']);
		$condition = "1=1";
		
		if($user_name!=="" && $user_name!=0)
		$condition.=" and user_name like '%".$user_name."%' ";
		
		$pid = $GLOBALS['db']->getAll("SELECT DISTINCT pid from ".DB_PREFIX."user");
		$flatmap = array_map("array_pop",$pid);
		$pid=implode(',',$flatmap);
		
		if($pid)
			$condition.=" and id in (" .$pid. " )" ;
		
		$sql_count = "SELECT count(*) FROM ".DB_PREFIX."user u left join ".DB_PREFIX."user_sta us on u.id = us.user_id  WHERE  $condition ";
		$count = $GLOBALS['db']->getOne($sql_count);
	
		if (! empty ( $_REQUEST ['listRows'] )) {
			$listRows = $_REQUEST ['listRows'];
		} else {
			$listRows = '';
		}
		
		$p = new Page ( $count, $listRows );
		if($count>0){
			$sql = "SELECT * FROM ".DB_PREFIX."user u left join ".DB_PREFIX."user_sta us on u.id = us.user_id  WHERE  $condition LIMIT ".($p->firstRow . ',' . $p->listRows);
		
			$list = $GLOBALS['db']->getAll($sql);
			foreach($list as $k=>$v){
				$list[$k]['pidcount'] = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user where pid = ".$list[$k]['id']);
				//提成
				$list[$k]['percentage'] = $GLOBALS['db']->getOne("select sum(repay_money-self_money+impose_money) from ".DB_PREFIX."deal_load_repay where user_id = ".$list[$k]['id']);
					
			}
			$this->assign("list",$list);
		}
	
		$page = $p->show();
		$this->assign ( "page", $page );
		$this->display();
	}
	
}
?>