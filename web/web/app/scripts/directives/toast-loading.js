'use strict';

angular.module('weChatHrApp')
.service('toastLoading',['$rootScope',function($rootScope){
    this.show = function(str){
        str='请耐心等待支付结果';
        if(str && str.length !== 0){
            this.toastStr = str;
            $rootScope.$broadcast('toast-loading-show');
        }
    };
    this.hide=function(){
        $rootScope.$broadcast('toast-loading-hide');
    }
}])
.directive('toastLoadingContainer',['$animate','toastLoading', function ($animate,toastLoading) {
    return {
        replace: true,
        scope: true,
        template: '<div id="toast2" class="gone" > <h1 class="loading-after">{{toastStr}}</h1></div>',
        restrict: 'EA',
        link: function postLink(scope, element, attrs) {
            function showToast(){
                scope.showToast = true;
                $animate.removeClass( element,'gone');
                $animate.addClass( element,'show');
                scope.toastStr = toastLoading.toastStr;
                //setTimeout(function(){
                //    if(scope.showToast)
                //    {
                //        scope.showToast = false;
                //        $animate.removeClass(element,'show');
                //        $animate.addClass(element,'gone');
                //    }
                //},1500);
            }
            function hideToast(){
                scope.showToast = false;
                $animate.removeClass(element,'show');
                $animate.addClass(element,'gone');
            }

            scope.$on('toast-loading-show', function () {
                showToast();
            });
            scope.$on('toast-loading-hide', function () {
                hideToast();
            });
        }
    };
}]);
