<?php
$str="";
echo strlen($str);
class A{

}
function aaa(array $arr, A $a){
	print_r($arr);
	print_r($a);
}
aaa($GLOBALS, new A());
?>