<?php
namespace MicroEngine;
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/12/12
 * Time: 17:16
 */
use PHPUnit\Framework\TestCase;

$GLOBALS['_W'] = array();
class MyUnitTester extends TestCase {

	public function setUp() {
		parent::setUp();
		ini_set('display_errors', '1');
		error_reporting(E_ALL ^ E_NOTICE);
		global $_W;
		define('IN_IA', true);
		define('IA_ROOT', (str_replace("\\", '/', __DIR__.'/../../')));
		$configfile = IA_ROOT . "/data/config.php";
		$config = require $configfile;
		$_W['config'] = $config;
		$_W['config']['db']['tablepre'] = !empty($_W['config']['db']['master']['tablepre']) ? $_W['config']['db']['master']['tablepre'] : $_W['config']['db']['tablepre'];
		require IA_ROOT . '/framework/version.inc.php';
		require IA_ROOT . '/framework/const.inc.php';
		require IA_ROOT . '/framework/class/loader.class.php';
		load()->func('global');
		load()->func('compat');
		load()->func('pdo');
		load()->classs('account');
		load()->model('cache');
		load()->model('account');
		load()->model('setting');
		load()->model('module');
		load()->library('agent');
		load()->classs('db');
	}
}