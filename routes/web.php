<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('one-time-password', function () {
    return view('onetimepassword.notify');
});

Route::get('/api-docs', function () {
    return view('api-docs.index');
});

Route::get('/api-docs/item-units', function () {
    return view('api-docs.item-units');
});

Route::get('/api-docs/item-categories', function () {
    return view('api-docs.item-categories');
});

Route::get('/api-docs/log-descriptions', function () {
    return view('api-docs.log-descriptions');
});