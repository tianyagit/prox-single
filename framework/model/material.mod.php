<?php
defined('IN_IA') or exit('Access Denied');

/**
 * 同步微信素材
 * @param array $material 从微信接口拉取到的素材数据
 * @param array $exist_material 微信与本地同时存在的素材id集合（用于删除微信端不存在，本地存在的素材）
 * @param array $type 素材类型
 * @return array() 本地与微信端都存在的素材id集合
 */
function material_sync($material, $exist_material, $type) {
	global $_W;
	$material = empty($material) ? array() : $material;
	foreach ($material as $news) {
		$attachid = '';
		$material_exist = pdo_get('wechat_attachment', array('uniacid' => $_W['uniacid'], 'media_id' => $news['media_id']));
		if (empty($material_exist)) {
			$material_data = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $_W['acid'],
				'media_id' => $news['media_id'],
				'type' => $type,
				'model' => 'perm',
				'createtime' => $news['update_time']
			);
			if ($type == 'image') {
				$material_data['filename'] = $news['name'];
				$material_data['attachment'] = $news['url'];
			}
			if ($type == 'voice') {
				$material_data['filename'] = $news['name'];
			}
			if ($type == 'video') {
				$material_data['tag'] = iserializer(array('title' => $news['name']));
			}
			pdo_insert('wechat_attachment', $material_data);
			$attachid = pdo_insertid();
		} else {
			if ($type == 'image') {
				$material_data = array(
					'createtime' => $news['update_time'],
					'attachment' => $news['url'],
					'filename' => $news['name']
				);
				pdo_update('wechat_attachment', $material_data, array('uniacid' => $_W['uniacid'], 'media_id' => $news['media_id']));
			}
			if ($type == 'voice') {
				$material_data = array(
					'createtime' => $news['update_time'],
					'filename' => $news['name']
				);
				pdo_update('wechat_attachment', $material_data, array('uniacid' => $_W['uniacid'], 'media_id' => $news['media_id']));
			}
			if ($type == 'video') {
				$tag = empty($material_exist['tag']) ? array() : iunserializer($material_exist['tag']);
				$material_data = array(
					'createtime' => $news['update_time'],
					'tag' => iserializer(array('title' => $news['name'], 'url' => $tag['url']))
				);
				pdo_update('wechat_attachment', $material_data, array('uniacid' => $_W['uniacid'], 'media_id' => $news['media_id']));
			}
			$exist_material[] = $material_exist['id'];
		}
		if ($type == 'news') {
			$attachid = empty($attachid) ? $material_exist['id'] : $attachid;
			pdo_delete('wechat_news', array('uniacid' =>$_W['uniacid'], 'attach_id' => $attachid));
			foreach ($news['content']['news_item'] as $key => $new) {
				$new_data = array(
					'uniacid' => $_W['uniacid'],
					'attach_id' => $attachid,
					'thumb_media_id' => $new['thumb_media_id'],
					'thumb_url' => $new['thumb_url'],
					'title' => $new['title'],
					'author' => $new['author'],
					'digest' => $new['digest'],
					'content' => $new['content'],
					'content_source_url' => $new['content_source_url'],
					'show_cover_pic' => $new['show_cover_pic'],
					'url' => $new['url'],
					'displayorder' => $key,
				);
				pdo_insert('wechat_news', $new_data);
			}
		}
	}
	return $exist_material;
}

/**
 * 生成本地图文素材
 * @param array $post_data
 * @return int 本地图文素材ID
 */
function material_news_set($data, $attach_id = '0') {
	global $_W;
	$attach_id = intval($attach_id);
	foreach ($data as $key => $news) {
		if (empty($news['title']) || empty($news['author']) || empty($news['content'])){
			return error('-1', '参数不能为空');
		}
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
			'show_cover_pic' => $news['show_cover_pic'] ? 1 : 0,
			'displayorder' => $key
		);
	}
	if (!empty($attach_id)){
		$wechat_attachment = pdo_get('wechat_attachment', array(
			'id' => $attach_id,
			'uniacid' => $_W['uniacid']
		));
		if (empty($wechat_attachment)){
			return error('-2', '编辑素材不存在');
		}
		$wechat_attachment['model'] = 'local';
		pdo_update('wechat_attachment', $wechat_attachment, array(
			'id' => $attach_id
		));
		foreach ($post_news as $id => $news) {
			pdo_update('wechat_news', $news, array(
				'id' => $news['id']
			));
		}
	} else {
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
	return $attach_id;
}

