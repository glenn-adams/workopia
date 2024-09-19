<?php
require __DIR__ . '/../vendor/autoload.php';

use Framework\Router;
use Framework\Session;

Session::start();

require '../helpers.php';

// Set up a basic router functionality

// Instantiate Router Class object
$router = new Router();

// Get routes
$routes = require basePath('routes.php');
// inspectAndDie($routes);

// Get current URI & HTTP METHOD
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);  // PHP_URL_PATH separates path from query string

$router->route($uri);
