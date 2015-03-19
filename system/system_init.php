<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------
define("IS_DEBUG",false);
define("SHOW_DEBUG",false);
if (PHP_VERSION >= '5.0.0')
{
	$begin_run_time = @microtime(true);
}
else
{
	$begin_run_time = @microtime();
}

@set_magic_quotes_runtime (0);
define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?True:False);
if(IS_DEBUG)
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
else
error_reporting(0);
if(!defined('IS_CGI'))
define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
 if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER["PHP_SELF"]);
            define('_PHP_FILE_',  rtrim(str_replace($_SERVER["HTTP_HOST"],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',  rtrim($_SERVER["SCRIPT_NAME"],'/'));
        }
    }
if(!defined('APP_ROOT')) {
        // 网站URL根目录
        $_root = dirname(_PHP_FILE_);
        $_root = (($_root=='/' || $_root=='\\')?'':$_root);
        $_root = str_replace("/system","",$_root);
        define('APP_ROOT', $_root  );
}
if(!defined('APP_ROOT_PATH')) 
define('APP_ROOT_PATH', str_replace('system/system_init.php', '', str_replace('\\', '/', __FILE__)));
define("MAX_DYNAMIC_CACHE_SIZE",1000);  //动态缓存最数量

//定义$_SERVER['REQUEST_URI']兼容性
if (!isset($_SERVER['REQUEST_URI']))
{
		if (isset($_SERVER['argv']))
		{
			$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
		}
		else
		{
			$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
		}
		$_SERVER['REQUEST_URI'] = $uri;
}
filter_request($_GET);
filter_request($_POST);

//关于安装的检测
if(!file_exists(APP_ROOT_PATH."public/install.lock"))
{
	app_redirect(APP_ROOT."/install/index.php");
}

//引入数据库的系统配置及定义配置函数
update_sys_config();
$sys_config = require APP_ROOT_PATH.'system/config.php';
function app_conf($name)
{
	if($name=="SITE_TITLE"){
		$name = "SHOP_TITLE";
	}
	return stripslashes($GLOBALS['sys_config'][$name]);
}
//end 引入数据库的系统配置及定义配置函数

require APP_ROOT_PATH.'system/utils/es_cookie.php';
require APP_ROOT_PATH.'system/utils/es_session.php';

function get_http()
{
	return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
}

function get_domain()
{
	/* 协议 */
	$protocol = get_http();

	/* 域名或IP地址 */
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
	{
		$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
	}
	elseif (isset($_SERVER['HTTP_HOST']))
	{
		$host = $_SERVER['HTTP_HOST'];
	}
	else
	{
		/* 端口 */
		if (isset($_SERVER['SERVER_PORT']))
		{
			$port = ':' . $_SERVER['SERVER_PORT'];

			if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol))
			{
				$port = '';
			}
		}
		else
		{
			$port = '';
		}

		if (isset($_SERVER['SERVER_NAME']))
		{
			$host = $_SERVER['SERVER_NAME'] . $port;
		}
		elseif (isset($_SERVER['SERVER_ADDR']))
		{
			$host = $_SERVER['SERVER_ADDR'] . $port;
		}
	}

	return $protocol . $host;
}

function get_host()
{
	/* 域名或IP地址 */
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
	{
		$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
	}
	elseif (isset($_SERVER['HTTP_HOST']))
	{
		$host = $_SERVER['HTTP_HOST'];
	}
	else
	{
		if (isset($_SERVER['SERVER_NAME']))
		{
			$host = $_SERVER['SERVER_NAME'];
		}
		elseif (isset($_SERVER['SERVER_ADDR']))
		{
			$host = $_SERVER['SERVER_ADDR'];
		}
	}
	return $host;
}


function filter_ma_request($str){
	$search = array("../","\n","\r","\t","\r\n","'","<",">","\"","%");
		
	return str_replace($search,"",$str);
}

