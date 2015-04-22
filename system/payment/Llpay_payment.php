<?php
$payment_lang = array(
	'name'	=>	'连连支付',
	'llpay_account'	=>	'商户编号',
	'llpay_key'	=>	'安全检验码',
        'sign_type'     =>      '签名方式', 
        'sign_type_RSA' =>      'RSA 安全签名机制',
        'sign_type_MD5' =>      'MD5 安全签名机制',
        'PAY_FAILED'    =>      '支付失败',
        'SIGN_FAILED'   =>      '签名验证失败',
);
$config = array(
	'llpay_account'	=>	array(
		'INPUT_TYPE'	=>	'0',
	), //商户编号是商户在连连钱包支付平台上开设的商户号码，为18位数字，如：201306081000001016
	'llpay_key'	=>	array(
		'INPUT_TYPE'	=>	'0'
	), //安全检验码，以数字和字母组成的字符.
        'sign_type'    =>       array(
                'INPUT_TYPE'	=>	'1',
                'VALUES'	=> 	array('RSA','MD5')
        ),

);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true)
{
    $module['class_name']    = 'Llpay';

    /* 名称 */
    $module['name']    = $payment_lang['name'];


    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '1';

    /* 配置信息 */
    $module['config'] = $config;
    
    $module['lang'] = $payment_lang;
    
    $module['reg_url'] = 'http://www.lianlianpay.com/';
    
    return $module;
}

