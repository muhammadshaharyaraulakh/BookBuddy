<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$trimmedPath = rtrim($path, '/');
if (empty($trimmedPath)) {
    $trimmedPath = '/';
}

$routes = [
    '/login'            => __DIR__ . '/auth/login/login.php',
    '/login.php'        => __DIR__ . '/auth/login/login.php',
    '/register'         => __DIR__ . '/auth/registration/registration.php',
    '/register.php'     => __DIR__ . '/auth/registration/registration.php',
    '/registration'     => __DIR__ . '/auth/registration/registration.php',
    '/registration.php' => __DIR__ . '/auth/registration/registration.php',
    '/forgot'           => __DIR__ . '/auth/forgot/forgot.php',
    '/forgot.php'       => __DIR__ . '/auth/forgot/forgot.php',
    '/confirm'          => __DIR__ . '/auth/otp/otp.php',
    '/confirm.php'      => __DIR__ . '/auth/otp/otp.php',
    '/otp'              => __DIR__ . '/auth/otp/otp.php',
    '/otp.php'          => __DIR__ . '/auth/otp/otp.php',
    '/resetpassword'    => __DIR__ . '/auth/reset/reset.php',
    '/resetpassword.php'=> __DIR__ . '/auth/reset/reset.php',
    '/reset'            => __DIR__ . '/auth/reset/reset.php',
    '/reset.php'        => __DIR__ . '/auth/reset/reset.php',
    '/403'              => __DIR__ . '/403.php',
    '/403.php'          => __DIR__ . '/403.php',
    '/logout'           => __DIR__ . '/auth/logout.php',
];

if (isset($routes[$trimmedPath])) {
    require $routes[$trimmedPath];
    exit;
}

$requestedFile = __DIR__ . $path;
if ($path !== '/' && file_exists($requestedFile) && !is_dir($requestedFile)) {
    return false;
}

if ($path === '/' || $path === '/index.php') {
    require __DIR__ . '/index.php';
    exit;
}

return false;
