<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2
 * Time: 9:27
 */
define('IN_SYS', true);

$code = $_GET['code'];
$state = $_GET['state'];

$redirect = $_W['siteroot'] . "index.php?c=user&a=login&code=$code&state=$state&login_type=qq";

header("Location:$redirect");