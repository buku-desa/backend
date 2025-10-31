<?php

// routes/web.php
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Mail;

Route::get('/test-mail', function () {
    Mail::raw('Ini pesan percobaan dari LDBD.', function ($message) {
        $message->to('test@example.com')
            ->subject('Tes Email LDBD');
    });
    return 'Email test sudah dikirim!';
});
