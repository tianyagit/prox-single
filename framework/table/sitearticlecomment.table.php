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
			$this->query->fill('iscomment', ARTICLE_COMMENT)->whereId($comment['parentid'])->save();
		}
		$comment['createtime'] = TIMESTAMP;
		$this->query->fill($comment)->save();
		return true;
	}
}