<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignaturePadController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return 'All caches cleared!';
});

Route::get('/clear-all', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Cleared all caches!";
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('csrf-token', function (Request $request) {
    $request->session()->regenerateToken();

    return response()->json(['token' => csrf_token()])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
})->name('csrf-token');

Route::get('concentform/{id?}/{iConcernFormId?}/{PatientsConcernFormId?}', 'App\Http\Controllers\Api\PrescriptionController@concentform');
Route::post('upload', 'App\Http\Controllers\Api\PrescriptionController@upload')->name('patient.upload');
Route::get('{patientName}/{guid}', 'App\Http\Controllers\Api\CghsPatientInvoiceController@viewCghsPatientInvoicePdfOnWeb')
    ->where('guid', '[0-9a-fA-F-]{36}');

Route::get('signaturepad', [SignaturePadController::class, 'index']);
Route::post('signaturepad', [SignaturePadController::class, 'upload'])->name('signaturepad.upload');