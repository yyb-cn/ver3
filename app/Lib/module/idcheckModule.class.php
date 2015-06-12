<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
class idcheckModule extends SiteBaseModule
{
	public function index(){
		/* 
		# 函数功能：计算身份证号码中的检校码 
		# 函数名称：idcard_verify_number 
		# 参数表 ：string $idcard_base 身份证号码的前十七位 
		# 返回值 ：string 检校码 
		# 更新时间：Fri Mar 28 09:50:19 CST 2008 
		*/  
		function idcard_verify_number($idcard_base){  
		if (strlen($idcard_base) != 17){  
		   return false;  
		}  
			$factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); //debug 加权因子  
			$verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); //debug 校验码对应值  
			$checksum = 0;  
			for ($i = 0; $i < strlen($idcard_base); $i++){  
				$checksum += substr($idcard_base, $i, 1) * $factor[$i];  
			}  
			$mod = $checksum % 11;  
			$verify_number = $verify_number_list[$mod];  
			return $verify_number;  
		}  
		/* 
		# 函数功能：将15位身份证升级到18位 
		# 函数名称：idcard_15to18 
		# 参数表 ：string $idcard 十五位身份证号码 
		# 返回值 ：string 
		# 更新时间：Fri Mar 28 09:49:13 CST 2008 
		*/  
		function idcard_15to18($idcard){  
			if (strlen($idcard) != 15){  
				return false;  
			}else{// 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码  
				if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){  
					$idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 15);  
				}else{  
					$idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 15);  
				}  
			}  
			$idcard = $idcard . idcard_verify_number($idcard);  
			return $idcard;  
		}  
		/* 
		# 函数功能：18位身份证校验码有效性检查 
		# 函数名称：idcard_checksum18 
		# 参数表 ：string $idcard 十八位身份证号码 
		# 返回值 ：bool 
		# 更新时间：Fri Mar 28 09:48:36 CST 2008 
		*/  
		function idcard_checksum18($idcard){  
			if (strlen($idcard) != 18){ return false; }  
			$idcard_base = substr($idcard, 0, 17);  
			if (idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){  
				return false;  
			}else{  
				return true;  
			}  
		}  
		/* 
		# 函数功能：身份证号码检查接口函数 
		# 函数名称：check_id 
		# 参数表 ：string $idcard 身份证号码 
		# 返回值 ：bool 是否正确 
		# 更新时间：Fri Mar 28 09:47:43 CST 2008 
		*/  
		function check_id($idcard) {  
		if(strlen($idcard) == 15 || strlen($idcard) == 18){  
		   if(strlen($idcard) == 15){  
			$idcard = idcard_15to18($idcard);  
		   }  
		   if(idcard_checksum18($idcard)){  
			return true;  
		   }else{  
			return false;  
		   }  
		}else{  
		   return false;  
		}  
		}
		
		
		//验证返回AJAX
		if(check_id($_POST['idno'])){
		
	    $ecv['user_id'] =$GLOBALS['user_info']['id'];
  		$ecv['receive'] = 1;
		$ecv['receive_time'] = get_gmtime();
		$ecv['ecv_type_id'] = 27;	
		$ecv['last_time'] = get_gmtime()+604800;
		$ecv['password']=rand(10000000,99999999);
        $ecv['sn'] = uniqid();
       $GLOBALS['db']->autoExecute(DB_PREFIX."ecv",$ecv);    
       $user_ecv['log_info'] ="注册就送20投资代金劵";
   	   $user_ecv['log_time'] =get_gmtime();
	   $user_ecv['money'] =0;
	   $user_ecv['account_money'] =$GLOBALS['user_info']['money'];
	   $user_ecv['user_id'] =$GLOBALS['user_info']['id'];
   $GLOBALS['db']->autoExecute(DB_PREFIX."user_money_log",$user_ecv);   		
			echo json_encode(1);
		}
		else{
			echo json_encode(0);
		}


	}
}