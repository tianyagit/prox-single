<?php
$header = <<<'EOF'
    代码格式化命令
    php-cs-fixer fix ./web/source/utility/resource.ctrl.php
EOF;
$config = PhpCsFixer\Config::create()
    ->setIndent("\t")
    ->setLineEnding("\n")
    ->setRules([
            '@Symfony' => true,
            'braces'=> ['position_after_functions_and_oop_constructs' => 'same'], //大括号放一行
            'concat_space'=>["spacing" => "one"] //操作符之间一个空格
        ])
;
return $config;