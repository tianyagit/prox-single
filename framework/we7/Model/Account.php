<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 11:47
 */

namespace We7\Model;


use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
	protected $table = 'account';

	protected $primaryKey = 'uniacid';

	public $timestamps = false; //不自动更新 created_at updated_at\
}