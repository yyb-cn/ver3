<?php

class aboutfeeModule  extends SiteBaseModule {
    function index() {
		$GLOBALS['tmpl']->caching = true;
		$GLOBALS['tmpl']->cache_lifetime = 6000;  //首页缓存10分钟
		$cache_id  = md5(MODULE_NAME.ACTION_NAME);	
		if (!$GLOBALS['tmpl']->is_cached("page/aboutfee.html", $cache_id))
		{	
			$info = get_article_buy_uname("aboutfee");
			$GLOBALS['tmpl']->assign("info",$info);
			
			$seo_title = $info['seo_title']!=''?$info['seo_title']:$info['title'];
			$GLOBALS['tmpl']->assign("page_title",$seo_title);
			$seo_keyword = $info['seo_keyword']!=''?$info['seo_keyword']:$info['title'];
			$GLOBALS['tmpl']->assign("page_keyword",$seo_keyword.",");
			$seo_description = $info['seo_description']!=''?$info['seo_description']:$info['title'];
			$GLOBALS['tmpl']->assign("page_description",$seo_description.",");
		}
		$GLOBALS['tmpl']->display("page/aboutfee.html",$cache_id);
    }
	function fengkong(){
		
		$GLOBALS['tmpl']->display("page/index_fengkong.html");
		}
	function mechanism(){
		
		$GLOBALS['tmpl']->display("page/index_mechanism.html");
	}
	function jigou(){
		
		$GLOBALS['tmpl']->display("page/index_jigou.html");
	}
	function about_us_hzhb(){
	
		$GLOBALS['tmpl']->display("page/about_us_hzhb.html");
	}
	function about_us_join(){
	
		$GLOBALS['tmpl']->display("page/about_us_join.html");
	}
	function about_us_pfcf(){
	
		$GLOBALS['tmpl']->display("page/about_us_pfcf.html");
	}
	function about_us_tdjs(){
	
		$GLOBALS['tmpl']->display("page/about_us_tdjs.html");
	}
	function about_us_zzzs(){
	
		$GLOBALS['tmpl']->display("page/about_us_zzzs.html");
	}
}
?>