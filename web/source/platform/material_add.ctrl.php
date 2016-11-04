<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('news', 'tomedia', 'addnews', 'thumb_upload', 'image_upload');
$do = in_array($do, $dos) ? $do : 'news';

$_W['page']['title'] = '新增素材-微信素材';
load()->func('file');
load()->model('material');
uni_user_permission_check('material_mass');

if ($do == 'tomedia') {
	$url = $_GPC['url'];
	message(error('0', tomedia($url)), '', 'ajax');
}

if($do == 'news') {
	$newsid = intval($_GPC['newsid']);
	$news_list = pdo_getall('wechat_news', array('uniacid' => $_W['uniacid'], 'attach_id' => $newsid), array(), '',  'displayorder ASC');
	template('platform/material_add');
}

if($do == 'addnews') {
	$wechat_api = WeAccount::create($_W['acid']);
	$post = $_GPC['__input'];
	$operate = $post['operate'];
	$articles = array();
	$post_news = array();
	//获取所有的图片素材，构造一个已media_id为键的数组(为了获取图片的url)
	$image_list = $wechat_api->batchGetMaterial('image');
	$image_list = $image_list['data'];
	$image_data = array();
	foreach ($image_list as $image) {
		$image_data[$image['media_id']] = $image;
	}
	foreach($post['news'] as $key => $news) {
		if ($operate == 'add') {
			$row = array(
				'title' => urlencode($news['title']),
				'author' => urlencode($news['author']),
				'digest' => urlencode($news['description']),
				'content' => urlencode(addslashes(htmlspecialchars_decode($news['content']))),
				'show_cover_pic' => 1,
				'content_source_url' => urlencode($news['content_source_url']),
				'thumb_media_id' => $news['media_id'],
			);
			$articles['articles'][] = $row;
			$post_news[] = array(
				'uniacid' => $_W['uniacid'],
				'thumb_media_id' => $news['media_id'],
				'thumb_url' => $image_data[$news['media_id']]['url'],
				'title' => $news['title'],
				'author' => $news['author'],
				'digest' => $news['digest'],
				'content' => htmlspecialchars_decode($news['content']),
				'content_source_url' => $news['content_source_url'],
				'show_cover_pic' => 1,
				'url' => '',
				'displayorder' => $key
			);
		} else {
			$attach_mediaid =  pdo_getcolumn('wechat_attachment', array('id' => $post['attach_id'], 'uniacid' => $_W['uniacid']), 'media_id');
			$wechat_news[] = array(
				'media_id' => $attach_mediaid,
				'index' => $key,
				'articles' => array(
					'title' => urlencode($news['title']),
					'thumb_media_id' =>  urlencode($news['media_id']),
					'author' => urlencode($news['author']),
					'digest' => urlencode($news['digest']),
					'show_cover_pic' => 1,
					'content' => urlencode(addslashes(htmlspecialchars_decode($news['content']))),
					'content_source_url' => urlencode('www.baidu.com')
				)
			);
			$news['url'] = $image_data[$news['media_id']]['url'];
			$post_news[$news['id']] = array(
				'title' => $news['title'],
				'thumb_media_id' => $news['media_id'],
				'thumb_url' => $news['url'],
				'author' => $news['author'],
				'digest' => $news['digest'],
				'show_cover_pic' => 1,
				'content' => htmlspecialchars_decode($news['content']),
				'content_source_url' => 'www.baidu.com',
				'displayorder' => $key,
			);
		}
	}
	if ($operate == 'add') {
		$result = $wechat_api->addMatrialNews($articles);
		if(is_error($result)) {
			message($result, '', 'ajax');
		}
		$wechat_attachment = array(
			'uniacid' => $_W['uniacid'],
			'acid' => $_W['acid'],
			'media_id' => $result,
			'type' => 'news',
			'model' => 'perm',
			'createtime' => time()
		);
		pdo_insert('wechat_attachment', $wechat_attachment);
		$attach_id = pdo_insertid();
		foreach ($post_news as $news) {
			$news['attach_id'] = $attach_id;
			pdo_insert('wechat_news', $news);
		}
		message(error(0, '创建图文素材成功'), '', 'ajax');
	} else {
		foreach ($wechat_news as $edit_news) {
			$result = $wechat_api->editMaterialNews($edit_news);
			if (is_error($result)) {
				message($result, '', 'ajax');
			}
		}
		foreach ($post_news as $id => $news) {
			pdo_update('wechat_news', $news, array('uniacid' => $_W['uniacid'], 'id' => $id));
		}
		message(error(0, '更新图文素材成功'), '', 'ajax');
	}
}
