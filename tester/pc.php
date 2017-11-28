<?php
use Testify\Testify;
define('TEST','test');
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/framework/library/testify/Testify.php';

load()->func('communication');
load()->model('account');

$tester = new Testify('微擎1.x测试用例');
$tester->test('添加pc', function() {
	/* @var $pc PcTable*/
	$pc = table('webapp');
	$uniacid = $pc->create(array('name'=>'pc2','description'=>'pc2'));

});

$tester->run();