<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$we7_system_menu = array();
$we7_system_menu['account'] = array(
	'title' => '公众号',
	'url' => url('platform/reply'),
	'section' => array(
		'platform_plus' => array(
			'title' => '增强功能',
			'menu' => array(
				'platform_reply' => array(
					'title' => '自动回复',
					'url' => url('platform/reply'),
					'icon' => 'wi wi-reply',
					'permission_name' => 'platform_reply',
					'sub_permission' => array(
						array(
							'title' => '关键字自动回复 ',
							'permission_name' => 'platform_reply',
						),
						array(
							'title' => '非关键字自动回复 ',
							'permission_name' => 'platform_reply_special',
						),
						array(
							'title' => '欢迎/默认回复',
							'permission_name' => 'platform_reply_system',
						),
					),
				),
				'platform_menu' => array(
					'title' => '自定义菜单',
					'url' => url('platform/menu'),
					'icon' => 'wi wi-custommenu',
					'permission_name' => 'platform_menu',
				),
				'platform_qr' => array(
					'title' => '二维码/转化链接',
					'url' => url('platform/qr'),
					'icon' => 'wi wi-qrcode',
					'permission_name' => 'platform_qr',
					'sub_permission' => array(
						array(
							'title' => '二维码',
							'permission_name' => 'platform_qr',
						),
						array(
							'title' => '转化链接',
							'permission_name' => 'platform_url2qr',
						),
					),
				),
				'platform_mass_task' => array(
					'title' => '定时群发',
					'url' => url('platform/mass'),
					'icon' => 'wi wi-crontab',
					'permission_name' => 'platform_mass_task',
				),
				'platform_material' => array(
					'title' => '素材/编辑器',
					'url' => url('platform/material'),
					'icon' => 'wi wi-redact',
					'permission_name' => 'platform_material',
				),
				'platform_site' => array(
					'title' => '微官网',
					'url' => url('site/multi/display'),
					'icon' => 'wi wi-home',
					'permission_name' => 'platform_site',
					'sub_permission' => array(
						array(
							'title' => '添加/编辑',
							'permission_name' => 'platform_site_post',
						),
						array(
							'title' => '删除',
							'permission_name' => 'platform_site_delete',
						),
					),
				)
			),
		),
		'platform_module' => array(
			'title' => '应用模块',
			'menu' => array(),
		),
		'mc' => array(
			'title' => '粉丝',
			'menu' => array(
				'mc_fans' => array(
					'title' => '粉丝管理',
					'url' => url('mc/fans'),
					'icon' => 'wi wi-fansmanage',
					'permission_name' => 'mc_fans',
				),
				'mc_member' => array(
					'title' => '会员管理',
					'url' => url('mc/member'),
					'icon' => 'wi wi-fans',
					'permission_name' => 'mc_member',
				)
			),
		),
		'profile' => array(
			'title' => '配置',
			'menu' => array(
				'profile' => array(
					'title' => '参数配置',
					'url' => url('profile/payment'),
					'icon' => 'wi wi-parameter',
					'permission_name' => 'profile_setting',
				)
			),
		),
	),
);

// $we7_system_menu['wxapp'] = array(
// 	'title' => '小程序',
// 	'section' => array(
// 	),
// );

