<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2
 * Time: 9:27
 */
define('IN_SYS', true);
require '../framework/bootstrap.inc.php';

$code = $_GPC['code'];
$state = $_GPC['state'];
$redirect = $_W['siteroot'] . "web/index.php?c=user&a=login&code=$code&state=$state&login_type=qq";

header("Location:$redirect");