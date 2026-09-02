<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$tmpStorage = '/tmp/storage';

$directories = [
    $tmpStorage . '/app',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/framework/views',
    $tmpStorage . '/logs',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

putenv('APP_ENV=local');
putenv('APP_DEBUG=true');
putenv('LOG_CHANNEL=stderr');
putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=cookie');

if (empty($_ENV['APP_KEY']) && empty(getenv('APP_KEY'))) {
    $defaultKey = 'base64:pauv5+vQ80eGUEbLuPJwPQJOaEZQjCHeeNRb6+Q0XMs=';
    putenv("APP_KEY={$defaultKey}");
    $_ENV['APP_KEY'] = $defaultKey;
}

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // 1. ضبط مسارات التخزين والـ Views فوراً
    $app->useStoragePath($tmpStorage);
    $app['config']->set('view.compiled', $tmpStorage . '/framework/views');
    $app['config']->set('session.driver', 'cookie');
    $app['config']->set('cache.default', 'array');
    $app['config']->set('logging.default', 'stderr');

    // 2. تسجيل الـ View Provider صراحة للتأكد من وجود 'view' في الحاوية
    $app->register(Illuminate\View\ViewServiceProvider::class);

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

    $response->send();
    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<h1>Laravel Exception Details:</h1>';
    echo '<p><b>Message:</b> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><b>File:</b> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>';
    echo '<h3>Trace:</h3><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}
