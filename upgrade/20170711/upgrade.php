<?php
/**
 * ims_users表添加副创始人字段
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';
if (!pdo_fieldexists('users', 'is_vice_founder')) {
	pdo_query("ALTER TABLE tablename('users') ADD COLUMN `is_vice_founder` TINYINT(1) NOT NULL DEFAULT 0  COMMENT '是否是副创始人 1:是 0:否' AFTER `groupid`;");
}
if (!pdo_fieldexists('users', 'vice_founder_id')) {
	pdo_query("ALTER TABLE tablename('users') ADD COLUMN `vice_founder_id` int(10) NOT NULL DEFAULT 0 COMMENT '副创始人uid' AFTER `uid`;");
}
if (!pdo_fieldexists('uni_group', 'vice_founder_id')) {
	pdo_query("ALTER TABLE tablename('uni_group') ADD COLUMN `vice_founder_id` int(10) NOT NULL DEFAULT 0 COMMENT '副创始人uid' AFTER `id`;");
}
if (!pdo_fieldexists('users_group', 'vice_founder_id')) {
	pdo_query("ALTER TABLE tablename('users_group') ADD COLUMN `vice_founder_id` int(10) NOT NULL DEFAULT 0 COMMENT '副创始人uid' AFTER `id`;");
}