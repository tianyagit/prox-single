<?php
/**
 * 新增素材
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 *
 */
defined('IN_IA') or exit('Access Denied');
load()->func('file');
load()->model('material');
load()->model('account');
$dos = array(
	'news',
	'tomedia',
	'addnews' 
);
$do = in_array($do, $dos) ? $do : 'news';

uni_user_permission_check('platform_material');

$_W['page']['title'] = '新增素材-微信素材';

if ($do == 'tomedia') {
	iajax('0', tomedia($_GPC['url']), '');
}

if ($do == 'news') {
	$type = trim($_GPC['type']);
	$newsid = intval($_GPC['newsid']);
	$upload_limit = material_upload_limit();
	if (empty($newsid)) {
		if ($type == 'reply') {
			$reply_news_id = intval($_GPC['reply_news_id']);
			$news = pdo_get('news_reply', array(
				'id' => $reply_news_id 
			));
			$news_list = pdo_getall('news_reply', array(
				'parent_id' => $news['id'] 
			), array(), '', ' displayorder ASC');
			$news_list = array_merge(array(
				$news 
			), $news_list);
			if (! empty($news_list)) {
				foreach ($news_list as $key => &$row_news) {
					$row_news = array(
						'uniacid' => $_W['uniacid'],
						'thumb_url' => $row_news['thumb'],
						'title' => $row_news['title'],
						'author' => $row_news['author'],
						'digest' => $row_news['description'],
						'content_source_url' => preg_replace('/(http|https):\/\/.\/index.php/', './index.php', $row_news['url']),
						'content' => $row_news['content'],
						'show_cover_pic' => intval($row_news['incontent']),
						'displayorder' => $key 
					);
				}
			}
		}
	} else {
		$attachment = material_get($newsid);
		$news_list = $attachment['news'];
	}
	template('platform/material-post');
}

if ($do == 'addnews') {
	$account_api = WeAccount::create($_W['acid']);
	$post_news = array();
	$is_sendto_wechat = trim($_GPC['target']) == 'wechat' ? true : false;
	$attach_id = intval($_GPC['attach_id']);
	if (empty($_GPC['news'])) {
		iajax(- 1, '提交内容参数有误');
	}
	foreach ($_GPC['news'] as $key => $news) {
		$post_news[] = array(
			'id'	=> $news['id'],
			'uniacid' => $_W['uniacid'],
			'thumb_media_id' => $news['media_id'],
			'thumb_url' => $news['thumb'],
			'title' => $news['title'],
			'author' => $news['author'],
			'digest' => $news['digest'],
			'content' => htmlspecialchars_decode($news['content']),
			'content_source_url' => $news['content_source_url'],
			'show_cover_pic' => $news['show_cover_pic'],
			'displayorder' => $key
		);
	}
	if (!empty($attach_id)){
		$wechat_attachment = pdo_get('wechat_attachment', array(
			'id' => $attach_id,
			'uniacid' => $_W['uniacid']
		));
		$wechat_attachment['model'] = 'local';
		pdo_update('wechat_attachment', $wechat_attachment, array(
			'id' => $attach_id
		));
		foreach ($post_news as $id => $news) {
			pdo_update('wechat_news', $news, array(
				'id' => $news['id']
			));
		}
	}else{
		$wechat_attachment = array(
			'uniacid' => $_W['uniacid'],
			'acid' => $_W['acid'],
			'media_id' => '',
			'type' => 'news',
			'model' => 'local',
			'createtime' => time()
		);
		pdo_insert('wechat_attachment', $wechat_attachment);
		$attach_id = pdo_insertid();
		foreach ($post_news as $key => $news) {
			$news['attach_id'] = $attach_id;
			pdo_insert('wechat_news', $news);
		}
	}
	if ($is_sendto_wechat) {
		$result = material_local_news_upload($attach_id);
	}
	if (is_error($result)){
		iajax(-1, '提交微信素材失败');
	}else{
		iajax(0, '编辑图文素材成功');
	}
}