/**
 * 获取素材
 * @param array $material 素材的id或者mediaid
 * @return array() 素材内容
 */
function material_get($attach_id) {
	if (empty($attach_id)) {
		return error(1, "素材id参数不能为空");
	}
	if (is_numeric($attach_id)) {
		$material = pdo_get('wechat_attachment', array('id' => $attach_id));
	} else {
		$media_id = trim($attach_id);
		$material = pdo_get('wechat_attachment', array('media_id' => $media_id)); 
	}
	if (!empty($material)) {
		if ($material['type'] == 'news') {
			$news = pdo_getall('wechat_news', array('attach_id' => $material['id']), array(), '', ' displayorder DESC');
			if (!empty($news)) {
				foreach ($news as &$news_row) {
					$news_row['content_source_url'] = preg_replace('/(http|https):\/\/.\/index.php/', './index.php', $news_row['content_source_url']);
					$news_row['thumb_url'] = tomedia($news_row['thumb_url']);
					preg_match_all('/src=[\'\"]?([^\'\"]*)[\'\"]?/i', $news_row['content'], $match);
					if (!empty($match[1])) {
						foreach ($match[1] as $val) {
							if ((strexists($val, 'http://') || strexists($val, 'https://')) && (strexists($val, 'mmbiz.qlogo.cn') || strexists($val, 'mmbiz.qpic.cn'))) {
								$news_row['content'] = str_replace($val, tomedia($val), $news_row['content']);
							}
						}
					}
					$news_row['content'] = str_replace('data-src', 'src', $news_row['content']);
				}
				unset($news_row);
			} else {
				return error('1', '素材不存在');
			}
			$material['news'] = $news;
		} elseif ($material['type'] == 'image') {
			$material['attachment'] = tomedia($material['attachment']);
		}
		return $material;
	} else {
		return error('1', "素材不存在");
	}
}

/**
 * 构造素材回复消息结构
 * @param array $material 素材的id
 * @return array() 回复消息结构
 */
function material_build_reply($attach_id) {
	if (empty($attach_id)) {
		return error(1, "素材id参数不能为空");
	}
	$cachekey = cache_system_key('material_reply:' . $attach_id);
	$reply = cache_load($cachekey);
	if (!empty($reply)) {
		return $reply;
	}
	$reply_material = material_get($attach_id);
	$reply = array();
	if ($reply_material['type'] == 'news') {
		if (!empty($reply_material['news'])) {
			foreach ($reply_material['news'] as $material) {
				$reply[] = array(
					'title' => $material['title'],
					'description' => $material['description'],
					'picurl' => $material['thumb_url'],
					'url' => !empty($material['content_source_url']) ? $material['content_source_url'] : $material['url'],
				);
			}
		}
	}
	cache_write($cachekey, $reply, CACHE_EXPIRE_MIDDLE);
	return $reply;
}

/**
 *将内容中通过tomeida()转义的微信图片代理地址替换成微信图片原生地址
 * @param $content string 待处理的图文内容
 */
function material_strip_wechat_image_proxy($content) {
	global $_W;
	$match_wechat = array();
	$content = htmlspecialchars_decode($content);
	preg_match_all ('/<img.*src=[\'"](.*)[\'"].*\/?>/iU', $content, $match_wechat);
	if (!empty($match_wechat[1])) {
		foreach ($match_wechat[1] as $val) {
			$wechat_thumb_url = urldecode(str_replace($_W['siteroot'] . 'web/index.php?c=utility&a=wxcode&do=image&attach=', '', $val));
			$content = str_replace($val, $wechat_thumb_url, $content);
		}
	}
	return $content;
}

