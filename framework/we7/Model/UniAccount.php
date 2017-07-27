<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 11:36
 */

namespace We7\Model;


use Illuminate\Database\Eloquent\Model;

/**
 *  公众账号
 * Class UniAccount
 * @package framework\we7\Model
 */
class UniAccount extends Model
{
	protected $table = 'uni_account';

	protected $primaryKey = 'uniacid';

	public $timestamps = false; //不自动更新 created_at updated_at\

	/**
	 *  公众账号菜单
	 */
	public function menus()
	{
		return $this->hasMany('We7\Model\UniAccountMenus', 'uniacid','uniacid');
	}

	/**
	 *  公众账号模块
	 */
	public function modules()
	{
		return $this->hasMany('We7\Model\UniAccountModules', 'uniacid', 'uniacid');
	}

	/**
	 *  Account 表一对一
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function baseaccount()
	{
		return $this->hasOne('We7\Model\Account','uniacid','uniacid');
	}

	/**
	 *  公众号是否已删除
	 */
	public function isDelete()
	{
		return $this->baseaccount->isdeleted;
	}





	public static function current()
	{

	}


}