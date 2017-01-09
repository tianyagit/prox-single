<?php
/**
 * 基本文字回复处理类
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class CoreModuleProcessor extends WeModuleProcessor {
	
	public function respond() {

		$reply_type = $this->reply_type;
		$key = array_rand($reply_type);
		$type = $reply_type[$key];
		switch($type) {
			case 'basic':
				$result = $this->basic_respond();
				return $this->respText($result);
				break;
			case 'image':
				$result = $this->image_respond();
				return $this->respImage($result);
				break;
			case 'music':
				$result = $this->music_respond();
				return $this->respMusic(array(
						'Title'	=> $result['title'],
						'Description' => $result['description'],
						'MusicUrl' => $result['url'],
						'HQMusicUrl' => $result['hqurl'],
					));
				break;
			case 'news':
				$result = $this->news_respond();
				return $this->respNews($result);
				break;
			case 'voice':
				$result = $this->voice_respond();
				return $this->respVoice($result);
				break;
			case 'video':
				$result = $this->video_respond();
				return $this->respVideo(array(
						'MediaId' => $result['mediaid'],
						'Title' => $result['title'],
						'Description' => $result['description']
					));
				break;
		}
	}
	private function basic_respond() {
		$sql = "SELECT * FROM " . tablename('basic_reply') . " WHERE `rid` IN ({$this->rule})  ORDER BY RAND() LIMIT 1";
		$reply = pdo_fetch($sql);
		if (empty($reply)) {
			return false;
		}
		$reply['content'] = htmlspecialchars_decode($reply['content']);
		//过滤HTML
		$reply['content'] = str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $reply['content']);
		$reply['content'] = strip_tags($reply['content'], '<a>');
		return $reply['content'];
	}
	private function image_respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT `mediaid` FROM " . tablename('images_reply') . " WHERE `rid`=:rid";
		$mediaid = pdo_fetchcolumn($sql, array(':rid' => $rid));
		if (empty($mediaid)) {
			return false;
		}
		return $mediaid;
	}
	private function music_respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('music_reply') . " WHERE `rid`=:rid ORDER BY RAND()";
		$item = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($item['id'])) {
			return false;
		}
		return $item;
	}
	private function news_respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('news_reply') . " WHERE rid = :id AND parent_id = -1 ORDER BY displayorder DESC, id ASC LIMIT 8";
		$commends = pdo_fetchall($sql, array(':id' => $rid));
		if (empty($commends)) {
			//此处是兼容写法。
			$sql = "SELECT * FROM " . tablename('news_reply') . " WHERE rid = :id AND parent_id = 0 ORDER BY RAND()";
			$main = pdo_fetch($sql, array(':id' => $rid));
			if(empty($main['id'])) {
				return false;
			}
			$sql = "SELECT * FROM " . tablename('news_reply') . " WHERE id = :id OR parent_id = :parent_id ORDER BY parent_id ASC, displayorder DESC, id ASC LIMIT 8";
			$commends = pdo_fetchall($sql, array(':id'=>$main['id'], ':parent_id'=>$main['id']));
		}
		if(empty($commends)) {
			return false;
		}
		$news = array();
		foreach($commends as $c) {
			$row = array();
			$row['title'] = $c['title'];
			$row['description'] = $c['description'];
			!empty($c['thumb']) && $row['picurl'] = tomedia($c['thumb']);
			$row['url'] = empty($c['url']) ? $this->createMobileUrl('detail', array('id' => $c['id'])) : $c['url'];
			$news[] = $row;
		}
		return $news;
	}
	private function voice_respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT `mediaid` FROM " . tablename('voice_reply') . " WHERE `rid`=:rid";
		$mediaid = pdo_fetchcolumn($sql, array(':rid' => $rid));
		if (empty($mediaid)) {
			return false;
		}
		return $mediaid;
	}
	private function video_respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('video_reply') . " WHERE `rid`=:rid";
		$item = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($item)) {
			return false;
		}
		return $item;
	}
}