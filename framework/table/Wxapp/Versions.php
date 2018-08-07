<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Wxapp;

class Versions extends \We7Table {
	protected $tableName = 'wxapp_versions';
	protected $primaryKey = 'id';
	protected $field = array(
		'uniacid',
		'multiid',
		'version',
		'description',
		'modules',
		'design_method',
		'template',
		'quickmenu',
		'createtime',
		'appjson',
		'default_appjson',
		'use_default',
		'type',
		'entry_id',
		'last_modules',
	);
	protected $default = array(
		'uniacid' => '',
		'multiid' => '',
		'version' => '',
		'description' => '',
		'modules' => '',
		'design_method' => '',
		'template' => '',
		'quickmenu' => '',
		'createtime' => '',
		'appjson' => '',
		'default_appjson' => '',
		'use_default' => 1,
		'type' => 0,
		'entry_id' => 0,
		'last_modules' => '',
	);

	/**
	 * 获取小程序最新的4个版本
	 * @param int $uniacid
	 */
	public function latestVersion($uniacid) {
		return $this->query->where('uniacid', $uniacid)->orderby('id', 'desc')->limit(4)->getall('id');
	}

	public function getById($version_id) {
		$result = $this->query->where('id', $version_id)->get();
		if (!empty($result)) {
			$result['modules'] = iunserializer($result['modules']);
		}
		return $result;
	}
}