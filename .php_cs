<?php
$header = <<<'EOF'
This file is part of PHP CS Fixer.
(c) Fabien Potencier <fabien@symfony.com>
    Dariusz Rumiński <dariusz.ruminski@gmail.com>
This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;
$config = PhpCsFixer\Config::create()
    ->setIndent("\t")
    ->setLineEnding("\n")
    ->setRules([
            '@Symfony' => true,
            'braces'=> ['position_after_functions_and_oop_constructs' => 'same']
        ])
;
return $config;