<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/12
 * Time: 上午11:06
 */

class CacheKey
{
    const DEFAULT_EXPIRE = 86400;
    const INFO_PINYIN_TABLE = 'i_pinyin_table';
    const INFO_OPENID2UID = 'i_op2u';

    //Account
    const TABLE_ACCOUNT_USER = 't_acc_u';
    const INFO_ACCOUNT_INFO = 'i_acc_i';
    const INFO_ACCOUNT_NAME2ID = 'i_acc_n2id';

    //Data
    const INFO_DATA_INDUSTRY = 'i_data_inds';
    const TABLE_DATA_INDUSTRY = 't_data_inds';

    const INFO_DATA_LOCATION = 'i_data_loc';
    const TABLE_DATA_PROVINCE = 't_data_pro';
    const TABLE_DATA_CITY = 't_data_city';

    //Corp
    const TABLE_CORP_INFO = 't_corp_i';

    //User
    const TABLE_USER_INFO = 't_user_info';
    const INFO_USER_TOKEN = 'i_u_token';
    const INFO_USER_REFRESH_TOKEN = 'i_u_re_token';

    //Reply
    const TABLE_AUTO_REPLY_INFO = 't_au_re_i';

    //Pay
    const TABLE_PAY_INFO = 't_pay_i';

    //QRCode
    const TABLE_QRCODE_INFO = 't_qrcode_i';
    const TABLE_USER_FIRST_LOGIN = 't_u_fir';

    //city
    const INFO_CITY_LIST = 'i_city_l';
} 