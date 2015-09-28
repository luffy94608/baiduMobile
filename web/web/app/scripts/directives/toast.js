'use strict';

angular.module('weChatHrApp')
.service('toast',['$rootScope',function($rootScope){
    this.showToast = function(str){
        if(str && str.length !== 0){
            this.toastStr = str;
            $rootScope.$broadcast('toast-show');
        }
    };
}])
.directive('toastContainer',['$animate','toast', function ($animate,toast) {
    return {
        replace: true,
        scope: true,
        template: '<div id="toast" class="gone" > <h1>{{toastStr}}</h1></div>',
        restrict: 'EA',
        link: function postLink(scope, element, attrs) {
            function showToast(){
                scope.showToast = true;
                $animate.removeClass( element,'gone');
                $animate.addClass( element,'show');
                scope.toastStr = toast.toastStr;
                setTimeout(function(){
                    if(scope.showToast)
                    {
                        scope.showToast = false;
                        $animate.removeClass(element,'show');
                        $animate.addClass(element,'gone');
                    }
                },1500);
            }

            scope.$on('toast-show', function () {
                showToast();
            });
        }
    };
}]);
