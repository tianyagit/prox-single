<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$ms = array();
$ms['account'] = array(
	'title' => '公众号',
	'section' => array(
		'platform_plus' => array(
			'title' => '增强功能',
			'menu' => array(
				'platform_reply' => array(
					'title' => '自动回复',
					'url' => url('platform/reply'),
					'append' => array(
						'title' => '<i class="fa fa-plus"></i>',
						'url' => url('platform/reply/post')
					),
					'permission_name' => 'platform_reply',
				),
				'platform_menu' => array(
					'title' => '自定义菜单',
					'url' => url('platform/menu'),
					'permission_name' => 'platform_menu',
				),
				'platform_qr' => array(
					'title' => '二维码/转化链接',
					'url' => url('platform/qr'),
					'permission_name' => 'platform_qr',
				),
				'platform_mass' => array(
					'title' => '定时群发',
					'url' => url('platform/mass'),
					'permission_name' => 'platform_mass',
				),
				'platform_material' => array(
					'title' => '素材/编辑器',
					'url' => url('platform/material'),
					'permission_name' => 'platform_material',
				),
				'platform_site' => array(
					'title' => '微官网',
					'url' => url('site/multi/display'),
					'permission_name' => 'platform_site'
				)
			),
		),
		'mc' => array(
			'title' => '粉丝',
			'menu' => array(
				'mc_fans' => array(
					'title' => '粉丝管理',
					'url' => url('mc/fans'),
					'promisson_name' => 'mc_fans',
				),
				'mc_member' => array(
					'title' => '会员管理',
					'url' => url('mc/member'),
					'promisson_name' => 'mc_member',
				),
				'site_editor' => array(
					'title' => '会员中心',
					'url' => url('site/editor/uc'),
					'promisson_name' => 'site_editor'
				),
			),
		),
		'profile' => array(
			'title' => '配置',
			'menu' => array(
				'profile' => array(
					'title' => '参数配置',
					'url' => url('profile/payment'),
					'promisson_name' => 'profile',
				)
			),
		),
		'platform_module' => array(
			'title' => '应用模块',
			'menu' => array(),
		),
	),
);

$ms['wxapp'] = array(
	'title' => '小程序',
	'section' => array(
	),
);

