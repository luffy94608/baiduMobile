/**
 * xiufei.lu
 */
'use strict';

angular.module('weChatHrApp.directive')
    .service('confirm',['$rootScope',function($rootScope){
        this.show = function(opts){
            this.opts={
              'title':'您有未支付的订单',
              'cancelTitle':'取消',
              'confirmTitle':'去支付'
            };
            angular.extend(this.opts,opts);
            $rootScope.$broadcast('confirm-new');
        };
    }])
    .directive('confirmContainer',['confirm',function(confirm){
        return {
            replace: true,
            restrict: 'EA',
            scope: true,
            link:function (scope, elm, attrs){
                scope.showConfirm = false;
                function show(){
                    scope.showConfirm = true;
                    scope.info = confirm.opts;
                }
                scope.$on('confirm-new', function () {
                    show();
                });
                scope.hide=function(){
                    scope.showConfirm = false;
                };
                scope.confirm=function(){
                    alert(111)
                }
            },

            template:"<div class='hl-confirm ng-hide' ng-show='showConfirm' > <div class='hl-confirm-content' > <p class='hl-confirm-title'>{{info.title}}</p> <div class='hl-confirm-button'> <button class='box-flex-1 border-right-grey' ng-click='hide()' >{{info.cancelTitle}}</button> <button class='box-flex-1' ng-click='confirm()'>{{info.confirmTitle}}</button> </div> </div> </div>"
        };
    }]);