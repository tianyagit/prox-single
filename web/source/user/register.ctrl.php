<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');
load()->model('setting');
load()->classs('oauth2/oauth2client');

$dos = array('display', 'valid_mobile', 'register','check_username','get_extendfields');
$do = in_array($do, $dos) ? $do : 'display';

$_W['page']['title'] = '注册选项 - 用户设置 - 用户管理';
if (empty($_W['setting']['register']['open'])) {
	itoast('本站暂未开启注册功能，请联系管理员！', '', '');
}

$register_type = safe_gpc_belong(safe_gpc_string($_GPC['register_type']), array('system', 'mobile'), 'system');

if ($register_type == 'system') {
	$extendfields = OAuth2Client::create($register_type)->systemFields();
} else {
	$setting_sms_sign = setting_load('site_sms_sign');
	$register_sign = !empty($setting_sms_sign['site_sms_sign']['register']) ? $setting_sms_sign['site_sms_sign']['register'] : '';
}

if ($do == 'valid_mobile' || $do == 'register' && $register_type == 'mobile') {
	$validate_mobile = OAuth2Client::create('mobile')->validateMobile();
	if (is_error($validate_mobile)) {
		iajax(-1, $validate_mobile['message']);
	}
}

if ($do == 'valid_mobile') {
	iajax(0, '本地校验成功');
}

if ($do == 'register') {

	if(checksubmit() || $_W['ispost'] && $_W['isajax']) {

		$register_user = OAuth2Client::create($register_type)->register();
		if ($register_type == 'system') {
			if (is_error($register_user)) {
				itoast($register_user['message']);
			} else {
				itoast($register_user['message'], url('user/login'));
			}
		}

		if ($register_type == 'mobile') {
			if (is_error($register_user)) {
				iajax(-1, $register_user['message']);
			} else {
				iajax(0, $register_user['message'], url('user/login'));
			}
		}
	}
}

/*
 * 校验用户名是否存在
 * @lgl 20180302
 * */
if ($do == 'check_username') {
    load()->model('user');
    $member['username'] = trim($_GPC['username']);
    if(user_check(array('username' => $member['username']))) {
        iajax(-1, '非常抱歉，此用户名已经被注册，你需要更换注册名称！');
    }else{
        iajax(0,'用户名未被注册');
    }
}

/*
 * 获取用户注册字段
 * @lgl 20180302
 * */
if($do == 'get_extendfields'){
    $extendfields = OAuth2Client::create($register_type)->systemFields();

    // 给注册拓展字段添加 fieldErr 和 fieldMsg 属性 (前端验证提示)
    foreach($extendfields as $k => $v){
        $extendfields[$k][$k.'Err'] = false;        # 错误显示
        $extendfields[$k][$k.'Msg'] = '';           # 错误信息
    }

    iajax(0,$extendfields);
}

template('user/register');