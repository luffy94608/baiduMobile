/**
 * Created by su on 14-3-18.
 */
'use strict';

angular.module('weChatHrApp.directive')
.service('remind',['$rootScope',function($rootScope){
    this.show = function(str){
        str = !!str ? str : '请点击这里发送给朋友,<br/>或分享到朋友圈';
        this.html = str;
        $rootScope.$broadcast('remind-new');
    };
}])
.directive('remindContainer',['remind','$sce',function(remind){
    return {
        replace: true,
        restrict: 'EA',
        scope: true,
        link:function (scope, elm, attrs){
            scope.showHint = false;
            function showRemind(){
                scope.showHint = true;
                scope.html = remind.html;
            }
            scope.$on('remind-new', function () {
                showRemind();

            });
            scope.hideRemind=function(){
                scope.showHint = false;
            }
        },
        //controller:['$scope',function($scope){
        //    $scope.showHint = false;
        //    $scope.hideRemind=function(){
        //        $scope.showHint = false;
        //    }
        //}],
        template:'<div  class="ng-hide js_toaster_content" id="remind" ng-show="showHint"  ng-click="hideRemind();"><div id="remind-1" ng-bind-html="html"></div></div>'
    };
}]);