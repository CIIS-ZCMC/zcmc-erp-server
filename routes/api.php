<?php

use App\Http\Controllers\ActivityCommentController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ApplicationObjectiveController;
use App\Http\Controllers\ApplicationTimelineController;
use App\Http\Controllers\Areas\DepartmentController;
use App\Http\Controllers\Areas\DivisionController;
use App\Http\Controllers\Areas\SectionController;
use App\Http\Controllers\Areas\UnitController;
use App\Http\Controllers\AssignedAreaController;
use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\AopApplicationController;
use App\Http\Controllers\DeadlineController;
use App\Http\Controllers\ItemImportController;
use App\Http\Controllers\Libraries\ItemCategoryController;
use App\Http\Controllers\Libraries\ItemClassificationController;
use App\Http\Controllers\Libraries\ItemController;
use App\Http\Controllers\Libraries\ItemReferenceTerminologyController;
use App\Http\Controllers\Libraries\ItemRequestController;
use App\Http\Controllers\Libraries\ItemUnitController;
use App\Http\Controllers\LogDescriptionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\PpmpApplicationController;
use App\Http\Controllers\ProcurementModesController;
use App\Http\Controllers\PurchaseTypeController;
use App\Http\Controllers\SuccessIndicatorController;
use App\Http\Controllers\TypeOfFunctionController;
use App\Http\Controllers\Libraries\TerminologyController;
use Illuminate\Support\Facades\Route;

// Public routes - no authentication required
Route::post('authenticate', [AuthController::class, 'login'])->name('login');
// Route::get('authenticate', [AuthController::class, 'login']); // Added GET support for authentication

// Protected routes - require API authentication
Route::middleware('auth.api')->group(function () {
    // User profile/data routes
    Route::get('user', [AuthController::class, 'index']);
    Route::get('auth/user', [AuthController::class, 'index']);
    Route::delete('logout', [AuthController::class, 'logout']);

    // Routes with specific permissions
    Route::middleware('ability:ERP-AOP-MAN:view-all')->group(function () {
        Route::get('aop-requests', [AopApplicationController::class, 'aopRequests']);
    });
});



