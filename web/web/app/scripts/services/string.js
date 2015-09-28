'use strict';

angular.module('weChatHrApp')
    .factory('string', function string() {
        String.prototype.format = function () {
            var args = arguments;
            return this.replace(/{(\d+)}/g, function (match, number) {
                return typeof args[number] != 'undefined' ? args[number] : match;
            });
        };

        return {
            LOGIN_SUCCESS:'登陆成功',
            LOGIN_SUCCESS_NO_BACK:'登陆成功，请返回'
        };
    });
