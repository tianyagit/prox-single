<?php
namespace PHPSTORM_META {

    override(\table(0), map([
        '' => '@',
        'modules_recycle' => \We7\Table\Modules\Recycle::class,
	    'modules_bindings' => \We7\Table\Modules\Bindings::class,
	    'modules_cloud' => \We7\Table\Modules\Cloud::class,
	    'modules_ignore' => \We7\Table\Modules\Ignore::class,
	    'modules_modules' => \We7\Table\Modules\Modules::class,
	    'modules_plugin' => \We7\Table\Modules\Plugin::class,
	    'modules_rank' => \We7\Table\Modules\Rank::class,
		'uni_account_modules' => \We7\Table\Uni\AccountModules::class,
		'system_stat_visit' => \We7\Table\System\StatVisit::class,
		'article_comment' => \We7\Table\Article\comment::class,
    ]));
}
