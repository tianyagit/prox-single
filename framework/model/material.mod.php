<?php
/**
 * 同步微信素材
 * @param array $material 从微信接口拉取到的素材数据
 * @param array $exist_material 微信与本地同时存在的素材id集合（用于删除微信端不存在，本地存在的素材）
 * @return mixed
 */
function syncMaterial($material, $exist_material) {
	global $_W;
	foreach ($material as $news) {
		$news_exist = pdo_get('wechat_attachment', array('uniacid' => $_W['uniacid'], 'media_id' => $news['media_id']));
		if (empty($news_exist)) {
			$news_data = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $_W['acid'],
				'media_id' => $news['media_id'],
				'type' => 'news',
				'model' => 'perm',
				'createtime' => $news['update_time']
			);
			pdo_insert('wechat_attachment', $news_data);
		} else {
			pdo_delete('wechat_news', array('uniacid' =>$_W['uniacid'], 'attach_id' => $news_exist['id']));
			$exist_material[] = $news_exist['id'];
		}
		foreach ($news['content']['news_item'] as $key => $new) {
			$new_data = array(
				'uniacid' => $_W['uniacid'],
				'attach_id' => $news_exist['id'],
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
	return $exist_material;
}