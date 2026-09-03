<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$tmpStorage = '/tmp/storage';
$tmpBootstrapCache = '/tmp/bootstrap/cache';

$directories = [
    $tmpStorage . '/app',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/framework/views',
    $tmpStorage . '/logs',
    $tmpStorage . '/database',
    $tmpBootstrapCache,
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// تجهيز قاعدة البيانات SQLite
$dbPath = $tmpStorage . '/database/database.sqlite';
if (!file_exists($dbPath)) {
    if (file_exists(__DIR__ . '/../database/database.sqlite')) {
        copy(__DIR__ . '/../database/database.sqlite', $dbPath);
    } else {
        touch($dbPath);
    }
}

// ضبط البيئة والـ Sessions والـ Cookies
putenv('ASSET_URL=/');
putenv('APP_ENV=production');
putenv('APP_DEBUG=true');
putenv('LOG_CHANNEL=stderr');
putenv('DB_CONNECTION=sqlite');
putenv("DB_DATABASE={$dbPath}");
putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=cookie'); // حفظ الـ Session في الكوكي لعدم فقدان تسجيل الدخول
putenv('SESSION_LIFETIME=120');
putenv('VIEW_COMPILED_PATH=' . $tmpStorage . '/framework/views');

if (empty($_ENV['APP_KEY']) && empty(getenv('APP_KEY'))) {
    $defaultKey = 'base64:pauv5+vQ80eGUEbLuPJwPQJOaEZQjCHeeNRb6+Q0XMs=';
    putenv("APP_KEY={$defaultKey}");
    $_ENV['APP_KEY'] = $defaultKey;
}

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    $app->useStoragePath($tmpStorage);
    $app->useBootstrapPath('/tmp/bootstrap');

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();

    // إنشاء الجداول والأدمن تلقائياً مرة واحدة عند أول تشغيل
    if (!file_exists($tmpStorage . '/installed.lock')) {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            \App\Models\User::updateOrCreate(
                ['email' => 'admin@admin.com'],
                [
                    'name' => 'Admin',
                    'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                ]
            );
            file_put_contents($tmpStorage . '/installed.lock', 'locked');
        } catch (\Throwable $ex) {
            // تجاهل لو الجداول أنشئت بالفعل
        }
    }

    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);

    $response->send();
    $kernel->terminate($request, $response);
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<h1>Deployment Exception:</h1>';
    echo '<p><b>Message:</b> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><b>File:</b> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>';
    echo '<h3>Trace:</h3><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}
