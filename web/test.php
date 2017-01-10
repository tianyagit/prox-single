<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/index.php : v 14b9a4299104 : 2015/09/11 10:44:21 : yanghf $
 */
define('IN_SYS', true);
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/bootstrap.sys.inc.php';
load()->web('common');
load()->web('template');
load()->func('communication');
load()->model('cache');
load()->model('cloud');
load()->classs('coupon');
load()->func('file');

$old_path = IA_ROOT.'/data/old';
$new_path = IA_ROOT.'/data/new';
$old_file_tree = file_tree($old_path);
foreach ($old_file_tree as &$old_file) {
	$old_file = str_replace('D:/MicroEngine/pros/data/old/', '', $old_file);
}
$new_file_tree = file_tree($new_path);
foreach ($new_file_tree as &$new_file) {
	$new_file = str_replace('D:/MicroEngine/pros/data/new/', '', $new_file);
}
$delete_file = array_diff($old_file_tree, $new_file_tree);
foreach ($delete_file as $key => &$file) {
	$ignore_dir = array('addons/', '/resource/components', 'attachment/', 'framework/library');
	$is_ignore = false;
	foreach ($ignore_dir as $ignore) {
		if (strexists($file, $ignore)) {
			$is_ignore = true;
			break;
		}
	}
	if ($is_ignore === true) {
		unset($delete_file[$key]);
		continue;
	}
//	if (!is_dir(dirname('delete/'.$file))) {
//		mkdirs(dirname('delete/'.$file));
//	}
//	$file_content = file_get_contents('D:/MicroEngine/pros/data/old/'.$file);
//	file_put_contents("delete/". $file, $file_content);
}
//$delete_file = json_encode($delete_file);
//$delete_file = '{"63":"app\/resource\/css\/common.css","65":"app\/resource\/css\/mui.min.css","106":"app\/resource\/images\/bg-banner.png","206":"app\/resource\/js\/app\/moment.js","208":"app\/resource\/js\/lib\/calendar.js","212":"app\/resource\/js\/lib\/underscore-min.js","214":"app\/source\/activity\/__init.php","387":"framework\/builtin\/userapi\/api\/gold.php","389":"framework\/builtin\/userapi\/api\/test.php","849":"framework\/model\/frame.mod.php","1278":"web\/resource\/css\/bootstrap-theme.min.css","1281":"web\/resource\/css\/emoji.css","1282":"web\/resource\/css\/font-awesome.min.css","1290":"web\/resource\/fonts\/glyphicons-halflings-regular.eot","1291":"web\/resource\/fonts\/glyphicons-halflings-regular.svg","1292":"web\/resource\/fonts\/glyphicons-halflings-regular.ttf","1293":"web\/resource\/fonts\/glyphicons-halflings-regular.woff","1415":"web\/resource\/images\/bg_repno.gif","1416":"web\/resource\/images\/cancel-custom-off.png","1417":"web\/resource\/images\/cancel-custom-on.png","1447":"web\/resource\/images\/gw-bg.jpg","1448":"web\/resource\/images\/gw-logo.png","1449":"web\/resource\/images\/gw-qr.jpg","1451":"web\/resource\/images\/gw-yx.png","1452":"web\/resource\/images\/icon_audio.png","1453":"web\/resource\/images\/media.jpg","1476":"web\/resource\/images\/money.png","1482":"web\/resource\/images\/star-off-big.png","1483":"web\/resource\/images\/star-on-big.png","1484":"web\/resource\/images\/subscribe.gif","1488":"web\/resource\/js\/app\/biz.js","1490":"web\/resource\/js\/app\/config.js","1491":"web\/resource\/js\/app\/coupon.js","1492":"web\/resource\/js\/app\/domReady.js","1493":"web\/resource\/js\/app\/industry.js","1494":"web\/resource\/js\/app\/location.js","1495":"web\/resource\/js\/app\/material.js","1496":"web\/resource\/js\/app\/trade.js","1498":"web\/resource\/js\/app\/wapeditor.js","1499":"web\/resource\/js\/lib\/angular-sanitize.min.js","1500":"web\/resource\/js\/lib\/angular.min.js","1501":"web\/resource\/js\/lib\/bootstrap-filestyle.min.js","1504":"web\/resource\/js\/lib\/chart.min.js","1513":"web\/resource\/js\/lib\/json2.js","1515":"web\/resource\/js\/lib\/raty.min.js","1520":"web\/source\/account\/default.ctrl.php","1521":"web\/source\/account\/delete.ctrl.php","1523":"web\/source\/account\/groups.ctrl.php","1524":"web\/source\/account\/permission.ctrl.php","1528":"web\/source\/account\/summary.ctrl.php","1529":"web\/source\/account\/switch.ctrl.php","1530":"web\/source\/account\/welcome.ctrl.php","1531":"web\/source\/activity\/__init.php","1532":"web\/source\/activity\/desk.ctrl.php","1533":"web\/source\/activity\/module.ctrl.php","1540":"web\/source\/cloud\/device.ctrl.php","1541":"web\/source\/cloud\/diagnose.ctrl.php","1546":"web\/source\/cron\/__init.php","1547":"web\/source\/cron\/display.ctrl.php","1548":"web\/source\/cron\/entry.ctrl.php","1549":"web\/source\/extension\/__init.php","1550":"web\/source\/extension\/menu.ctrl.php","1551":"web\/source\/extension\/module.ctrl.php","1552":"web\/source\/extension\/platform.ctrl.php","1553":"web\/source\/extension\/service.ctrl.php","1554":"web\/source\/extension\/subscribe.ctrl.php","1555":"web\/source\/extension\/theme.ctrl.php","1557":"web\/source\/material\/__init.php","1558":"web\/source\/material\/display.ctrl.php","1559":"web\/source\/material\/mass.ctrl.php","1560":"web\/source\/material\/post.ctrl.php","1562":"web\/source\/mc\/broadcast.ctrl.php","1563":"web\/source\/mc\/credit.ctrl.php","1564":"web\/source\/mc\/fangroup.ctrl.php","1566":"web\/source\/mc\/fields.ctrl.php","1567":"web\/source\/mc\/group.ctrl.php","1568":"web\/source\/mc\/mass.ctrl.php","1570":"web\/source\/mc\/notice.ctrl.php","1571":"web\/source\/mc\/passport.ctrl.php","1572":"web\/source\/mc\/plugin.ctrl.php","1573":"web\/source\/mc\/tplnotice.ctrl.php","1575":"web\/source\/mc\/uc.ctrl.php","1576":"web\/source\/paycenter\/__init.php","1577":"web\/source\/paycenter\/card.ctrl.php","1584":"web\/source\/platform\/special.ctrl.php","1588":"web\/source\/profile\/deskmenu.ctrl.php","1589":"web\/source\/profile\/jsauth.ctrl.php","1593":"web\/source\/profile\/printer.ctrl.php","1599":"web\/source\/site\/info.ctrl.php","1603":"web\/source\/site\/solution.ctrl.php","1605":"web\/source\/stat\/__init.php","1606":"web\/source\/stat\/card.ctrl.php","1607":"web\/source\/stat\/cash.ctrl.php","1608":"web\/source\/stat\/credit1.ctrl.php","1609":"web\/source\/stat\/credit2.ctrl.php","1610":"web\/source\/stat\/paycenter.ctrl.php","1614":"web\/source\/system\/content_provider.ctrl.php","1615":"web\/source\/system\/cron.ctrl.php","1621":"web\/source\/system\/sysinfo.ctrl.php","1622":"web\/source\/system\/tools.ctrl.php","1624":"web\/source\/system\/welcome.ctrl.php","1633":"web\/source\/user\/permission.ctrl.php","1635":"web\/source\/user\/register.ctrl.php","1638":"web\/source\/utility\/bindcall.ctrl.php","1639":"web\/source\/utility\/checkattach.ctrl.php","1640":"web\/source\/utility\/checkupgrade.ctrl.php","1641":"web\/source\/utility\/code.ctrl.php","1642":"web\/source\/utility\/coupon.ctrl.php","1644":"web\/source\/utility\/emoji.ctrl.php","1646":"web\/source\/utility\/fans.ctrl.php","1651":"web\/source\/utility\/notice.ctrl.php","1652":"web\/source\/utility\/subscribe.ctrl.php","1653":"web\/source\/utility\/sync.ctrl.php","1655":"web\/source\/utility\/verifycode.ctrl.php","1659":"web\/themes\/default\/account\/groups.html","1660":"web\/themes\/default\/account\/guide.html","1661":"web\/themes\/default\/account\/permission.html","1663":"web\/themes\/default\/account\/post.html","1665":"web\/themes\/default\/account\/select.html","1666":"web\/themes\/default\/account\/summary.html","1667":"web\/themes\/default\/account\/welcome.html","1669":"web\/themes\/default\/article\/news-show.html","1672":"web\/themes\/default\/article\/notice-show.html","1674":"web\/themes\/default\/cloud\/device.html","1675":"web\/themes\/default\/cloud\/diagnose.html","1680":"web\/themes\/default\/common\/footer-cms.html","1681":"web\/themes\/default\/common\/footer-gw.html","1684":"web\/themes\/default\/common\/header-cms.html","1685":"web\/themes\/default\/common\/header-gw.html","1688":"web\/themes\/default\/cron\/display.html","1689":"web\/themes\/default\/extension\/designer.html","1690":"web\/themes\/default\/extension\/desitemp.html","1691":"web\/themes\/default\/extension\/menu.html","1692":"web\/themes\/default\/extension\/module-permission.html","1693":"web\/themes\/default\/extension\/module-tabs.html","1694":"web\/themes\/default\/extension\/module.html","1695":"web\/themes\/default\/extension\/permission.html","1696":"web\/themes\/default\/extension\/platform.html","1697":"web\/themes\/default\/extension\/post.html","1698":"web\/themes\/default\/extension\/select-account.html","1699":"web\/themes\/default\/extension\/select-groups.html","1700":"web\/themes\/default\/extension\/service-post.html","1701":"web\/themes\/default\/extension\/service-tabs.html","1702":"web\/themes\/default\/extension\/service.html","1703":"web\/themes\/default\/extension\/subscribe.html","1704":"web\/themes\/default\/extension\/switch.html","1705":"web\/themes\/default\/extension\/theme-tabs.html","1706":"web\/themes\/default\/extension\/theme.html","1707":"web\/themes\/default\/extension\/web.html","1709":"web\/themes\/default\/home\/welcome-mc.html","1710":"web\/themes\/default\/home\/welcome-platform.html","1711":"web\/themes\/default\/home\/welcome-setting.html","1712":"web\/themes\/default\/home\/welcome-site.html","1713":"web\/themes\/default\/home\/welcome-solution.html","1716":"web\/themes\/default\/material\/display.html","1717":"web\/themes\/default\/material\/mass.html","1718":"web\/themes\/default\/material\/post.html","1719":"web\/themes\/default\/material\/send.html","1720":"web\/themes\/default\/mc\/broadcast.html","1721":"web\/themes\/default\/mc\/coupon-model.html","1722":"web\/themes\/default\/mc\/credit.html","1724":"web\/themes\/default\/mc\/fansgroup.html","1725":"web\/themes\/default\/mc\/fields.html","1726":"web\/themes\/default\/mc\/group.html","1728":"web\/themes\/default\/mc\/notice.html","1729":"web\/themes\/default\/mc\/passport.html","1730":"web\/themes\/default\/mc\/plugin.html","1731":"web\/themes\/default\/mc\/tplnotice.html","1732":"web\/themes\/default\/mc\/trade.html","1733":"web\/themes\/default\/mc\/uc.html","1734":"web\/themes\/default\/paycenter\/payinfo.html","1735":"web\/themes\/default\/paycenter\/wechat.html","1743":"web\/themes\/default\/platform\/resource.html","1744":"web\/themes\/default\/platform\/service.html","1745":"web\/themes\/default\/platform\/special-display.html","1746":"web\/themes\/default\/platform\/special-message.html","1747":"web\/themes\/default\/platform\/stat-history.html","1748":"web\/themes\/default\/platform\/stat-keyword_hit.html","1749":"web\/themes\/default\/platform\/stat-keyword_miss.html","1750":"web\/themes\/default\/platform\/stat-keyword_search.html","1751":"web\/themes\/default\/platform\/stat-rule_hit.html","1752":"web\/themes\/default\/platform\/stat-rule_miss.html","1753":"web\/themes\/default\/platform\/stat-rule_search.html","1754":"web\/themes\/default\/platform\/stat-setting.html","1755":"web\/themes\/default\/platform\/stat-trend.html","1757":"web\/themes\/default\/profile\/deskmenu.html","1758":"web\/themes\/default\/profile\/jsauth.html","1760":"web\/themes\/default\/profile\/module_setting.html","1763":"web\/themes\/default\/profile\/permission.html","1764":"web\/themes\/default\/profile\/printer.html","1765":"web\/themes\/default\/profile\/work.html","1766":"web\/themes\/default\/site\/article.html","1767":"web\/themes\/default\/site\/category.html","1769":"web\/themes\/default\/site\/info.html","1770":"web\/themes\/default\/site\/multi.html","1772":"web\/themes\/default\/site\/slide.html","1773":"web\/themes\/default\/site\/solution.html","1774":"web\/themes\/default\/site\/style.html","1775":"web\/themes\/default\/stat\/card.html","1776":"web\/themes\/default\/stat\/cash.html","1777":"web\/themes\/default\/stat\/credit1.html","1778":"web\/themes\/default\/stat\/credit2.html","1779":"web\/themes\/default\/stat\/paycenter.html","1783":"web\/themes\/default\/system\/content_provider.html","1784":"web\/themes\/default\/system\/cron.html","1791":"web\/themes\/default\/system\/sysinfo.html","1793":"web\/themes\/default\/system\/welcome.html","1794":"web\/themes\/default\/user\/access.html","1797":"web\/themes\/default\/user\/edit.html","1798":"web\/themes\/default\/user\/fields.html","1799":"web\/themes\/default\/user\/group.html","1801":"web\/themes\/default\/user\/menu.html","1804":"web\/themes\/default\/user\/register.html","1805":"web\/themes\/default\/user\/select.html","1806":"web\/themes\/default\/utility\/emoji.html","1807":"web\/themes\/default\/utility\/emulator.html","1808":"web\/themes\/default\/utility\/fans.html","1812":"web\/themes\/index.html"}';
//print_r(json_decode($delete_file, false));die;
$s = array('app/source/activity', 'web/source/activity', 'web/source/cron', 'web/source/extension', 'web/source/material', 'web/source/paycenter', 'web/source/stat', 'web/themes/default/cron', 'web/themes/default/cron', 'web/themes/default/material', 'web/themes/default/paycenter', 'web/themes/default/stat');
print_r(json_encode(array_merge($delete_file, $s)));die;







