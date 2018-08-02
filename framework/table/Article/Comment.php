<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Article;

class Comment extends \We7Table {
	protected $tableName = 'article_comment';
	protected $primaryKey = 'id';
	protected $field = array(
		'articleid',
		'parentid',
		'uid',
		'content',
		'is_read',
		'iscomment',
		'createtime',
	);
	protected $default = array(
		'articleid' => '',
		'parentid' => 0,
		'uid' => '',
		'content' => '',
		'is_read' => 1,
		'iscomment' => 1,
		'createtime' => '',
	);

	public function addComment($comment) {
		if (!empty($comment['parentid'])) {
			$result = $this->where('id', $comment['parentid'])->fill('iscomment', ARTICLE_COMMENT)->save();
			if ($result === false) {
				return false;
			}
		}
		$comment['createtime'] = TIMESTAMP;
		return $this->fill($comment)->save();
	}

	public function getArticleComments($articleid, $iscomment, $pageindex, $pagesize = 15) {
		$query = $this->where('articleid', $articleid)->where('parentid', 0);
		if ($iscomment) {
			$query->where('iscomment' , $iscomment);
		}

		$total = $query->count();
		if (empty($total)) {
			return array();
		}
		$comments = $query->orderby('id', 'DESC')->page($pageindex, $pagesize)->getall('id');

		$uids = array();
		foreach ($comments as $comment) {
			$uids[$comment['uid']] = $comment['uid'];
		}
		$reply_comments = $this->where('parentid', array_keys($comments))->orderby('id', 'DESC')->getall();
		if (!empty($reply_comments)) {
			foreach ($reply_comments as $item) {
				$uids[$item['uid']] = $item['uid'];
			}
		}
		if (!empty($uids)) {
			$users = $this->getQuery()
				->select('u.uid, u.username, p.realname, p.nickname, p.avatar, p.mobile')
				->from('users', 'u')
				->leftjoin('users_profile', 'p')
				->on(array('u.uid' => 'p.uid'))
				->where('u.uid', $uids)
				->getall('uid');
		}
		$replys = array();
		if (!empty($reply_comments)) {
			foreach ($reply_comments as $item) {
				if (!empty($users[$item['uid']])) {
					$item = array_merge($item, $users[$item['uid']]);
				}
				$replys[$item['parentid']][] = $item;
			}
		}

		foreach ($comments as $k => &$comment) {
			$comment['createtime'] = date('Y-m-d H:i', $comment['createtime']);

			if (!empty($users[$comment['uid']])) {
				$comment = array_merge($comment, $users[$comment['uid']]);
			}

			if (!empty($replys[$comment['id']])) {
				$comment['replys'] = $replys[$comment['id']];
			} else {
				$comment['replys'] = array();
			}
		}
		return array('list' => $comments, 'total' => $total);
	}
}