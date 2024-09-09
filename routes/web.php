<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OdooTestController;

 

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-odoo', [OdooTestController::class, 'testConnection']);
Route::post('/createCustomer', [OdooTestController::class, 'createCustomer']);
Route::post('/createInvoice', [OdooTestController::class, 'createInvoice']);