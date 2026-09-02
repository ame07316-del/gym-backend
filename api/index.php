<?php

// 1. فرض إعدادات البيئة في بداية التشغيل قبل تحميل Laravel
putenv('LOG_CHANNEL=stderr');
putenv('VIEW_COMPILED_PATH=/tmp/views');
putenv('SESSION_DRIVER=cookie');
putenv('CACHE_STORE=array');

$_ENV['LOG_CHANNEL'] = 'stderr';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/views';
$_ENV['SESSION_DRIVER'] = 'cookie';
$_ENV['CACHE_STORE'] = 'array';

// 2. إنشاء المجلدات المؤقتة داخل /tmp
$tmpDir = '/tmp';
@mkdir($tmpDir . '/views', 0755, true);
@mkdir($tmpDir . '/sessions', 0755, true);

// 3. تحميل الـ Autoloader والتطبيق
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 4. تغيير مسار الـ Storage ليصبح /tmp بدلاً من القرص الأساسي
$app->useStoragePath($tmpDir);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
