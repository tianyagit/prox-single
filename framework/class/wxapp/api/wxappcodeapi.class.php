<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/30
 * Time: 10:15
 */

/**
 * -1	 系统繁忙
86000 	 不是由第三方代小程序进行调用
86001 	  不存在第三方的已经提交的代码
85006 	  标签格式错误
85007 	  页面路径错误
85008 	  类目填写错误
85009 	  已经有正在审核的版本
85010	  item_list有项目为空
85011 	  标题填写错误
85023 	  审核列表填写的项目数不在1-5以内
86002	  小程序还未设置昵称、头像、简介。请先设置完后再重新提交。
 * Class WxAppCodeException
 */
class WxAppCodeApiException extends Exception {


}

class WxAppCodeApi extends WxAppBaseApi {

	const COMMIT_URL = 'https://api.weixin.qq.com/wxa/commit';
	const GET_QRCODE_URL = 'https://api.weixin.qq.com/wxa/get_qrcode';
	const CATEGORY_URL = 'https://api.weixin.qq.com/wxa/get_category';
	const GET_PAGE_URL  = 'https://api.weixin.qq.com/wxa/get_page';
	const SUBMIT_AUDIT_URL = 'https://api.weixin.qq.com/wxa/submit_audit';
	const GET_AUDITSTATUS_URL = 'https://api.weixin.qq.com/wxa/get_auditstatus';
	const GET_LATEST_AUDITSTATUS = 'https://api.weixin.qq.com/wxa/get_latest_auditstatus';
	const RELEASE_URL = 'https://api.weixin.qq.com/wxa/release';
	const CHANGE_VISITSTATUS_URL = 'https://api.weixin.qq.com/wxa/change_visitstatus';



	private function buildUrl($url) {
		return $url.'?access_token='.$this->accessToken();
	}

	protected function post($url, $data) {
		$url = $this->buildUrl($url);
		return parent::post($url, $data);

	}

	protected function get($url) {
		$url = $this->buildUrl($url);
		return parent::get($url);
	}



	public function commitCode($template_id, $ext_json, $user_version, $user_desc) {
		$postdata = array(
			'template_id'=>$template_id,
			'ext_json'=>json_encode($ext_json),
			'user_version'=>$user_version,
			'user_desc'=>$user_desc);

		return $this->post(self::COMMIT_URL, $postdata);
	}

	/*
	 * 获取体验小程序的体验二维码
	 */
	public function getQrcode() {
		$image = $this->get(self::GET_QRCODE_URL);
		return $image;

	}

	/**
	 * 获取授权小程序帐号的可选类目
	 */
	public function getCategory() {
		$data = $this->get(self::CATEGORY_URL);
		return $data['category_list'];
	}

	/**
	 * 获取小程序的第三方提交代码的页面配置（仅供第三方开发者代小程序调用）
	 */
	public function getPage() {
		$data = $this->get(self::GET_PAGE_URL);
		return $data['page_list'];
	}
	/**
	 *  提交审核
	 * item_list	提交审核项的一个列表（至少填写1项，至多填写5项）
	address	小程序的页面，可通过“获取小程序的第三方提交代码的页面配置”接口获得
	tag	小程序的标签，多个标签用空格分隔，标签不能多于10个，标签长度不超过20
	first_class	一级类目名称，可通过“获取授权小程序帐号的可选类目”接口获得
	second_class	二级类目(同上)
	third_class	三级类目(同上)
	first_id	一级类目的ID，可通过“获取授权小程序帐号的可选类目”接口获得
	second_id	二级类目的ID(同上)
	third_id	三级类目的ID(同上)
	title
	 */
	public function submitAudit($data) {
		$data = $this->post(self::SUBMIT_AUDIT_URL, $data);
		return $data['auditid'];
	}

	/**
	 *  获取审核状态
	 * @result 审核状态，其中0为审核成功，1为审核失败，2为审核中
	 *          reason 当status=1，审核被拒绝时，返回的拒绝原因
	 */
	public function getAuditStatus($auditid) {
		$data = $this->post(self::GET_AUDITSTATUS_URL, array('auditid'=>$auditid));
		return array('status'=>$data['status'], 'reason'=> $data['reason']);
	}

	/**
	 * 查询最新一次提交的审核状态（仅供第三方代小程序调用）
	 */
	public function getLatestAuditstatus() {
		$data = $this->get(self::GET_LATEST_AUDITSTATUS);
		return array('auditid'=>$data['auditid'],'status'=>$data['status'], 'reason'=> $data['reason']);
	}

	/**
	 * 发布已通过审核的小程序（仅供第三方代小程序调用）
	 * @return  boolean
	 */
	public function release() {
		$this->post(self::RELEASE_URL,array());
		return true;
	}

	/**
	 *  修改小程序线上代码的可见状态（仅供第三方代小程序调用）
	 * @param $open_or_close boolean
	 * @return boolean
	 */
	public function changeVisitstatus($open_or_close = true) {
		$this->post(self::CHANGE_VISITSTATUS_URL, array('action'=> $open_or_close? 'open': 'close'));
		return true;
	}
}