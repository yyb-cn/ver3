var href;
var menuName;
$(document).ready(function(){
	$(".menu").find("a").bind("click",function(){
		$(".menu").find("a").removeClass("current");
		href = $(this).attr("href");
		menuName = $(this).html();
		parent.tab.tabHref(href,menuName);
		$(this).addClass("current");
		return false;
	});
	
	//$(".menu").find("a").first().click();
});