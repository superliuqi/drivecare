/**
 * Created by Diego on 16/4/26.
 */
$(function(){
    _time();
});
var timeFlag=true;
var time=15;
var el=document.getElementById("clickWarp");//要滑动的元素,发现用jq的画,hammer就会报错...
var hammertime = new Hammer(el);
    hammertime.on('tap', function(ev) {
        getAdvertise();
    });

function getAdvertise(){
    var sponsorUrl = $('input[name="sponsorUrl"]').val();
    $.ajax({
        url:"/receiveInsurance",
        headers:{
            'X-CSRF-TOKEN':$('meta[name="_token"]').attr('content')
        },
        type:'post',
        dataType:'json',
        data:{tokenKey:$('input[name="tokenKey"]').val()},
        success:function(data){
            if(data.ERRORCODE =="0")
            {
                timeFlag=false;
            	$('.success-get iframe').attr('src',sponsorUrl);
                $(".success-get").show();
                $('.clickLayer').show();
                // $(".success-get iframe").load(function(){
                //     if($(window.frames[0].document)){
                //       var video=$(window.frames[0].document).find("video")[0];
                //         if(video.length>0){//如果有视频video
                //             video.bind("ended",function(){
                //                 //视频播放完成后跳转
                //                 window.location.href = "/showSuccess"+window.location.search;
                //             })
                //         }  
                //     }                   
                //     else{//没有视频那就倒计时
                $('.clickLayer').click(function(event) {
                    clearTimeout(countTime);
                    var endtime = time1,
                        stop;
                    if(isOk){
                        setInterval(function(){
                            if (endtime >= 1) {
                                endtime--;
                                $(".time").html(endtime);
                            }else{
                                clearTimeout(stop);
                                $('.advers,.time').hide();
                                $('.close-iframe').show();                    
                            }
                        },1000);
                        isOk = false;
                    }
                });
                countDown();
            }
        },
        error:function(e){
            //alert(e)
//                        $(".zl-ad-title").css("opacity",1);
//                        $(that).css('left',0);
        }
    })
}
//倒计时
var countTime,isOk=true;
var time1=parseInt($(".time").html());
function countDown(){
    if(time1>=1)
    {
        time1--;
        $(".time").html(time1);
        countTime= setTimeout('countDown()',1000);
    }else{
    	window.location.href = "/showSuccess"+window.location.search;
        $(".success-get").hide();
        $('.clickLayer').hide();
        //
        return !1;
    }
}

$(".close-iframe").click(function(){
    $(".success-get").hide();
    $('.clickLayer').hide();
    window.location.href = "/showSuccess"+window.location.search;
});

//什么都不操作的话,15秒后关闭app
function _time(){
    if(timeFlag){
        time=time-1;
        if(time===0){
            closeApp();
            return;
        }
        setTimeout(function(){_time()},1000);
    }
}
//关闭app
function closeApp(){
    console.log("cc");
    android.finish();
}