$ms['system'] = array(
	'title' => '系统管理',
	'url' => url('system/account'),
	'section' => array(
		'wxplatform' => array(
			'title' => '微信',
			'menu' => array(
				'system_account' => array(
					'title' => '微信公众号管理',
					'url' => url('system/account'),
					'permission_name' => 'system_account',
				),
				'system_platform' => array(
					'title' => '微信开放平台设置',
					'url' => url('system/platform'),
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
					'permission_name' => 'system_module',
				),
				'system_module_group' => array(
					'title' => '应用权限套餐',
					'url' => url('system/platform'),
					'permission_name' => 'system_module_group',
				),
			)
		),
		'user' => array(
			'title' => '帐户/用户',
			'menu' => array(
				'system_user_profile' => array(
					'title' => '我的帐户',
					'url' => url('user/profile'),
					'permission_name' => 'system_user_profile',
				),
				'system_user_display' => array(
					'title' => '用户管理',
					'url' => url('user/display'),
					'permission_name' => 'system_user_display',
				),
				'system_user_group' => array(
					'title' => '用户组管理',
					'url' => url('user/group'),
					'permission_name' => 'system_user_group',
				),
			)
		),
		'cloud' => array(
			'title' => '云服务',
			'menu' => array(
				'system_profile' => array(
					'title' => '系统更新',
					'url' => url('system/profile'),
					'permission_name' => 'system_module',
				),
				'system_user' => array(
					'title' => '注册站点',
					'url' => url('system/user'),
					'permission_name' => 'system_module_group',
				),
				'system_user_group' => array(
					'title' => '短信管理',
					'url' => url('system/user/group'),
					'permission_name' => 'system_module_group',
				),
			)
		),
		'acticle' => array(
			'title' => '文章/公告',
			'menu' => array(
				'system_article_news' => array(
					'title' => '文章管理',
					'url' => url('article/news'),
					'permission_name' => 'system_article_news',
				),
				'system_article_notice' => array(
					'title' => '公告管理',
					'url' => url('article/notice'),
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
					'permission_name' => 'system_setting_updatecache',
				),
				'system_setting_site' => array(
					'title' => '站点设置',
					'url' => url('system/site'),
					'permission_name' => 'system_setting_site',
				),
				'system_setting_attachment' => array(
					'title' => '附件设置',
					'url' => url('system/attachment'),
					'permission_name' => 'system_setting_attachment',
				),
				'system_setting_common' => array(
					'title' => '其他设置',
					'url' => url('system/common'),
					'permission_name' => 'system_setting_common',
				),
				'system_setting_systeminfo' => array(
					'title' => '系统信息',
					'url' => url('system/systeminfo'),
					'permission_name' => 'system_setting_systeminfo',
				),
				'system_setting_logs' => array(
					'title' => '查看日志',
					'url' => url('system/logs'),
					'permission_name' => 'system_setting_logs',
				),
			)
		),
		'utility' => array(
			'title' => '常用系统工具',
			'menu' => array(
				'system_setting_filecheck' => array(
					'title' => '系统文件校验',
					'url' => url('system/filecheck'),
					'permission_name' => 'system_setting_filecheck',
				),
				'system_setting_optimize' => array(
					'title' => '性能优化',
					'url' => url('system/optimize'),
					'permission_name' => 'system_setting_optimize',
				),
				'system_setting_database' => array(
					'title' => '数据库',
					'url' => url('system/database'),
					'permission_name' => 'system_setting_database',
				),
				'system_setting_scan' => array(
					'title' => '木马查杀',
					'url' => url('system/scan'),
					'permission_name' => 'system_setting_scan',
				),
				'system_setting_bom' => array(
					'title' => '检测文件BOM',
					'url' => url('system/bom'),
					'permission_name' => 'system_setting_bom',
				),
			)
		),
	),
);

return $ms;