cache_build_frame_menu();

$system_menu = cache_load('system_frame');
print_r($system_menu);
exit;
$qiniu_conf = base64_encode(file_get_contents(IA_ROOT.'/framework/library/qiniu/src/Qiniu/Config.php'));
$qiniu_zone = base64_encode(file_get_contents(IA_ROOT.'/framework/library/qiniu/src/Qiniu/Zone.php'));
$cos_conf = base64_encode(file_get_contents(IA_ROOT.'/framework/library/cos/Qcloud_cos/Conf.php'));
$qiniu_conf = 'PD9waHAKbmFtZXNwYWNlIFFpbml1OwoKZmluYWwgY2xhc3MgQ29uZmlnCnsKICAgIGNvbnN0IFNES19WRVIgPSAnNy4wLjYnOwoKICAgIGNvbnN0IEJMT0NLX1NJWkUgPSA0MTk0MzA0OyAvLzQqMTAyNCoxMDI0IOWIhuWdl+S4iuS8oOWdl+Wkp+Wwj++8jOivpeWPguaVsOS4uuaOpeWPo+inhOagvO+8jOS4jeiDveS/ruaUuQoKICAgIGNvbnN0IElPX0hPU1QgID0gJ2h0dHA6Ly9pb3ZpcC16MS5xYm94Lm1lJzsgICAgICAgICAgICAvLyDkuIPniZvmupDnq5lIb3N0CiAgICBjb25zdCBSU19IT1NUICA9ICdodHRwOi8vcnMucWJveC5tZSc7ICAgICAgICAgICAgICAgLy8g5paH5Lu25YWD5L+h5oGv566h55CG5pON5L2cSG9zdAogICAgY29uc3QgUlNGX0hPU1QgPSAnaHR0cDovL3JzZi5xYm94Lm1lJzsgICAgICAgICAgICAgIC8vIOWIl+S4vuaTjeS9nEhvc3QKICAgIGNvbnN0IEFQSV9IT1NUID0gJ2h0dHA6Ly9hcGkucWluaXUuY29tJzsgICAgICAgICAgICAvLyDmlbDmja7lpITnkIbmk43kvZxIb3N0CgogICAgcHJpdmF0ZSAkdXBIb3N0OyAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8vIOS4iuS8oEhvc3QKICAgIHByaXZhdGUgJHVwSG9zdEJhY2t1cDsgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAvLyDkuIrkvKDlpIfnlKhIb3N0CgogICAgcHVibGljIGZ1bmN0aW9uIF9fY29uc3RydWN0KFpvbmUgJHogPSBudWxsKSAgICAgICAgIC8vIOaehOmAoOWHveaVsO+8jOm7mOiupOS4unpvbmUwCiAgICB7CiAgICAgICAgaWYgKCR6ID09PSBudWxsKSB7CiAgICAgICAgICAgICR6ID0gWm9uZTo6em9uZTAoKTsKICAgICAgICB9CiAgICAgICAgJHRoaXMtPnVwSG9zdCA9ICR6LT51cEhvc3Q7CiAgICAgICAgJHRoaXMtPnVwSG9zdEJhY2t1cCA9ICR6LT51cEhvc3RCYWNrdXA7CiAgICB9CgogICAgcHVibGljIGZ1bmN0aW9uIGdldFVwSG9zdCgpCiAgICB7CiAgICAgICAgcmV0dXJuICR0aGlzLT51cEhvc3Q7CiAgICB9CgogICAgcHVibGljIGZ1bmN0aW9uIGdldFVwSG9zdEJhY2t1cCgpCiAgICB7CiAgICAgICAgcmV0dXJuICR0aGlzLT51cEhvc3RCYWNrdXA7CiAgICB9Cn0K';
$qiniu_zone = "PD9waHAKbmFtZXNwYWNlIFFpbml1OwoKZmluYWwgY2xhc3MgWm9uZQp7CiAgICBwdWJsaWMgJHVwSG9zdDsKICAgIHB1YmxpYyAkdXBIb3N0QmFja3VwOwoKICAgIHB1YmxpYyBmdW5jdGlvbiBfX2NvbnN0cnVjdCgkdXBIb3N0LCAkdXBIb3N0QmFja3VwKQogICAgewogICAgICAgICR0aGlzLT51cEhvc3QgPSAkdXBIb3N0OwogICAgICAgICR0aGlzLT51cEhvc3RCYWNrdXAgPSAkdXBIb3N0QmFja3VwOwogICAgfQoKICAgIHB1YmxpYyBzdGF0aWMgZnVuY3Rpb24gem9uZTAoKQogICAgewogICAgICAgIHJldHVybiBuZXcgc2VsZignaHR0cDovL3VwLXoxLnFpbml1LmNvbScsICdodHRwOi8vdXBsb2FkLXoxLnFpbml1LmNvbScpOwogICAgfQoKICAgIHB1YmxpYyBzdGF0aWMgZnVuY3Rpb24gem9uZTEoKQogICAgewogICAgICAgIHJldHVybiBuZXcgc2VsZignaHR0cDovL3VwLXoxLnFpbml1LmNvbScsICdodHRwOi8vdXBsb2FkLXoxLnFpbml1LmNvbScpOwogICAgfQp9Cg==";
$cos_conf = 'PD9waHANCm5hbWVzcGFjZSBRY2xvdWRfY29zOw0KDQpjbGFzcyBDb25mDQp7DQogICAgY29uc3QgUEtHX1ZFUlNJT04gPSAndjMuMyc7DQoNCiAgICBjb25zdCBBUElfSU1BR0VfRU5EX1BPSU5UID0gJ2h0dHA6Ly93ZWIuaW1hZ2UubXlxY2xvdWQuY29tL3Bob3Rvcy92MS8nOw0KICAgIGNvbnN0IEFQSV9WSURFT19FTkRfUE9JTlQgPSAnaHR0cDovL3dlYi52aWRlby5teXFjbG91ZC5jb20vdmlkZW9zL3YxLyc7DQogICAgY29uc3QgQVBJX0NPU0FQSV9FTkRfUE9JTlQgPSAnaHR0cDovL3dlYi5maWxlLm15cWNsb3VkLmNvbS9maWxlcy92MS8nOw0KICAgIC8v6K+35YiwaHR0cDovL2NvbnNvbGUucWNsb3VkLmNvbS9jb3Pljrvojrflj5bkvaDnmoRhcHBpZOOAgXNpZOOAgXNrZXkNCiAgICBjb25zdCBBUFBJRCA9ICcnOw0KICAgIGNvbnN0IFNFQ1JFVF9JRCA9ICcnOw0KICAgIGNvbnN0IFNFQ1JFVF9LRVkgPSAnJzsNCg0KDQogICAgcHVibGljIHN0YXRpYyBmdW5jdGlvbiBnZXRVQSgpIHsNCiAgICAgICAgcmV0dXJuICdjb3MtcGhwLXNkay0nLnNlbGY6OlBLR19WRVJTSU9OOw0KICAgIH0NCn0NCg0KLy9lbmQgb2Ygc2NyaXB0DQo=';
die;die;
die;die;
die;die;
echo 222;die;