/**
 * 获取内容中所有非微信图片的图片地址
 * @param $content string 待处理的内容
 * @param $images array 内容中所有图片的地址
 */
function material_get_image_url($content) {
	global $_W;
	$content = htmlspecialchars_decode ($content);
	$match = array ();
	$images = array ();
	preg_match_all ('/<img.*src=[\'"](.*\.(?:png|jpg|jpeg|jpe|gif))[\'"].*\/?>/iU', $content, $match);
	if (!empty($match[1])) {
		foreach ($match[1] as $val) {
			if ((strexists ($val, 'http://') || strexists ($val, 'https://')) && !strexists ($val, 'mmbiz.qlogo.cn') && !strexists ($val, 'mmbiz.qpic.cn')) {
				$images[] = $val;
			} else {
				if (strexists ($val, './attachment/images/')) {
					$images[] = tomedia ($val);
				}
			}
		}
	}
	return $images;
}

/**
 * 替换图文素材内容中图片url地址（把非微信url替换成微信url）
 * @param $content string 待处理的图文内容
 */
function material_parse_content($content) {
	global $_W;
	$content = material_strip_wechat_image_proxy($content);
	$images = material_get_image_url($content);
	if (!empty($images)) {
		foreach ($images as $image) {
			$thumb = file_fetch(tomedia($image), 1024, 'material/images');
			if(is_error($thumb)) {
				return $thumb;
			}
			$thumb = ATTACHMENT_ROOT . $thumb;
			$account_api = WeAccount::create($_W['acid']);
			$result = $account_api->uploadNewsThumb($thumb);
			if (is_error($result)) {
				return $result;
			} else {
				$content = str_replace($image, $result, $content);
			}
		}
	}
	return $content;
}

/**
 * 根据附件ID将，本地图文上传至微信服务器
 * @param int $attach_id
 * 
 */
function material_local_news_upload($attach_id) {
	global $_W;
	$account_api = WeAccount::create($_W['acid']);
	$material = material_get($attach_id);
	if (is_error($material)){
		return error('-1', '获取素材文件失败');
	}
	foreach ($material['news'] as $news) {
		$news['content'] = material_parse_content($news['content']);
		if (is_error($news['content'])) {
			return error('-2', $news['content']);
		}
		if (empty($news['thumb_media_id']) && !empty($news['thumb_url'])) {
			$result = material_local_upload_by_url($news['thumb_url']);
			if (is_error($result)){
				return error('-3', $result['message']);
			}
			$news['thumb_media_id'] = $result['media_id'];
			$news['thumb_url'] = $result['url'];
		}
		pdo_update('wechat_news', $news, array(
			'id' => $news['id']
		));
		if (empty($material['media_id'])){
			$articles['articles'][] = $news;
		} else {
			$edit_attachment['media_id'] = $material['media_id'];
			$edit_attachment['index'] = $news['displayorder'];
			$edit_attachment['articles'] = $news;
			$result = $account_api->editMaterialNews($edit_attachment);
			if (is_error($result)){
				return error('-4', $result['message']);
			}
		}
	}
	if (empty($material['media_id'])){
		$media_id = $account_api->addMatrialNews($articles);
		if (is_error($media_id)) {
			return error('-5', $media_id, '');
		}
		pdo_update('wechat_attachment', array(
			'media_id' => $media_id,
			'model' => 'perm'
		), array(
			'uniacid' => $_W['uniacid'],
			'id' => $attach_id
		));
	} else {
		pdo_update('wechat_attachment', array(
			'model' => 'perm'
		), array(
			'uniacid' => $_W['uniacid'],
			'id' => $attach_id
		));
	}
	return $material;
}

/**
 * 目前图片、音频、视频素材上传都用这个方法
 * 上传素材文件到微信，获取mediaId
 * @param string $url
 * 
 */
