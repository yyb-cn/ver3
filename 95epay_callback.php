<?php
if(!defined('ROOT_PATH'))
define('ROOT_PATH', str_replace('95epay_callback.php', '', str_replace('\\', '/', __FILE__)));

global $pay_req;
$pay_req['ctl'] = "payment";
$pay_req['act'] = isset($_REQUEST['act'])?($_REQUEST['act']):"response";
$pay_req['class_name'] = "Sqepay";
//include ROOT_PATH."index.php";
if ($_REQUEST['act'] == "query") {
	//print_r($_REQUEST);
	switch($_REQUEST['order'] )
	{
		case "0";
		$result1='失败';
		break;
		case "1";
		$result1='成功';
		break;
		case "2";
		$result1='待处理';
		break;
		case "3";
		$result1='取消';
		break;
		case "4";
		$result1='结果未返回';
		break;
		default;
		$result1='无状态';
		break;
	}

//	echo '<p style="background:#0099FF;text-align:center;height:6em;line-height:7em"><span style="font-size:4em;color:#000;">订单状态：'.$result1.'</span></p>';
	
	

	switch($_REQUEST['succeed'] )
	{
		case "success";
		$result2='信息验证成功，订单查询过程完整';
		break;
		case "Error_01";
		$result2='订单号为空，取消查询';
		break;
		case "Error_02";
		$result2='商户号为空，取消查询';
		break;
		case "Error_03";
		$result2='返回地址为空，取消查询';
		break;
		case "Error_04";
		$result2='MD5加密字符串为空，取消查询';
		break;	
		case "Error_05";
		$result2='订单不存在，取消查询';
		break;	
		case "Error_06";
		$result2='商户不存在，取消查询';
		break;	
		case Error_07;
		$result2='MD5加密字符串验证错误，取消查询';
		break;	
		case Error_08;
		$result2='单号不唯一，取消查询';
		break;	
	}
        $BillNo = $_REQUEST['BillNo'];//订单号
        $money = $_REQUEST['amount'];//金额
        $date =$_REQUEST['Date'];//交易时间
//		echo '<p style="background:#FFCCFF;text-align:center;height:6em;line-height:7em"><span style="font-size:32px;font-size:4em;color:#000;">验证状态：'.$result2.'</span></p>';
	
        $content = " 订单号: '.$BillNo.' ____ 交易时间：'.$date.' ___ 订单状态：'.$result1.'___ 验证状态：'.$result2.'___ 金额：'.$money.'"."\n";
        file_put_contents("log_order.txt",$content,FILE_APPEND);
	
} else {
	include ROOT_PATH."index.php";
}
?>