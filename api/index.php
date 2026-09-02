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

// 1. فرض إعدادات الـ Environment والـ Debug من الكود مباشرة
putenv('APP_ENV=production');
putenv('APP_DEBUG=true');
putenv('LOG_CHANNEL=stderr');
putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=cookie');

// ضع هنا مفتاح APP_KEY الخاص بك من ملف .env المحلي إذا لم يكن موجوداً
if (!getenv('base64:pauv5+vQ80eGUEbLuPJwPQJOaEZQjCHeeNRb6+Q0xec=')) {
    putenv('APP_KEY=base64:pauv5+vQ80eGUEbLuPJwPQJOaEZQjCHeeNRb6+Q0xec=');
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath($tmpStorage);

// 2. إجبار Laravel على تفعيل الـ Debug والمشغلات الآمنة
$app->booted(function () use ($app) {
    $config = $app->make('config');
    $config->set('app.debug', true);
    $config->set('logging.default', 'stderr');
    $config->set('session.driver', 'cookie');
    $config->set('cache.default', 'array');
});

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();
$kernel->terminate($request, $response);