function material_local_upload_by_url($url, $type='images') {
	global $_W;
	$account_api = WeAccount::create($_W['acid']);
	if (! empty($_W['setting']['remote']['type'])) {
		$remote_file_url = tomedia($url);
		$filepath = file_fetch($remote_file_url,0,'');
		if(is_error($filepath)) {
			return $filepath;
		}
		$filepath = ATTACHMENT_ROOT . $filepath;
	} else {
		$url = str_replace('/attachment/', '', parse_url($url, PHP_URL_PATH));
		$filepath = ATTACHMENT_ROOT . $url;
	}
	return $account_api->uploadMediaFixed($filepath, $type);
}

/**
 * 同步本地素材到微信
 * @param int $material_id
 * @return array
 */
function material_local_upload($material_id){
	global $_W;
	$type_arr = array('1' => 'images', '2' => 'voices', '3' => 'videos');
	$material = pdo_get('core_attachment', array(
			'uniacid' => $_W['uniacid'],
			'id' => $material_id
	));
	if (empty($material)) {
		return error('-1', '同步素材不存在或已删除');
	}
	return material_local_upload_by_url($material['attachment'], $type_arr[$material['type']]);
}

/**
 * 获取后台设置上传文件大小限制
 *
 * @return array
 */
function material_upload_limit() {
	global $_W;
	$default = 5 * 1024 * 1024;
	$upload_limit = array(
		'num' => '30',
		'image' => $default,
		'voice' => $default,
		'video' => $default
	);
	$upload = $_W['setting']['upload'];
	if (isset($upload['image']['limit']) && (bytecount($upload['image']['limit'].'kb')>0)){
		$upload_limit['image'] = bytecount($upload['image']['limit'].'kb');
	}
	if (isset($upload['image']['limit']) && (bytecount($upload['audio']['limit'].'kb')>0)){
		$upload_limit['voice'] = $upload_limit['video'] = bytecount($upload['audio']['limit'].'kb');
	}
	return $upload_limit;
}

/**
 * 删除图文素材
 * @param int $material_id 素材id
 * @type string $type 素材类型
 * 
 */
function material_news_delete($material_id){
	global $_W;
	if (empty($_W['isfounder']) && $_W['role'] != ACCOUNT_MANAGE_NAME_MANAGER) {
		return error('-1', '您没有权限删除该文件');
	}
	$material_id = intval($material_id);
	$material = pdo_get('wechat_attachment', array(
			'uniacid' => $_W['uniacid'],
			'id' => $material_id,
	));
	if (empty($material)){
		return error('-2', '素材文件不存在或已删除');
	}
	if (!empty($material['media_id'])){
		$account_api = WeAccount::create($_W['acid']);
		$result = $account_api->delMaterial($material['media_id']);
	}
	if (is_error($result)){
		return $result;
	}
	pdo_delete('wechat_news', array(
			'uniacid' => $_W['uniacid'],
			'attach_id' => $material_id
	));
	pdo_delete('wechat_attachment', array(
			'uniacid' => $_W['uniacid'],
			'id' => $material_id
	));
	return $result;
}

/**
 * 删除除图文外其他素材
 * @param int $material_id 素材id
 * @param string $location 微信/本地 素材
 */
function material_delete($material_id, $location){
	global $_W;
	if (empty($_W['isfounder']) && $_W['role'] != ACCOUNT_MANAGE_NAME_MANAGER) {
		return error('-1', '您没有权限删除该文件');
	}
	$material_id = intval($material_id);
	$table = $location == 'wechat' ? 'wechat_attachment' : 'core_attachment';
	$material = pdo_get($table, array(
					'uniacid' => $_W['uniacid'],
					'id' => $material_id)
				);
	if (empty($material)){
		return error('-2', '素材文件不存在或已删除');
	}
	if ($location == 'wechat' && !empty($material['media_id'])){
		$account_api = WeAccount::create($_W['acid']);
		$result = $account_api->delMaterial($material['media_id']);
	} else {
		if (! empty($_W['setting']['remote']['type'])) {
			$result = file_remote_delete($material['attachment']);
		} else {
			$result = file_delete($material['attachment']);
		}
	}
	if (is_error($result)) {
		return error('-3', '删除文件操作发生错误');
	}
	pdo_delete($table, array(
		'uniacid' => $_W['uniacid'],
		'id' => $material_id
	));
	return $result;
}

