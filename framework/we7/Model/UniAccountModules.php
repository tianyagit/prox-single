<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 11:53
 */

namespace framework\we7\Model;


use Illuminate\Database\Eloquent\Model;

class UniAccountModules extends Model
{
	protected $table = 'uni_account_modules';

	protected $primaryKey = 'id';


	public function uniAccount()
	{
		return $this->belongsTo('We7\Model\UniAccount','uniacid','uniacid');
	}
}