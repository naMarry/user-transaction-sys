<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: PUT, POST, GET, DELETE, OPTIONS");

require './vendor/autoload.php';
require_once __DIR__ . '/controllers/userController.php';

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$basePath = '/php/banking-sys/user-service';
$route = str_replace($basePath, '', $request);

if ($route == '/' && $method === 'GET') {
    echo 'Hello there';
} elseif ($route == '/api/create-admin' && $method === 'POST') {
    $controller = new UserController();
    $controller->generateAdmin();
} elseif ($route == '/api/create-user' && $method === 'POST') {
    $controller = new UserController();
    $controller->generateUser();
} elseif ($route == '/api/login' && $method === 'POST') {
    $controller = new UserController();
    $controller->login();
} elseif ($route == '/api/logout' && $method === 'POST') {
    $controller = new UserController();
    $controller->logout();
} elseif (preg_match('/^\/api\/get-user\/(\d+)$/', $route, $matches) && $method === 'GET') {
    $controller = new UserController();
    $controller->retreiveUser($matches[1]);  
} elseif ($route == '/api/get-users' && $method === 'GET') {
    $controller = new UserController();
    $controller->getAllUsers();  
} elseif (preg_match('/^\/api\/get-balance\/(\d+)$/', $route, $matches) && $method === 'GET') {
    $controller = new UserController();
    $controller->getBalance($matches[1]);  
} elseif (preg_match('/^\/api\/deposit-balance\/(\d+)$/', $route, $matches) && $method === 'POST') {
    $controller = new UserController();
    $controller->depositBalance($matches[1]);  
} elseif (preg_match('/^\/api\/withdraw-balance\/(\d+)$/', $route, $matches) && $method === 'POST') {
    $controller = new UserController();
    $controller->withdrawBalance($matches[1]);  
} elseif ($route == '/api/check-login-user' && $method === 'GET') {
    $controller = new UserController();
    $controller->getAuthorizationUser();  
} else {
    http_response_code(404);
    echo json_encode(['message' => 'Route not found']);
}
