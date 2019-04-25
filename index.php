<?php declare(strict_types=1);

//var_dump($_SERVER);
//var_dump($_SERVER['REQUEST_URI']);
//var_dump($_SERVER['PATH_INFO']);

// GET, POST values
//var_dump($_REQUEST);

$email = $_REQUEST['email'] ?? '';
$password = $_REQUEST['password'] ?? '';

$salt = 'MyLittleSaltHere2019';

$token = sha1($email . $password . $salt . microtime());

$result = [
    $token
];
header('Content-Type: application/json');
echo json_encode($result);