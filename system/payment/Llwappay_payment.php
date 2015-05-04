<?php
$payment_lang = array(
	'name'	=>	'连连wap支付',
	'llpay_account'	=>	'商户编号',
	'key'	=>	'安全检验码',
        'PAY_FAILED'    =>      '支付失败',
        'SIGN_FAILED'   =>      '签名验证失败',
        'FAILED'        =>      '操作有误',
        'CODE_ERROR'    =>      '商户号错误，请联系客服',
        
);
$config = array(
	'llpay_account'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //商户编号是商户在连连钱包支付平台上开设的商户号码，为18位数字，如：201306081000001016
	'key'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //安全检验码，以数字和字母组成的字符.

);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Llwappay';

    /* 名称 */
    $module['name']    = $payment_lang['name'];

    /* 支付方式：1：在线支付；0：线下支付 ；2:wap手机支付*/
    $module['online_pay'] = '2';

    /* 配置信息 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
    
    $module['reg_url'] = 'http://www.lianlianpay.com/';
    
    return $module;
}

// 连连支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Llwappay_payment implements payment {
    const VERSION = '1.1';//请求应用标识 为wap版本
    const TRANSPORT = 'HTTP';//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    const USERREQ_IP='';//防钓鱼ip 可不传或者传下滑线格式 
    const VALID_ORDER = '10080';//订单有效时间  分钟为单位，默认为10080分钟（7天） 
    const INPUT_CHARSET = 'utf-8';//字符编码格式 目前支持 gbk 或 utf-8
    const BUSI_PARTNER = '101001';//虚拟商品销售： 101001 实物商品销售： 109001
    public function get_payment_code($payment_notice_id)
	{
        require_once(APP_ROOT_PATH.'system/payment/Llpay/llwappay_submit.class.php');
        $result = array();
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
                $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = '".$payment_notice['user_id']."'");
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
                
                if($user_info['idcardpassed']!= 1||$user_info['idno']==''){
                   
                   $result['show_err']=("您的实名信息尚未认证！为保护您的账户安全，请先完成实名认证。"); 
                   
                }
                if($user_info['mobilepassed']!=1||$user_info['mobile']==''){
                   $result['show_err']=("您的手机号尚未认证！为保护您的账户安全，请先完成手机号认证。"); 
                   
                }
/**************************请求参数**************************/

                //商户用户唯一编号
                $user_id = $payment_notice['user_id'];

                //支付类型
                $busi_partner = $this::BUSI_PARTNER;

                //商户订单号
                $no_order = $payment_notice['notice_sn'];
                //商户网站订单系统中唯一订单号，必填

                //付款金额
                $money_order = $money;
                //必填

                //商品名称
                $name_goods = $payment_notice['notice_sn']."订单充值 ￥".$money;

                //姓名
                $acct_name = $user_info['real_name'];//真实姓名

                //身份证号
                $id_no = $user_info['idno'];

                $data = array();
                $user_info_dt_register = date('YmdHis', $user_info['create_time']);
                $data = array("frms_ware_category" =>"2009",//商品类目 互联网理财 
                              "user_info_mercht_userno"=>$payment_notice['user_id'],//商户用户唯一标识 用户在商户系统中的标识
                              "user_info_mercht_userlogin" =>$user_info['user_name'],//商户用户登陆名 用户在商户系统中的登陆名（手机号、邮箱等标识）
                              "user_info_bind_phone" => $user_info['mobile'],//绑定手机号 如有，需要传送
                              "user_info_dt_register" => $user_info_dt_register, //注册时间 YYYYMMDDH24MISS
                              //"user_info_full_name" =>$user_info['real_name'],//用户真实姓名
                              //"user_info_id_type" => "0",//用户注册证件类型 0：身份证或企业经营证件 1：户口簿，2：护照 3：军官证, 4：士兵证 5： 港澳居民来往内地通行证
                              //"user_info_id_no" =>$id_no,//*用户注册证件号码
                              //"user_info_identify_state" =>"1",//是否实名认证 1：:是 0：无认证 商户自身是否对用户信息 进行实名认证。
                              //"user_info_identify_type" => "3",//实名认证方式 是实名认证时，必填1：银行卡认证2：现场认证3：身份证远程认证4：其它认证
                        );        
                //风险控制参数
                $risk_item = json_encode($data);
                $risk_item = addslashes(stripslashes($risk_item));
                
                //订单有效期
                $valid_order = $this::VALID_ORDER;

                //服务器异步通知页面路径
                $notify_url = SITE_DOMAIN.'/llwappay_notify.php';
                //需http://格式的完整路径，不能加?id=123这类自定义参数

                //页面跳转同步通知页面路径
                $return_url = SITE_DOMAIN.'/llwappay_return.php';
                //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

                $llpay_config = array(
                    "oid_partner" =>trim($payment_info['config']['llpay_account']), //商户编号是商户在连连钱包支付平台上开设的商户号码，为18位数字，如：201306081000001016
                    "key" => trim($payment_info['config']['key']), //安全检验码，以数字和字母组成的字符
                    "version" => $this::VERSION,//版本号
                    "app_request" => '3', //请求应用标识 为wap版本，不需修改
                    "id_type" => '0', //证件类型 0为身份证
                    "sign_type"=> 'MD5',//签名方式 不需修改
                    "valid_order" => $this::VALID_ORDER, //订单有效期
                    "input_charset" => $this::INPUT_CHARSET,//字符编码格式 目前支持 gbk 或 utf-8
                    "transport" => $this::TRANSPORT,//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
                );
                
