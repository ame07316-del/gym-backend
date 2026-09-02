<?php

// 1. إنشاء المجلدات المؤقتة داخل /tmp
$tmpDir = '/tmp';
@mkdir($tmpDir . '/views', 0755, true);
@mkdir($tmpDir . '/sessions', 0755, true);
@mkdir($tmpDir . '/cache', 0755, true);

// 2. تحميل الـ Autoloader والتطبيق
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. تعيين المسارات المؤقتة وإعدادات الـ Logging والـ Cache بأمان
$app->useStoragePath($tmpDir);

config([
    'logging.default' => 'stderr',
    'view.compiled' => $tmpDir . '/views',
    'session.driver' => 'cookie',
    'cache.default' => 'file',
    'cache.stores.file.path' => $tmpDir . '/cache',
]);

// 4. تشغيل الطلب
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
