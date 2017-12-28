<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
class AttachmentTable extends We7Table {



	public function local($local = true) {
		if(! $local) {
			$this->attachment_table_name = 'wechat_attachment';
		}
		return $this;
	}
	public function getById($att_id, $type = 1) {
		return $this->query->from($this->attachment_table_name)->where('id', $att_id)->where('type', $type)->get();
	}

	public function searchAttachmentList() {
		return $this->query->from($this->attachment_table_name)->orderby('createtime', 'desc')->getall();
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

class WechatAttachmentTable extends AttachmentTable {
	protected $attachment_table_name = 'wechat_attachment';
}
