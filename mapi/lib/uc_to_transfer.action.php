<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_to_transfer
{
	public function index(){
		
		$root = array();
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		
		$id = intval($GLOBALS['request']['id']);
		
			
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id >0){
			require APP_ROOT_PATH.'app/Lib/uc_func.php';
			
			$root['user_login_status'] = 1;
									
			$result = getUcToTransfer($id);
			 
			$root['transfer'] = $result['transfer'];			
			$root['response_code'] = $result['status'];
			$root['show_err'] = $result['show_err'];
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "转让详情";
		output($root);		
	}
}
?>
