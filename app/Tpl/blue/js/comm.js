// JavaScript Document

/* 头部 */
var topHeight;
window.onscroll = function () {  
	topHeight = $(document).scrollTop();
	if(topHeight > 125){
		$('.header').css('height','40px');
		$('.logo').css('margin-top','5px');
		$('.logo img').css('height','30px');
		$('.header ul').css('margin-top','10px');
		
		$('.nav_bar_chird').css('top','10px');
	}
	else{
		$('.header').css('height','85px');
		$('.logo').css('margin-top','15px');
		$('.logo img').css('height','100%');
		$('.header ul').css('margin-top','30px');
		
		$('.nav_bar_chird').css('top','-10px');
	}
};

$(document).ready(function() {
	$('.weixin').hover(
		function(){
			$('.weixin_QR').show();
		},
		function(){
			$('.weixin_QR').hide();
		}
	)
	$('.nav_bar').hover(
		function(){
			$(this).find('ul').show();
		},
		function(){
			$(this).find('ul').hide();
		}
	)
})
/* 头部结束 */