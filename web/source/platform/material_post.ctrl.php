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

$dos = array('news', 'tomedia', 'addnews', 'replace_image_url');
$do = in_array($do, $dos) ? $do : 'news';
uni_user_permission_check('material_mass');
$_W['page']['title'] = '新增素材-微信素材';

//把图文素材内容中的图片url替换成微信的url
if ($do == 'replace_image_url') {
	$content = htmlspecialchars_decode($_GPC['content']);
	$match = array();
	preg_match_all('/<img.*src=[\'"](.*\.(?:png|jpg|jpeg|jpe|gif))[\'"].*\/?>/iU', $content, $match);
	if(!empty($match[1])) {
		foreach($match[1] as $val) {
			if((strexists($val, 'http://') || strexists($val, 'https://')) && !strexists($val, 'mmbiz.qlogo.cn') && !strexists($val, 'mmbiz.qpic.cn')) {
				$images[] = $val;
			} else {
				if(strexists($val, './attachment/images/')) {
					$images[] = tomedia($val);
				}
			}
		}
	}
	if (!empty($images)) {
		foreach ($images as $image) {
			$thumb = file_fetch(tomedia($image), 1024, 'material/images');
			if(is_error($thumb)) {
				message($thumb, '', 'ajax');
			}
			$thumb = ATTACHMENT_ROOT . $thumb;
			$account_api = WeAccount::create($_W['acid']);
			$data = array(
				'media' => '@'. $thumb,
			);
			$result = $account_api->uploadNewsThumb($data);
			if (is_error($result)) {
				message($result, '', 'ajax');
			} else {
				$content = str_replace($image, $result['message'], $content);
			}
		}
	}
	message(error(0, $content), '', 'ajax');
}

if ($do == 'tomedia') {
	message(error('0', tomedia($_GPC['url'])), '', 'ajax');
}

if($do == 'news') {
	$newsid = intval($_GPC['newsid']);
	$news_list = pdo_getall('wechat_news', array('uniacid' => $_W['uniacid'], 'attach_id' => $newsid), array(), '',  'displayorder ASC');
	template('platform/material_post');
}

if($do == 'addnews') {
	$account_api = WeAccount::create($_W['acid']);
	$operate = $_GPC['operate'] == 'add' ? 'add' : 'edit';
	$articles = array();
	$post_news = array();

	$image_data = array();
	//获取所有的图片素材，构造一个以media_id为键的数组(为了获取图片的url)
	$image_list = $account_api->batchGetMaterial('image');
	$image_list = $image_list['item'];
	foreach ($image_list as $image) {
		$image_data[$image['media_id']] = $image;
	}

	if(!empty($_GPC['news'])) {
		foreach($_GPC['news'] as $key => $news) {
			//微信接口结构
			$news_info = array(
				'title' => $news['title'],
				'author' => $news['author'],
				'digest' => $news['description'],
				'content' => addslashes(htmlspecialchars_decode($news['content'])),
				'show_cover_pic' => 1,
				'content_source_url' => $news['content_source_url'],
				'thumb_media_id' => $news['media_id'],
			);
			$post_data = array(
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

			if ($operate == 'add') {
				$post_news[] = $post_data;
				$articles['articles'][] = $news_info;
			} else {
				$attach_mediaid =  pdo_getcolumn('wechat_attachment', array('id' => intval($_GPC['attach_id']), 'uniacid' => $_W['uniacid']), 'media_id');
				$wechat_news[] = array(
					'media_id' => $attach_mediaid,
					'index' => $key,
					'articles' => $news_info
				);
				$post_news[$news['id']] = $post_data;
			}
		}
	}
	if ($operate == 'add') {
		$media_id = $account_api->addMatrialNews($articles);
		if(is_error($result)) {
			message($result, '', 'ajax');
		}
		$wechat_attachment = array(
			'uniacid' => $_W['uniacid'],
			'acid' => $_W['acid'],
			'media_id' => $media_id,
			'type' => 'news',
			'model' => 'perm',
			'createtime' => time()
		);
		pdo_insert('wechat_attachment', $wechat_attachment);
		$attach_id = pdo_insertid();
		$wechat_new = $account_api->getMaterial($result, $news);
		foreach ($post_news as $key => $news) {
			$news['attach_id'] = $attach_id;
			$news['url'] = $wechat_new['news_item'][$key]['url'];
			pdo_insert('wechat_news', $news);
		}
		message(error(0, '创建图文素材成功'), '', 'ajax');
	} else {
		foreach ($wechat_news as $edit_news) {
			$result = $account_api->editMaterialNews($edit_news);
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