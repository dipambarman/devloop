<?php

use App\Services\ErrorFingerprintService;

it('normalizes error messages by stripping UUIDs, hex addresses, line numbers, and integers', function () {
    $service = new ErrorFingerprintService;

    $raw = 'Failed to process job 12345 with UUID c9a646d3-9c61-4cc9-bc55-e9b46e3e57f2 at memory 0x7fff5fbff820 line 42:15';
    $normalized = $service->normalizeMessage($raw);

    expect($normalized)->toBe('Failed to process job with UUID at memory line');
});

it('extracts error types correctly from JS and PHP error messages', function () {
    $service = new ErrorFingerprintService;

    expect($service->extractErrorType('TypeError: Cannot read properties of undefined'))->toBe('TypeError');
    expect($service->extractErrorType('Illuminate\Database\QueryException: SQLSTATE[42S02]'))->toBe('QueryException');
    expect($service->extractErrorType('DivisionByZeroError in Math.php'))->toBe('DivisionByZeroError');
    expect($service->extractErrorType('Something went wrong'))->toBe('UnknownError');
});

it('extracts top frame from V8, Safari, and PHP stack traces', function () {
    $service = new ErrorFingerprintService;

    $v8Stack = "TypeError: Cannot read properties\n    at handleRequest (/var/www/src/controllers/userController.js:45:12)\n    at Layer.handle";
    expect($service->parseTopFrame($v8Stack))->toBe('handleRequest@userController.js');

    $phpStack = "#0 /var/www/app/Http/Controllers/OrderController.php(65): App\Services\OrderService->checkout()\n#1 /var/www/vendor/laravel/framework";
    expect($service->parseTopFrame($phpStack))->toBe('App\Services\OrderService->checkout()@OrderController.php');
});

it('computes identical fingerprints across different environments and line numbers', function () {
    $service = new ErrorFingerprintService;

    // Linux environment
    $stackLinux = "Error: Connection timed out\n    at queryDatabase (/home/ubuntu/app/database.js:120:45)";
    $fp1 = $service->computeFingerprint('Connection timed out at queryDatabase', 'Error', $stackLinux);

    // Windows environment with different line number
    $stackWindows = "Error: Connection timed out\n    at queryDatabase (C:\\Users\\Developer\\Project\\database.js:342:10)";
    $fp2 = $service->computeFingerprint('Connection timed out at queryDatabase', 'Error', $stackWindows);

    expect($fp1)->toHaveLength(16);
    expect($fp2)->toHaveLength(16);
    expect($fp1)->toBe($fp2);
});

it('computes different fingerprints for different errors', function () {
    $service = new ErrorFingerprintService;

    $fp1 = $service->computeFingerprint('Undefined variable $user', 'ErrorException', 'at show@UserController.php');
    $fp2 = $service->computeFingerprint('Database connection refused', 'PDOException', 'at connect@Database.php');

    expect($fp1)->not->toBe($fp2);
});
