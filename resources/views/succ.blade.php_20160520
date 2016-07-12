<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>领取成功</title>
    {!! HTML::style('css/ad.css') !!}
</head>
<body>
<input type='hidden' name='accountID' value="{{ $accountID }}"></input>
<input type='hidden' name='accessToken' value="{{ $accessToken }}"></input>
<div class="succ">
    <a href="/clause" class="l">意外险保障条款</a>
    <a href="/claims" class="r">理赔流程</a>
    <img src="{{url()}}/images/25276645.jpg" class="bk">
    <img src="{{ $sponsorLogo }}" class="logo">
    <h1>{{ $sponsorName }}</h1>
    <h3>成功为您购买50万意外保障</h3>
    <a class="exit" href="javascript:;">无操作<span>30</span>秒后自动关闭</a>
    <p>为了保障您的保险权益,请确保 IMEI: <span>{{ $imei }}</span> 该设备在驾驶过程中处于开启状态</p>
</div>
{!! HTML::script('js/lib/jquery-2.2.1.min.js') !!}
{!! HTML::script('js/lib/hammer.min.js') !!}
{!! HTML::script('js/lib/hammer-time.js') !!}
<script>
    $(function(){
        countDown();
        $(".exit").click(function(){
            closeApp();
        })
    });
    function countDown(){
        var num=parseInt($(".exit span").html());
        if(num>0){
            var reduce=num-1;
            $(".exit span").html(reduce);
            setTimeout(function(){countDown();},1000);
        }else{
            closeApp();
        }
    }
    function closeApp(){
        //关闭app方法
        android.finish();
    }
    android.receive('true');
    setTimeout(function (){android.saveDate($('input[name="accountID"]').val(),$('input[name="accessToken"]').val())},3000);
</script>
</body>
</html>