// 连连支付模型
require_once(APP_ROOT_PATH.'system/libs/payment.php');
class Llpay_payment implements payment {
    const VERSION = '1.0';//版本号
    const TRANSPORT = 'HTTP';//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    const USERREQ_IP='';//防钓鱼ip 可不传或者传下滑线格式 
    const VALID_ORDER = '10080';//订单有效时间  分钟为单位，默认为10080分钟（7天） 
    const INPUT_CHARSET = 'utf-8';//字符编码格式 目前支持 gbk 或 utf-8
    const BUSI_PARTNER = '101001';//虚拟商品销售： 101001 实物商品销售： 109001
    public function get_payment_code($payment_notice_id)
	{
        require_once(APP_ROOT_PATH.'system/payment/Llpay/llpay_submit.class.php');
		$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
                $user_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = '".$payment_notice['user_id']."'");
		$money = round($payment_notice['money'],2);
		$payment_info = $GLOBALS['db']->getRow("select id,config,logo from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
		$payment_info['config'] = unserialize($payment_info['config']);
                
                if($user_info['idcardpassed']!= 1||$user_info['idno']==''){
                   
                   showErr("<div style='font-size:18px'>您的实名信息尚未认证！</div>为保护您的账户安全，请先完成实名认证。",1,url("index","uc_account#security")); 
                   die(); 
                }
                if($user_info['mobilepassed']!=1||$user_info['mobile']==''){
                   showErr("<div style='font-size:18px'>您的手机号尚未认证！</div>为保护您的账户安全，请先完成手机号认证。",1,url("index","uc_account#security")); 
                   die(); 
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

                //订单地址 
                $url_order = '';

                //订单描述
                $info_order = '';

                //银行网银编码
                $bank_code = '';

                //支付方式 D 认证支付 此支付方式与指定银行网银编号配合使用
                $pay_type = 'D';

                //卡号
                $card_no = '';

                //姓名
                $acct_name = $user_info['real_name'];//真实姓名

                //身份证号
                $id_no = $user_info['idno'];

                //协议号 用户首次签约支付时生成的协议号，可用于二次支付
                $no_agree = '';

                //修改标记
                $flag_modify = '';
                
                $data = array();
                $user_info_dt_register = date('YmdHis', $user_info['create_time']);
                $data = array("frms_ware_category" =>"2009",//商品类目 互联网理财 
                              "user_info_mercht_userno"=>$payment_notice['user_id'],//商户用户唯一标识 用户在商户系统中的标识
                              "user_info_mercht_userlogin" =>$user_info['user_name'],//商户用户登陆名 用户在商户系统中的登陆名（手机号、邮箱等标识）
                              "user_info_bind_phone" => $user_info['mobile'],//绑定手机号 如有，需要传送
                              "user_info_dt_register" => $user_info_dt_register, //注册时间 YYYYMMDDH24MISS
                              "user_info_full_name" =>$user_info['real_name'],//用户真实姓名
                              "user_info_id_type" => "0",//用户注册证件类型 0：身份证或企业经营证件 1：户口簿，2：护照 3：军官证, 4：士兵证 5： 港澳居民来往内地通行证
                              "user_info_id_no" =>$id_no,//*用户注册证件号码
                              "user_info_identify_state" =>"1",//是否实名认证 1：:是 0：无认证 商户自身是否对用户信息 进行实名认证。
                              "user_info_identify_type" => "3",//实名认证方式 是实名认证时，必填1：银行卡认证2：现场认证3：身份证远程认证4：其它认证
                        );        
                //风险控制参数
                $risk_item = json_encode($data);//采用 json 串的模式传入

                //分账信息数据
                $shareing_data = '';//分帐信息数据

                //返回修改信息地址
                $back_url = '';

                //订单有效期
                $valid_order = $this::VALID_ORDER;

                //服务器异步通知页面路径
                $notify_url = SITE_DOMAIN.APP_ROOT.'/llpay_notify.php';
                //需http://格式的完整路径，不能加?id=123这类自定义参数

                //页面跳转同步通知页面路径
                $return_url = SITE_DOMAIN.APP_ROOT.'/llpay_return.php';
                //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
                
                $getip = '';
                
                $llpay_config = array(
                    "oid_partner" =>trim($payment_info['config']['llpay_account']), //商户编号是商户在连连钱包支付平台上开设的商户号码，为18位数字，如：201306081000001016
                    "key" => trim($payment_info['config']['llpay_key']), //安全检验码，以数字和字母组成的字符
                    "version" => $this::VERSION,//版本号
//                    "userreq_ip" => $formartip, //防钓鱼ip 可不传或者传下滑线格式 
                    "id_type" => '0', //证件类型 0为身份证
                    "sign_type"=> trim($payment_info['config']['sign_type']),//签名方式 不需修改
                    "valid_order" => $this::VALID_ORDER, //订单有效期
                    "input_charset" => $this::INPUT_CHARSET,//字符编码格式 目前支持 gbk 或 utf-8
                    "transport" => $this::TRANSPORT,//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
                   
                );
                
                /************************************************************/
                date_default_timezone_set('PRC');
                //构造要请求的参数数组，无需改动
                $parameter = array (
                        "version" => $this::VERSION,
                        "oid_partner" => trim($payment_info['config']['llpay_account']),
                        "sign_type" => trim($payment_info['config']['sign_type']),
                        //"userreq_ip" => trim($getip),
                        "id_type" => '0',//证件类型 0 为身份证
                        "valid_order" => $valid_order,//订单有效期 *
                        "user_id" => $user_id,//商户用户唯一编号 *
                        "timestamp" => local_date('YmdHis', time()),
                        "busi_partner" => $busi_partner,//支付类型 *
                        "no_order" => $no_order,//商户订单号 *
                        "dt_order" => local_date('YmdHis', time()),
                        "name_goods" => $name_goods,//商品名称 *
                        //"info_order" => $info_order, //订单描述
                        "money_order" => $money_order,//付款金额 *
                        "notify_url" => $notify_url,//服务器异步通知页面路径
                        "url_return" => $return_url,//页面跳转同步通知页面路径
                        //"url_order" => $url_order, //订单地址 
                        //"bank_code" => $bank_code,//银行网银编码
                        "pay_type" => $pay_type,//支付方式 D 认证支付 此支付方式与指定银行网银编号配合使用 *
                        //"no_agree" => $no_agree,//协议号 用户首次签约支付时生成的协议号，可用于二次支付
                        //"shareing_data" => $shareing_data,//分帐信息数据
                        "risk_item" => $risk_item,//风险控制参数 *
                        "id_no" => $id_no,
                        "acct_name" => $acct_name,//真实姓名*
                        //"flag_modify" => $flag_modify, //修改标记
                        //"card_no" => $card_no,//卡号
                        //"back_url" => $back_url//返回修改信息地址
                );
                //建立请求
                
                $llpaySubmit = new LLpaySubmit($llpay_config);
                $html_text = $llpaySubmit->buildRequestForm($parameter, "post", "确认");

                return $html_text;

	}
	
	public function response($request)
	{
            require_once(APP_ROOT_PATH.'system/payment/Llpay/llpay_cls_json.php');
                
		$payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Llpay'");  
                $payment['config'] = unserialize($payment['config']);

            if (empty ($request)) { //判断POST来的数组是否为空
			return false;
		} else {
			//首先对获得的商户号进行比对
			if (trim($request['oid_partner' ]) != trim($payment['config']['llpay_account'])) {
				//商户号错误
				return false;
			}

			//生成签名结果
			$parameter = array (
				'oid_partner' => $request['oid_partner'],//商户编号
				'sign_type' => $request['sign_type'],//签名方式
				'dt_order' => $request['dt_order'],//商户订单时间
				'no_order' =>  $request['no_order'],//商户订单号
				'oid_paybill' => $request['oid_paybill'],//支付单号
				'money_order' => $request['money_order'],//交易金额
				'result_pay' =>  $request['result_pay'],//支付结果
				'settle_date' => $request['settle_date'],//清算日期
				'info_order' =>$request['info_order'],//订单描述
				'pay_type'=>$request['pay_type'],//支付方式
				'bank_code'=>$request['bank_code'],//银行编号
			);

			if (!$this->getSignVeryfy($parameter, trim($request['sign'],$payment))) {
				showErr($GLOBALS['payment_lang']["SIGN_FAILED"]);//签名验证失败
			}
                        
                        if($parameter['result_pay'] == 'SUCCESS'){//成功支付
                            
                            $payment_notice_sn = $request['no_order' ];//订单ID
                            $outer_notice_sn = $parameter['oid_paybill'];//交易流水号
                            $payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where notice_sn = '".$payment_notice_sn."'");
                            require_once APP_ROOT_PATH."system/libs/cart.php";
                            $rs = payment_paid($payment_notice['id'],$outer_notice_sn);	//$rs 为0时：更新 ".DB_PREFIX."payment_notice 表的is_paid = 1失败。
                            $is_paid = intval($GLOBALS['db']->getOne("select is_paid from ".DB_PREFIX."payment_notice where id = '".intval($payment_notice['id'])."'"));
                            if ($is_paid == 1){
                                    app_redirect(url("index","payment#incharge_done",array("id"=>$payment_notice['id']))); //支付成功
                            }else{
                                    app_redirect(url("index","payment#pay",array("id"=>$payment_notice['id'])));
                            }
                            
                        }else{
                            
                            showErr($GLOBALS['payment_lang']["PAY_FAILED"]);
                            
                        }
		}
                
	}
	

        public function notify($request)
	{
            //生成签名结果
            $payment = $GLOBALS['db']->getRow("select id,config from ".DB_PREFIX."payment where class_name='Llpay'");  //获取连连支付的基本配置信息
            $payment['config'] = unserialize($payment['config']);
			$is_notify = true;
			include_once (APP_ROOT_PATH.'system/payment/Llpay/llpay_cls_json.php');
			$json = new JSON;
			$str = file_get_contents("php://input");
			$val = $json->decode($str);
			$oid_partner = trim($val-> {
				'oid_partner' });
			$sign_type = trim($val-> {
				'sign_type' });
			$sign = trim($val-> {
				'sign' });
			$dt_order = trim($val-> {
				'dt_order' });
			$no_order = trim($val-> {
				'no_order' });
			$oid_paybill = trim($val-> {
				'oid_paybill' });
			$money_order = trim($val-> {
				'money_order' });
			$result_pay = trim($val-> {
				'result_pay' });
			$settle_date = trim($val-> {
				'settle_date' });
			$info_order = trim($val-> {
				'info_order' });
			$pay_type = trim($val-> {
				'pay_type' });
			$bank_code = trim($val-> {
				'bank_code' });
			$no_agree = trim($val-> {
				'no_agree' });
			$id_type = trim($val-> {
				'id_type' });
			$id_no = trim($val-> {
				'id_no' });
			$acct_name = trim($val-> {
				'acct_name' });
		
		//首先对获得的商户号进行比对
		if ($oid_partner != trim($payment['config']['llpay_account'])) {
			//商户号错误
			file_put_contents("log.txt", "异步通知:商户号错误,验证失败\n", FILE_APPEND);
                        //验证失败
                        die("{'ret_code':'9999','ret_msg':'验签失败'}");

		}
		$parameter = array (
			'oid_partner' => $oid_partner,
			'sign_type' => $sign_type,
			'dt_order' => $dt_order,
			'no_order' => $no_order,
			'oid_paybill' => $oid_paybill,
			'money_order' => $money_order,
			'result_pay' => $result_pay,
			'settle_date' => $settle_date,
			'info_order' => $info_order,
			'pay_type' => $pay_type,
			'bank_code' => $bank_code,
			'no_agree' => $no_agree,
			'id_type' => $id_type,
			'id_no' => $id_no,
			'acct_name' => $acct_name
		);
		if (!$this->getSignVeryfy($parameter, $sign,$payment)) {
			file_put_contents("log.txt", "异步通知:签名错误，验证失败\n", FILE_APPEND);
                        //验证失败
                        die("{'ret_code':'9999','ret_msg':'验签失败'}");
		}
		file_put_contents("log.txt", "异步通知 验证成功\n", FILE_APPEND);
                die("{'ret_code':'0000','ret_msg':'交易成功'}"); //请不要修改或删除
                
	}
	
        /**
	 * 获取返回时的签名验证结果
	 * @param $para_temp 通知返回来的参数数组
	 * @param $sign 返回的签名结果
         * @param $payment 获取设置连连支付的基本配置信息
	 * @return 签名验证结果
	 */
	private function getSignVeryfy($para_temp, $sign,$payment) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter($para_temp);

		//对待签名参数数组排序
		$para_sort = argSort($para_filter);

		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = createLinkstring($para_sort);

		//file_put_contents("log.txt", "原串:" . $prestr . "\n", FILE_APPEND);
		//file_put_contents("log.txt", "sign:" . $sign . "\n", FILE_APPEND);
		$isSgin = false;
		switch (strtoupper(trim($payment['config']['sign_type']))) {
			case "MD5" :
				$isSgin = md5Verify($prestr, $sign, $payment['config']['llpay_key']);
				break;
			default :
				$isSgin = false;
		}

		return $isSgin;
	}
        
	public function get_display_code()
	{
		$payment_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where class_name='Llpay'");
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