$ms['platform'][] =  array(
	'title' => '基本功能',
	'items' => array(
		array(
			'title' => '文字回复',
			'url' => url('platform/reply', array('m' => 'basic')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>', 
				'url' => url('platform/reply/post', array('m' => 'basic'))
			),
			'permission_name' => 'platform_reply_basic'
		),
		array(
			'title' => '图文回复',
			'url' => url('platform/reply', array('m' => 'news')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>', 
				'url' => url('platform/reply/post', array('m' => 'news')),
			),
			'permission_name' => 'platform_reply_news'
		),
		array(
			'title' => '音乐回复',
			'url' => url('platform/reply', array('m' => 'music')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>', 
				'url' => url('platform/reply/post', array('m' => 'music'))
			),
			'permission_name' => 'platform_reply_music'
		),
		array(
			'title' => '图片回复',
			'url' => url('platform/reply', array('m' => 'images')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>',
				'url' => url('platform/reply/post', array('m' => 'images'))
			),
			'permission_name' => 'platform_reply_images'
		),
		array(
			'title' => '语音回复',
			'url' => url('platform/reply', array('m' => 'voice')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>',
				'url' => url('platform/reply/post', array('m' => 'voice'))
			),
			'permission_name' => 'platform_reply_voice'
		),
		array(
			'title' => '视频回复',
			'url' => url('platform/reply', array('m' => 'video')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>',
				'url' => url('platform/reply/post', array('m' => 'video'))
			),
			'permission_name' => 'platform_reply_video'
		),
		array(
			'title' => '自定义接口回复',
			'url' => url('platform/reply', array('m' => 'userapi')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>', 
				'url' => url('platform/reply/post', array('m' => 'userapi')),
			),
			'permission_name' => 'platform_reply_userapi'
		),
		array(
			'title' => '系统回复',
			'url' => url('platform/special/display'),
			'permission_name' => 'platform_reply_system'
		),
		array(
			'title' => '自动回复',
			'url' => './index.php?c=platform&a=autoreply&m=keyword',
			'append' => array(
				'title' => '',
				'url' => '',
			),
			'permission_name' => 'platform_reply_autoreply',
		),
	)
);
$ms['platform'][] =  array(
	'title' => '高级功能',
	'items' => array(
		array(
			'title' => '常用服务接入',
			'url' => url('platform/service/switch'),
			'permission_name' => 'platform_service'
		),
		array(
			'title' => '自定义菜单',
			'url' => url('platform/menu'),
			'permission_name' => 'platform_menu'
		),
		array(
			'title' => '特殊消息回复',
			'url' => url('platform/special/message'),
			'permission_name' => 'platform_special'
		),
		array(
			'title' => '二维码管理',
			'url' => url('platform/qr'),
			'permission_name' => 'platform_qr'
		),
		array(
			'title' => '多客服接入',
			'url' => url('platform/reply', array('m' => 'custom')),
			'permission_name' => 'platform_reply_custom'
		),
		array(
			'title' => '长链接二维码',
			'url' => url('platform/url2qr'),
			'permission_name' => 'platform_url2qr'
		)
	)
);
$ms['platform'][] =  array(
	'title' => '数据统计',
	'items' => array(
		array(
			'title' => '聊天记录',
			'url' => url('platform/stat/history'),
			'permission_name' => 'platform_stat_history'
		),
		array(
			'title' => '回复规则使用情况',
			'url' => url('platform/stat/rule'),
			'permission_name' => 'platform_stat_rule'
		),
		array(
			'title' => '关键字命中情况',
			'url' => url('platform/stat/keyword'),
			'permission_name' => 'platform_stat_keyword'
		),
		array(
			'title' => '参数',
			'url' => url('platform/stat/setting'),
			'permission_name' => 'platform_stat_setting'
		)
	)
);
$ms['site'][] =  array(
	'title' => '微站管理',
	'items' => array(
		array(
			'title' => '站点管理',
			'url' => url('site/multi/display'),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>',
				'url' => url('site/multi/post'),
			),
			'permission_name' => 'site_multi_display'
		),
		array(
			'title' => '站点添加/编辑',
			'is_permission' => 1,
			'permission_name' => 'site_multi_post'
		),
		array(
			'title' => '站点删除',
			'is_permission' => 1,
			'permission_name' => 'site_multi_del'
		),
		array(
			'title' => '模板管理',
			'url' => url('site/style/template'),
			'permission_name' => 'site_style_template'
		),
		array(
			'title' => '模块模板扩展',
			'url' => url('site/style/module'),
			'permission_name' => 'site_style_module'
		),
	)
);
$ms['site'][] =  array(
	'title' => '特殊页面管理',
	'items' => array(
		array(
			'title' => '会员中心',
			'url' => url('site/editor/uc'),
			'permission_name' => 'site_editor_uc'
		),
		array(
			'title' => '专题页面', 
			'url' => url('site/editor/page'),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>',
				'url' => url('site/editor/design'),
			),
			'permission_name' => 'site_editor_page'
		),
	)
);
$ms['site'][] =  array(
	'title' => '功能组件',
	'items' => array(
		array(
			'title' => '分类设置',
			'url' => url('site/category'),
			'permission_name' => 'site_category'
		),
		array(
			'title' => '文章管理',
			'url' => url('site/article'),
			'permission_name' => 'site_article'
		),
	)
);
$ms['mc'][] = array(
	'title' => '粉丝管理',
	'items' => array(
		array(
			'title' => '粉丝分组',
			'url' => url('mc/fangroup'),
			'permission_name' => 'mc_fangroup'
		),
		array(
			'title' => '粉丝',
			'url' => url('mc/fans'),
			'permission_name' => 'mc_fans'
		),
	)
);

