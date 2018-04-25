<?php
require_once __DIR__.'/../vendor/autoload.php';
require __DIR__ . '/../source/Jacwright/RestServer/RestServer.php';
require __DIR__.'/BaseController.php';
require 'TestController.php';
require 'TestAuthController.php';

$server = new \Jacwright\RestServer\RestServer('debug');
$server->addClass('TestController');
$server->addClass('TestAuthController');
$server->handle();
