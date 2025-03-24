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

    Route::get('item-units', "ItemUnitController@index");
    Route::post('item-units', "ItemUnitController@store");
    Route::put('item-units', "ItemUnitController@update");
    Route::delete('item-units', "ItemUnitController@destroy");
    
    Route::get('item-categories', "ItemCategoryController@index");
    Route::post('item-categories', "ItemCategoryController@store");
    Route::put('item-categories', "ItemCategoryController@update");
    Route::delete('item-categories', "ItemCategoryController@destroy");
    
    Route::get('item-classifications', "itemClassificationController@index");
    Route::post('item-classifications', "itemClassificationController@store");
    Route::put('item-classifications', "itemClassificationController@update");
    Route::delete('item-classifications', "itemClassificationController@destroy");
    
    Route::get('success-indicators', "SuccessIndicatorController@index");
    Route::post('success-indicators', "SuccessIndicatorController@store");
    Route::put('success-indicators', "SuccessIndicatorController@update");
    Route::delete('success-indicators', "SuccessIndicatorController@destroy");
    
    Route::get('type-of-functions', "TypeOfFunctionController@index");
    Route::post('type-of-functions', "TypeOfFunctionController@store");
    Route::put('type-of-functions', "TypeOfFunctionController@update");
    Route::delete('type-of-functions', "TypeOfFunctionController@destroy");
    
    Route::get('purchase-types', "PurchaseTypeController@index");
    Route::post('purchase-types', "PurchaseTypeController@store");
    Route::put('purchase-types', "PurchaseTypeController@update");
    Route::delete('purchase-types', "PurchaseTypeController@destroy");
    
    Route::get('objectives', "ObjectiveController@index");
    Route::post('objectives', "ObjectiveController@store");
    Route::put('objectives', "ObjectiveController@update");
    Route::delete('objectives', "ObjectiveController@destroy");
    
    Route::get('log-descriptions', "LogDescriptionController@index");
    Route::post('log-descriptions', "LogDescriptionController@store");
    Route::put('log-descriptions', "LogDescriptionController@update");
    Route::delete('log-descriptions', "LogDescriptionController@destroy");
});