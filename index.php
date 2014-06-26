<?php
require_once 'vendor/autoload.php';

session_cache_limiter(false);
session_start();

\Slim\Slim::registerAutoloader();

$controller = new \Slim\Controller();
$controller->run();
