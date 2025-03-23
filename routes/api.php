<?php

use App\Http\Middleware\Cors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('test', function (Request $request) {
    return response()->json(['message' => "PASSED"], 200);
});

Route::middleware('auth.api:auth_user_provider')->group(function () {
    Route::namespace('App\Http\Controllers')->group(function () {
        Route::get('user', 'AuthController@index');
    });
});

Route::namespace('App\Http\Controllers')->group(function () {  
    Route::post('login', 'AuthController@login');
    Route::get('signup', 'AuthController@signup');

    // Route::get('item-units', "ItemUnitController@index");
    // Route::post('item-units', "ItemUnitController@store");
    // Route::put('item-units', "ItemUnitController@update");
    // Route::delete('item-units', "ItemUnitController@destroy");
    
    // Route::get('item-categories', "ItemCategoryController@index");
    // Route::post('item-categories', "ItemCategoryController@store");
    // Route::put('item-categories', "ItemCategoryController@update");
    // Route::delete('item-categories', "ItemCategoryController@destroy");
});