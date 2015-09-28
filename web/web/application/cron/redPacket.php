<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/2/10
 * Time: 下午4:24
 */
require_once dirname(__FILE__) . '/../configs/SystemConfig.php';

//curl_setopt($ch,CURLOPT_SSLCERT,'/Users/jet/Desktop/Project/wechat_qy/wrm/apiclient_cert.pem');
//curl_setopt($ch,CURLOPT_SSLKEY,'/Users/jet/Desktop/Project/wechat_qy/wrm/apiclient_key.pem');
//curl_setopt($ch,CURLOPT_CAINFO,'/Users/jet/Desktop/Project/wechat_qy/wrm/rootca.pem');

//$info = new WechatPayInfo();
//$info->out_trade_no = WechatRedPacket::getMchBillNumber($info->mch_id,time());
//$info->total_fee = 10;
//$info->attach='test_num:1';
//$info->body = '测试商品简介';
//$info->spbill_create_ip = '192.168.1.166';
//$info->trade_type = 'JSAPI';
//$info->openid = 'oxxUYtxxegG82xOKSlrQf9N0YFMY';
////
//$redPacket = new WechatPay('wxda0556e3b4db9b0f','nsyRYEX2oxfJPrAR8tVcvVBRFGfxZAas','1240986202');
//
////统一下单api
////$redPacket->createPreOrder($info);
////生成qrcode url
//$url = $redPacket->createPayQRcodeUrl('1234567890');

//$model = new HalloModel();
//$info = $model->getCorpInfo();
//echo(json_encode($info));

//$client = new HolloTokenService();
//$res = $client->getVerifyCode('18510628786',0);
//$res = $client->getAccessTokenByPwd('13910840138','aaaaaa');
//$res = $client->getAccessTokenByCode('18510628786','7502');
//$res = $client->refreshToken('03138550034911e4b00c5254002cbe0c','eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiIwMzEzODU1MDAzNDkxMWU0YjAwYzUyNTQwMDJjYmUwYyIsInNpZ25hdHVyZSI6ImRiMDdlMDBhZmEwMTExZTRiMDBjNTI1NDAwMmNiZTBjIn0.kaUBkIDc2nlZtFoGj2_5QRWZ9rOLUmZ18OWSgiLY3Og');
//echo($res);

//bus
$client = new HolloBusService();
//$res = $client->getBusPathList(10,116.318779,39.969424);
//$res = $client->getBusPathDetail('5549cc4dc1626b39b31bb9f7');
//$res = $client->getBusActivityTicket();
//$res = $client->getBusHistoryTicket(1);
//$res= $client->getShuttleList();
//$res= $client->getShuttleTicket();
//$res = $client->getCustomBusPath();
$res = $client->getCustomBusPath();
//$res = $client->deleteCustomBusPath("558d6c1420a6fdda1e56bfce");

//user
//$client = new HolloUserService('','eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiIwMzEzODU1MDAzNDkxMWU0YjAwYzUyNTQwMDJjYmUwYyIsImV4cCI6MTQzMTU3NzA0N30.GWY-L3pYyWFs-cF-MtyDCriBOf3d_95z-k7nnsFeFe0');
//$res = $client->getMissionList();
//$res = $client->getUserInfo();
//$res = $client->updateUserInfo('jet');
//$res = $client->getUserInfo();
//$res = $client->getMyMileage(1);
//$res = $client->getMyBalance(1);
//$res = $client->getMyCoupons(1);

echo($res);

