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
		'is_like',
		'is_reply',
		'createtime',
	);
	protected $default = array(
		'articleid' => '',
		'parentid' => 0,
		'uid' => '',
		'content' => '',
		'is_like' => 2,
		'is_reply' => 2,
		'createtime' => '',
	);

	public function addComment($comment) {
		if (!empty($comment['parentid'])) {
			$result = $this->where('id', $comment['parentid'])->fill('is_reply', 1)->save();
			if ($result === false) {
				return false;
			}
		}
		$comment['createtime'] = TIMESTAMP;
		$comment['is_like'] = 2;
		return $this->fill($comment)->save();
	}

	public function likeComment($uid, $articleid, $comment_id) {
		$this->fill(array(
			'uid' => $uid,
			'articleid' => $articleid,
			'parentid' => $comment_id,
			'is_like' => 1,
			'is_reply' => 1,
			'content' => '',
			'createtime' => TIMESTAMP,
		));
		return $this->save();
	}

	public function getCommentsAndLikeNum($articleid, $pageindex, $pagesize = 15) {
		$comments = $this->where('articleid', $articleid)
			->where('parentid', 0)
			->where('is_like', 2)
			->orderby('id', 'DESC')
			->page($pageindex, $pagesize)
			->getall('id');
		$total = $this->getLastQueryTotal();

		if (!empty($comments)) {
			$this->extendUserinfo($comments);

			$like_comments = $this->getQuery()
				->select(array('parentid', 'count(*) as sum'))
				->where('parentid', array_keys($comments))
				->where('is_like', 1)
				->groupby('parentid')
				->getall('parentid');

			foreach ($comments as $k => &$comment) {
				$comment['createtime'] = date('Y-m-d H:i', $comment['createtime']);

				if (!empty($like_comments[$comment['id']])) {
					$comment['sum_like'] = $like_comments[$comment['id']]['sum'];
				} else {
					$comment['sum_like'] = 0;
				}
			}
		}
		return array('list' => $comments, 'total' => $total);
	}

	public function extendUserinfo(&$comments) {
		if (empty($comments)) {
			return true;
		}
		$uids = array();
		foreach ($comments as $comment) {
			$uids[$comment['uid']] = $comment['uid'];
		}
		if (!empty($uids)) {
			$users = $this->getQuery()
				->select('u.uid, u.username, p.realname, p.nickname, p.avatar, p.mobile')
				->from('users', 'u')
				->leftjoin('users_profile', 'p')
				->on(array('u.uid' => 'p.uid'))
				->where('u.uid', $uids)
				->getall('uid');

			foreach ($comments as &$comment) {
				if (!empty($users[$comment['uid']])) {
					$comment = array_merge($comment, $users[$comment['uid']]);
				}
			}
		}
		return true;
	}
}