<?php
use Testify\Testify;
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/framework/library/testify/Testify.php';

load()->func('communication');
load()->model('message');
load()->model('user');

$tester = new Testify('测试消息推送');
$tester->test('测试消息推送', function() {

//	message_notify(MESSAGE_REGISTER_TYPE, '平台用户注册', 1, 1, array());
//	message_notify(MESSAGE_ORDER_TYPE, '创建订单', 1, '100225111', array('product'=>'模块', 'amount'=>100));
	message_load_in_notice(1);

});

$tester->run();