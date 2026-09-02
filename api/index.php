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

// 1. تعيين المفتاح تلقائياً إن لم يوجد في البيئة
putenv('APP_ENV=production');
putenv('APP_DEBUG=true');
putenv('LOG_CHANNEL=stderr');
putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=cookie');

// إذا كان الـ APP_KEY غير مسجل، استخدم مفتاحك المحلي هنا بدلاً من النص المؤقت
if (empty($_ENV['base64:pauv5+vQ80eGUEbLuPJwPQJOaEZQjCHeeNRb6+Q0xec=']) && empty(getenv('APP_KEY'))) {
    $defaultKey = 'base64:pauv5+vQ80eGUEbLuPJwPQJOaEZQjCHeeNRb6+Q0xec=';
    putenv("APP_KEY={$defaultKey}");
    $_ENV['APP_KEY'] = $defaultKey;
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath($tmpStorage);

$app->booted(function () use ($app, $tmpStorage) {
    $config = $app->make('config');
    $config->set('view.compiled', $tmpStorage . '/framework/views');
    $config->set('session.driver', 'cookie');
    $config->set('cache.default', 'array');
    $config->set('logging.default', 'stderr');
});

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();
$kernel->terminate($request, $response);
