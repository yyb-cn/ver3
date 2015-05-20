<?php
if(!defined('ROOT_PATH'))
define('ROOT_PATH', str_replace('llwappay_return.php', '', str_replace('\\', '/', __FILE__)));

global $pay_req;
$pay_req['ctl'] = "payment";
$pay_req['act'] = "response";
$pay_req['class_name'] = "Llwappay";
include ROOT_PATH."index.php";
//header('Location:'.SITE_DOMAIN);
?>