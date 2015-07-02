<?php
header("Content-Type: text/html; charset=UTF-8");
require './system/common.php';
require './app/Lib/app_init.php';
//$payment_notice_id = intval($_REQUEST['id']);//转为整数
//
//    	$payment_notice = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment_notice where id = ".$payment_notice_id);
		
//		
//		$payment_info = $GLOBALS['db']->getRow("select config from ".DB_PREFIX."payment where id=".intval($payment_notice['payment_id']));
//		$payment_info['config'] = unserialize($payment_info['config']);
       
		//$_MerchantID = $payment_info['config']['baofoo_account'];
        //$_Md5Key = $payment_info['config']['baofoo_key'];

$start_time = ($_REQUEST['start_time']=='')?'1412040063':$_REQUEST['start_time'];
$end_time = ($_REQUEST['end_time']=='')?'1412040068':$_REQUEST['end_time'];

        $MerNo = "181138";
	$MD5key = "aWOv]Fct";
        $MerUrl = "http://www.pfcf88.com/95epay_callback_1.php?act=query";
        $post_data = array();
        $order = $GLOBALS['db']->getAll("select notice_sn from ".DB_PREFIX."payment_notice where is_paid = 0 and create_time >".$start_time ." and create_time< ".$end_time);//查询订单
        foreach ($order as $key => $value) {
            
            $BillNo = $value['notice_sn'];
            $MD5Info = getSignature($MerNo, $BillNo, $MerUrl, $MD5key);
            $post_data['MerNo']  = $MerNo;  
            $post_data['BillNo'] = $BillNo;  
            $post_data['MerUrl'] = $MerUrl; 
            $post_data['MD5Info'] = $MD5Info;  
            $data = curl_post("http://www.95epay.cn/ReconciliationPort", $post_data);  
        echo $key;
        }
echo "done";
//        var_dump($data);



function getSignature($MerNo, $BillNo, $MerUrl, $MD5key){
	$_SESSION['MerNo'] = $MerNo;
	$_SESSION['MD5key'] = $MD5key;
	$sign_params  = array(
        'BillNo'       => $BillNo, 
        'MerNo'       => $MerNo,
        'MerUrl'       => $MerUrl
    );
  $sign_str = "";
  ksort($sign_params);
  foreach ($sign_params as $key => $val) {
                               
       $sign_str .= sprintf("%s=%s&", $key, $val);                
                
   }
   print $sign_str;print '<br/><br/><br/>';
   return strtoupper(md5($sign_str.strtoupper(md5($MD5key))));   

	
}

function curl_post($url, $post) {  
    $options = array(  
        CURLOPT_RETURNTRANSFER => true,  
        CURLOPT_HEADER         => false,  
        CURLOPT_POST           => true,  
        CURLOPT_POSTFIELDS     => $post,  
    );  


    $ch = curl_init($url);  
    curl_setopt_array($ch, $options);  
    $result = curl_exec($ch);  
    curl_close($ch);  
    return $result;  
}  


?>