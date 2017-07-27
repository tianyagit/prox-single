<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 18:00
 */

namespace We7\Model;


use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
	protected $table = 'test_laraveldb';

	protected $primaryKey = 'id';

	public $timestamps = false; //不自动更新 created_at updated_at\

	protected $fillable = ['name','age']; // 可以插入数据库的字段  TestModel::create(array('name'=>'name','age'=>1)
}