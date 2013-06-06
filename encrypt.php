<?php
$string = 'It works ? Or not it works ?';
$pass = '1234';
$method = 'aes128';

$str = openssl_encrypt ($string, $method, $pass,'' ,'iviviviviviviviv');
var_dump($str);

$regenerated = openssl_decrypt($str, $method, $pass,'' ,'iviviviviviviviv');

var_dump($regenerated);

$base = 'base';
var_dump(base64_encode($base));