$ms['mc'][] = array(
	'title' => '会员中心',
	'items' => array(
		array(
			'title' => '会员中心访问入口',
			'url' => url('platform/cover/mc'),
			'permission_name' => 'platform_cover_mc'
		),
		array(
			'title' => '会员',
			'url' => url('mc/member'),
			'permission_name' => 'mc_member'
		),
		array(
			'title' => '会员组',
			'url' => url('mc/group'),
			'permission_name' => 'mc_group'
		),
		array(
			'title' => '会员微信通知',
			'url' => url('mc/tplnotice'),
			'permission_name' => 'mc_tplnotice'
		),
		array(
			'title' => '会员积分管理',
			'url' => url('mc/creditmanage'),
			'permission_name' => 'mc_creditmanage'
		),
		array(
			'title' => '会员字段管理',
			'url' => url('mc/fields'),
			'permission_name' => 'mc_fields'
		)
	)
);
$ms['mc'][] = array(
	'title' => '会员卡管理',
	'items' => array(
		array(
			'title' => '会员卡访问入口',
			'url' => url('platform/cover/card'),
			'permission_name' => 'platform_cover_card'
		),
		array(
			'title' => '会员卡管理',
			'url' => url('mc/card'),
			'permission_name' => 'mc_card'
		),
		array(
			'title' => '商家设置',
			'url' =>url('mc/business'),
			'permission_name' => 'mc_business'
		),
		array(
			'title' => '店员操作访问入口',
			'url' => url('platform/cover/clerk'),
			'permission_name' => 'platform_cover_clerk'
		),
		array(
			'title' => '操作店员管理',
			'url' => url('activity/offline'),
			'permission_name' => 'activity_offline'
		)
	)
);
$ms['mc'][] = array(
	'title' => '积分兑换',
	'items' => array(
		array(
			'title' => '折扣券',
			'url' => url('activity/coupon'),
			'permission_name' => 'activity_coupon'
		),
		array(
			'title' => '代金券',
			'url' => url('activity/token'),
			'permission_name' => 'activity_token'
		),
		array(
			'title' => '真实物品',
			'url' => url('activity/goods'),
			'permission_name' => 'activity_goods',
		),
				array(
			'title' => '微信卡券',
			'url' => url('wechat/manage'),
			'permission_name' => 'wechat_manage'
		),
		array(
			'title' => '卡券核销',
			'url' => url('wechat/consume'),
			'permission_name' => 'wechat_consume',
		),
	)
);
$ms['mc'][] = array(
	'title' => '通知中心',
	'items' => array(
		array(
			'title' => '群发消息&通知',
			'url' => url('mc/broadcast'),
			'permission_name' => 'mc_broadcast',
		),
		array(
			'title' => '微信群发',
			'url' => url('mc/mass'),
			'permission_name' => 'mc_mass',
		),
		array(
			'title' => '通知参数',
			'url' => url('profile/notify'),
			'permission_name' => 'profile_notify',
		),
	)
);

$ms['setting'][] = array(
	'title' => '公众号选项',
	'items' => array(
		array(
			'title' => '支付参数',
			'url' => url('profile/payment'),
			'permission_name' => 'profile_payment',
		),
		array(
			'title' => '借用 oAuth 权限',
			'url' => url('mc/passport/oauth'),
			'permission_name' => 'mc_passport_oauth',
		),
		array(
			'title' => '借用 JS 分享权限',
			'url' => url('profile/jsauth'),
			'permission_name' => 'profile_jsauth',
		),
	)
);
$ms['setting'][] = array(
	'title' => '会员及粉丝选项',
	'items' => array(
		array(
			'title' => '积分设置',
			'url' => url('mc/credit'),
			'permission_name' => 'mc_credit',
		),
		array(
			'title' => '注册设置',
			'url' => url('mc/passport/passport'),
			'permission_name' => 'mc_passport_passport',
		),
		array(
			'title' => '粉丝同步设置',
			'url' => url('mc/passport/sync'),
			'permission_name' => 'mc_passport_sync',
		),
		array(
			'title' => 'UC站点整合',
			'url' => url('mc/uc'),
			'permission_name' => 'mc_uc',
		),
	)
);
$ms['setting'][] = array(
	'title' => '其他功能选项',
	'items' => array(
			)
);

$ms['ext'][] = array(
	'title' => '管理',
	'items' => array(
		array(
			'title' => '扩展功能管理',
			'url' => url('profile/module'),
			'permission_name' => 'profile_module',
		),
	)
);
return $ms;
