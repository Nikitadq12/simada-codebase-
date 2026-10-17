<?php
/**
 * Front controller / router SIMADA.
 * Akses di localhost: http://localhost/simada/public/
 */
spl_autoload_register(function ($class) {
    foreach (['core', 'models', 'controllers'] as $dir) {
        $file = __DIR__ . "/../$dir/$class.php";
        if (is_file($file)) { require $file; return; }
    }
});
Auth::start();

$routes = [
    'login'       => [AuthController::class, 'login'],
    'logout'      => [AuthController::class, 'logout'],
    'dashboard'   => [DashboardController::class, 'index'],
    'users'       => [UserController::class, 'index'],
    'user-toggle' => [UserController::class, 'toggle'],
    'tender'      => [TenderController::class, 'index'],
    'paket'       => [TenderController::class, 'detail'],
    'progres'     => [ProgresController::class, 'index'],
];
$page = $_GET['page'] ?? (Auth::check() ? 'dashboard' : 'login');
[$class, $method] = $routes[$page] ?? $routes['login'];
(new $class())->$method();
