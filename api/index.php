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

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath($tmpStorage);

// ضبط مسار الـ Views Compiled والـ Cache على مجلد /tmp المتاح للكتبة في Vercel
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
