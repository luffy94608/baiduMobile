'use strict';

angular.module('weChatHrApp.service').factory('cache', ['$cacheFactory', function ($cacheFactory) {
    return $cacheFactory('cache');
}]);