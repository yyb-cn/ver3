function showNextNav(a){
	var show = $(a).next('ul').is(':hidden');
	if(show){
		$(a).next('ul').slideDown();
	}
	else{
		$(a).next('ul').slideUp();
	}
}

var bodyWidth;

$(window).ready(function() {
    bodyWidth = $(document.body).outerWidth(true);
	if(bodyWidth <= 1320){
		$('.header').hide();
		$('.header_top').show();
		$('.wraper').css('margin-left',0);
	}
	else{
		$('.header_top').hide();
		$('.header').show();
		$('.wraper').css('margin-left',200);
	}
	if(bodyWidth <= 1850){
		$('.wraper').css('width',1100);
	}
	
	var bodyHeight = $(window).height();
	bodyHeight = bodyHeight - 100;
	$('.header').css('height',bodyHeight);
});

$(window).resize(function() {
	bodyWidth = $(document.body).outerWidth(true);
	if(bodyWidth <= 1320){
		$('.header').hide();
		$('.header_top').show();
		$('.wraper').css('margin-left',0);
	}
	else{
		$('.header_top').hide();
		$('.header').show();
		$('.wraper').css('margin-left',200);
	}
	if(bodyWidth <= 1850){
		$('.wraper').css('width',1100);
	}
	else{
		$('.wraper').css('width',1650);
	}
	//  $('.logo').html($(window).width());
	//  alert($(window).width()); //浏览器时下窗口可视区域宽度 
	//  alert($(window).height()); //浏览器时下窗口可视区域高度 
	//  alert($(document).height()); //浏览器时下窗口文档的高度 
	//  alert($(document.body).height());//浏览器时下窗口文档body的高度 
	//  alert($(document.body).outerHeight(true));//浏览器时下窗口文档body的总高度 包括border padding margin 
	//  
	//  alert($(document).width());//浏览器时下窗口文档对于象宽度 
	//  alert($(document.body).width());//浏览器时下窗口文档body的高度 
	//  alert($(document.body).outerWidth(true));//浏览器时下窗口文档body的总宽度 包括border padding margin 
})


function getStyle(obj,name)
{
	if(obj.currentStyle)
	{
		return obj.currentStyle[name]
	}
	else
	{
		return getComputedStyle(obj,false)[name]
	}
}

function getByClass(oParent,nClass)
{
	var eLe = oParent.getElementsByTagName('*');
	var aRrent  = [];
	for(var i=0; i<eLe.length; i++)
	{
		if(eLe[i].className == nClass)
		{
			aRrent.push(eLe[i]);
		}
	}
	return aRrent;
}

function startMove(obj,att,add)
{
	clearInterval(obj.timer)
	obj.timer = setInterval(function(){
	   var cutt = 0 ;
	   if(att=='opacity')
	   {
		   cutt = Math.round(parseFloat(getStyle(obj,att)));
	   }
	   else
	   {
		   cutt = Math.round(parseInt(getStyle(obj,att)));
	   }
	   var speed = (add-cutt)/4;
	   speed = speed>0?Math.ceil(speed):Math.floor(speed);
	   if(cutt==add)
	   {
		   clearInterval(obj.timer)
	   }
	   else
	   {
		   if(att=='opacity')
		   {
			   obj.style.opacity = (cutt+speed)/100;
			   obj.style.filter = 'alpha(opacity:'+(cutt+speed)+')';
		   }
		   else
		   {
			   obj.style[att] = cutt+speed+'px';
		   }
	   }
	   
	},30)
}

  window.onload = function()
  {
	  var oDiv = document.getElementById('playBox');
	  var oPre = getByClass(oDiv,'pre')[0];
	  var oNext = getByClass(oDiv,'next')[0];
	  var oUlBig = getByClass(oDiv,'oUlplay')[0];
	  var aBigLi = oUlBig.getElementsByTagName('li');
	  var oDivSmall = getByClass(oDiv,'smalltitle')[0]
	  var aLiSmall = oDivSmall.getElementsByTagName('li');
	  
	  function tab()
	  {
	     for(var i=0; i<aLiSmall.length; i++)
	     {
		    aLiSmall[i].className = '';
	     }
	     aLiSmall[now].className = 'thistitle'
	     startMove(oUlBig,'left',-(now*aBigLi[0].offsetWidth))
	  }
	  var now = 0;
	  for(var i=0; i<aLiSmall.length; i++)
	  {
		  aLiSmall[i].index = i;
		  aLiSmall[i].onclick = function()
		  {
			  now = this.index;
			  tab();
		  }
	 }
	  oPre.onclick = function()
	  {
		  now--
		  if(now ==-1)
		  {
			  now = aBigLi.length;
		  }
		   tab();
	  }
	   oNext.onclick = function()
	  {
		   now++
		  if(now ==aBigLi.length)
		  {
			  now = 0;
		  }
		  tab();
	  }
	  var timer = setInterval(oNext.onclick,3000) //滚动间隔时间设置
	  oDiv.onmouseover = function()
	  {
		  clearInterval(timer)
	  }
	   oDiv.onmouseout = function()
	  {
		  timer = setInterval(oNext.onclick,3000) //滚动间隔时间设置
	  }
  }