<?php
/**
 * 添加小程序数据常规分析表
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

pdo_query("CREATE TABLE IF NOT EXISTS `ims_wxapp_general_analysis2` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`uniacid`  int(10) NOT NULL ,
`session_cnt`  int(10) NOT NULL COMMENT '打开次数' ,
`visit_pv`  int(10) NOT NULL COMMENT '访问次数' ,
`visit_uv`  int(10) NOT NULL COMMENT '访问人数' ,
`visit_uv_new`  int(10) NOT NULL COMMENT '新用户数' ,
`type`  varchar(50) NOT NULL COMMENT '1、概况趋势；2、访问日趋势；3、访问周趋势；4、访问月趋势；5、访问分布；6、访问日留存；7、周留存；8、访问月留存；9、访问页面' ,
`stay_time_uv` FLOAT(10,4) NOT NULL COMMENT '人均停留时长' ,
`stay_time_session` FLOAT(10,4) NOT NULL COMMENT '次均停留时长' ,
`visit_depth` FLOAT(10,4) NOT NULL COMMENT '平均访问深度' ,
`ref_date`  varchar(10) NOT NULL COMMENT '时间' ,
PRIMARY KEY (`id`),
INDEX `uniacid` (`uniacid`) USING BTREE ,
INDEX `type` (`type`) USING BTREE 
)
COMMENT='小程序数据常规分析表'
;");