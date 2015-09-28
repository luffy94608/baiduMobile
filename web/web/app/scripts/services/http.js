/**
 * Created by su on 14-3-17.
 */

'use strict';

var service = angular.module('weChatHrApp.service');
service.factory('httpInterceptor',function($q){
    return function(promise){
        return promise.then(function(response){
            return response;
        },function(response){
            return $q.reject(response);
        });
    };
});
service.factory('httpProtocol',function($http,$q,$location,toast,$rootScope,APP_DEBUG,$window,string,util){

    var angularUrl = "/api/angular";
    var halloUrl = "/api/hollo/";
    var POST_TYPE = {
        SEND_ERROR:{
            prefix: angularUrl,
            url:'script-error'
        },
        GET_TRAVEL_LIST:{
            prefix:halloUrl,
            url :"get-travel-list"
        },
        GET_TRAVEL_DETAIL:{
            prefix:halloUrl,
            url :"get-travel-detail"
        }

};


    var direct = false;
    $rootScope.serverError = false;
    $rootScope.serverMaintenance = false;

    var httpFun = function(data,type,analytics,withoutLoading){
        var ignoreLoadingBar = false;
        var url = type.prefix + type.url;
        if(analytics){
            url = url+"?"+analytics;
        }
        if(withoutLoading){
            ignoreLoadingBar =true;
        }

        data.trace_type = 'ajax';
        data.refer = $window.location.href;

        return $http({method:'POST',data:data,url:url,ignoreLoadingBar:ignoreLoadingBar}).then(function(response){
            if (typeof response.data === 'object'){
                if(APP_DEBUG){
                    window.console.log('=========='+url+'===========\n');
                    window.console.log(response.data);
                }
                if(response.data.code === 401){
                    window.location.replace(response.data.url);
                    return $q.reject(response.data);
                }
                if(response.data.code === 301){
                    $location.url(response.data.redirect_url).replace();
                    return $q.reject(response.data);
                }
                if(response.data.code === 3011){
                    window.location.replace(response.data.redirect_url);
                    return $q.reject(response.data);
                }
                if(response.data.code === 302){
                    $window.history.back();
                    return $q.reject(response.data);
                }
                if(response.data.code === 503){
                    $rootScope.serverMaintenance = true;
                    return $q.reject(response.data);
                }

                if(response.data.code !== 0 ){
                    if(APP_DEBUG && response.data && response.data.error){
                        toast.showToast(response.data.error);
                    }
                    if( response.data && response.data.desc){
                        toast.showToast(response.data.desc);
                    }
                    return $q.reject(response);
                }

                $rootScope.serverError = false;
                $rootScope.serverMaintenance = false;
                return response.data.data;
            }
            return response.data;

        },function(response){
            if(APP_DEBUG){
                window.console.log(response);
            }
            if(!direct ){
                if(response.status === 500){
                    $rootScope.serverError = true;
                }else{
                    toast.showToast('服务器错误');
                }
            }
            $q.reject(response);
        });
    };


    return{
        wpost:httpFun,
        POST_TYPE:POST_TYPE
    };
});

