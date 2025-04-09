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

Route::get('/api-docs/success-indicators', function () {
    return view('api-docs.success-indicators');
});

Route::get('/api-docs/type-of-functions', function () {
    return view('api-docs.type-of-functions');
});

Route::get('/api-docs/purchase-types', function () {
    return view('api-docs.purchase-type');
});

Route::get('/api-docs/objectives', function () {
    return view('api-docs.objectives');
});

Route::get('/api-docs/item-classifications', function () {
    return view('api-docs.item-classifications');
});

Route::get('/api-docs/items', function () {
    action:
    return view('api-docs.items');
});

Route::get('/api-docs/procurement-modes', function () {
    return view('api-docs.procurement-modes');
});

Route::get('/api-docs/ppmp-application', function () {
    return view('api-docs.ppmp-application');
});