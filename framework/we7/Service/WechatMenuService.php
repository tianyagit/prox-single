<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 18:23
 */

namespace We7\Service;



use We7\Model\UniAccount;

class WechatMenuService
{

	private $current;
	public function __construct(ICurrent $current)
	{
		$this->current = $current;
	}

	public function index($keyword)
	{
		$account = $this->current->uniAccount();// $_W['uniacid'];
		$list = app('db')->table('uni_account_menus')->where('keyword','like','%'.$keyword.'%')->where('uniacid',$account->uniacid)->paginate(5);
	}

	public function index2($keyword)
	{
		$account = UniAccount::current();
		$list = $account->menus()->where('keyword',$keyword)->paginate(5);

	}
}