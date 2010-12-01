<?php

function __autoload($class)
{
    $file = $class.'.php';
    require_once dirname(__FILE__).'/src/'.$file;
}

spl_autoload_register('__autoload');

if (php_sapi_name()=='cli') {
	$ploy = new Ploy($argv);
	exit(0);
}
