<?php
$a=$b=1;
$c=5;
function fun1(){
	global $a,$c;
	$c=&$a;
}
fun1();
echo $c;//global 是变量的别名（引用）  输出 5
//echo PHP_EOL;
function fun2(){
	$GLOBALS['c']=$GLOBALS['b'];//$GLOBALS['c] 是变量本身
}
fun2();
echo $c;// 输出 1
?>