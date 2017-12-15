<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class WxappTable extends AccountTable {
	
	private $version_table = 'wxapp_versions';
	
	/**
	 * 获取小程序最新的4个版本
	 * @param int $uniacid
	 */
	public function latestVersion($uniacid) {
		if (empty($uniacid)) {
			return array();
		}
		return $this->query->from($this->version_table)
				->where('uniacid', $uniacid)
				->orderby('id', 'desc')->limit(4)->getall('id');
	}
}