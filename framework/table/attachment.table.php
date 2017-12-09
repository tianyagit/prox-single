<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/11/28
 * Time: 9:34
 */
class AttachmentTable extends We7Table {
	const LOCAL = 'core_attachment';
	const WX = 'wechat_attachment';
	private $local = true;
	public function local($local) {
		$this->local = $local ? true : false;
		if($local) {
			$this->query->from(static::LOCAL);
			return $this;
		}
		$this->query->from(static::WX);
		return $this;
	}

	public function getById($att_id, $type = 1) {
		return $this->query->where('id', $att_id)->where('type', $type)->get();
	}

	public function searchAttachmentList($local = true) {
		$table_name = !empty($local) ? 'core_attachment' : 'wechat_attachment';
		return $this->query->from($table_name)->orderby('createtime', 'desc')->getall();
	}

	public function searchWithType($type) {
		$this->query->where(array('type' => $type));
		return $this;
	}

	public function searchWithUniacid($uniacid) {
		$this->query->where(array('uniacid' => $uniacid));
		return $this;
	}

	public function searchWithUploadDir($module_upload_dir) {
		$this->query->where(array('module_upload_dir' => $module_upload_dir));
		return $this;
	}

	public function searchWithUid($uid) {
		$this->query->where(array('uid' => $uid));
		return $this;
	}

	public function searchWithTime($start_time, $end_time) {
		$this->query->where(array('createtime >=' => $start_time))->where(array('createtime <=' => $end_time));
		return $this;
	}
}