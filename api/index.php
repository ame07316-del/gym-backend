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

$dbPath = $tmpStorage . '/database/database.sqlite';
if (!file_exists($dbPath)) {
    if (file_exists(__DIR__ . '/../database/database.sqlite')) {
        copy(__DIR__ . '/../database/database.sqlite', $dbPath);
    } else {
        touch($dbPath);
    }
}

// إجبار Laravel على عدم الالتزام بـ Secure Cookie لتجاوز تحذير Dangerous في Chrome
putenv('ASSET_URL=/');
putenv('APP_ENV=production');
putenv('APP_DEBUG=true');
putenv('LOG_CHANNEL=stderr');
putenv('DB_CONNECTION=sqlite');
putenv("DB_DATABASE={$dbPath}");
putenv('CACHE_STORE=file');
putenv('SESSION_DRIVER=file');
putenv('SESSION_SECURE_COOKIE=false');
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

    // عمل Migrate و Create User
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
            ]
        );
    } catch (\Throwable $ex) {
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
