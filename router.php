<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$trimmedPath = rtrim($path, '/');
if (empty($trimmedPath)) {
    $trimmedPath = '/';
}

$routes = [
    '/login'             => __DIR__ . '/auth/login/login.php',
    '/login.php'         => __DIR__ . '/auth/login/login.php',
    '/register'          => __DIR__ . '/auth/registration/registration.php',
    '/register.php'      => __DIR__ . '/auth/registration/registration.php',
    '/registration'      => __DIR__ . '/auth/registration/registration.php',
    '/registration.php'  => __DIR__ . '/auth/registration/registration.php',
    '/forgot'            => __DIR__ . '/auth/forgot/forgot.php',
    '/forgot.php'        => __DIR__ . '/auth/forgot/forgot.php',
    '/confirm'           => __DIR__ . '/auth/otp/otp.php',
    '/confirm.php'       => __DIR__ . '/auth/otp/otp.php',
    '/otp'               => __DIR__ . '/auth/otp/otp.php',
    '/otp.php'           => __DIR__ . '/auth/otp/otp.php',
    '/resetpassword'     => __DIR__ . '/auth/reset/reset.php',
    '/resetpassword.php' => __DIR__ . '/auth/reset/reset.php',
    '/reset'             => __DIR__ . '/auth/reset/reset.php',
    '/reset.php'         => __DIR__ . '/auth/reset/reset.php',
    '/403'               => __DIR__ . '/403.php',
    '/403.php'           => __DIR__ . '/403.php',
    '/logout'            => __DIR__ . '/auth/logout.php',
    '/shop'              => __DIR__ . '/shop.php',
    '/shop.php'          => __DIR__ . '/shop.php',
    '/cart'              => __DIR__ . '/cart.php',
    '/cart.php'          => __DIR__ . '/cart.php',
    '/profile'           => __DIR__ . '/profile.php',
    '/profile.php'       => __DIR__ . '/profile.php',
    '/orders'            => __DIR__ . '/orders.php',
    '/orders.php'        => __DIR__ . '/orders.php',
    '/book'              => __DIR__ . '/book.php',
    '/book.php'          => __DIR__ . '/book.php',

    '/books'             => __DIR__ . '/admin/adminPages/book.php',
    '/books.php'         => __DIR__ . '/admin/adminPages/book.php',
    '/adminbooks'        => __DIR__ . '/admin/adminPages/book.php',
    '/admin'             => __DIR__ . '/admin/adminPages/book.php',
    '/addbook'           => __DIR__ . '/admin/adminPages/addBook.php',
    '/addbook.php'       => __DIR__ . '/admin/adminPages/addBook.php',
    '/add-book'          => __DIR__ . '/admin/adminPages/addBook.php',
    '/categories'        => __DIR__ . '/admin/adminPages/categories.php',
    '/categories.php'    => __DIR__ . '/admin/adminPages/categories.php',
    '/dailydeal'         => __DIR__ . '/admin/adminPages/dailyDeal.php',
    '/dailydeal.php'     => __DIR__ . '/admin/adminPages/dailyDeal.php',
    '/daily-deal'        => __DIR__ . '/admin/adminPages/dailyDeal.php',
    '/adminorders'       => __DIR__ . '/admin/adminPages/order.php',
    '/adminorders.php'   => __DIR__ . '/admin/adminPages/order.php',
    '/admin-orders'      => __DIR__ . '/admin/adminPages/order.php',
    '/order'             => __DIR__ . '/admin/adminPages/order.php',
    '/updatebook'        => __DIR__ . '/admin/adminPages/updateBook.php',
    '/updatebook.php'    => __DIR__ . '/admin/adminPages/updateBook.php',
    '/update-book'       => __DIR__ . '/admin/adminPages/updateBook.php',
    '/orderdetails'      => __DIR__ . '/admin/adminPages/orderDetails.php',
    '/orderdetails.php'  => __DIR__ . '/admin/adminPages/orderDetails.php',
    '/order-details'     => __DIR__ . '/admin/adminPages/orderDetails.php',
];

if (isset($routes[$trimmedPath])) {
    $target = $routes[$trimmedPath];
    if (str_contains($target, '/admin/')) {
        require_once __DIR__ . '/function/function.php';
        protectAdmin();
    }
    require $target;
    exit;
}

$requestedFile = __DIR__ . $path;
if ($path !== '/' && file_exists($requestedFile) && !is_dir($requestedFile)) {
    if (str_contains($requestedFile, '/admin/')) {
        require_once __DIR__ . '/function/function.php';
        protectAdmin();
    }
    return false;
}

if ($path === '/' || $path === '/index.php') {
    require __DIR__ . '/index.php';
    exit;
}

return false;
