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
});

Route::namespace('App\Http\Controllers')->group(function () {
    Route::post('login', 'AuthController@login');
    Route::get('signup', 'AuthController@signup');

    Route::get('objective-success-indicators', "ObjectiveSuccessIndicatorController@index");

    Route::get('items', "ItemController@index");
    Route::post('items', "ItemController@store");
    Route::put('items', "ItemController@update");
    Route::delete('items', "ItemController@destroy");

    Route::post('item-requests/{id}/update-status', "ItemRequestController@approve");
    Route::get('item-requests', "ItemRequestController@index");
    Route::post('item-requests', "ItemRequestController@store");
    Route::put('item-requests/{id}', "ItemRequestController@update");
    Route::delete('item-requests', "ItemRequestController@destroy");

    Route::post('item-units/import', "ItemUnitController@import");
    Route::get('item-units/template', "ItemUnitController@downloadTemplate");
    Route::get('item-units', "ItemUnitController@index");
    Route::post('item-units', "ItemUnitController@store");
    Route::put('item-units', "ItemUnitController@update");
    Route::delete('item-units', "ItemUnitController@destroy");

    Route::post('item-categories/import', "ItemCategoryController@import");
    Route::get('item-categories/template', "ItemCategoryController@downloadTemplate");
    Route::get('item-categories', "ItemCategoryController@index");
    Route::get('item-categories/trashbin', "ItemCategoryController@trash");
    Route::post('item-categories', "ItemCategoryController@store");
    Route::put('item-categories', "ItemCategoryController@update");
    Route::put('item-categories/{id}/restore', "ItemCategoryController@restore");
    Route::delete('item-categories', "ItemCategoryController@destroy");

    Route::post('item-classifications/import', "itemClassificationController@import");
    Route::get('item-classifications/template', "itemClassificationController@downloadTemplate");
    Route::get('item-classifications', "itemClassificationController@index");
    Route::get('item-classifications/trashbin', "itemClassificationController@trash");
    Route::post('item-classifications', "itemClassificationController@store");
    Route::put('item-classifications', "itemClassificationController@update");
    Route::put('item-classifications/{id}/restore', "itemClassificationController@restore");
    Route::delete('item-classifications', "itemClassificationController@destroy");

    Route::get('success-indicators', "SuccessIndicatorController@index");
    Route::post('success-indicators', "SuccessIndicatorController@store");
    Route::put('success-indicators', "SuccessIndicatorController@update");
    Route::delete('success-indicators', "SuccessIndicatorController@destroy");

    Route::get('type-of-functions', "TypeOfFunctionController@index");
    Route::get('type-of-functions/trashbin', "TypeOfFunctionController@trash");
    Route::post('type-of-functions', "TypeOfFunctionController@store");
    Route::put('type-of-functions', "TypeOfFunctionController@update");
    Route::put('type-of-functions/{id}/restore', "TypeOfFunctionController@restore");
    Route::delete('type-of-functions', "TypeOfFunctionController@destroy");

    Route::get('purchase-types', "PurchaseTypeController@index");
    Route::get('purchase-types/trashbin', "PurchaseTypeController@trash");
    Route::post('purchase-types', "PurchaseTypeController@store");
    Route::put('purchase-types', "PurchaseTypeController@update");
    Route::put('purchase-types/{id}/restore', "PurchaseTypeController@restore");
    Route::delete('purchase-types', "PurchaseTypeController@destroy");

    Route::get('objectives', "ObjectiveController@index");
    Route::post('objectives', "ObjectiveController@store");
    Route::put('objectives', "ObjectiveController@update");
    Route::delete('objectives', "ObjectiveController@destroy");

    Route::get('procurement-modes', "ProcurementModesController@index");
    Route::post('procurement-modes', "ProcurementModesController@store");
    Route::put('procurement-modes/{id}', "ProcurementModesController@update");
    Route::delete('procurement-modes', "ProcurementModesController@destroy");

    Route::get('divisions/import', "DivisionController@import");
    Route::get('divisions', "DivisionController@index");
    Route::put('divisions', "DivisionController@update");
    Route::delete('divisions', "DivisionController@destroy");

    Route::get('departments/import', "DepartmentController@import");
    Route::get('departments', "DepartmentController@index");
    Route::put('departments', "DepartmentController@update");
    Route::delete('departments', "DepartmentController@destroy");

    Route::get('sections/import', "SectionController@import");
    Route::get('sections', "SectionController@index");
    Route::put('sections', "SectionController@update");
    Route::delete('sections', "SectionController@destroy");

    Route::get('units/import', "UnitController@import");
    Route::get('units', "UnitController@index");
    Route::put('units', "UnitController@update");
    Route::delete('units', "UnitController@destroy");

    Route::post('log-descriptions/template', "LogDescriptionController@import");
    Route::post('log-descriptions/import', "LogDescriptionController@downloadTemplate");
    Route::get('log-descriptions', "LogDescriptionController@index");
    Route::post('log-descriptions', "LogDescriptionController@store");
    Route::put('log-descriptions', "LogDescriptionController@update");
    Route::delete('log-descriptions', "LogDescriptionController@destroy");

    // AssignedArea Routes - UMIS Integration
    Route::get('assigned-areas', "AssignedAreaController@index");
    Route::get('assigned-areas/{id}', "AssignedAreaController@show");
    Route::post('umis/areas/update', "AssignedAreaController@processUMISUpdate");

    // Ppmp Application Module
    Route::apiResource('ppmp-applications', 'PpmpApplicationController');

    // Ppmp Item Module
    Route::apiResource('ppmp-items', 'PpmpItemController');

    // Activity Module
    Route::apiResource('activities', 'ActivityController');
    Route::apiResource('activity-comments', 'ActivityCommentController');
    Route::get('comments-per-activity', 'ActivityController@commentsPerActivity');
    Route::post('activities/{id}/mark-reviewed', 'ActivityController@markAsReviewed');
    Route::post('activities/{id}/mark-unreviewed', 'ActivityController@markAsUnreviewed');
    Route::post('update-osi/{id}', 'ObjectiveSuccessIndicatorController@updateForApproverModule');

    // Approver  Module
    Route::get('aop-requests', 'AopApplicationController@aopRequests');
    Route::get('manage-aop-request/{id}', 'ApplicationObjectiveController@manageAopRequest');
    // Route::get('application-timeline/{id}', 'ApplicationTimelineController@show');
    // Route::get('application-timelines', 'ApplicationTimelineController@index');
    Route::apiResource('application-timelines', 'ApplicationTimelineController');
    Route::get('show-objective-activity/{id}', 'ApplicationObjectiveController@showObjectiveActivity');
    Route::put('edit-objective-and-success-indicator', 'ApplicationObjectiveController@editObjectiveAndSuccessIndicator');
    Route::post('process-aop-request', 'AopApplicationController@processAopRequest');
    // Approver  Module
    Route::get('aop-requests', 'AopApplicationController@aopRequests');
    Route::get('manage-aop-request/{id}', 'ApplicationObjectiveController@manageAopRequest');
    // Route::get('application-timeline/{id}', 'ApplicationTimelineController@show');
    // Route::get('application-timelines', 'ApplicationTimelineController@index');
    Route::apiResource('application-timelines', 'ApplicationTimelineController');
    Route::get('show-objective-activity/{id}', 'ApplicationObjectiveController@showObjectiveActivity');
    Route::put('edit-objective', 'ApplicationObjectiveController@editObjectiveAndSuccessIndicator');
    Route::post('process-aop-request', 'AopApplicationController@processAopRequest');

    // Aop Application Module
    Route::get('aop-applications', 'AopApplicationController@index');
    Route::post('aop-application-store', 'AopApplicationController@store');
    Route::post('aop-application-update/{id}', 'AopApplicationController@update');
    Route::get('aop-application-show/{id}', 'AopApplicationController@show');
    Route::get('aop-application-summary/{id}', 'AopApplicationController@getAopApplicationSummary');
    Route::get('aop-application-timeline/{id}', 'AopApplicationController@showTimeline');
    Route::get('aop-remarks/{id}', 'AopApplicationController@aopRemarks');

    Route::get('get-areas', 'AopApplicationController@getAllArea');
    Route::get('get-designations', 'AopApplicationController@getAllDesignations');
    Route::get('get-users', 'AopApplicationController@getUsersWithDesignation');
    Route::post('export-aop/{id}', 'AopApplicationController@export');

    // Variant Dummy
    Route::get('variant', function () {
        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'name' => 'High',
                    'description' => 'Description for Variant 1',
                ],
                [
                    'id' => 2,
                    'name' => 'Low',
                    'description' => 'Description for Variant 2',
                ],
                [
                    'id' => 3,
                    'name' => 'Mid',
                    'description' => 'Description for Variant 3',
                ],
            ]
        ], 200);
    });
});