if(app_conf("URL_MODEL")==1)
{
	//重写模式
	$current_url = APP_ROOT;
	$current_file = explode("/",_PHP_FILE_);
	$current_file = $current_file[count($current_file)-1];
	if($current_file=='index.php'||$current_file=='shop.php')
	$app_index = "";
	else 
	$app_index = str_replace(".php","",$current_file);
	if($app_index!="")
	$current_url = $current_url."/".$app_index;
	
	$rewrite_param = $_REQUEST['rewrite_param'];
	$rewrite_param = explode("/",$rewrite_param);

	foreach($rewrite_param as $k=>$param_item)
	{
		if($param_item!='')
		$rewrite_param_array[] = $param_item;
	}
	foreach ($rewrite_param_array as $k=>$v)
	{
		if(substr($v,0,1)=='-')
		{
			//扩展参数
			$v = substr($v,1);
			$ext_param = explode("-",$v);
			foreach($ext_param as $kk=>$vv)
			{
				if($kk%2==0)
				{
					if(preg_match("/(\w+)\[(\w+)\]/",$vv,$matches))
					{
						$_GET[$matches[1]][$matches[2]] = $ext_param[$kk+1];
					}
					else
					$_GET[$ext_param[$kk]] = $ext_param[$kk+1];
					
					if($ext_param[$kk]!="p")
					{
						$current_url.=$ext_param[$kk];	
						$current_url.="-".$ext_param[$kk+1]."-";
					}
				}
			}			
		}
		elseif($k==0)
		{
			//解析ctl与act
			$ctl_act = explode("-",$v);
			if($ctl_act[0]!='cid'&&$ctl_act[0]!='id'&&$ctl_act[0]!='aid'&&$ctl_act[0]!='qid'&&$ctl_act[0]!='pid'&&$ctl_act[0]!='cid'&&$ctl_act[0]!='a')
			{
				$_GET['ctl'] = $ctl_act[0];
				$_GET['act'] = $ctl_act[1];	
		
				$current_url.="/".$ctl_act[0];	
				if($ctl_act[1]!="")
				$current_url.="-".$ctl_act[1]."/";	
				else
				$current_url.="/";	
			}
			else
			{
				//扩展参数
				$ext_param = explode("-",$v);
				foreach($ext_param as $kk=>$vv)
				{
					if($kk%2==0)
					{
						if(preg_match("/(\w+)\[(\w+)\]/",$vv,$matches))
						{
							$_GET[$matches[1]][$matches[2]] = $ext_param[$kk+1];
						}
						else
						$_GET[$ext_param[$kk]] = $ext_param[$kk+1];
						
						if($ext_param[$kk]!="p")
						{
							if($kk==0)$current_url.="/";
							$current_url.=$ext_param[$kk];	
							$current_url.="-".$ext_param[$kk+1]."-";	
						}
					}
				}
			}
			
		}elseif($k==1)
		{
			//扩展参数
			$ext_param = explode("-",$v);
			foreach($ext_param as $kk=>$vv)
			{
				if($kk%2==0)
				{
					if(preg_match("/(\w+)\[(\w+)\]/",$vv,$matches))
					{
						$_GET[$matches[1]][$matches[2]] = $ext_param[$kk+1];
					}
					else
					$_GET[$ext_param[$kk]] = $ext_param[$kk+1];
					
					if($ext_param[$kk]!="p")
					{
						$current_url.=$ext_param[$kk];	
						$current_url.="-".$ext_param[$kk+1]."-";
					}
				}
			}			
		}
	}
	$current_url = substr($current_url,-1)=="-"?substr($current_url,0,-1):$current_url;	

	$domain = get_host();
	if(strpos($domain,".".app_conf("DOMAIN_ROOT")))
	{
		$city = str_replace(".".app_conf("DOMAIN_ROOT"),"",$domain);	
		if($city!='')
		$_GET['city'] = $city;
	}
}
unset($_REQUEST['rewrite_param']);
unset($_GET['rewrite_param']);

//引入时区配置及定义时间函数
if(function_exists('date_default_timezone_set'))
	date_default_timezone_set(app_conf('DEFAULT_TIMEZONE'));
//end 引入时区配置及定义时间函数

//定义缓存
require APP_ROOT_PATH.'system/cache/Cache.php';
$cache = CacheService::getInstance();
require_once APP_ROOT_PATH."system/cache/CacheFileService.php";
$fcache = new CacheFileService();  //专用于保存静态数据的缓存实例
$fcache->set_dir(APP_ROOT_PATH."public/runtime/data/");
//end 定义缓存

//定义DB
require APP_ROOT_PATH.'system/db/db.php';
define('DB_PREFIX', app_conf('DB_PREFIX')); 
define('TIME_UTC', get_gmtime()); 
define('SITE_DOMAIN', get_domain()); 
if(!file_exists(APP_ROOT_PATH.'public/runtime/app/db_caches/'))
	mkdir(APP_ROOT_PATH.'public/runtime/app/db_caches/',0777);
$pconnect = false;
$db = new mysql_db(app_conf('DB_HOST').":".app_conf('DB_PORT'), app_conf('DB_USER'),app_conf('DB_PWD'),app_conf('DB_NAME'),'utf8',$pconnect);
//end 定义DB


