<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/21
 * Time: 16:32.
 */
namespace We7\V10;

error_reporting(~E_WARNING || ~E_NOTICE);
defined('IN_IA') or exit('Access Denied');
require __DIR__.'/../../framework/library/pinyin/pinyin.php';
class UpdateModule {

	public $description = '更新模块首字母';
	public function up() {
		$pinyin = new \Pinyin_Pinyin();
		$module_list = pdo_getall('modules', array('title_initial' => ''));
		foreach ($module_list as $module) {
			$title = $pinyin->get_first_char($module['title']);
			pdo_update('modules', array('title_initial' => $title), array('mid' => $module['mid']));
		}
	}
}
