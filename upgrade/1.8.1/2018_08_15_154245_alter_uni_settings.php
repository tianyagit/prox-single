<?php

namespace We7\V181;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1534318965
 * @version 1.8.1
 */

class AlterUniSettings {

	/**
	 *  执行更新
	 */
	public function up() {
		global $_W;
		if (!pdo_fieldexists('uni_settings', 'attachment_limit') && !pdo_fieldexists('uni_settings', 'attachment_size')) {
			pdo_query("ALTER TABLE " . tablename('uni_settings') . " ADD (`attachment_limit` INT(11) DEFAULT 0 COMMENT '单位M', `attachment_size` BIGINT(20) UNSIGNED DEFAULT 0 COMMENT '单位KB')");

			$attachdir = glob(IA_ROOT . '/' . $_W['config']['upload']['attachdir'] . '/*');
			if (!empty($attachdir)) {
				foreach ($attachdir as $attach) {
					if (!is_dir($attach)) {
						continue;
					}
					$attach = glob($attach . '/*');
					foreach ($attach as $dir) {
						if (!is_dir($dir)) {
							continue;
						}
						$uniacid = substr($dir, strripos($dir, '/') + 1);
						$uniacid = pdo_getcolumn('account', array('uniacid' => $uniacid), 'uniacid');
						if (!empty($uniacid)) {
							$size = dir_size($dir);
							$size = round($size / 1024);
							$set_id = pdo_getcolumn('uni_settings', array('uniacid' => $uniacid), 'uniacid');
							if (empty($set_id)) {
								pdo_insert('uni_settings', array('attachment_size' => $size, 'uniacid' => $uniacid));
							} else {
								pdo_update('uni_settings', array('attachment_size +=' => $size), array('uniacid' => $uniacid));
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		