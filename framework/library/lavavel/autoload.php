<?php

defined('IN_IA') or exit('Access Denied');

require __DIR__.'/illuminate/support/helpers.php';
require __DIR__.'/symfony/polyfill-mbstring/bootstrap.php';
require __DIR__.'/danielstjules/stringy/src/Create.php';

//load()->psr4('We7\\',IA_ROOT.'/framework/we7');
autoload()->psr4('Stringy\\',__DIR__.'/danielstjules/stringy/src'); //illuminate/support 依赖的类库
// 加载laravel 组件
autoload()->psr4('Illuminate\\Container\\',__DIR__.'/illuminate/container'); //容器
autoload()->psr4('Illuminate\\Contracts\\',__DIR__.'/illuminate/contracts'); //接口规范
autoload()->psr4('Illuminate\\Database\\', __DIR__.'/illuminate/database'); // 数据库
autoload()->psr4('Illuminate\\Support\\', __DIR__.'/illuminate/support'); // 辅助函数
autoload()->psr4('Illuminate\\Pagination\\', __DIR__.'/illuminate/pagination'); // 数据库分页

autoload()->psr4('Corbon\\', __DIR__.'/nesbot/carbon/src'); // 时间处理类  illuminate/database 用到
autoload()->psr4('Symfony\\Polyfill\\Mbstring\\', __DIR__.'/symfony/polyfill-mbstring'); // illuminate/support 用到
autoload()->psr4('Symfony\\Component\\Translation\\', __DIR__.'/symfony/translation'); //Corbon 用到的国际化类库 （illuminate/database） 依赖
autoload()->psr4('Symfony\\Component\\VarDumper\\',__DIR__.'/symfony/var-dumper/Symfony/Component/VarDumper'); //调试
