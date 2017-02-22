<?php
//测试pdo_get(), pdo_getall(), pdo_getcolumn(), get_slice()函数
define('IN_SYS', true);
require '../framework/bootstrap.inc.php';

$create_table_sql =  "
CREATE TABLE IF NOT EXISTS `ims_test_pdo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
   PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";

pdo_query($create_table_sql);

$data = pdo_fetchall('SELECT * FROM '. tablename('test_pdo'));
if (empty($data)) {
	$test_data = array(
		array(
			'uniacid' => '1',
			'title' => '微擎团队',
			'displayorder' => '1',
			'status' => '1'
		),
		array(
			'uniacid' => '2',
			'title' => '微擎服务团队',
			'displayorder' => '2',
			'status' => '1'
		),
		array(
			'uniacid' => '3',
			'title' => '微擎订阅号',
			'displayorder' => '3',
			'status' => '1'
		),
	);
	foreach ($test_data as &$data) {
		pdo_insert('test_pdo', $data);
	}
}

//pdo_get()
$table = 'test_pdo';
$param = array(
	'uniacid' => 2
);
$param_a = array(
	'uniacid' => 1,
	'displayorder' => 1,
);

//pdo_get()第二个参数有值时
$result_a = pdo_get($table, $param);
if ($result_a != $data[1]) {
	echo "pdo_get()函数返回值不正确, result_a";die;
}

//pdo_get()第二个参数无值时
$result_b = pdo_get($table, array());
if ($result_b != $data[0]) {
	echo "pdo_get()函数返回值不正确, result_b";die;
}

//pdo_get()第二个参数为多值时
$result_c = pdo_get($table, $param_a);
if ($result_c != $data[0]) {
	echo "pdo_get()函数返回值不正确, result_b";die;
}