function site_cloud_ad($params) {
	global $_W;
	$querystring = array(
		'ad_type' => $params['ad_type'],
		'type' => $params['type'],
		'module' => $params['module'],
		'uniacid' => $_W['uniacid'],
		'site_key' => $_W['setting']['site']['key'],
	);
	$url = 'http://s.we7.cc/index.php?c=store&a=link&do=ad&'.http_build_query($querystring, null, '&');
	$ret = ihttp_request($url, array(), array(), 10);
	if (is_error($ret)) {
		echo '';
	}
	
	echo <<<eof
<script type="text/javascript">{$ret['content']}</script>
eof;
}
	
$params = array(
	'ad_type' => 1,
	'type' => 'view',
	'module' => 'ewei_shopping',
	'uniacid' => 20,
	'site_key' => 40716
);
site_cloud_ad($params);

exit;
$ret = cloud_flow_site_stat_day(array('page' => 2, 'size' => 1));
print_r($ret);
exit;
/* $ret = cloud_flow_ad_type_list();
print_r($ret);
exit;
 */
/* $ret = cloud_flow_app_list_get(281);
print_r($ret);
exit;
 */

$ret = cloud_flow_app_post(281, 'we7_demo', 1);
print_r($ret);
$ret = cloud_flow_app_get(281, 'we7_demo');
print_r($ret);
exit;

$uniaccount = $_W['uniaccount'];
$uniaccount['uniacid']++;
$data = array(
	'title' => $uniaccount['name'],
	'uniacid' => $uniaccount['uniacid'] + 1,
	'original' => $uniaccount['original'],
	'gh_type' => $uniaccount['level'],
);
$ret = cloud_flow_uniaccount_post($data);
print_r($ret);
/* $ret = cloud_flow_uniaccount_get($uniaccount['uniacid']);
print_r($ret); */

exit;
$flow_master = array(
	'site_key' => 30128,
	'linkman' => 'linkman value',
	'mobile' => 'mobile value',
	'address' => 'address value',
	'id_card_photo' => 'id_card_photo value', // 身份证 url
	'business_licence_photo' => 'business_licence_photo value', // 营业执照 url
);
$ret = cloud_flow_master_post($flow_master);
print_r($ret);
$ret = cloud_flow_master_get();
print_r($ret);
'teswt';
'test';
'test';
'test';
'test';
'test';