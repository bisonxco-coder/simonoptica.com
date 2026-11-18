<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UploadedAnalyseB2bController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SOAPController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TracerController;
use App\Http\Controllers\UploadedShapeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/forgot', [AuthController::class, 'forgot']);
Route::post('auth/reset', [AuthController::class, 'reset']);

Route::post('soap/product-category-initial', [SOAPController::class, 'productCategoryInitial']);
Route::post('soap/product-category-update', [SOAPController::class, 'productCategoryUpdate']);
Route::post('soap/default-min-thk', [SOAPController::class, 'defaultMinThk']);
Route::post('soap/login', [SOAPController::class, 'checkLogin']);
Route::post('soap/changePassword', [SOAPController::class, 'changePassword']);
Route::post('soap/logout', [SOAPController::class, 'logout']);
Route::post('soap/interpolate', [SOAPController::class, 'interpolate']);
Route::post('soap/individual-ini', [SOAPController::class, 'getIndividualIni']);
Route::post('soap/shapes', [SOAPController::class, 'getShapesList']);
Route::post('soap/lens-filter', [SOAPController::class, 'getLensFilter']);
Route::post('soap/sales-frames-supplier-list', [SOAPController::class, 'getSalesFramesSupplierList']);
Route::post('soap/sales-frames-list', [SOAPController::class, 'getSalesFramesList']);
Route::post('soap/sales-shapes-list', [SOAPController::class, 'getSalesShapesList']);
Route::post('soap/lens-types', [SOAPController::class, 'getLensTypes']);
Route::post('soap/lens-types-only', [SOAPController::class, 'getLensTypesOnly']);
Route::post('soap/frame-supplier', [SOAPController::class, 'getFramesSupplierList']);
Route::post('soap/ranges-combination', [SOAPController::class, 'getRangesCombination']);
Route::post('soap/overview-order-sheet', [SOAPController::class, 'getOverviewOrderSheet']);
Route::post('soap/orderbasket-order-sheet', [SOAPController::class, 'getOrderbasketOrderSheet']);
Route::post('soap/frame-pic', [SOAPController::class, 'getFramePic']);
Route::post('soap/frame-by-id', [SOAPController::class, 'getFrameByID']);
Route::post('soap/lens-geo', [SOAPController::class, 'getLensGeo']);
Route::post('soap/convert-drill-holes', [SOAPController::class, 'requestConvertDrillHoles']);
Route::post('soap/transform-drill-holes', [SOAPController::class, 'requestTransformDrillHoles']);
Route::post('soap/scale-shape', [SOAPController::class, 'scaleShape']);
Route::post('soap/calculation', [SOAPController::class, 'getCalculation']);
Route::post('soap/decrypt-trace-data', [SOAPController::class, 'decryptTracedata']);
Route::post('soap/remark-key', [SOAPController::class, 'getRemarkKey']);
Route::post('soap/stock-lenses-by-prod-list', [SOAPController::class, 'getStockLensesByProdList']);
Route::post('soap/stock-lenses-whole-sale', [SOAPController::class, 'getStockLensesWholeSale']);
Route::post('soap/customer-list-whole-sale', [SOAPController::class, 'getCustomerListWholeSale']);
Route::post('soap/prod-lists-whole-sale', [SOAPController::class, 'getProdListsWholeSale']);
Route::post('soap/stock-items', [SOAPController::class, 'getStockItems']);
Route::post('soap/shop-list', [SOAPController::class, 'getShopList']);
Route::post('soap/shop-customer-details', [SOAPController::class, 'getCustomerDetails']);
Route::post('soap/create-customer', [SOAPController::class, 'createCustomer']);
Route::post('soap/change-customer-details', [SOAPController::class, 'changeCustomerDetails']);
Route::post('soap/delete-customer', [SOAPController::class, 'deleteCustomer']);

Route::post('orders/list', [OrderController::class, 'getOrders']);

Route::post('language/landing', [LanguageController::class, 'getLandingTranslations']);
Route::get('language/default', [LanguageController::class, 'getDefaultLanguage']);
Route::get('language/codes', [LanguageController::class, 'getLanguageCodes']);

Route::middleware('soap.authenticate')->group(function () {
    Route::post('tracer', [TracerController::class, 'index']);
    Route::post('tracer/save', [TracerController::class, 'store']);
    Route::post('tracer/delete', [TracerController::class, 'destroy']);
    Route::post('tracer/update', [TracerController::class, 'update']);
    Route::post('tracer/get-last-tracer-configuration', [TracerController::class, 'getLastTracerConfiguration']);
    Route::post('tracer/get-url', [TracerController::class, 'getUrl']);
    Route::post('tracer/tracer-definitions', [TracerController::class, 'getTracerDefinitions']);

    Route::post('language', [LanguageController::class, 'index']);

    Route::post('orders/orderbasket-list', [OrderController::class, 'getOrderbasketOrders']);
    Route::post('orders/translate-individual', [OrderController::class, 'translateIndividual']);
    Route::post('orders/send', [OrderController::class, 'sendOrders']);
    Route::post('orders/delete', [OrderController::class, 'deleteOrder']);
    Route::post('orders/save', [OrderController::class, 'saveOrder']);
    Route::post('orders/edit', [OrderController::class, 'editOrder']);
    Route::post('orders/save-bulkorder', [OrderController::class, 'saveBulkorder']);
    Route::post('orders/edit-bulkorder', [OrderController::class, 'editBulkorder']);
    Route::post('orders/send-bulkorder', [OrderController::class, 'sendBulkorder']);
    Route::post('orders/translate-bulkorder-item-list', [OrderController::class, 'translateBulkorderItemList']);
    Route::post('orders/check', [OrderController::class, 'checkOrderReceived']);

    Route::post('uploaded-shapes/', [UploadedShapeController::class, 'index']);
    Route::post('uploaded-shapes/save', [UploadedShapeController::class, 'store']);
    Route::post('uploaded-shapes/delete', [UploadedShapeController::class, 'destroy']);
    Route::post('uploaded-shapes/standard-shapes-images', [UploadedShapeController::class, 'getStandardShapesImages']);
    Route::post('uploaded-shapes/standard-shape-form', [UploadedShapeController::class, 'getStandardShapeForm']);

    Route::post('settings/get-ini-properties', [SettingsController::class, 'getIniProperties']);

    Route::post('uploaded-analyse-b2b', [UploadedAnalyseB2bController::class, 'save']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::get('users/user', [UserController::class, 'details']);
    Route::put('users/user', [UserController::class, 'updateOwn']);
});

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('admin/users', [UserController::class, 'index']);
    Route::post('admin/users', [UserController::class, 'store']);
    Route::get('admin/users/{user}', [UserController::class, 'show']);
    Route::put('admin/users/{user}', [UserController::class, 'update']);
    Route::delete('admin/users/{user}', [UserController::class, 'destroy']);
});

Route::get('contact-data', function (){
    return Storage::disk('custom_files')->get('contact.html');
});
Route::get('privacy-data', function (){
    return Storage::disk('custom_files')->get('privacy.html');
});
Route::get('imprint-data', function (){
    return Storage::disk('custom_files')->get('imprint.html');
});
