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
 * 获取素材
 * @param array $material 素材的id
 * @return array() 素材内容
 */
function material_get($attach_id) {
	if (empty($attach_id)) {
		return error(1, "素材id参数不能为空");
	}
	$material = pdo_get('wechat_attachment', array('id' => $attach_id));
	if (!empty($material)) {
		if ($material['type'] == 'news') {
			$news = pdo_getall('wechat_news', array('attach_id' => $material['id']), array(), '', ' displayorder ASC');
			if (!empty($news)) {
				foreach ($news as &$news_row) {
					$news_row['thumb_url'] = tomedia($news_row['thumb_url']);
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
					'url' => $material['content_source_url'],
				);
			}
		}
	}
	cache_write($cachekey, $reply, CACHE_EXPIRE_MIDDLE);
	return $reply;
}