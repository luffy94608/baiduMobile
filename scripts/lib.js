/**
 * Created by luffy on 15/9/16.
 */
$(document).ready(function(){
    $.showToast = function(string){
        if(!$('#toast').length){
            $('<div id="toast"><h1></h1></div>').appendTo('body');
        }
        var $toast = $('#toast');
        if(string!=''){
            $('#toast h1').text(string);
            $toast.show();
        }
        if( $toast.hasClass('show') )
            return;
        $toast.addClass('show');
        setTimeout(function(){
            $toast.removeClass('show');
            setTimeout(function(){
                $toast.hide();
            },500);
        },1500);
    };

    //$.ajax({
    //    type:'POST',
    //    url:'http://wxdev.hollo.cn/api/wechat/get-js-sign-Jsonp',
    //    data:{url:location.href.split('#')[0]},
    //    dataType:'jsonp',
    //    jsonp: "callback",
    //    success:function(data){
    //        wx.config({
    //            debug: true,
    //            appId: data.appid,
    //            timestamp: data.timestamp,
    //            nonceStr: data.noncestr,
    //            signature: data.signature,
    //            jsApiList: [
    //                'onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo',
    //                'chooseImage','previewImage','uploadImage','downloadImage',
    //                'hideOptionMenu','showOptionMenu','hideMenuItems','showMenuItems','hideAllNonBaseMenuItem','showAllNonBaseMenuItem','closeWindow','scanQRCode',
    //                'startRecord','stopRecord','onVoiceRecordEnd','playVoice','pauseVoice','stopVoice','onVoicePlayEnd','uploadVoice','downloadVoice','translateVoice','getNetworkType','openLocation','getLocation',
    //                'chooseWXPay'
    //            ]
    //        });
    //        wx.error(function(res){
    //            // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名
    //            window.console.log("wx error data is "+res);
    //        });
    //        wx.ready(function(){
    //            wx.getLocation({
    //                type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
    //                success: function (res) {
    //                    var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
    //                    var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
    //                    var speed = res.speed; // 速度，以米/每秒计
    //                    var accuracy = res.accuracy; // 位置精度
    //                    console.log('latitude'+latitude);
    //                }
    //            });
    //        });
    //
    //    }
    //})
});