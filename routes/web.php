<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('one-time-password', function () {
    return view('onetimepassword.notify');
});
