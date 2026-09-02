<?php

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

putenv('APP_ENV=production');
putenv('APP_DEBUG=true');
putenv('LOG_CHANNEL=stderr');
putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=cookie');
putenv('VIEW_COMPILED_PATH=' . $tmpStorage . '/framework/views');

if (empty($_ENV['APP_KEY']) && empty(getenv('APP_KEY'))) {
    $defaultKey = 'base64:pauv5+vQ80eGUEbLuPJwPQJOaEZQjCHeeNRb6+Q0XMs=';
    putenv("APP_KEY={$defaultKey}");
    $_ENV['APP_KEY'] = $defaultKey;
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath($tmpStorage);

// معالجة الطلب وإرجاع الـ HTML
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
