//获取url路径上的参数
function GetQueryString(name) {
   var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)","i");
   var r = window.location.search.substr(1).match(reg);
   if (r!=null) return (r[2]); return null;
}

//这里计算倒计时

function timer(k)
{	
    var ts = k;//计算剩余的毫秒数   
    var dd = parseInt(ts/60/60/60/24,10);//计算剩余的天数
    var hh = parseInt(ts/60/60/60%24,10);//计算剩余的小时数
    var mm = parseInt(ts/60/60%60, 10);//计算剩余的分钟数
    var ss = parseInt(ts/60%60,10);//计算剩余的秒数
    var ms = parseInt(ts%60,10);//计算剩余的毫秒数
    dd = checkTime(dd);
    hh = checkTime(hh);
    mm = checkTime(mm);
    ss = checkTime(ss);
    ms = checkTime(ms);
    var res =dd+'天'+hh+'小时'+mm+'分'+ss+'秒'; 
    if(k<=0){
        location.reload('true');
    }
   return res;
}
function checkTime(i)
{
    if (i < 10){ i = "0" + i;} return i;
}

function clock(b,newWin){											
	b--;
	a = timer(b);
	var str = '<a href="goods/goods.html?aid='+newWin[i].aid+'"><li class="mui-ellipsis"><img src="' + newWin[i].thumb + '" alt="" width="95%" /><div><span>第(' + newWin[i].section + ')期' + newWin[i].name + '</span><span class="green">' + a + '</span></div></li></a>';
	$('.three ul').append(str);
	};

var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?1ae2bf3e81df474afe7b96df997e2bf0";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();

  
function getCountDown(timestamp){
                var nowTime = new Date();
                var endTime = new Date(timestamp);

                var t = endTime.getTime() - nowTime.getTime();
                if (t==0) {
                  window.location.reload();
                }
               var d=Math.floor(t/1000/60/60/24);
                var hour=Math.floor(t/1000/60/60%24);
                   var min=Math.floor(t/1000/60%60);
                   var sec=Math.floor(t/1000%60);
    
                if (hour < 10) {
                     hour = "0" + hour;
                }
                if (min < 10) {
                     min = "0" + min;
                }
                if (sec < 10) {
                     sec = "0" + sec;
                }
                var countDownTime = d+"天"+hour + "小时" + min + "分" + sec+"秒";            
          return countDownTime;
        }