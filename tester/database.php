<?php
use Testify\Testify;

require '../framework/bootstrap.inc.php';
require IA_ROOT . '/framework/library/testify/Testify.php';

load()->func('communication');

$tester = new Testify('测试数据库备份');

$tester->test('数据库备份', function(){
	global $tester;
	$loginurl = 'http://pros.we7.cc/web/index.php?c=user&a=login&';
	$response = ihttp_get($loginurl);
	preg_match('/name="token" value="(.*?)" type="hidden"/i', $response['content'], $matchs);
	$token = $matchs['1'];
		$username = 'admin';
		$password = '123456';
		$submit = '登录';
		$post = array(
				'username' => $username,
				'password' => $password,
				'submit' => $submit,
				'token' => $token
				);
	$responses = ihttp_request($loginurl, $post);
	$login_success = '欢迎回来，admin。';
	$result = strpos($responses['content'],$login_success);
	if (empty($result)) {
		$sign = false;
	} else {
		$sign = true;
	}
	$cookiejar =$responses['headers']['Set-Cookie'];
	$tester->assertEquals($sign, true);
	
	$backupurl = 'http://pros.we7.cc/web/index.php?c=system&a=database&do=backup&status=1&start=2';
	$response1 = ihttp_request($backupurl,'',array(
		'CURLOPT_COOKIE' => implode(';' , $cookiejar)));
	preg_match('/href="\.\/index.php\?c=system&a=database&do=backup&last_table=(.*?)&index=(.*?)&series=(.*?)&volume_suffix=(.*?)&folder_suffix=(.*?)&status=1"/i', $response1['content'], $match);
	$series = $match['3'] -1;
	$volume_suffix = "volume-".$match['4']."-".$series.".sql";
	$folder_suffix = $match['5'];
	$dir = IA_ROOT . '/data/backup';
	function rmdi_r($dirname) {
		$data = array();
		//判断是否为一个目录，非目录直接关闭
		if (is_dir($dirname)) {
			//如果是目录，打开他
			$name=opendir($dirname);
			//使用while循环遍历
			while ($file=readdir($name)) {
				//去掉本目录和上级目录的点
				if ($file=="." || $file=="..") {
					continue;
				}
				//如果目录里面还有一个目录，再次回调
				if (is_dir($dirname."/".$file)) {
					$result = rmdi_r($dirname."/".$file);
					$data = array(
							'dirname'=> $result['dirname'],
							'file'=> $result['file']
							);
				}
				if (is_file($dirname."/".$file)) {
					$data = array(
							'dirname'=> $dirname,
							'file'=> $file
							);
				}
			}
		//遍历完毕关闭文件
		closedir($name);
		return $data;
		}
	}
	$data = rmdi_r($dir);
	$start = strripos($data['dirname'],'/')+1;
	$dirname = substr($data['dirname'], $start);
	if ($dirname==$folder_suffix) {
		$backup_dir_sign = true;
	} else {
		$backup_dir_sign = false;
	}
	if ($data['file']==$volume_suffix) {
		$backup_file_sign = true;
	} else {
		$backup_file_sign = false;
	}
	$backup_success = "正在导出数据, 请不要关闭浏览器, 当前第 1 卷.";
	$result1 = strpos($response1['content'],$backup_success);
	if (empty($result1)) {
		$backup_sign = false;
	} else {
		$backup_sign = true;
	}
	$tester->assertEquals($backup_dir_sign, true);
	$tester->assertEquals($backup_file_sign, true);
	$tester->assertEquals($backup_sign, true);
	
});
	$tester->run();