//定义模板引擎
require  APP_ROOT_PATH.'system/template/template.php';
if(!file_exists(APP_ROOT_PATH.'public/runtime/app/tpl_caches/'))
	mkdir(APP_ROOT_PATH.'public/runtime/app/tpl_caches/',0777);	
if(!file_exists(APP_ROOT_PATH.'public/runtime/app/tpl_compiled/'))
	mkdir(APP_ROOT_PATH.'public/runtime/app/tpl_compiled/',0777);
$tmpl = new AppTemplate;

//end 定义模板引擎
$_REQUEST = array_merge($_GET,$_POST);
filter_request($_REQUEST);
$lang = require APP_ROOT_PATH.'/app/Lang/'.app_conf("SHOP_LANG").'/lang.php';


function run_info()
{

	if(!SHOW_DEBUG)return "";

	$query_time = number_format($GLOBALS['db']->queryTime,6);

	if($GLOBALS['begin_run_time']==''||$GLOBALS['begin_run_time']==0)
	{
		$run_time = 0;
	}
	else
	{
		if (PHP_VERSION >= '5.0.0')
		{
			$run_time = number_format(microtime(true) - $GLOBALS['begin_run_time'], 6);
		}
		else
		{
			list($now_usec, $now_sec)     = explode(' ', microtime());
			list($start_usec, $start_sec) = explode(' ', $GLOBALS['begin_run_time']);
			$run_time = number_format(($now_sec - $start_sec) + ($now_usec - $start_usec), 6);
		}
	}

	/* 内存占用情况 */
	if (function_exists('memory_get_usage'))
	{
		$unit=array('B','KB','MB','GB');
		$size = memory_get_usage();
		$used = @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		$memory_usage = lang("MEMORY_USED",$used);
	}
	else
	{
		$memory_usage = '';
	}

	/* 是否启用了 gzip */
	$enabled_gzip = (app_conf("GZIP_ON") && function_exists('ob_gzhandler'));
	$gzip_enabled = $enabled_gzip ? lang("GZIP_ON") : lang("GZIP_OFF");

	$str = lang("QUERY_INFO_STR",$GLOBALS['db']->queryCount, $query_time,$gzip_enabled,$memory_usage,$run_time);

	foreach($GLOBALS['db']->queryLog as $K=>$sql)
	{
		if($K==0)$str.="<br />SQL语句列表：";
		$str.="<br />行".($K+1).":".$sql;
	}

	return "<div style='width:940px; padding:10px; line-height:22px; border:1px solid #ccc; text-align:left; margin:30px auto; font-size:14px; color:#999; height:150px; overflow-y:auto;'>".$str."</div>";
}

function lang($key)
{
	$args = func_get_args();//取得所有传入参数的数组
	$key = strtoupper($key);
	if(isset($GLOBALS['lang'][$key]))
	{
		if(count($args)==1)
			return $GLOBALS['lang'][$key];
		else
		{
			$result = $key;
			$cmd = '$result'." = sprintf('".$GLOBALS['lang'][$key]."'";
			for ($i=1;$i<count($args);$i++)
			{
				$cmd .= ",'".$args[$i]."'";
			}
			$cmd.=");";
			eval($cmd);
			return $result;
		}
	}
	else
		return $key;
}


function adv_preg($r){
	return $GLOBALS['adv']['code'];
}


/**
 * 下个还款日
 */
function next_replay_month($time,$m=1){
	$str_t = to_timespan(to_date($time)." ".$m." month ");
	return $str_t;
}

/**
 * 判断审核的资料是否过期
 */
function user_info_expire($u_info){
	$time = TIME_UTC;
	$expire_time = 6*30*24*3600;
	
	if($u_info['workpassed']==1){
		if(($time - $u_info['workpassed_time']) > $expire_time){
			$expire['workpassed_expire'] = 1;
		}
	}
	if($u_info['incomepassed']==1){
		if(($time - $u_info['incomepassed_time']) > $expire_time){
			$expire['incomepassed_expire'] = 1;
		}
	}
	if($u_info['creditpassed']==1){
		if(($time - $u_info['creditpassed_time']) > $expire_time){
			$expire['creditpassed_expire'] = 1;
		}
	}
	if($u_info['residencepassed']==1){
		if(($time - $u_info['residencepassed_time']) > $expire_time){
			$expire['residencepassed_expire'] = 1;
		}
	}
	
	return $expire;
}

/*格式化统计*/
function format_conf_count($number){
	if($number=="")
		return "0<em>.00</em>";
		
	$attr_number = explode(".",$number);
	
	return $attr_number[0]."<em>".(trim($attr_number[1]) == "" ? ".00" : ".".$attr_number[1])."</em>";
}



?>