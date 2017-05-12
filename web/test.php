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

$_W['uniacid'] = 181;
$modules = uni_modules(false);
print_r($modules);exit;

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
	$ignore_dir = array('addons/', '/resource/components', 'attachment/', 'framework/library', 'app/themes');
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
}
$delete_files = '["app\/resource\/css\/common.css","app\/resource\/css\/mui.min.css","app\/resource\/images\/bg-banner.png","app\/resource\/js\/app\/moment.js","app\/resource\/js\/lib\/calendar.js","app\/resource\/js\/lib\/underscore-min.js","app\/source\/activity\/__init.php","framework\/builtin\/userapi\/api\/gold.php","framework\/builtin\/userapi\/api\/test.php","framework\/model\/frame.mod.php","web\/resource\/css\/bootstrap-theme.min.css","web\/resource\/css\/emoji.css","web\/resource\/css\/font-awesome.min.css","web\/resource\/fonts\/glyphicons-halflings-regular.eot","web\/resource\/fonts\/glyphicons-halflings-regular.svg","web\/resource\/fonts\/glyphicons-halflings-regular.ttf","web\/resource\/fonts\/glyphicons-halflings-regular.woff","web\/resource\/images\/bg_repno.gif","web\/resource\/images\/cancel-custom-off.png","web\/resource\/images\/cancel-custom-on.png","web\/resource\/images\/gw-bg.jpg","web\/resource\/images\/gw-logo.png","web\/resource\/images\/gw-qr.jpg","web\/resource\/images\/gw-yx.png","web\/resource\/images\/icon_audio.png","web\/resource\/images\/media.jpg","web\/resource\/images\/money.png","web\/resource\/images\/star-off-big.png","web\/resource\/images\/star-on-big.png","web\/resource\/images\/subscribe.gif","web\/resource\/js\/app\/biz.js","web\/resource\/js\/app\/config.js","web\/resource\/js\/app\/coupon.js","web\/resource\/js\/app\/domReady.js","web\/resource\/js\/app\/industry.js","web\/resource\/js\/app\/location.js","web\/resource\/js\/app\/material.js","web\/resource\/js\/app\/trade.js","web\/resource\/js\/app\/wapeditor.js","web\/resource\/js\/lib\/angular-sanitize.min.js","web\/resource\/js\/lib\/angular.min.js","web\/resource\/js\/lib\/bootstrap-filestyle.min.js","web\/resource\/js\/lib\/chart.min.js","web\/resource\/js\/lib\/json2.js","web\/resource\/js\/lib\/raty.min.js","web\/source\/account\/default.ctrl.php","web\/source\/account\/delete.ctrl.php","web\/source\/account\/groups.ctrl.php","web\/source\/account\/permission.ctrl.php","web\/source\/account\/summary.ctrl.php","web\/source\/account\/switch.ctrl.php","web\/source\/account\/welcome.ctrl.php","web\/source\/activity\/__init.php","web\/source\/activity\/desk.ctrl.php","web\/source\/activity\/module.ctrl.php","web\/source\/cloud\/device.ctrl.php","web\/source\/cloud\/diagnose.ctrl.php","web\/source\/cron\/__init.php","web\/source\/cron\/display.ctrl.php","web\/source\/cron\/entry.ctrl.php","web\/source\/extension\/__init.php","web\/source\/extension\/menu.ctrl.php","web\/source\/extension\/module.ctrl.php","web\/source\/extension\/platform.ctrl.php","web\/source\/extension\/service.ctrl.php","web\/source\/extension\/subscribe.ctrl.php","web\/source\/extension\/theme.ctrl.php","web\/source\/material\/__init.php","web\/source\/material\/display.ctrl.php","web\/source\/material\/mass.ctrl.php","web\/source\/material\/post.ctrl.php","web\/source\/mc\/broadcast.ctrl.php","web\/source\/mc\/credit.ctrl.php","web\/source\/mc\/fangroup.ctrl.php","web\/source\/mc\/fields.ctrl.php","web\/source\/mc\/group.ctrl.php","web\/source\/mc\/mass.ctrl.php","web\/source\/mc\/notice.ctrl.php","web\/source\/mc\/passport.ctrl.php","web\/source\/mc\/plugin.ctrl.php","web\/source\/mc\/tplnotice.ctrl.php","web\/source\/mc\/uc.ctrl.php","web\/source\/paycenter\/__init.php","web\/source\/paycenter\/card.ctrl.php","web\/source\/platform\/special.ctrl.php","web\/source\/profile\/deskmenu.ctrl.php","web\/source\/profile\/jsauth.ctrl.php","web\/source\/profile\/printer.ctrl.php","web\/source\/site\/info.ctrl.php","web\/source\/site\/solution.ctrl.php","web\/source\/stat\/__init.php","web\/source\/stat\/card.ctrl.php","web\/source\/stat\/cash.ctrl.php","web\/source\/stat\/credit1.ctrl.php","web\/source\/stat\/credit2.ctrl.php","web\/source\/stat\/paycenter.ctrl.php","web\/source\/system\/content_provider.ctrl.php","web\/source\/system\/cron.ctrl.php","web\/source\/system\/sysinfo.ctrl.php","web\/source\/system\/tools.ctrl.php","web\/source\/system\/welcome.ctrl.php","web\/source\/user\/permission.ctrl.php","web\/source\/user\/register.ctrl.php","web\/source\/utility\/bindcall.ctrl.php","web\/source\/utility\/checkattach.ctrl.php","web\/source\/utility\/checkupgrade.ctrl.php","web\/source\/utility\/code.ctrl.php","web\/source\/utility\/coupon.ctrl.php","web\/source\/utility\/emoji.ctrl.php","web\/source\/utility\/fans.ctrl.php","web\/source\/utility\/notice.ctrl.php","web\/source\/utility\/subscribe.ctrl.php","web\/source\/utility\/sync.ctrl.php","web\/source\/utility\/verifycode.ctrl.php","web\/themes\/default\/account\/groups.html","web\/themes\/default\/account\/guide.html","web\/themes\/default\/account\/permission.html","web\/themes\/default\/account\/post.html","web\/themes\/default\/account\/select.html","web\/themes\/default\/account\/summary.html","web\/themes\/default\/account\/welcome.html","web\/themes\/default\/article\/news-show.html","web\/themes\/default\/article\/notice-show.html","web\/themes\/default\/cloud\/device.html","web\/themes\/default\/cloud\/diagnose.html","web\/themes\/default\/common\/footer-cms.html","web\/themes\/default\/common\/footer-gw.html","web\/themes\/default\/common\/header-cms.html","web\/themes\/default\/common\/header-gw.html","web\/themes\/default\/cron\/display.html","web\/themes\/default\/extension\/designer.html","web\/themes\/default\/extension\/desitemp.html","web\/themes\/default\/extension\/menu.html","web\/themes\/default\/extension\/module-permission.html","web\/themes\/default\/extension\/module-tabs.html","web\/themes\/default\/extension\/module.html","web\/themes\/default\/extension\/permission.html","web\/themes\/default\/extension\/platform.html","web\/themes\/default\/extension\/post.html","web\/themes\/default\/extension\/select-account.html","web\/themes\/default\/extension\/select-groups.html","web\/themes\/default\/extension\/service-post.html","web\/themes\/default\/extension\/service-tabs.html","web\/themes\/default\/extension\/service.html","web\/themes\/default\/extension\/subscribe.html","web\/themes\/default\/extension\/switch.html","web\/themes\/default\/extension\/theme-tabs.html","web\/themes\/default\/extension\/theme.html","web\/themes\/default\/extension\/web.html","web\/themes\/default\/home\/welcome-mc.html","web\/themes\/default\/home\/welcome-platform.html","web\/themes\/default\/home\/welcome-setting.html","web\/themes\/default\/home\/welcome-site.html","web\/themes\/default\/home\/welcome-solution.html","web\/themes\/default\/material\/display.html","web\/themes\/default\/material\/mass.html","web\/themes\/default\/material\/post.html","web\/themes\/default\/material\/send.html","web\/themes\/default\/mc\/broadcast.html","web\/themes\/default\/mc\/coupon-model.html","web\/themes\/default\/mc\/credit.html","web\/themes\/default\/mc\/fansgroup.html","web\/themes\/default\/mc\/fields.html","web\/themes\/default\/mc\/group.html","web\/themes\/default\/mc\/notice.html","web\/themes\/default\/mc\/passport.html","web\/themes\/default\/mc\/plugin.html","web\/themes\/default\/mc\/tplnotice.html","web\/themes\/default\/mc\/trade.html","web\/themes\/default\/mc\/uc.html","web\/themes\/default\/paycenter\/payinfo.html","web\/themes\/default\/paycenter\/wechat.html","web\/themes\/default\/platform\/resource.html","web\/themes\/default\/platform\/service.html","web\/themes\/default\/platform\/special-display.html","web\/themes\/default\/platform\/special-message.html","web\/themes\/default\/platform\/stat-history.html","web\/themes\/default\/platform\/stat-keyword_hit.html","web\/themes\/default\/platform\/stat-keyword_miss.html","web\/themes\/default\/platform\/stat-keyword_search.html","web\/themes\/default\/platform\/stat-rule_hit.html","web\/themes\/default\/platform\/stat-rule_miss.html","web\/themes\/default\/platform\/stat-rule_search.html","web\/themes\/default\/platform\/stat-setting.html","web\/themes\/default\/platform\/stat-trend.html","web\/themes\/default\/profile\/deskmenu.html","web\/themes\/default\/profile\/jsauth.html","web\/themes\/default\/profile\/module_setting.html","web\/themes\/default\/profile\/permission.html","web\/themes\/default\/profile\/printer.html","web\/themes\/default\/profile\/work.html","web\/themes\/default\/site\/article.html","web\/themes\/default\/site\/category.html","web\/themes\/default\/site\/info.html","web\/themes\/default\/site\/multi.html","web\/themes\/default\/site\/slide.html","web\/themes\/default\/site\/solution.html","web\/themes\/default\/site\/style.html","web\/themes\/default\/stat\/card.html","web\/themes\/default\/stat\/cash.html","web\/themes\/default\/stat\/credit1.html","web\/themes\/default\/stat\/credit2.html","web\/themes\/default\/stat\/paycenter.html","web\/themes\/default\/system\/content_provider.html","web\/themes\/default\/system\/cron.html","web\/themes\/default\/system\/sysinfo.html","web\/themes\/default\/system\/welcome.html","web\/themes\/default\/user\/access.html","web\/themes\/default\/user\/edit.html","web\/themes\/default\/user\/fields.html","web\/themes\/default\/user\/group.html","web\/themes\/default\/user\/menu.html","web\/themes\/default\/user\/register.html","web\/themes\/default\/user\/select.html","web\/themes\/default\/utility\/emoji.html","web\/themes\/default\/utility\/emulator.html","web\/themes\/default\/utility\/fans.html","web\/themes\/index.html","app\/source\/activity","web\/source\/activity","web\/source\/cron","web\/source\/extension","web\/source\/material","web\/source\/paycenter","web\/source\/stat","web\/themes\/default\/cron","web\/themes\/default\/cron","web\/themes\/default\/material","web\/themes\/default\/paycenter","web\/themes\/default\/stat"]';
$delete_files = json_decode($delete_files, true);
$sa = array('framework/builtin/reply');
print_r(json_encode(array_merge($delete_file, $delete_files, $sa)));die;die;

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
die;die;die;
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