<?php


use Testify\Testify;

require '../framework/bootstrap.inc.php';
require IA_ROOT . '/framework/library/testify/Testify.php';
//防止30秒运行超时的错误（Maximum execution time of 30 seconds exceeded).
set_time_limit(0);
load()->func('communication');

$tester = new Testify('测试数据库');

$tester->test('测试 DB 操作' ,function(){


	$accounts = pdox()->table('account')->get();

	$menus = pdox()->table('core_menu')->where('title','基本设置')->get();

// 测试添加
	$insert = pdox()->table('test_laraveldb')->insert(['name'=>'h','age'=>1]);

// 测试添加
	$insert1 = pdox()->table('test_laraveldb')->insert(['name'=>'h1','age'=>2]);

	// 测试插入多条
	$insert2 = pdox()->table('test_laraveldb')->insert(array(array('name'=>'h1','age'=>2),array('name'=>'h1','age'=>2)));

	$update = pdox()->table('test_laraveldb')->where('name','h')->update(['age'=>111]);

	$select = pdox()->table('test_laraveldb')->first();//只取第一条记录

	// 测试删除
//	$delete = pdox()->table('test_laraveldb')->where('age','>',1)->delete();

	// 测试 join
	$join = pdox()->table('uni_account as account')
		->leftJoin('uni_account_menus as menu', 'account.uniacid','=','menu.uniacid')
//		->where('account.uniacid',269)
		->where('menu.title','测试优化')->get();

	// 测试分页
	$pages = pdox()->table('uni_account')->paginate(5);//测试分页

//	$pages->total(); 总记录数
//	$pages->currentPage();//当前页
//	$pages->hasMorePages(); //是否更多页
	foreach ($pages as $page) { //分页 返回对象 Illuminate\Pagination\LengthAwarePaginator::class  实现了iterator 接口 所以可以直接 foreach
		echo $page['name'].PHP_EOL;
	}


});

$tester->test('测试 ORM 操作' ,function(){
	$user = We7\Model\User::find(1); //用户

	$account = \We7\Model\UniAccount::find(281); //查询公众账号

	$menus = $account->menus()->getQuery()->paginate(5); // 公众账号下边的菜单 每页显示5 条 （1 对多 测试）

	$allmenus = $account->menus; // 公众账号下所有菜单 （1 对多 测试）

	$isdelete = $account->baseaccount->isconnect;// 公众号是否已连接 （1 对 1 测试）

	$isdelete = $account->isDelete();// 写到Model 里

	// 测试插入
	\We7\Model\TestModel::create(array('name'=>'model insert','age'=>123));

	// 测试插入2
	$model=new \We7\Model\TestModel();
	$model->name = 'model insert 2';
	$model->age = 12222;
	$model->save();
	$model->id;// 主键自增的话 自动获取 主键

	// 测试插入3
	$model = new \We7\Model\TestModel(['name'=>'model insert3','age'=>14444]);
	$model->save();

	// 测试更新1
	$model = \We7\Model\TestModel::where('name','model insert3')->first();
	$model->age = 555;
	$model->name = 'update1';
	$model->update();

	// 测试删除
	\We7\Model\TestModel::whereIn('age',[123,111])->delete();

	//根据主键删除
	\We7\Model\TestModel::destroy(110);

});


$tester->test('测试 依赖注入',function (){

	app('test')->testDI();
});

$tester->run();
