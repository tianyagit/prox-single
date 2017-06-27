<?php
/**
 * 添加小程序数据常规分析表
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

pdo_query("CREATE TABLE IF NOT EXISTS `ims_wxapp_general_analysis` (
`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`uniacid` int(10) NOT NULL ,
`session_cnt` int(10) NOT NULL,
`visit_pv` int(10) NOT NULL,
`visit_uv` int(10) NOT NULL,
`visit_uv_new` int(10) NOT NULL,
`type` tinyint(2) NOT NULL,
`stay_time_uv` varchar(10) NOT NULL,
`stay_time_session` varchar(10) NOT NULL,
`visit_depth` varchar(10) NOT NULL,
`ref_date`  varchar(8) NOT NULL,
PRIMARY KEY (`id`),
INDEX `uniacid` (`uniacid`) USING BTREE ,
INDEX `ref_date` (`ref_date`) USING BTREE 
);");