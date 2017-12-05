<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');
if (!empty($_W['uid'])) {
	header('Location: '.url('account/display'));
	exit;
}

/*获取站点配置信息*/
$settings = $_W['setting'];
/* xstart */
if (IMS_FAMILY == 'x') {
	if (!empty($settings['site_welcome_module'])) {
		$site = WeUtility::createModuleSite($settings['site_welcome_module']);
		if (!is_error($site)) {
			exit($site->systemWelcomeDisplay());
		}
	}
}
/* xend */
$copyright = $settings['copyright'];
$copyright['slides'] = iunserializer($copyright['slides']);
if (isset($copyright['showhomepage']) && empty($copyright['showhomepage'])) {
	header("Location: ".url('user/login'));
	exit;
}
load()->model('article');
$notices = article_notice_home();
$news = article_news_home();
template('account/welcome');