/************************************************************/
                
                $parameter = array (
                        "version" => $this::VERSION,
                        "oid_partner" => trim($payment_info['config']['llpay_account']),//商户编号
                        "app_request" => '3',
                        "sign_type" => 'MD5',
                        "id_type" => '0',//证件类型 0 为身份证
                        "valid_order" => $valid_order,//订单有效期 *
                        "user_id" => $user_id,//商户用户唯一编号 *
                        "busi_partner" => $busi_partner,//支付类型 *
                        "no_order" => $no_order,//商户订单号 *
                        "dt_order" => local_date('YmdHis', time()),
                        "name_goods" => $name_goods,//商品名称 *
                        "money_order" => $money_order,//付款金额 *
                        "notify_url" => $notify_url,//服务器异步通知页面路径
                        "url_return" => $return_url,//页面跳转同步通知页面
                        "risk_item" => $risk_item,//风险控制参数 *
                        "id_no" => $id_no,
                        "acct_name" => $acct_name,//真实姓名*
                );
                
                
                $llpaySubmit = new LLpaySubmit($llpay_config);
                $result['req_data'] = $llpaySubmit->buildRequestPara($parameter);
                $result['notify_url'] = $llpaySubmit->llpay_gateway_new;
                $result['pay_code'] = 'llwappay';
                $result['is_wap'] = 1;
                $result['method'] = 'post';
                return $result;

	}
	
	public function response($request)
	{
            $return_res = array(
			'info'=>'',
			'status'=>false,
		);
               
            include_once(APP_ROOT_PATH.'system/payment/Llpay/llpay_cls_json.php');
            require_once(APP_ROOT_PATH.'system/payment/Llpay/llwappay_notify.class.php');
  
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Llwappay'");  
                $payment['config'] = unserialize($payment['config']);
                
                $llpay_config = array(
                        "oid_partner" =>trim($payment['config']['llpay_account']), //商户编号是商户在连连钱包支付平台上开设的商户号码，为18位数字，如：201306081000001016
                        "key" => trim($payment['config']['key']), //安全检验码，以数字和字母组成的字符
                        "version" => $this::VERSION,//版本号
                        "app_request" => '3', //请求应用标识 为wap版本，不需修改
                        "id_type" => '0', //证件类型 0为身份证
                        "sign_type"=> 'MD5',//签名方式 不需修改
                        "valid_order" => $this::VALID_ORDER, //订单有效期
                        "input_charset" => $this::INPUT_CHARSET,//字符编码格式 目前支持 gbk 或 utf-8
                        "transport" => $this::TRANSPORT,//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
                    );
                
                $json = new JSON;
                $res_data = $_GET["res_data"];
                
                //签名方式
                $sign_type= $json->decode($res_data)-> {'sign_type'};
                
                //签名
                $sign = $json->decode($res_data)-> {'sign'};
                
                //商户编号
                $oid_partner = $json->decode($res_data)-> {'oid_partner'};
                
                //商户订单时间格式：YYYYMMDDH24MISS 14 位数字，精确到秒
                $dt_order = $json->decode($res_data)-> {'dt_order'};
                
                //商户订单号
                $no_order = $json->decode($res_data)-> {'no_order'};
                
                //交易流水号
                $oid_paybill = $json->decode($res_data)-> {'oid_paybill'};
                
                //支付结果
                $result_pay =  $json->decode($res_data)-> {'result_pay'};
                
                //交易金额
                $money_order = $json->decode($res_data)->{'money_order'};
                
                //清算日期 
                $settle_date = $json->decode($res_data)->{'settle_date'};
                
                $llpayNotify = new LLpayNotify($llpay_config);
                $verify_result = $llpayNotify->verifyReturn();
                
                if($verify_result) {//验证成功
                    file_put_contents("log.txt","手机wap同步通知:成功\n", FILE_APPEND);
                    if($result_pay == 'SUCCESS') {
                        
                        $payment_notice_sn = $no_order;//订单ID
                        $outer_notice_sn = $oid_paybill;//交易流水号
                        $payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_sn."'");
                        require_once APP_ROOT_PATH."system/libs/cart.php";
                        $rs = payment_paid($payment_notice['id'],$outer_notice_sn);
                        $is_paid = intval($GLOBALS['db']->getOne("select is_paid from ".DB_PREFIX."payment_notice where id = '".intval($payment_notice['id'])."'"));
                        if ($is_paid == 1){
                            
                            $data['show_err'] = "支付成功";
                                //app_redirect(url("index","payment#incharge_done",array("id"=>$payment_notice['id']))); //支付成功
                        }else{
                            $data['show_err'] = "系统内部错误";
                                //app_redirect(url("index","payment#pay",array("id"=>$payment_notice['id'])));
                        }
                    }else {
                        
                        $data['show_err'] = $GLOBALS['payment_lang']["PAY_FAILED"]." ".$result_pay;
                       
                    }
                    
                    
                }
                else {
                    $data['show_err'] = '支付失败';
                    file_put_contents("log.txt","手机wap同步通知 验证失败\n", FILE_APPEND);
                }
                return ($data);
	}
	
        public function notify($request)
	{
            $return_res = array(
            'info' => '',
            'status' => false,
            );
            require_once(APP_ROOT_PATH.'system/payment/Llpay/llwappay_notify.class.php');
            $payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Llwappay'");  //获取连连支付的基本配置信息
            $payment['config'] = unserialize($payment['config']);
            $llpay_config = array(
                        "oid_partner" =>trim($payment['config']['llpay_account']), //商户编号是商户在连连钱包支付平台上开设的商户号码，为18位数字，如：201306081000001016
                        "key" => trim($payment['config']['key']), //安全检验码，以数字和字母组成的字符
                        "version" => $this::VERSION,//版本号
                        "app_request" => '3', //请求应用标识 为wap版本，不需修改
                        "id_type" => '0', //证件类型 0为身份证
                        "sign_type"=> 'MD5',//签名方式 不需修改
                        "valid_order" => $this::VALID_ORDER, //订单有效期
                        "input_charset" => $this::INPUT_CHARSET,//字符编码格式 目前支持 gbk 或 utf-8
                        "transport" => $this::TRANSPORT,//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
                    );
            
            $llpayNotify = new LLpayNotify($llpay_config);
            $verify_result = $llpayNotify->verifyNotify();
            
            if ($verify_result->result) { //验证成功
                //获取连连支付的通知返回参数，可参考技术文档中服务器异步通知参数列表
                $no_order = $llpayNotify->notifyResp['no_order'];//商户订单号
                $oid_paybill = $llpayNotify->notifyResp['oid_paybill'];//连连支付单号
                $result_pay = $llpayNotify->notifyResp['result_pay'];//支付结果，SUCCESS：为支付成功
                $money_order = $llpayNotify->notifyResp['money_order'];// 支付金额
                if($result_pay == "SUCCESS"){
                       //成功后执行
                }
                file_put_contents("log.txt", "手机wap异步通知 验证成功\n", FILE_APPEND);
                die("{'ret_code':'0000','ret_msg':'交易成功'}"); //请不要修改或删除
                
                } else {
                        file_put_contents("log.txt", "手机wap异步通知 验证失败\n", FILE_APPEND);
                        //验证失败
                        die("{'ret_code':'9999','ret_msg':'验签失败'}");
                        //调试用，写文本函数记录程序运行情况是否正常
                        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
	}
	
	public function get_display_code()
	{
		$payment_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name='Llwappay'");
		if($payment_item)
		{
			$html = "<div style='float:left;'>".
					"<input type='radio' name='payment' value='".$payment_item['id']."' />&nbsp;".
					$payment_item['name'].
					"：</div>";
			if($payment_item['logo']!='')
			{
				$html .= "<div style='float:left; padding-left:10px;'><img src='".APP_ROOT.$payment_item['logo']."' /></div>";
			}
			$html .= "<div style='float:left; padding-left:10px;'>".nl2br($payment_item['description'])."</div>";
			return $html;
		}
		else
		{
			return '';
		}
	}
        
        
}
?>