'use strict';

angular.module('weChatHrApp')
  .controller('ErrorCtrl', function ($rootScope,$route,$scope) {
        $rootScope.serverError = true;
        $rootScope.serverMaintenance = true;
        $rootScope.bg_white = true;
        return false
  });
