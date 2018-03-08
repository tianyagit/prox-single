<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');
class SitearticlecommentTable extends We7Table {
	protected $tableName = 'site_article_comment';
	protected $primaryKey = 'id';
	protected $field = array('id', 'uniacid', 'articleid', 'parentid', 'uid', 'openid', 'content', 'createtime');

	public function articleCommentList() {
		return $this->query->from($this->tableName)->getall('id');
	}


	public function articleCommentOrder($order = 'DESC') {
		$order = empty($order) ? 'DESC' : 'ASC';
		return $this->query->orderby('createtime', $order);
	}

	public function articleCommentAdd($comment) {
		if (!empty($comment['parentid'])) {
			table('sitearticlecomment')->where('id', $comment['parentid'])->fill('iscomment', ARTICLE_COMMENT)->save();
		}
		$comment['createtime'] = TIMESTAMP;
		table('sitearticlecomment')->fill($comment)->save();
		return true;
	}

	public function srticleCommentUnread($articleIds) {
		global $_W;
		return $this->query->from($this->tableName)->select('articleid, count(*) as count')->where('uniacid', $_W['uniacid'])->where('articleid', $articleIds)->where('is_read', ARTICLE_COMMENT_NOREAD)->groupby('articleid')->getall('articleid');
	}
}