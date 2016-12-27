<?php
define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/bootstrap.sys.inc.php';
require IA_ROOT . '/web/common/common.func.php';

unlink(IA_ROOT . '/addons/we7_coupon/manifest.xml');
unlink(IA_ROOT . '/addons/we7_coupon/install.php');
unlink(IA_ROOT . '/addons/we7_coupon/developer.cer');
