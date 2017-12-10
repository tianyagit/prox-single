<?php
use Testify\Testify;
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/framework/library/testify/Testify.php';
load()->func('file');
load()->classs('image');
define('CACHE_FILE_PATH', IA_ROOT . '/data/cache/');

$tester = new Testify('测试file缓存函数');
$tester->test('测试是否开启了file缓存', function() {
	global $_W, $tester;
	$tester->assertEquals($_W['config']['setting']['cache'], 'file');
});
$tester->test('测试写入', function(){
	global $tester;
	//$arr = array(3,5,6,7,8,12,4);
	$arr = 'wsaf232hjjikt2aa';
	$filevalue = cache_write('statda.txt', $arr);
	$tester->assertTrue($filevalue);
});
$tester->test('测试读取', function(){
	global $tester;
	$arr = array(3,5,6,7,8,12,4);
	$filevalue = cache_read('statda');
	$tester->assertEquals($filevalue, $arr);
});
//
$tester->test('测试图片', function() {
	Image::create(__DIR__.'/a.jpg')->resize(150, 150)->crop(10,50,4)
		->saveTo(__DIR__.'/aaa.jpg', 15);
});
////
$tester->test('测试thumb', function (){
	file_image_thumb(__DIR__.'/a.jpg', '', 100);
});
//
$tester->test('测试crop', function (){
	file_image_crop(__DIR__.'/a.jpg', __DIR__.'/ccc.jpg', 100, 50, 4);
});

$tester->test('测试压缩', function (){

	file_image_quality(__DIR__.'/a.jpg', __DIR__.'/ddd.jpg', 'jpg');

});
$tester->test('测试删除数据', function(){
	global $tester;
	$filevalue = cache_delete('stat:todaylock:652');
	$tester->assertTrue($filevalue);
});
$tester->test('测试清空数据', function(){
	global $tester;
	cache_clean();
	$exist = file_tree(CACHE_FILE_PATH);
	$tester->assertFalse($exist);
});
$tester->run();