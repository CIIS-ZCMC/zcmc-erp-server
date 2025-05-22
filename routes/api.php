<?php

use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\AopApplicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes - no authentication required
Route::post('authenticate', [AuthController::class, 'login'])->name('login');
// Route::get('authenticate', [AuthController::class, 'login']); // Added GET support for authentication

// Protected routes - require API authentication
Route::middleware('auth.api')->group(function () {
    // User profile/data routes
    Route::get('user', [AuthController::class, 'index']);
    Route::get('auth/user', [AuthController::class, 'index']);
    // Routes with specific permissions
    Route::middleware('ability:ERP-AOP-MAN:view-all')->group(function () {
        Route::get('aop-requests', [AopApplicationController::class, 'aopRequests']);
        Route::post('aop-application-store', [AopApplicationController::class, 'store']);
    });
});



Route::namespace('App\Http\Controllers')->group(function () {

    Route::namespace('Libraries')->group(function () {
        // Item routes
        Route::get('items', "ItemController@index");
        Route::post('items', "ItemController@store");
        Route::put('items', "ItemController@update");
        Route::delete('items', "ItemController@destroy");

        // Item Request routes
        Route::post('item-requests/{id}/update-status', "ItemRequestController@approve");
        Route::get('item-requests', "ItemRequestController@index");
        Route::post('item-requests', "ItemRequestController@store");
        Route::put('item-requests/{id}', "ItemRequestController@update");
        Route::delete('item-requests', "ItemRequestController@destroy");

        // Item Units routes
        Route::post('item-units/import', "ItemUnitController@import");
        Route::get('item-units/template', "ItemUnitController@downloadTemplate");
        Route::get('item-units', "ItemUnitController@index");
        Route::post('item-units', "ItemUnitController@store");
        Route::put('item-units', "ItemUnitController@update");
        Route::delete('item-units', "ItemUnitController@destroy");

        // Variant routes
        Route::get('variants', "VariantController@index");
        Route::get('variants/trashbin', "VariantController@trash");
        Route::post('variants', "VariantController@store");
        Route::put('variants/{variant}', "VariantController@update");
        Route::put('variants/{id}/restore', "VariantController@restore");
        Route::delete('variants/{variant}', "VariantController@destroy");

        // Snomed routes
        Route::get('snomeds', "SnomedController@index");
        Route::get('snomeds/trashbin', "SnomedController@trashbin");
        Route::post('snomeds', "SnomedController@store");
        Route::put('snomeds/{snomed}', "SnomedController@update");
        Route::put('snomeds/{id}/restore', "SnomedController@restore");
        Route::delete('snomeds/{snomed}', "SnomedController@destroy");

        // Item Categories routes
        Route::post('item-categories/import', "ItemCategoryController@import");
        Route::get('item-categories/template', "ItemCategoryController@downloadTemplate");
        Route::get('item-categories', "ItemCategoryController@index");
        Route::get('item-categories/trashbin', "ItemCategoryController@trash");
        Route::post('item-categories', "ItemCategoryController@store");
        Route::put('item-categories', "ItemCategoryController@update");
        Route::put('item-categories/{id}/restore', "ItemCategoryController@restore");
        Route::delete('item-categories', "ItemCategoryController@destroy");

        // Item Classifications routes
        Route::post('item-classifications/import', "itemClassificationController@import");
        Route::get('item-classifications/template', "itemClassificationController@downloadTemplate");
        Route::get('item-classifications', "itemClassificationController@index");
        Route::get('item-classifications/trashbin', "itemClassificationController@trash");
        Route::post('item-classifications', "itemClassificationController@store");
        Route::put('item-classifications', "itemClassificationController@update");
        Route::put('item-classifications/{id}/restore', "itemClassificationController@restore");
        Route::delete('item-classifications', "itemClassificationController@destroy");
    });

    // Success Indicators routes
    Route::get('success-indicators', "SuccessIndicatorController@index");
    Route::post('success-indicators', "SuccessIndicatorController@store");
    Route::put('success-indicators', "SuccessIndicatorController@update");
    Route::delete('success-indicators', "SuccessIndicatorController@destroy");

    // Type of Functions routes
    Route::get('type-of-functions', "TypeOfFunctionController@index");
    Route::get('type-of-functions/trashbin', "TypeOfFunctionController@trash");
    Route::post('type-of-functions', "TypeOfFunctionController@store");
    Route::put('type-of-functions', "TypeOfFunctionController@update");
    Route::put('type-of-functions/{id}/restore', "TypeOfFunctionController@restore");
    Route::delete('type-of-functions', "TypeOfFunctionController@destroy");

    // Purchase Types routes
    Route::get('purchase-types', "PurchaseTypeController@index");
    Route::get('purchase-types/trashbin', "PurchaseTypeController@trash");
    Route::post('purchase-types', "PurchaseTypeController@store");
    Route::put('purchase-types', "PurchaseTypeController@update");
    Route::put('purchase-types/{id}/restore', "PurchaseTypeController@restore");
    Route::delete('purchase-types', "PurchaseTypeController@destroy");

    // Objectives routes
    Route::get('objectives', "ObjectiveController@index");
    Route::post('objectives', "ObjectiveController@store");
    Route::put('objectives', "ObjectiveController@update");
    Route::delete('objectives', "ObjectiveController@destroy");

    // Procurement Modes routes
    Route::get('procurement-modes', "ProcurementModesController@index");
    Route::post('procurement-modes', "ProcurementModesController@store");
    Route::put('procurement-modes/{id}', "ProcurementModesController@update");
    Route::delete('procurement-modes', "ProcurementModesController@destroy");

    // Divisions routes
    Route::get('divisions/import', "DivisionController@import");
    Route::get('divisions', "DivisionController@index");
    Route::put('divisions', "DivisionController@update");
    Route::delete('divisions', "DivisionController@destroy");

    // Departments routes
    Route::get('departments/import', "DepartmentController@import");
    Route::get('departments', "DepartmentController@index");
    Route::put('departments', "DepartmentController@update");
    Route::delete('departments', "DepartmentController@destroy");

    // Sections routes
    Route::get('sections/import', "SectionController@import");
    Route::get('sections', "SectionController@index");
    Route::put('sections', "SectionController@update");
    Route::delete('sections', "SectionController@destroy");

    // Units routes
    Route::get('units/import', "UnitController@import");
    Route::get('units', "UnitController@index");
    Route::put('units', "UnitController@update");
    Route::delete('units', "UnitController@destroy");

    // Log Descriptions routes
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
    Route::get('ppmp-item-search', 'PpmpItemController@search');
    Route::apiResource('ppmp-items', 'PpmpItemController');
    Route::apiResource('ppmp-item-requests', 'PpmpItemRequestControlller');

    // Activity Module
    Route::apiResource('activities', 'ActivityController');
    Route::apiResource('activity-comments', 'ActivityCommentController');
    Route::get('comments-per-activity', 'ActivityController@commentsPerActivity');
    Route::post('activities/{id}/mark-reviewed', 'ActivityController@markAsReviewed');

    // Approver Module
    Route::get('manage-aop-request/{id}', 'ApplicationObjectiveController@manageAopRequest');
    Route::get('application-timeline/{id}', 'ApplicationTimelineController@show');
    Route::get('application-timelines', 'ApplicationTimelineController@index');
    Route::apiResource('application-timelines', 'ApplicationTimelineController');
    Route::get('show-objective-activity/{id}', 'ApplicationObjectiveController@showObjectiveActivity');
    Route::put('edit-objective-and-success-indicator', 'ApplicationObjectiveController@editObjectiveAndSuccessIndicator');
    Route::post('process-aop-request', 'AopApplicationController@processAopRequest');

    // Aop Application Module
    Route::get('aop-applications', 'AopApplicationController@index');
    // Route::post('aop-application-store', 'AopApplicationController@store');
    Route::post('aop-application-update/{id}', 'AopApplicationController@update');
    Route::get('aop-application-show/{id}', 'AopApplicationController@show');
    Route::get('aop-application-summary/{id}', 'AopApplicationController@getAopApplicationSummary');
    Route::get('aop-application-timeline/{id}', 'AopApplicationController@showTimeline');
    Route::get('aop-remarks/{id}', 'AopApplicationController@aopRemarks');
    Route::get('get-areas', 'AopApplicationController@getAllArea');
    Route::get('get-designations', 'AopApplicationController@getAllDesignations');
    Route::get('get-users', 'AopApplicationController@getUsersWithDesignation');
    Route::post('export-aop/{id}', 'AopApplicationController@export');
    Route::get('preview-aop/{id}', 'AopApplicationController@preview');
    Route::post('import/items', 'ItemImportController@import');

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

    // Deadline routes
    Route::get('deadlines', 'DeadlineController@index');
    Route::post('aop-deadline-store', 'DeadlineController@storeAopDeadline');
    Route::post('ppmp-deadline-store', 'DeadlineController@storePpmpDeadline');
    Route::post('aop-deadline-update/{id}', 'DeadlineController@updateAopDeadline');
    Route::post('ppmp-deadline-update/{id}', 'DeadlineController@updatePpmpDeadline');
});
