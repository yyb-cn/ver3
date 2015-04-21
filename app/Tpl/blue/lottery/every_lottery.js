/*******************************************/
/**      author:  hoho                   **/
/**      http://www.thinkcart.net        **/
/******************************************/

//定义参数
var index = 1,              //当前选中对象的位置
    fast,                   //在哪个位置开始加速
    num   = 8,              //共有多少个抽奖对象
    cycle,                  //转动多少圈
    speed = 300,            //开始时速度
    flag  = false,          //正在抽奖标志
    lucky,                  //中奖号码，实际应用由后台产生
    award,                  //奖品名称
    lottery;                //抽奖对象

//开始抽奖
function start_lottery(){
    var score_id=$("#score_id").val();
     if(score_id==''){
	  alert('请登录用户，大奖欢迎你！');
	    return false;
	 }
	 
    $.ajax({
        url: 'index.php?ctl=increase&act=every_lottery',
        type: "post",
        data:null,
        dataType: "json",
        timeout: 20000,
        cache: false,
        beforeSend: function(){// 提交之前
      },
        error: function(){//出错
            flag=false;
        },
		
        success: function(res){//成功
	
            if(typeof(res.award_id)!='undefined'){
                lucky = res.award_id;    //中奖号码
                award = res.award_name;  //奖品名称
              // show_lottery();
				    alert('恭喜您获得：'+award+'%的加息劵');
            }else{
                flag=false;
                alert('今天已经抽奖！明天更多丰富礼品等着你来拿');
            }
        }
    });
}