$we7_system_menu['system'] = array(
	'title' => '系统管理',
	'url' => url('account/manage'),
	'section' => array(
		'wxplatform' => array(
			'title' => '微信',
			'menu' => array(
				'system_account' => array(
					'title' => ' 微信公众号管理',
					'url' => url('account/manage'),
					'icon' => 'wi wi-wechat',
					'permission_name' => 'system_account',
				),
				'system_platform' => array(
					'title' => ' 微信开放平台设置',
					'url' => url('system/platform'),
					'icon' => 'wi wi-exploitsetting',
					'permission_name' => 'system_platform',
				),
			)
		),
		'module' => array(
			'title' => '应用模块',
			'menu' => array(
				'system_module' => array(
					'title' => '我的应用管理',
					'url' => url('system/module'),
					'icon' => 'wi wi-appsetting',
					'permission_name' => 'system_module',
				),
				'system_module_group' => array(
					'title' => '应用权限套餐',
					'url' => url('system/module-group'),
					'icon' => 'wi wi-appjurisdiction',
					'permission_name' => 'system_module_group',
				),
			)
		),
		'user' => array(
			'title' => '帐户/用户',
			'menu' => array(
				'system_my' => array(
					'title' => '我的帐户',
					'url' => url('user/profile'),
					'icon' => 'wi wi-account',
					'permission_name' => 'system_my',
				),
				'system_user' => array(
					'title' => '用户管理',
					'url' => url('user/display'),
					'icon' => 'wi wi-user',
					'permission_name' => 'system_user',
				),
				'system_user_group' => array(
					'title' => '用户组管理',
					'url' => url('user/group'),
					'icon' => 'wi wi-userjurisdiction',
					'permission_name' => 'system_user_group',
				),
			)
		),
		'cloud' => array(
			'title' => '云服务',
			'menu' => array(
				'system_profile' => array(
					'title' => '系统更新',
					'url' => url('cloud/upgrade'),
					'icon' => 'wi wi-update',
					'permission_name' => 'system_cloud_upgrade',
				),
				'system_cloud_register' => array(
					'title' => '注册站点',
					'url' => url('cloud/profile'),
					'icon' => 'wi wi-registersite',
					'permission_name' => 'system_cloud_register',
				),
				'system_cloud_sms' => array(
					'title' => '短信管理',
					'url' => url('cloud/sms'),
					'icon' => 'wi wi-message',
					'permission_name' => 'system_cloud_sms',
				),
			)
		),
		'acticle' => array(
			'title' => '文章/公告',
			'menu' => array(
				'system_article' => array(
					'title' => '文章管理',
					'url' => url('article/news'),
					'icon' => 'wi wi-article',
					'permission_name' => 'system_article',
				),
				'system_article_notice' => array(
					'title' => '公告管理',
					'url' => url('article/notice'),
					'icon' => 'wi wi-notice',
					'permission_name' => 'system_article_notice',
				)
			)
		),
		'setting' => array(
			'title' => '设置',
			'menu' => array(
				'system_setting_updatecache' => array(
					'title' => '更新缓存',
					'url' => url('system/updatecache'),
					'icon' => 'wi wi-cache',
					'permission_name' => 'system_setting_updatecache',
				),
				'system_setting_site' => array(
					'title' => '站点设置',
					'url' => url('system/site'),
					'icon' => 'wi wi-parameter',
					'permission_name' => 'system_setting_site',
				),
				'system_setting_menu' => array(
					'title' => '菜单设置',
					'url' => url('system/menu'),
					'icon' => 'wi wi-parameter',
					'permission_name' => 'system_setting_menu',
				),
				'system_setting_attachment' => array(
					'title' => '附件设置',
					'url' => url('system/attachment'),
					'icon' => 'wi wi-attachment',
					'permission_name' => 'system_setting_attachment',
				),
				'system_setting_common' => array(
					'title' => '其他设置',
					'url' => url('system/common'),
					'icon' => 'wi wi-parameter',
					'permission_name' => 'system_setting_common',
				),
				'system_setting_systeminfo' => array(
					'title' => '系统信息',
					'url' => url('system/systeminfo'),
					'icon' => 'wi wi-parameter',
					'permission_name' => 'system_setting_systeminfo',
				),
				'system_setting_logs' => array(
					'title' => '查看日志',
					'url' => url('system/logs'),
					'icon' => 'wi wi-log',
					'permission_name' => 'system_setting_logs',
				),
			)
		),
		'utility' => array(
			'title' => '常用系统工具',
			'menu' => array(
				'system_utility_filecheck' => array(
					'title' => '系统文件校验',
					'url' => url('system/filecheck'),
					'icon' => 'wi wi-file',
					'permission_name' => 'system_utility_filecheck',
				),
				'system_utility_optimize' => array(
					'title' => '性能优化',
					'url' => url('system/optimize'),
					'icon' => 'wi wi-optimize',
					'permission_name' => 'system_utility_optimize',
				),
				'system_utility_database' => array(
					'title' => '数据库',
					'url' => url('system/database'),
					'icon' => 'wi wi-sql',
					'permission_name' => 'system_utility_database',
				),
				'system_utility_scan' => array(
					'title' => '木马查杀',
					'url' => url('system/scan'),
					'icon' => 'wi wi-safety',
					'permission_name' => 'system_utility_scan',
				),
				'system_utility_bom' => array(
					'title' => '检测文件BOM',
					'url' => url('system/bom'),
					'icon' => 'wi wi-bom',
					'permission_name' => 'system_utility_bom',
				),
			)
		),
	),
);

$we7_system_menu['sprovider'] = array (
	'title' => '广告联盟',
	'url' => url('system/content-provider'),
	'section' => array(),
);

$we7_system_menu['appmarket'] = array(
		'title' => '应用市场',
		'url' => 'http://s.we7.cc',
		'section' => array(),
);

return $we7_system_menu;