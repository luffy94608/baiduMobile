/**
 * Created by su on 14-3-17.
 */

'use strict';

var config_model = angular.module('weChatHrApp.service');

var config_dic = {
    APP_NAME:'哈罗班车',
    APP_DEBUG:false,
    APP_IMPORT_ENABLE:true,
    APP_CDN_ROOT:globalConfig.APP_CDN_ROOT,
    IMG_HOST:globalConfig.IMG_HOST,
    APP_HOST:globalConfig.APP_HOST,
    APP_URL:globalConfig.APP_URL,
    AES_UID:globalConfig.AES_UID,
    AES_OPEN_ID:globalConfig.AES_OPEN_ID
};

angular.forEach(config_dic,function(key,value) {
    config_model.constant(value,key);
});
