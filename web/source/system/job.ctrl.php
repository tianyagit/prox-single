<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('job');
$dos = array('list', 'execute', 'display', 'create');
$do = in_array($do, $dos) ? $do : 'display';
if (!defined('IFRAME')) {
	define('IFRAME', 'system');
}
if ($do == 'display') {
	$list = job_list();
	array_walk($list, function(&$item){
		$progress = $item['total'] > 0 ? $item['handled']/$item['total']*100 : 0;
		$item['progress'] = $item['status'] ? 100 : intval($progress);
		$item['create_time'] = date('Y-m-d H:m:s', $item['create_time']);
		return $item;
	});
	template('system/job');
}

if ($do == 'execute') {
	if ($_W['isfounder']) {
		$id = intval($_GPC['id']);
		$result = job_execute($id);
		if (is_error($result)) {
			iajax(1, $result['message']);
		}

		iajax(0,  $result['message']);

	}
}

if ($do == 'create') {
	$result = job_create_delete_account(281);//创建一个删除任务
	var_dump($result);
}



