<?php
/**
 * 素材管理列表页
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('material');
load()->model('mc');
load()->func('file');

$dos = array(
	'display',
	'sync',
	'del_material',
	'send',
	'trans' 
);
$do = in_array($do, $dos) ? $do : 'display';

uni_user_permission_check('platform_material');

$_W['page']['title'] = '永久素材-微信素材';

if ($do == 'send') {
	$group = intval($_GPC['group']);
	$type = trim($_GPC['type']);
	$id = intval($_GPC['id']);
	$media = pdo_get('wechat_attachment', array(
		'uniacid' => $_W['uniacid'],
		'id' => $id 
	));
	if (empty($media)) {
		iajax(1, '素材不存在', '');
	}
	$media_id = trim($media['media_id']);
	$account_api = WeAccount::create();
	$result = $account_api->fansSendAll($group, $type, $media['media_id']);
	if (is_error($result)) {
		iajax(1, $result['message'], '');
	}
	$groups = pdo_get('mc_fans_groups', array(
		'uniacid' => $_W['uniacid'],
		'acid' => $_W['acid'] 
	));
	if (! empty($groups)) {
		$groups = iunserializer($groups['groups']);
	}
	$record = array(
		'uniacid' => $_W['uniacid'],
		'acid' => $_W['acid'],
		'groupname' => $groups[$group]['name'],
		'fansnum' => $groups[$group]['count'],
		'msgtype' => $type,
		'group' => $group,
		'attach_id' => $id,
		'status' => 0,
		'type' => 0,
		'sendtime' => TIMESTAMP,
		'createtime' => TIMESTAMP 
	);
	pdo_insert('mc_mass_record', $record);
	iajax(0, '发送成功！', '');
}

if ($do == 'display') {
	$type = trim($_GPC['type']) ? trim($_GPC['type']) : 'news';
	$islocal = isset($_GPC['islocal']) && (trim($_GPC['islocal']) != '' || trim($_GPC['islocal']) != '0') ? true : false;
	$group = mc_fans_groups(true);
	if ($type == 'news') {
		$condition = " as a RIGHT JOIN " . tablename('wechat_news') . " as b ON a.id = b.attach_id WHERE a.uniacid = :uniacid AND a.type = :type AND (a.model = :model || a.model = :modela)";
		$params = array(
			':uniacid' => $_W['uniacid'],
			':type' => $type,
			':model' => 'perm',
			':modela' => 'local' 
		);
		$id = intval($_GPC['id']);
		$title = addslashes($_GPC['title']);
		if (! empty($title)) {
			$condition .= ' AND (b.title LIKE :title OR b.author = :title OR b.digest LIKE :title)';
			$params[':title'] = '%' . $title . "%";
		}
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize = 21;
		$limit = " ORDER BY a.createtime DESC, b.id ASC LIMIT " . ($pageindex - 1) * $pagesize . ", {$pagesize}";
		$total = pdo_fetchall("SELECT a.* FROM " . tablename('wechat_attachment') . $condition, $params);
		$total = count($total);
		$material_list = pdo_fetchall("SELECT a.* FROM " . tablename('wechat_attachment') . $condition . $limit, $params, 'id');
		
		if (! empty($material_list)) {
			foreach ($material_list as &$material) {
				$material['items'] = pdo_fetchall("SELECT * FROM " . tablename('wechat_news') . " WHERE uniacid = :uniacid AND attach_id = :attach_id ORDER BY displayorder ASC", array(
					':uniacid' => $_W['uniacid'],
					':attach_id' => $material['id'] 
				));
				if (! empty($material['items'])) {
					$material['prompt_msg'] = false;
					foreach ($material['items'] as $material_row) {
						if (empty($material_row['title']) || empty($material_row['thumb_url']) || empty($material_row['content'])) {
							$material['prompt_msg'] = true;
							break;
						}
					}
				}
			}
			unset($material);
		}
		$pager = pagination($total, $pageindex, $pagesize);
	}
	
	if ($type == 'image') {
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize = 12;
		if ($islocal) {
			$image_list = pdo_getslice('core_attachment', array(
				'uniacid' => $_W['uniacid'],
				'type' => '1' 
			), array(
				$pageindex,
				$pagesize 
			), $total, array(), '', 'createtime DESC');
			$pager = pagination($total, $pageindex, $pagesize);
		} else {
			$image_list = pdo_getslice('wechat_attachment', array(
				'uniacid' => $_W['uniacid'],
				'type' => 'image',
				'model' => 'perm' 
			), array(
				$pageindex,
				$pagesize 
			), $total, array(), '', 'createtime DESC');
			$pager = pagination($total, $pageindex, $pagesize);
		}
	}
	
	if ($type == 'voice') {
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize = 12;
		if ($islocal) {
			$voice_list = pdo_getslice('core_attachment', array(
				'uniacid' => $_W['uniacid'],
				'type' => '2' 
			), array(
				$pageindex,
				$pagesize 
			), $total, array(), '', 'createtime DESC');
			$pager = pagination($total, $pageindex, $pagesize);
		} else {
			$voice_list = pdo_getslice('wechat_attachment', array(
				'uniacid' => $_W['uniacid'],
				'type' => 'voice',
				'model' => 'perm' 
			), array(
				$pageindex,
				$pagesize 
			), $total, array(), '', 'createtime DESC');
			$pager = pagination($total, $pageindex, $pagesize);
		}
	}
	
	if ($type == 'video') {
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize = 12;
		if ($islocal) {
			$video_list = pdo_getslice('core_attachment', array(
				'uniacid' => $_W['uniacid'],
				'type' => '3' 
			), array(
				$pageindex,
				$pagesize 
			), $total, array(), '', 'createtime DESC');
			$pager = pagination($total, $pageindex, $pagesize);
		} else {
			$video_list = pdo_getslice('wechat_attachment', array(
				'uniacid' => $_W['uniacid'],
				'type' => 'video',
				'model' => 'perm' 
			), array(
				$pageindex,
				$pagesize 
			), $total, array(), '', 'createtime DESC');
			foreach ($video_list as &$row) {
				$row['tag'] = $row['tag'] == '' ? array() : iunserializer($row['tag']);
			}
			unset($row);
			$pager = pagination($total, $pageindex, $pagesize);
		}
	}
}

if ($do == 'del_material') {
	$material_id = intval($_GPC['material_id']);
	$postserver = trim($_GPC['server']) == 'local' ? 'local' : 'wechat';
	$del_code = 0;
	if ($postserver == 'wechat') {
		$account_api = WeAccount::create($_W['acid']);
		$material = pdo_get('wechat_attachment', array(
			'uniacid' => $_W['uniacid'],
			'id' => $material_id 
		));
		$result = $account_api->delMaterial($material['media_id']);
		if ($result['errcode'] == 0) {
			$result = error(0, $material['type']);
			if ($material['type'] == 'news') {
				pdo_delete('wechat_news', array(
					'uniacid' => $_W['uniacid'],
					'attach_id' => $material['id'] 
				));
			}
			pdo_delete('wechat_attachment', array(
				'uniacid' => $_W['uniacid'],
				'id' => $material_id 
			));
		}
	} elseif ($postserver == 'local') {
		$local_type_array = array(
			'1' => 'image',
			'2' => 'voice',
			'3' => 'video' 
		);
		$material = pdo_get('core_attachment', array(
			'uniacid' => $_W['uniacid'],
			'id' => $material_id 
		));
		if (empty($material)) {
			iajax(- 1, '文件不存在或已经删除');
		}
		if (empty($_W['isfounder']) && $_W['role'] != ACCOUNT_MANAGE_NAME_MANAGER) {
			iajax(- 1, '您没有权限删除该文件');
		}
		if (! empty($_W['setting']['remote']['type'])) {
			$status = file_remote_delete($material['attachment']);
		} else {
			$status = file_delete($material['attachment']);
		}
		if (is_error($status)) {
			iajax(- 1, '删除文件操作发生错误');
		}
		pdo_delete('core_attachment', array(
			'uniacid' => $_W['uniacid'],
			'id' => $material_id 
		));
		$result = error(0, $local_type_array[$material['type']]);
	}
	iajax($del_code, $result);
}

if ($do == 'sync') {
	$account_api = WeAccount::create($_W['acid']);
	$pageindex = max(1, $_GPC['pageindex']);
	$type = empty($_GPC['type']) ? 'news' : $_GPC['type'];
	$news_list = $account_api->batchGetMaterial($type, ($pageindex - 1) * 20);
	$wechat_existid = empty($_GPC['wechat_existid']) ? array() : $_GPC['wechat_existid'];
	if ($pageindex == 1) {
		$original_newsid = pdo_getall('wechat_attachment', array(
			'uniacid' => $_W['uniacid'],
			'type' => $type,
			'model' => 'perm' 
		), array(
			'id' 
		), 'id');
		$original_newsid = array_keys($original_newsid);
		$wechat_existid = material_sync($news_list['item'], array(), $type);
		if ($news_list['total_count'] > 20) {
			$total = ceil($news_list['total_count'] / 20);
			iajax('1', array(
				'type' => $type,
				'total' => $total,
				'pageindex' => $pageindex + 1,
				'wechat_existid' => $wechat_existid,
				'original_newsid' => $original_newsid 
			), '');
		}
	} else {
		$wechat_existid = material_sync($news_list['item'], $wechat_existid, $type);
		$total = intval($_GPC['total']);
		$original_newsid = $_GPC['original_newsid'];
		if ($total != $pageindex) {
			iajax('1', array(
				'type' => $type,
				'total' => $total,
				'pageindex' => $pageindex + 1,
				'wechat_existid' => $wechat_existid,
				'original_newsid' => $original_newsid 
			), '');
		}
		if (empty($original_newsid)) {
			$original_newsid = array();
		}
	}
	$delete_id = array_diff($original_newsid, $wechat_existid);
	if (! empty($delete_id) && is_array($delete_id)) {
		foreach ($delete_id as $id) {
			pdo_delete('wechat_attachment', array(
				'uniacid' => $_W['uniacid'],
				'id' => $id 
			));
			pdo_delete('wechat_news', array(
				'uniacid' => $_W['uniacid'],
				'attach_id' => $id 
			));
		}
	}
	iajax(0, '更新成功！', '');
}

if ($do == 'trans') {
	$material_id = intval($_GPC['material_id']);
	$type = trim($_GPC['type']);
	$allow_type_arr = array(
		'image',
		'voice',
		'video',
		'thumb',
		'audio' 
	);
	if (! in_array($type, $allow_type_arr)) {
		iajax(- 1, '参数有误');
	}
	$material = pdo_get('core_attachment', array(
		'uniacid' => $_W['uniacid'],
		'id' => $material_id 
	));
	if (empty($material)) {
		iajax(- 1, '同步素材不存在或已删除');
	}
	if (! empty($_W['setting']['remote']['type'])) {
		// 同步素材到本地
		$remote_file_url = tomedia($material['attachment']);
		$remote_file_url_parts = parse_url($remote_file_url);
		$remote_file_url_parts['basename'] = pathinfo($remote_file_url, PATHINFO_BASENAME);
		$remote_file_url_parts['dirname'] = pathinfo($remote_file_url_parts['path'], PATHINFO_DIRNAME) . '/';
		$remote_file_download = downloadFile($remote_file_url, $remote_file_url_parts['dirname'], $remote_file_url_parts['basename']);
		if (! $remote_file_download) {
			iajax(- 1, '本地同步素材失败');
		}
		$filepath = $remote_file_download;
	} else {
		$filepath = ATTACHMENT_ROOT . $material['attachment'];
	}
	// 同步到微信
	$acc = WeAccount::create($_W['uniacid']);
	$token = $acc->getAccessToken();
	if (is_error($token)) {
		$result['message'] = $token['message'];
		iajax(- 1, $result['message']);
	}
	$sendapi = 'https://api.weixin.qq.com/cgi-bin/material/add_material' . "?access_token={$token}&type={$type}";
	$data = array(
		'media' => '@' . $filepath 
	);
	if ($type == 'video') {
		$description = array(
			'title' => urlencode(trim($remote_file_url_parts['basename'])),
			'introduction' => urlencode(trim($remote_file_url_parts['basename'])) 
		);
		$data['description'] = urldecode(json_encode($description));
	}
	
	$resp = ihttp_request($sendapi, $data);
	if (is_error($resp)) {
		$result['error'] = 0;
		$result['message'] = $resp['message'];
		iajax(- 1, $result['message']);
	}
	$content = @json_decode($resp['content'], true);
	if (empty($content)) {
		$result['error'] = 0;
		$result['message'] = "接口调用失败, 元数据: {$resp['meta']}";
		iajax(- 1, $result['message']);
	}
	if (! empty($content['errcode'])) {
		$result['error'] = 0;
		$result['message'] = "访问微信接口错误, 错误代码: {$content['errcode']}, 错误信息: {$content['errmsg']},错误详情：{$acc->error_code($content['errcode'])}";
		iajax(- 1, json_encode($result));
	}
	if (! empty($content['media_id'])) {
		$result['media_id'] = $content['media_id'];
	}
	if (! empty($content['thumb_media_id'])) {
		$result['media_id'] = $content['thumb_media_id'];
	}
	$insert = array(
		'uniacid' => $_W['uniacid'],
		'acid' => $_W['acid'],
		'uid' => $_W['uid'],
		'filename' => $remote_file_url_parts['basename'],
		'attachment' => $remote_file_url_parts['path'],
		'media_id' => $result['media_id'],
		'type' => $type,
		'model' => 'perm',
		'createtime' => TIMESTAMP 
	);
	if ($type == 'image' || $type == 'thumb') {
		$size = getimagesize($filepath);
		$insert['width'] = $size[0];
		$insert['height'] = $size[1];
		$insert['tag'] = $content['url'];
		if (! empty($insert['tag'])) {
			$insert['attachment'] = $content['url'];
		}
		$result['width'] = $size[0];
		$result['hieght'] = $size[1];
	}
	if ($type == 'video') {
		$insert['tag'] = iserializer($description);
	}
	pdo_insert('wechat_attachment', $insert);
	$result['type'] = $type;
	$result['url'] = tomedia($remote_file_url_parts['path']);
	
	if ($type == 'image' || $type == 'thumb') {
		@unlink($filepath);
	}
	if ($type == 'video') {
		$result['title'] = $description['title'];
		$result['introduction'] = $description['introduction'];
	}
	$result['mode'] = 'perm';
	// 删除本地文件和数据库
	file_delete($remote_file_url_parts['path']);
	pdo_delete('core_attachment', array(
		'id' => $material_id 
	));
	iajax(0, json_encode($result));
}

template('platform/material');