<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 11:28
 */

namespace We7\Model;


use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	protected $table = 'users';

	protected $primaryKey = 'uid';

	public $timestamps = false; //不自动更新 created_at updated_at

	/**
	 *  当前用户所有的公众账号 和小程序
	 * @return
	 */
	public function accounts()
	{
		return $this->belongsToMany('We7\Model\UniAccount','uni_account_users','uid','uniacid');
//		return $this->belongsToMany('We7\Model\UniAccount','acid','uid');
	}


}