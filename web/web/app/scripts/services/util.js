/**
 * Created by su on 14-3-14.
 *
 */

/* jslint indent: 4 */

'use strict';

angular.module('weChatHrApp.service')
    .service('util', function ($location,$rootScope) {

        this.queryString = function () {
            // This function is anonymous, is executed immediately and
            // the return value is assigned to QueryString!
            var query_string = {};
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i=0;i<vars.length;i++) {
                var pair = vars[i].split("=");
                // If first entry with this name
                if (typeof query_string[pair[0]] === "undefined") {
                    query_string[pair[0]] = pair[1];
                    // If second entry with this name
                } else if (typeof query_string[pair[0]] === "string") {
                    var arr = [ query_string[pair[0]], pair[1] ];
                    query_string[pair[0]] = arr;
                    // If third or later entry with this name
                } else {
                    query_string[pair[0]].push(pair[1]);
                }
            }
            return query_string;
        };

        var userAgent = navigator.userAgent.toLowerCase();
        var query = this.queryString();
        if(query &&  query['ua']  ){
            userAgent = query['ua'].toLowerCase();
        }

        this.browser =  {
            version: (userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [0,'0'])[1],
            safari: /webkit/.test( userAgent ),
            opera: /opera/.test( userAgent ),
            msie: /msie/.test( userAgent ) && !/opera/.test( userAgent ),
            mozilla: /mozilla/.test( userAgent ) && !/(compatible|webkit)/.test( userAgent ),
            iPhone:userAgent.indexOf('iphone') > -1 || userAgent.indexOf('mac') > -1 || userAgent.indexOf('ipad') > -1,
            android:userAgent.indexOf('android') > -1 || userAgent.indexOf('linux') > -1,
            inWeChat: userAgent.indexOf('micromessenger') > -1,
            inWeibo:userAgent.indexOf('weibo') > -1
        };


        this.getLocationUrl = function(){
            var url = $location.protocol()+'://'+$location.host()+ $location.url();
            return url;
        };

        this.initPopUpAvatar = function(){
            var html = '\
			<div id="popBox" class="gone">\
			    <div class="overlay">\
			    </div>\
			    <div class="popBox-content">\
			        <img src="">\
			    </div>\
			</div>';
            if( $('#popBox').length===0){
                $('body').append(html);
            }

            $('img').unbind('click').bind('click',function(){
                var src = $(this).attr('src');
                window.console.log(src);
                if(src.length<1){
                    return false;
                }
                $('.popBox-content img').attr('src',src);
                //大头像弹出
                $('#popBox').show();
                var c = ( $(window).height() - $('.popBox-content').height() )/2;
                $('.popBox-content').css('top',c);
                $('#popBox').css('opacity','1');
            });
            $('#popBox').unbind('click').click(function(){           //大头像消失  如果只想点黑色部分才消失 选择器是'#popBox .overlay'
                $('#popBox').css('opacity','0');
                setTimeout(function(){
                    $('#popBox').hide();
                },300);
            });
            $('#popBox').off('touchmove').on('touchmove',function(e){  //大头像显示时，页面不滚动
                e.stopPropagation();
                e.preventDefault();
            });
        };
        $.hidePopAvatar=function(){
            $('#popBox').css('opacity','0');
            setTimeout(function(){
                $('#popBox').hide();
            },300);
        };
    });
