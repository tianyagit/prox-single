<?php
/**
 * qrcode_stat 表数据记录达到auto_increment 默认的自增长最大值  需要调大此值
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/common.func.php';

if(pdo_fieldexists('qrcode_stat', 'id')) {
	pdo_query("ALTER TABLE ".tablename('qrcode_stat')." AUTO_INCREMENT = 1000000;");
}