/**
 * Created by su on 14-3-14.
 */

'use strict';

angular.module('weChatHrApp.service',[]);
angular.module('weChatHrApp.directive',['ngAnimate']);
angular.module('weChatHrApp', [
    'ngSanitize',
    'ngRoute',
    'ngAnimate',
    'ngTouch',
    'angular-carousel',
    'weChatHrApp.directive',
    'weChatHrApp.service',
    'chieffancypants.loadingBar'

]).config(['$httpProvider',function($httpProvider) {
    // Use x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

    /**
     * The workhorse; converts an object to x-www-form-urlencoded serialization.
     * @param {Object} obj
     * @return {String}
     */
    var param = function(obj) {
        var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

        for(name in obj) {
            value = obj[name];

            if(value instanceof Array) {
                for(i=0; i<value.length; ++i) {
                    subValue = value[i];
                    fullSubName = name + '[' + i + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if(value instanceof Object) {
                for(subName in value) {
                    subValue = value[subName];
                    fullSubName = name + '[' + subName + ']';
                    innerObj = {};
                    innerObj[fullSubName] = subValue;
                    query += param(innerObj) + '&';
                }
            }
            else if(value !== undefined && value !== null){
                query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
            }
        }

        return query.length ? query.substr(0, query.length - 1) : query;
    };

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function(data) {
        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];

    $httpProvider.responseInterceptors.push('httpInterceptor');

}])
    .config(['$routeProvider','$sceDelegateProvider','cfpLoadingBarProvider','$locationProvider',function ($routeProvider,$sceDelegateProvider,cfpLoadingBarProvider,$locationProvider) {
        $locationProvider.html5Mode(true);
        $routeProvider
            .when('/', {
                templateUrl: '/views/list.html',
                controller: 'ListCtrl',
                resolve:{
                    initData:['$route','httpProtocol',function($route,httpProtocol){
                        $route.current.title='线路列表';
                        return  httpProtocol.wpost({},httpProtocol.POST_TYPE.GET_TRAVEL_LIST);
                    }]
                }
            })
            .when('/map', {
                templateUrl: '/views/map.html',
                controller: 'MapCtrl',
                resolve:{
                    initData:['$route','httpProtocol',function($route,httpProtocol){
                        $route.current.title='班车位置';
                        var $params=$route.current.params;
                        var lat=$params['lat'];
                        var lng=$params['lng'];
                        return  httpProtocol.wpost({lat:lat,lng:lng},httpProtocol.POST_TYPE.GET_TRAVEL_LIST);
                    }]
                }
            })
            .when('/hint/', {
                templateUrl: '/views/hint.html',
                controller: 'HintCtrl',
                resolve:{
                    initTitle:['$route','$rootScope',function($route,$rootScope){
                        $route.current.title='温馨提示';
                    }]
                }
            })
            .when('/error', {
              templateUrl: '/views/error.html',
              controller: 'ErrorCtrl',
                resolve:{
                    initTitle:['$route','$rootScope',function($route,$rootScope){
                        $route.current.title='穿越了';
                    }]
                }
            })
            .otherwise({
                redirectTo: '/error'
            });
    }])
    .run(['$route','$rootScope','string','httpProtocol','$timeout','$interval','toast','$location','$window','util','APP_CDN_ROOT',function($route,$rootScope,string,httpProtocol,$timeout,$interval,toast,$location,$window,util,APP_CDN_ROOT){

        //处理ios微信中标题不变bug
        var titleRefresh = function () {
            var $body = $('body');
            // hack在微信等webview中无法修改document.title的情况
            var $iframe = $('<iframe src="/favicon.ico" style="visibility: hidden"></iframe>').on('load', function () {
                setTimeout(function () {
                    $iframe.off('load').remove()
                }, 0)
            }).appendTo($body);
        };
        //重置自定义操作绑定的操作 如加载更多 remind 等
        var resetCustomHandle=function(){
            $('#loading_page').hide();//隐藏loading page
            //清空定时器
            if(window.loadingInterval){
                clearInterval(window.loadingInterval);
            }
            $timeout(function(){
                if($location.hash().length == 0){
                    $window.scrollTo(0,0);    //scroll to top of page after each route change
                }
            },50);

            if($route.current.title){
                window.document.title = $route.current.title;
            }else{
                window.document.title = '哈罗同行';
            }
            titleRefresh();
        };

        $rootScope.$on('$routeChangeSuccess', function(){
            resetCustomHandle();
            $interval.cancel($rootScope.timer)
        });

        $rootScope.inWeChat = util.browser.inWeChat;
        $rootScope.serverError = false;
        $rootScope.serverMaintenance = false;

//        样式控制
        $rootScope.bg_white = false;
        $rootScope.bg_light_blue = false;


    }]);
