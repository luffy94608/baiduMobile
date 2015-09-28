<?php
require_once dirname(__FILE__) . '/../application/configs/SystemConfig.php';
session_start();
$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$config = Yaf_Registry::get('config');
$imgUrl=$config->img->host;
$cdnUrl=$config->url->host;
$aesUid='';
$aesOpenId='';
?>


<!doctype html >
<html class="no-js" ng-app="weChatHrApp">
<head>
    <script>
        globalConfig ={
            APP_CDN_ROOT:'<?php  echo $cdnUrl?>',
            IMG_HOST:'<?php  echo $imgUrl?>',
            APP_HOST:'<?php echo $config['url']['host']; ?>',
            APP_URL:'<?php echo $config['url']['mobile']; ?>',
            AES_UID:'<?php echo  $aesUid; ?>',
            AES_OPENID:'<?php echo  $aesOpenId; ?>'
        };
    </script>
    <title>哈罗同行</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="哈罗同行，社区互助，一路同行">
    <meta name="keywords" content="哈罗同行，社区互助，一路同行,班车">
    <meta name="format-detection" content="telephone=no" />
    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <meta charset="utf-8">
    <!--  page loading css  -->
    <style type="text/css">
        .timer-loader{height:77px;width:231px;background:url("../images/loading-page/sprite-min.png") no-repeat -5px 0;background-size:100%;display:inline-block;position:relative;top:-35px;-webkit-animation:carRun 1.8s steps(18) infinite 0s;animation:carRun 1.8s steps(18) infinite 0s;}@-moz-keyframes carRun{0%{background-position:5px 0}100%{background-position:5px -1476px}}@-webkit-keyframes carRun{0%{background-position:5px 0}100%{background-position:5px -1476px}}@keyframes carRun{0%{background-position:5px 0}100%{background-position:5px -1476px}}
    </style>

    <!-- build:css /styles/vendor.css -->
    <!-- bower:css -->
    <link rel="stylesheet" href="/bower_components/angular-loading-bar/src/loading-bar.css" />
    <link rel="stylesheet" href="/bower_components/angular-carousel/dist/angular-carousel.css">
    <!-- endbower -->
    <!-- endbuild -->

    <!-- build:css /styles/style.css-->
    <link rel="stylesheet" href="/styles/style.css">
    <link rel="stylesheet" href="/styles/main.css">
    <!-- endbuild -->

</head>
<body class="pstn-reltv" ng-class="{'bg-white':bg_white,'bg-light-blue':bg_light_blue}"  ng-cloak >

<!--  page loading   -->
<div id="loading_page" style="width: 100%;height:100%;position: fixed;top:0;left:0;margin: 0;display: table;;z-index: 9999999;background: #41BAD9;text-align: center;">
    <div style="display: table-cell;text-align: center;vertical-align: middle;width: 100%;height:100%;">
        <span class="timer-loader" id=""></span>
    </div>
</div>
<script type="text/javascript">
    //loading动画兼容性设置
    var isAndroid=function(){
        var queryString = function () {
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
        var query = queryString();
        if(query &&  query['ua']  ){
            userAgent = query['ua'].toLowerCase();
        }
        return userAgent.indexOf('android') > -1 || userAgent.indexOf('linux') > -1
    };
    var initAnimation=function(){
        var tposition=['0 -82px','0 -164px','0 -246px','0 -328px','0 -410px','0 -492px','0 -574px','0 -656px','0 -738px','0 -820px','0 -902px','0 -984px','0 -1066px','0 -1148px','0 -1230px','0 -1312px','0 -1394px','0 -1476','0 0'];
        var tmax=tposition.length;
        var ti=0;
        window.loadingInterval=setInterval(function(){
            document.getElementsByClassName('timer-loader')[0].style.backgroundPosition=tposition[ti];
            ++ti;
            if(ti>=tmax){
                ti=0;
            }
            console.log(ti)
        },100);
    };
    if(isAndroid()){
        initAnimation();
    }
</script>

<!-- Add your site or application content here -->
<div ng-hide="serverError || serverMaintenance"  class="page-container" >

    <div id="container" class="container"   id="view_container"  ng-view="" ></div>

</div>

<div  ng-show="serverError || serverMaintenance" class='server-error'>
    <div class="error-logo"></div>
    <div class="server-error-content">
        <p ng-show="serverMaintenance" class="tip-common-words">为了给您提供更好的服务，正在努力升级中，过一会儿再来看吧</p>
        <p ng-show="serverError && !serverMaintenance" class="tip-common-words">服务器出了点小问题，正在努力恢复中</p>
        <p  ng-show="serverError && !serverMaintenance" class="refresh">
            <button onclick="window.location.reload();" class="refresh-btn "></button>
            <span class="refresh-title">刷新一下试试吧</span>
        </p>
    </div>
</div>

<toast-container></toast-container>
<remind-container></remind-container>

<script type="text/javascript" src="http://api.map.baidu.com/api?v=1.5&ak=bfZAP1awSTeGq0Izhr1QK3YZ"></script>
<script type="text/javascript" src="http://api.map.baidu.com/library/InfoBox/1.2/src/InfoBox_min.js"></script>
<script src="http://cdnjs.gtimg.com/cdnjs/libs/zepto/1.1.4/zepto.min.js"></script>

<!-- build:js /scripts/header.js -->
<!-- bower:js -->
<script src="/bower_components/angular/angular.min.js"></script>
<!-- endbower -->
<!-- endbuild -->

<!-- build:js /scripts/vendor.js -->
<!-- bower:js -->
<script src="/bower_components/angular-sanitize/angular-sanitize.js"></script>
<script src="/bower_components/angular-route/angular-route.js"></script>
<script src="/bower_components/angular-animate/angular-animate.js"></script>
<script src="/bower_components/angular-loading-bar/src/loading-bar.js"></script>
<script src="/bower_components/angular-touch/angular-touch.js"></script>
<script src="/bower_components/angular-carousel/dist/angular-carousel.js"></script>
<!-- endbower -->
<!-- endbuild -->

<!-- build:js({.tmp,app}) /scripts/scripts.js -->
<script src="/scripts/app.js"></script>
<script src="/scripts/services/util.js"></script>
<script src="/scripts/services/http.js"></script>
<script src="/scripts/services/config.js"></script>
<script src="/scripts/services/cache.js"></script>
<script src="/scripts/services/string.js"></script>
<script src="/scripts/directives/wcontact-remind.js" ></script>
<script src="/scripts/directives/toast.js"></script>
<script src="/scripts/directives/ngTouch.js"></script>
<script src="/scripts/directives/pull-up-refresh.js" ></script>

<script src="/scripts/controllers/error.js"></script>
<script src="/scripts/controllers/hint.js"></script>

<script src="/scripts/controllers/list.js"></script>
<script src="/scripts/controllers/map.js"></script>
<!-- endbuild -->


</body>

</html>
