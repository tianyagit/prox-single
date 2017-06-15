<?php
/**
 * qrcode_stat 表数据记录达到auto_increment 默认的自增长最大值  需要调大此值
 * 更新reply_type字段的值（兼容0.8版本）
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

if(pdo_fieldexists('qrcode_stat', 'id')) {
	pdo_query("ALTER TABLE ".tablename('qrcode_stat')." AUTO_INCREMENT = 1000000;");
}
//更新reply_type字段的值
$rules = pdo_getall('rule', array('reply_type' => 0));
if (!empty($rules)) {
	foreach ($rules as &$rule) {
		$rule['keywords'] = pdo_getall('rule_keyword', array('rid' => $rule['id']));
	}
	unset($rule);
	foreach ($rules as &$rule) {
		$rule['reply_type'] = 2;
		if (!empty($rule['keywords'])) {
			foreach ($rule['keywords'] as $keyword) {
				if ($keyword['type'] == 2 || $keyword['type'] == 3) {
					$rule['reply_type'] = 1;
					break;
				}
			}
		}
		pdo_update('rule', array('reply_type' => $rule['reply_type']), array('id' => $rule['id']));
	}
	unset($rule);
}