Route::
        namespace('App\Http\Controllers')->group(function () {

            Route::namespace('Libraries')->group(function () {
                // Terminologies routes
                Route::get('terminologies', [TerminologyController::class, 'index']);

                // Item routes
                Route::get('item-reference-terminologies', [ItemReferenceTerminologyController::class, 'index']);
                Route::post('item-reference-terminologies', [ItemReferenceTerminologyController::class, 'store']);
                Route::get('item-reference-terminologies/trashbin', [ItemReferenceTerminologyController::class, 'trash']);
                Route::put('item-reference-terminologies/{id}/restore', [ItemReferenceTerminologyController::class, 'restore']);
                Route::put('item-reference-terminologies', [ItemReferenceTerminologyController::class, 'update']);
                Route::delete('item-reference-terminologies', [ItemReferenceTerminologyController::class, 'destroy']);

                // Item routes
                Route::get('items', [ItemController::class, "index"]);
                Route::post('items', [ItemController::class, "store"]);
                Route::put('items', [ItemController::class, "update"]);
                Route::delete('items', [ItemController::class, "destroy"]);

                // Item Request routes
                Route::post('item-requests/{id}/update-status', [ItemRequestController::class, 'approve']);
                Route::get('item-requests', [ItemRequestController::class, 'index']);
                Route::post('item-requests', [ItemRequestController::class, 'store']);
                Route::put('item-requests/{id}', [ItemRequestController::class, 'update']);
                Route::delete('item-requests', [ItemRequestController::class, 'destroy']);

                // Item Units routes
                Route::post('item-units/import', [ItemUnitController::class, "import"]);
                Route::get('item-units/template', [ItemUnitController::class, "downloadTemplate"]);
                Route::get('item-units', [ItemUnitController::class, "index"]);
                Route::post('item-units', [ItemUnitController::class, "store"]);
                Route::put('item-units', [ItemUnitController::class, "update"]);
                Route::delete('item-units', [ItemUnitController::class, "destroy"]);

                // Item Categories routes
                Route::post('item-categories/import', [ItemCategoryController::class, "import"]);
                Route::get('item-categories/template', [ItemCategoryController::class, "downloadTemplate"]);
                Route::get('item-categories', [ItemCategoryController::class, "index"]);
                Route::get('item-categories/trashbin', [ItemCategoryController::class, "trash"]);
                Route::post('item-categories', [ItemCategoryController::class . "store"]);
                Route::put('item-categories', [ItemCategoryController::class, "update"]);
                Route::put('item-categories/{id}/restore', [ItemCategoryController::class, "restore"]);
                Route::delete('item-categories', [ItemCategoryController::class, "destroy"]);

                // Item Classifications routes
                Route::post('item-classifications/import', [ItemClassificationController::class, "import"]);
                Route::get('item-classifications/template', [itemClassificationController::class, "downloadTemplate"]);
                Route::get('item-classifications', [itemClassificationController::class, "index"]);
                Route::get('item-classifications/trashbin', [itemClassificationController::class, "trash"]);
                Route::post('item-classifications', [itemClassificationController::class, "store"]);
                Route::put('item-classifications', [itemClassificationController::class, "update"]);
                Route::put('item-classifications/{id}/restore', [itemClassificationController::class, "restore"]);
                Route::delete('item-classifications', [itemClassificationController::class, 'destroy']);
            });

            // Success Indicators routes
            Route::get('success-indicators', [SuccessIndicatorController::class, "index"]);
            Route::post('success-indicators', [SuccessIndicatorController::class, "store"]);
            Route::put('success-indicators', [SuccessIndicatorController::class, "update"]);
            Route::delete('success-indicators', [SuccessIndicatorController::class, "destroy"]);

            // Type of Functions routes
            Route::get('type-of-functions', [TypeOfFunctionController::class, "index"]);
            Route::get('type-of-functions/trashbin', [TypeOfFunctionController::class, "trash"]);
            Route::post('type-of-functions', [TypeOfFunctionController::class, "store"]);
            Route::put('type-of-functions', [TypeOfFunctionController::class, "update"]);
            Route::put('type-of-functions/{id}/restore', [TypeOfFunctionController::class, "restore"]);
            Route::delete('type-of-functions', [TypeOfFunctionController::class, "destroy"]);

            // Purchase Types routes
            Route::get('purchase-types', [PurchaseTypeController::class, "index"]);
            Route::get('purchase-types/trashbin', [PurchaseTypeController::class, "trash"]);
            Route::post('purchase-types', [PurchaseTypeController::class, "store"]);
            Route::put('purchase-types', [PurchaseTypeController::class, "update"]);
            Route::put('purchase-types/{id}/restore', [PurchaseTypeController::class, "restore"]);
            Route::delete('purchase-types', [PurchaseTypeController::class, "destroy"]);

            // Objectives routes
            Route::get('objectives', [ObjectiveController::class, "index"]);
            Route::post('objectives', [ObjectiveController::class, "store"]);
            Route::put('objectives', [ObjectiveController::class, "update"]);
            Route::delete('objectives', [ObjectiveController::class, "destroy"]);

            // Procurement Modes routes
            Route::get('procurement-modes', [ProcurementModesController::class, "index"]);
            Route::post('procurement-modes', [ProcurementModesController::class, "store"]);
            Route::put('procurement-modes/{id}', [ProcurementModesController::class, "update"]);
            Route::delete('procurement-modes', [ProcurementModesController::class, "destroy"]);

            Route::namespace('Areas')->group(function () {

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
            });

            // Log Descriptions routes
            Route::post('log-descriptions/template', [LogDescriptionController::class, "import"]);
            Route::post('log-descriptions/import', [LogDescriptionController::class, "downloadTemplate"]);
            Route::get('log-descriptions', [LogDescriptionController::class, "index"]);
            Route::post('log-descriptions', [LogDescriptionController::class, "store"]);
            Route::put('log-descriptions', [LogDescriptionController::class, "update"]);
            Route::delete('log-descriptions', [LogDescriptionController::class, "destroy"]);

            // AssignedArea Routes - UMIS Integration
            Route::get('assigned-areas', [AssignedAreaController::class, "index"]);
            Route::get('assigned-areas/{id}', [AssignedAreaController::class, "show"]);
            Route::post('umis/areas/update', [AssignedAreaController::class, "processUMISUpdate"]);

            // Ppmp Application Module
            Route::apiResource('ppmp-applications', PpmpApplicationController::class);
            Route::get('ppmp-receiving-list', [PpmpApplicationController::class, "receivingList"]);
            Route::get('ppmp-receiving-list-view/{id}', [PpmpApplicationController::class, "receivingListView"]);
            Route::post('ppmp-applications/{id}/receive', [PpmpApplicationController::class, "receivePpmpApplication"]);

            // Ppmp Item Module
            Route::get('ppmp-item-search', 'PpmpItemController@search');
            Route::apiResource('ppmp-items', 'PpmpItemController')->only(['index', 'store', 'update']);
            Route::delete('ppmp-items', 'PpmpItemController@destroy');
            Route::apiResource('ppmp-item-requests', 'PpmpItemRequestControlller');
            Route::get('ppmp-item-export', 'PpmpItemController@export');

            // Activity Module
            Route::apiResource('activities', ActivityController::class);
            Route::apiResource('activity-comments', ActivityCommentController::class);
            Route::get('comments-per-activity', [ActivityController::class, "commentsPerActivity"]);
            Route::post('activities/{id}/mark-reviewed', [ActivityController::class, "markAsReviewed"]);

            // Approver Module
            Route::get('manage-aop-request/{id}', [ApplicationObjectiveController::class, "manageAopRequest"]);
            Route::get('application-timeline/{id}', [ApplicationTimelineController::class, "show"]);
            Route::get('application-timelines', [ApplicationTimelineController::class, "index"]);
            Route::apiResource('application-timelines', ApplicationTimelineController::class);
            Route::get('show-objective-activity/{id}', [ApplicationObjectiveController::class, "showObjectiveActivity"]);
            Route::put('edit-objective-and-success-indicator', [ApplicationObjectiveController::class, "editObjectiveAndSuccessIndicator"]);
            Route::post('process-aop-request', [AopApplicationController::class, "processAopRequest"]);

            // Aop Application Module
            Route::get('aop-applications', [AopApplicationController::class, "index"]);
            Route::post('aop-application-store', [AopApplicationController::class, "store"]);
            Route::post('aop-application-update/{id}', [AopApplicationController::class, "update"]);
            Route::get('aop-application-show/{id}', [AopApplicationController::class, "show"]);
            Route::get('aop-application-summary', [AopApplicationController::class, "getUserAopSummary"]);
            Route::get('aop-application-timeline', [AopApplicationController::class, "showUserTimeline"]);
            Route::get('aop-remarks/{id}', [AopApplicationController::class, "aopRemarks"]);
            Route::get('get-areas', [AopApplicationController::class, "getAllArea"]);
            Route::get('get-designations', [AopApplicationController::class, "getAllDesignations"]);
            Route::get('get-users', [AopApplicationController::class, "getUsersWithDesignation"]);
            Route::post('export-aop/{id}', [AopApplicationController::class, "export"]);
            Route::get('preview-aop/{id}', [AopApplicationController::class, "preview"]);
            Route::post('import/items', [ItemImportController::class, "import"]);
            Route::get('aop-application-edit', [AopApplicationController::class, "edit"]);

            // Deadlines
            Route::get('deadlines', [DeadlineController::class, 'index']);
            Route::post('aop-deadline-store', [DeadlineController::class, 'storeAopDeadline']);
            Route::post('ppmp-deadline-store', [DeadlineController::class, 'storePpmpDeadline']);
            Route::post('aop-deadline-update/{id}', [DeadlineController::class, 'updateAopDeadline']);
            Route::post('ppmp-deadline-update/{id}', [DeadlineController::class, 'updatePpmpDeadline']);

            // Notification Module
            // FOR CRUD
            Route::apiResource('notifications', 'NotificationController');

            // GET ROUTES
            Route::get('notifications/seen/{id}', [NotificationController::class, 'markAsSeen']);
            Route::get('notifications/all-seen/{id}', [NotificationController::class, 'markAllAsSeen']);
            Route::get('notifications/employee-notifs/{profile_id}', [NotificationController::class, 'employeeNotifications']);
            Route::get('notifications/get-notifs-by-status/{seen}', [NotificationController::class, 'getNotificationByStatus']);
            Route::get('notifications/unseen-count', [NotificationController::class, 'getUnseenCount']);
        });
