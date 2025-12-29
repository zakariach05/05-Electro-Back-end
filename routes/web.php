<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug-mail', function () {
    $config = config('mail.mailers.smtp');
    return response()->json([
        'host' => $config['host'],
        'port' => $config['port'],
        'username' => $config['username'],
        'encryption' => $config['encryption'],
        'password_length' => strlen($config['password']),
        'password_first_3' => substr($config['password'], 0, 3)
    ]);
});
