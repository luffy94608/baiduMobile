'use strict';

angular.module('weChatHrApp')
    .controller('HintCtrl', function (util,AES_UID,$rootScope,$route,$scope,$routeParams,httpProtocol,$location,toast,remind) {
        $scope.title='支付失败';
        var title=$routeParams['hint'];
        if(title)
        {
            $scope.title=title
        }
        $rootScope.bg_white=true;
    });
