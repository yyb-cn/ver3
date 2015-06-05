<?php
// +----------------------------------------------------------------------
// | Fanwe 方维p2p借贷系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class introducModule extends SiteBaseModule
{
	public function index()
	{
		//文峰
		$GLOBALS['tmpl']->display("page/info_wenfeng.html");
		
	}
	public function chengjian()
	{
		//城建三期
		$GLOBALS['tmpl']->display("page/info_chengjian.html");
		
	}
	public function xindai()
	{
		//信贷赢
		$GLOBALS['tmpl']->display("page/info_xindai.html",$cache_id);
		
	}
	public function newyear()
	{
		//2015新年活动
		$GLOBALS['tmpl']->display("page/info_newyear.html");
		
	}
}
?>