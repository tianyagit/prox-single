<?php
/**
 * 小程序版本表添加字段last_use：是否上一次使用版本
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

//更新小程序相关表结构
if(!pdo_fieldexists('wxapp_versions', 'last_use')) {
	pdo_query("ALTER TABLE ". tablename('wxapp_versions') ." ADD `last_use` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '上一次使用：1、是；0、否';");
}