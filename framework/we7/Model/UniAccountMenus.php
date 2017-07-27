<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 11:31
 */

namespace We7\Model;


use Illuminate\Database\Eloquent\Model;

class UniAccountMenus extends Model
{
	protected $table = 'uni_account_menus';

	protected $primaryKey = 'id';


	public function uniAccount()
	{
		return $this->belongsTo('We7\Model\UniAccount','uniacid','uniacid');
	}


}