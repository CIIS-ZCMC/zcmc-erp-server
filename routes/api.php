<?php

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

    Route::get('items', "ItemController@index");
    Route::post('items', "ItemController@store");
    Route::put('items', "ItemController@update");
    Route::delete('items', "ItemController@destroy");

    Route::post('item-requests/{id}/update-status', "ItemRequestController@approve");
    Route::get('item-requests', "ItemRequestController@index");
    Route::post('item-requests', "ItemRequestController@store");
    Route::put('item-requests/{id}', "ItemRequestController@update");
    Route::delete('item-requests', "ItemRequestController@destroy");

    Route::post('item-units/template', "ItemUnitController@import");
    Route::post('item-units/import', "ItemUnitController@downloadTemplate");
    Route::get('item-units', "ItemUnitController@index");
    Route::post('item-units', "ItemUnitController@store");
    Route::put('item-units', "ItemUnitController@update");
    Route::delete('item-units', "ItemUnitController@destroy");
    
    Route::post('item-categories/template', "ItemCategoryController@import");
    Route::post('item-categories/import', "ItemCategoryController@downloadTemplate");
    Route::get('item-categories', "ItemCategoryController@index");
    Route::post('item-categories', "ItemCategoryController@store");
    Route::put('item-categories', "ItemCategoryController@update");
    Route::delete('item-categories', "ItemCategoryController@destroy");
    
    Route::post('item-classifications/template', "itemClassificationController@import");
    Route::post('item-classifications/import', "itemClassificationController@downloadTemplate");
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
    
    Route::get('procurement-modes', "ProcurementModesController@index");
    Route::post('procurement-modes', "ProcurementModesController@store");
    Route::put('procurement-modes/{id}', "ProcurementModesController@update");
    Route::delete('procurement-modes', "ProcurementModesController@destroy");
    
    Route::post('log-descriptions/template', "LogDescriptionController@import");
    Route::post('log-descriptions/import', "LogDescriptionController@downloadTemplate");
    Route::get('log-descriptions', "LogDescriptionController@index");
    Route::post('log-descriptions', "LogDescriptionController@store");
    Route::put('log-descriptions', "LogDescriptionController@update");
    Route::delete('log-descriptions', "LogDescriptionController@destroy");
});