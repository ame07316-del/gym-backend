<?php

$tmpStorage = '/tmp/storage';

// 1. بناء هيكل مجلدات Laravel الأساسية لتجنب أي أخطاء متعلقة بالكتابة
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

// 2. تحميل التطبيق
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. توجيه Storage بالكامل إلى المجلد المؤقت الجديد
$app->useStoragePath($tmpStorage);

// 4. تشغيل الطلب
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();
$kernel->terminate($request, $response);
